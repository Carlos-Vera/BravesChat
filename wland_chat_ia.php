<?php
/**
 * Plugin Name: Wland Chat iA
 * Plugin URI: https://github.com/Carlos-Vera/Wland-Chat-iA
 * Description: Integración profesional de chat con IA mediante bloque Gutenberg, con horarios personalizables y páginas excluidas. Backend refactorizado con diseño Bentō moderno.
 * Version: 1.2.4
 * Author: Carlos Vera
 * Contributors: Mikel Marqués
 * Author URI: https://braveslab.com
 * Text Domain: wland-chat
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: Commercial
 * License URI: LICENSE
 *
 * GitHub Plugin URI: Carlos-Vera/Wland-Chat-iA
 * GitHub Branch: main
 */

namespace WlandChat;

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('WLAND_CHAT_VERSION', '1.2.4');
define('WLAND_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WLAND_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WLAND_CHAT_PLUGIN_FILE', __FILE__);
define('WLAND_CHAT_TEXT_DOMAIN', 'wland-chat');

/**
 * Clase principal del plugin
 *
 * @package WlandChat
 * @since 1.0.0
 */
class WlandChatIA {

    /**
     * Instancia única del plugin (patrón Singleton)
     *
     * @since 1.0.0
     * @var WlandChatIA|null
     */
    private static $instance = null;

    /**
     * Obtener instancia única del plugin
     *
     * Implementa el patrón Singleton para garantizar una única instancia
     *
     * @since 1.0.0
     * @return WlandChatIA Instancia única del plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado para patrón Singleton
     *
     * Carga dependencias e inicializa hooks de WordPress
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Cargar archivos de dependencias del plugin
     *
     * Incluye todas las clases necesarias para el funcionamiento del plugin
     *
     * @since 1.0.0
     * @return void
     */
    private function load_dependencies() {
        require_once WLAND_CHAT_PLUGIN_DIR . 'includes/class_helpers.php';
        require_once WLAND_CHAT_PLUGIN_DIR . 'includes/class_cookie_manager.php';

        // Nuevo controlador de admin refactorizado
        require_once WLAND_CHAT_PLUGIN_DIR . 'includes/admin/class_admin_controller.php';

        require_once WLAND_CHAT_PLUGIN_DIR . 'includes/class_settings.php';
        require_once WLAND_CHAT_PLUGIN_DIR . 'includes/class_customizer.php';
        require_once WLAND_CHAT_PLUGIN_DIR . 'includes/class_block.php';
        require_once WLAND_CHAT_PLUGIN_DIR . 'includes/class_frontend.php';
    }

    /**
     * Inicializar hooks de WordPress
     *
     * Registra activación, desactivación, traducciones y enlaces de plugin
     *
     * @since 1.0.0
     * @return void
     */
    private function init_hooks() {
        // Activación y desactivación
        register_activation_hook(WLAND_CHAT_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WLAND_CHAT_PLUGIN_FILE, array($this, 'deactivate'));

        // Cargar traducciones
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Inicializar componentes
        add_action('plugins_loaded', array($this, 'init_components'));

        // Agregar enlaces en la página de plugins
        add_filter('plugin_action_links_' . plugin_basename(WLAND_CHAT_PLUGIN_FILE), array($this, 'add_action_links'));
    }

    /**
     * Activación del plugin
     *
     * Crea opciones por defecto, directorios necesarios y guarda versión
     *
     * @since 1.0.0
     * @return void
     */
    public function activate() {
        // Establecer opciones por defecto
        $defaults = array(
            'webhook_url' => 'https://flow.braveslab.com/webhook/1427244e-a23c-4184-a536-d02622f36325/chat',
            'header_title' => __('BravesLab AI Assistant', 'wland-chat'),
            'header_subtitle' => __('Artificial Intelligence Marketing Agency', 'wland-chat'),
            'welcome_message' => __('¡Hola! Soy el asistente de BravesLab, tu Artificial Intelligence Marketing Agency. Integramos IA en empresas para multiplicar resultados. ¿Cómo podemos ayudarte?', 'wland-chat'),
            'position' => 'bottom-right',
            'excluded_pages' => array(),
            'availability_enabled' => false,
            'availability_start' => '09:00',
            'availability_end' => '18:00',
            'availability_timezone' => 'Europe/Madrid',
            'availability_message' => __('Nuestro horario de atención es de 9:00 a 18:00. Déjanos tu mensaje y te responderemos lo antes posible.', 'wland-chat'),
            'display_mode' => 'modal',
            'gdpr_enabled' => false,
            'gdpr_message' => __('Este sitio utiliza cookies para mejorar tu experiencia y proporcionar un servicio de chat personalizado. Al continuar navegando, aceptas nuestra política de cookies.', 'wland-chat'),
            'gdpr_accept_text' => __('Aceptar', 'wland-chat'),
        );
        
        foreach ($defaults as $key => $value) {
            if (false === get_option('wland_chat_' . $key)) {
                add_option('wland_chat_' . $key, $value);
            }
        }
        
        // Crear directorios necesarios
        $upload_dir = wp_upload_dir();
        $wland_dir = $upload_dir['basedir'] . '/wland-chat';
        if (!file_exists($wland_dir)) {
            wp_mkdir_p($wland_dir);
        }
        
        // Guardar versión
        update_option('wland_chat_version', WLAND_CHAT_VERSION);

        // Detectar y reemplazar versiones antiguas
        $this->detect_and_replace_old_versions();

        flush_rewrite_rules();
    }
    
    /**
     * Desactivación del plugin
     *
     * Limpia reglas de reescritura al desactivar
     *
     * @since 1.0.0
     * @return void
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Detectar y reemplazar versiones antiguas del plugin
     *
     * Escanea el directorio de plugins en busca de versiones anteriores,
     * las desactiva si están activas y elimina los directorios
     *
     * @since 1.2.4
     * @return void
     */
    private function detect_and_replace_old_versions() {
        // Inicializar WP_Filesystem si no está disponible
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        global $wp_filesystem;

        $plugins_dir = WP_PLUGIN_DIR;
        $current_dir = basename(WLAND_CHAT_PLUGIN_DIR);
        $current_dir = rtrim($current_dir, '/');

        // Patrón para versiones antiguas: Wland-Chat-iA seguido de cualquier cosa
        $pattern = $plugins_dir . '/Wland-Chat-iA*';
        $dirs = glob($pattern, GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $dirname = basename($dir);
            if ($dirname !== $current_dir) {
                // Versión antigua encontrada
                $plugin_file = $dirname . '/wland_chat_ia.php';

                // Desactivar si está activo
                if (is_plugin_active($plugin_file)) {
                    deactivate_plugins($plugin_file);
                }

                // Eliminar el directorio
                $wp_filesystem->delete($dir, true);
            }
        }
    }

    /**
     * Cargar archivos de traducción del plugin
     *
     * Carga los archivos .mo/.po del directorio /languages
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wland-chat',
            false,
            dirname(plugin_basename(WLAND_CHAT_PLUGIN_FILE)) . '/languages'
        );
    }

    /**
     * Inicializar componentes del plugin
     *
     * Instancia todas las clases principales usando patrón Singleton
     *
     * @since 1.0.0
     * @return void
     */
    public function init_components() {
        WlandCookieManager::get_instance();
        Settings::get_instance();
        Customizer::get_instance();
        Block::get_instance();
        Frontend::get_instance();
    }

    /**
     * Agregar enlaces de acción en la página de plugins
     *
     * Añade enlaces rápidos a Dashboard y Ajustes en la lista de plugins
     *
     * @since 1.0.0
     * @param array $links Array de enlaces existentes del plugin
     * @return array Array modificado con enlaces adicionales
     */
    public function add_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=wland-chat-ia'),
            __('Dashboard', 'wland-chat')
        );

        $config_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=wland-chat-settings'),
            __('Ajustes', 'wland-chat')
        );

        array_unshift($links, $settings_link, $config_link);

        return $links;
    }
}

/**
 * Función de inicialización del plugin
 *
 * Punto de entrada principal que obtiene la instancia única del plugin
 *
 * @since 1.0.0
 * @return WlandChatIA Instancia del plugin
 */
if (!function_exists('WlandChat\wland_chat_init')) {
    function wland_chat_init() {
        return WlandChatIA::get_instance();
    }
}

// Iniciar el plugin
wland_chat_init();