<?php
/**
 * Plugin Name: BravesChat
 * Plugin URI: https://github.com/Carlos-Vera/BravesChat
 * Description: Chat profesional con integración nativa a N8N, Diseñado para ofrecer una experiencia de usuario moderna y un backend ultra rápido.
 * Version: 2.1.5
 * Author: Carlos Vera
 * Author URI: https://braveslab.com
 * Text Domain: braves-chat
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: Commercial
 * License URI: LICENSE
 *
 * GitHub Plugin URI: Carlos-Vera/BravesChat
 * GitHub Branch: main
 */

namespace BravesChat;

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('BRAVES_CHAT_VERSION', '2.1.5');
define('BRAVES_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BRAVES_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BRAVES_CHAT_PLUGIN_FILE', __FILE__);
define('BRAVES_CHAT_TEXT_DOMAIN', 'braves-chat');

/**
 * Clase principal del plugin
 *
 * @package BravesChat
 * @since 1.0.0
 */
class BravesChat {

    /**
     * Instancia única del plugin (patrón Singleton)
     *
     * @since 1.0.0
     * @var BravesChat|null
     */
    private static $instance = null;

    /**
     * Obtener instancia única del plugin
     *
     * Implementa el patrón Singleton para garantizar una única instancia
     *
     * @since 1.0.0
     * @return BravesChat Instancia única del plugin
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
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/class_helpers.php';
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/class_cookie_manager.php';

        // Nuevo controlador de admin refactorizado
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/class_admin_controller.php';

        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/class_settings.php';
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/class_customizer.php';
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/class_block.php';
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/class_frontend.php';
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
        register_activation_hook(BRAVES_CHAT_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(BRAVES_CHAT_PLUGIN_FILE, array($this, 'deactivate'));

        // Cargar traducciones
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Inicializar componentes
        add_action('plugins_loaded', array($this, 'init_components'));

        // Agregar enlaces en la página de plugins
        add_filter('plugin_action_links_' . plugin_basename(BRAVES_CHAT_PLUGIN_FILE), array($this, 'add_action_links'));
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
        // Cargar valores por defecto desde archivo de configuración
        $config_file = BRAVES_CHAT_PLUGIN_DIR . 'config/defaults.php';

        if (file_exists($config_file)) {
            $defaults = include $config_file;
        } else {
            // Fallback: valores genéricos si no existe config/defaults.php
            $defaults = array(
                'webhook_url' => '',
                'header_title' => __('Chat de Soporte', 'braves-chat'),
                'header_subtitle' => __('Estamos aquí para ayudarte', 'braves-chat'),
                'welcome_message' => __('¡Hola! ¿En qué podemos ayudarte hoy?', 'braves-chat'),
                'position' => 'bottom-right',
                'excluded_pages' => array(),
                'availability_enabled' => false,
                'availability_start' => '09:00',
                'availability_end' => '18:00',
                'availability_timezone' => 'UTC',
                'availability_message' => __('Nuestro horario de atención es de 9:00 a 18:00. Déjanos tu mensaje y te responderemos lo antes posible.', 'braves-chat'),
                'display_mode' => 'modal',
                'gdpr_enabled' => false,
                'gdpr_message' => __('Este sitio utiliza cookies para mejorar tu experiencia. Al continuar navegando, aceptas nuestra política de cookies.', 'braves-chat'),
                'gdpr_accept_text' => __('Aceptar', 'braves-chat'),
            );
        }
        
        foreach ($defaults as $key => $value) {
            if (false === get_option('braves_chat_' . $key)) {
                // Try to migrate from old option if exists
                $old_val = get_option('wland_chat_' . $key);
                if ($old_val !== false) {
                    add_option('braves_chat_' . $key, $old_val);
                } else {
                    add_option('braves_chat_' . $key, $value);
                }
            }
        }
        
        // Crear directorios necesarios
        $upload_dir = wp_upload_dir();
        $braves_dir = $upload_dir['basedir'] . '/braves-chat';
        if (!file_exists($braves_dir)) {
            wp_mkdir_p($braves_dir);
        }
        
        // Guardar versión
        update_option('braves_chat_version', BRAVES_CHAT_VERSION);

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
        // Asegurar que deactivate_plugins está disponible
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Inicializar WP_Filesystem si no está disponible
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        // Intentar inicializar WP_Filesystem
        if (WP_Filesystem()) {
            global $wp_filesystem;
            
            // Verificar si $wp_filesystem es válido
            if (!$wp_filesystem) {
                return;
            }

            $plugins_dir = WP_PLUGIN_DIR;
            $current_dir = basename(BRAVES_CHAT_PLUGIN_DIR);
            $current_dir = rtrim($current_dir, '/');

            // Patrón para versiones antiguas: Wland-Chat-iA seguido de cualquier cosa
            $pattern = $plugins_dir . '/Wland-Chat-iA*';
            $dirs = glob($pattern, GLOB_ONLYDIR);

            if ($dirs) {
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
            'braves-chat',
            false,
            dirname(plugin_basename(BRAVES_CHAT_PLUGIN_FILE)) . '/languages'
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
        BravesCookieManager::get_instance();
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
            admin_url('admin.php?page=braves-chat'),
            __('Dashboard', 'braves-chat')
        );

        $config_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=braves-chat-settings'),
            __('Ajustes', 'braves-chat')
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
 * @return BravesChat Instancia del plugin
 */
if (!function_exists('BravesChat\braves_chat_init')) {
    function braves_chat_init() {
        return BravesChat::get_instance();
    }
}

// Iniciar el plugin
braves_chat_init();