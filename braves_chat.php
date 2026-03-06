<?php
/**
 * Plugin Name: BravesChat
 * Plugin URI: https://github.com/Carlos-Vera/BravesChat
 * Description: Una herramienta profesional que conecta tu sitio con tu agente de N8N, permitiéndote ofrecer atención con iA directamente en tu web.
 * Version: 2.2.3
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
define('BRAVES_CHAT_VERSION', '2.2.3');
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
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/class_protection.php';
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

        // Información del plugin para el popup "Ver detalles" en la lista de plugins
        if (is_admin()) {
            add_filter('plugins_api', array($this, 'plugin_api_info'), 10, 3);
            add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
        }
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
        Protection::get_instance();
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

    /**
     * Añade el enlace "Ver detalles" en la fila del plugin en la lista de plugins
     *
     * @since 2.2.3
     * @param array  $links Array de meta-enlaces de la fila del plugin.
     * @param string $file  Ruta relativa del archivo principal del plugin.
     * @return array Array modificado con el enlace "Ver detalles".
     */
    public function add_plugin_row_meta($links, $file) {
        if (plugin_basename(BRAVES_CHAT_PLUGIN_FILE) !== $file) {
            return $links;
        }

        $url = add_query_arg(
            array(
                'tab'       => 'plugin-information',
                'plugin'    => 'braveschat',
                'TB_iframe' => 'true',
                'width'     => '772',
                'height'    => '800',
            ),
            admin_url('plugin-install.php')
        );

        $details_link = sprintf(
            '<a href="%s" class="thickbox open-plugin-details-modal">%s</a>',
            esc_url($url),
            __('Ver detalles', 'braves-chat')
        );

        array_splice($links, 2, 0, array($details_link));

        return $links;
    }

    /**
     * Proporcionar información del plugin para el popup "Ver detalles"
     *
     * @since 2.2.3
     * @param false|object $result Resultado actual (false = no interceptado aún).
     * @param string       $action Acción solicitada ('plugin_information', etc.).
     * @param object       $args   Argumentos de la solicitud ($args->slug).
     * @return false|object Datos del plugin o $result sin modificar.
     */
    public function plugin_api_info($result, $action, $args) {
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== 'braveschat') {
            return $result;
        }

        $plugin = new \stdClass();

        $plugin->name            = 'BravesChat';
        $plugin->slug            = 'braveschat';
        $plugin->version         = BRAVES_CHAT_VERSION;
        $plugin->author          = '<a href="https://braveslab.com">Carlos Vera &ndash; BRAVES LAB LLC</a>';
        $plugin->author_profile  = 'https://braveslab.com';
        $plugin->homepage        = 'https://braveslab.com/plugins/braveschat';
        $plugin->requires        = '5.8';
        $plugin->tested          = '6.9.1';
        $plugin->requires_php    = '7.4';
        $plugin->compatibility   = array(
            'woocommerce' => array(
                'tested'  => '10.5.3',
                'requires' => '3.0',
            ),
        );
        $plugin->rating          = 100;
        $plugin->num_ratings     = 5;
        $plugin->active_installs = 5;
        $plugin->last_updated    = '2026-03-05';
        $plugin->added           = '2025-09-15';

        $plugin->banners = array(
            'low'  => BRAVES_CHAT_PLUGIN_URL . 'assets/media/banner-772x250.webp',
            'high' => BRAVES_CHAT_PLUGIN_URL . 'assets/media/banner-1544x500.webp',
        );

        $plugin->sections = array(
            'description' => '
<p><strong><a href="https://braveslab.com/plugins/braveschat" target="_blank" rel="noreferer noopener">BravesChat</a></strong> es el puente entre tu WordPress y tus flujos de <strong>N8N</strong>: conecta cualquier agente de IA que hayas construido con tus visitantes, sin código adicional y en minutos.</p>

<h3>Diseñado para la comunidad N8N</h3>
<ul>
    <li><strong>Webhook listo para N8N:</strong> Apunta BravesChat a la URL de tu workflow y empieza a recibir mensajes al instante. Soporta token de autenticación en cabecera (<code>X-N8N-Auth</code>) para proteger tus endpoints.</li>
    <li><strong>Payload completo en cada mensaje:</strong> Cada petición incluye el mensaje actual, el historial de la conversación, el fingerprint del usuario y los metadatos de la página — todo lo que tus nodos de N8N necesitan para personalizar la respuesta.</li>
    <li><strong>Respuestas con Markdown:</strong> Los mensajes de tu agente se renderizan con formato enriquecido — negritas, listas, enlaces y código — sin configuración extra.</li>
    <li><strong>Historial de conversaciones con exportación CSV:</strong> Consulta todas las sesiones desde el panel de WordPress y expórtalas a tu CRM, hoja de cálculo o base de datos con un clic.</li>
</ul>

<h3>Listo para producción</h3>
<ul>
    <li><strong>Horario de atención configurable:</strong> Define cuándo está activo el chat y muestra un mensaje personalizado fuera de ese horario — ideal si tu agente depende de un humano en el loop.</li>
    <li><strong>Cumplimiento GDPR integrado:</strong> Banner de consentimiento que bloquea el chat hasta la aceptación del usuario. Montserrat cargada localmente, sin peticiones externas.</li>
    <li><strong>Personalización total de marca:</strong> Colores, textos, posición, skin y modo de visualización (widget flotante o pantalla completa) adaptables sin tocar código.</li>
    <li><strong>Compatible con WooCommerce 10.5+:</strong> Funciona en tiendas WooCommerce sin conflictos, permitiendo asistencia conversacional durante todo el proceso de compra.</li>
</ul>',

            'installation' => '
<h3>Instalación manual</h3>
<ol>
    <li>Descarga el archivo <code>braveschat.zip</code> desde GitHub Releases.</li>
    <li>En tu panel de WordPress, ve a <strong>Plugins &rarr; Añadir nuevo &rarr; Subir plugin</strong>.</li>
    <li>Selecciona el archivo ZIP y haz clic en <strong>Instalar ahora</strong>.</li>
    <li>Activa el plugin desde la lista de plugins instalados.</li>
</ol>
<h3>Configuración inicial</h3>
<ol>
    <li>Ve a <strong>BravesChat iA &rarr; Ajustes</strong> en el menú lateral del admin.</li>
    <li>Introduce la URL de tu webhook de N8N.</li>
    <li>Personaliza título, subtítulo y mensaje de bienvenida.</li>
    <li>Ajusta colores y skin en la sección <strong>Apariencia</strong>.</li>
    <li>Configura GDPR y Horarios según tus necesidades.</li>
    <li>Guarda y prueba el chat desde el frontend.</li>
</ol>',

            'faq' => '
<h4>¿Necesito una cuenta en N8N para usar BravesChat?</h4>
<p>Sí. BravesChat actúa como el widget de chat en tu WordPress, pero la inteligencia y las respuestas las gestiona tu propio flujo de trabajo en N8N. Puedes usar N8N Cloud o tu instancia self-hosted.</p>

<h4>¿Funciona con cualquier agente de IA en N8N?</h4>
<p>Sí. BravesChat envía el mensaje y el historial de la conversación a la URL de webhook que configures. El agente puede estar conectado a OpenAI, Claude, Gemini, Ollama o cualquier modelo que tu flujo soporte — BravesChat no impone restricciones.</p>

<h4>¿Qué datos se envían al webhook en cada mensaje?</h4>
<p>Cada petición incluye: el mensaje del usuario, el historial completo de la conversación, el fingerprint único del usuario, la URL de la página actual y metadatos del sitio. Puedes usar cualquiera de estos datos en tus nodos de N8N para personalizar la respuesta.</p>

<h4>¿El historial se guarda en la base de datos de WordPress?</h4>
<p>No. El historial de la sesión se guarda en el <code>localStorage</code> del navegador del usuario. El historial completo de conversaciones que ves en el panel de administración se obtiene directamente desde tu fuente de datos en N8N (por ejemplo, PostgreSQL) a través de un webhook separado.</p>

<h4>¿Puedo ocultar el chat en ciertas páginas?</h4>
<p>Sí. En la sección Ajustes puedes especificar las páginas donde el widget no debe aparecer.</p>

<h4>¿Es compatible con WooCommerce?</h4>
<p>Sí, BravesChat es compatible con WooCommerce 10.5+ y no genera conflictos con el proceso de compra ni con los estilos de la tienda.</p>

<h4>¿El plugin cumple con el RGPD / GDPR?</h4>
<p>Sí. Puedes activar un banner de consentimiento que bloquea el chat hasta que el usuario acepta. El fingerprinting de usuario no recoge datos personales. La tipografía Montserrat se carga localmente, sin peticiones a Google Fonts.</p>

<h4>¿Puedo usar BravesChat sin N8N?</h4>
<p>Técnicamente sí: el webhook puede apuntar a cualquier endpoint HTTP que devuelva JSON con el campo <code>output</code>. Sin embargo, el plugin está optimizado y documentado para flujos de N8N.</p>',

            'changelog' => '
<h4>v2.2.3 &mdash; 5 de marzo, 2026</h4>
<ul>
    <li>ADDED: Modal "Ver detalles" en la lista de plugins con información completa del plugin.</li>
    <li>IMPROVED: Editor de texto enriquecido (TinyMCE) para mensajes GDPR y mensajes fuera de horario.</li>
</ul>

<h4>v2.2.2 &mdash; 4 de marzo, 2026</h4>
<ul><li>ADDED: Protección de licencia — detecta plugins de exportación ZIP instalados.</li></ul>

<h4>v2.2.1 &mdash; 26 de febrero, 2026</h4>
<ul><li>FIXED: Los avisos de otros plugins ya no aparecen dentro del panel de BravesChat.</li></ul>

<h4>v2.2.0 &mdash; 26 de febrero, 2026</h4>
<ul>
    <li>ADDED: Visor completo de historial de conversaciones con modal por sesión.</li>
    <li>ADDED: Exportación del historial a CSV con todos los campos relevantes.</li>
    <li>IMPROVED: Conversaciones ordenadas de más reciente a más antigua.</li>
</ul>

<h4>v2.1.2 &mdash; 20 de febrero, 2026</h4>
<ul><li>IMPROVED: Sistema de aislamiento CSS para prevenir conflictos con temas.</li></ul>

<h4>v2.1.1 &mdash; 16 de febrero, 2026</h4>
<ul><li>IMPROVED: Renderizado incremental de Markdown en tiempo real.</li></ul>

<h4>v2.1.0 &mdash; 16 de febrero, 2026</h4>
<ul>
    <li>ADDED: Slider de velocidad de escritura configurable.</li>
    <li>ADDED: Soporte HTML/Markdown en el mensaje del banner GDPR.</li>
    <li>ADDED: Montserrat cargada localmente (cumplimiento GDPR).</li>
</ul>

<h4>v2.0.0 &mdash; 14 de febrero, 2026</h4>
<ul>
    <li>MAJOR: Reestructuración completa del sistema con nuevo namespace BravesChat.</li>
    <li>ADDED: Botón de maximizar, auto-crecimiento del textarea, estado minimizado.</li>
</ul>',

            'screenshots' => '
<ol>
    <li>
        <img src="' . BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-1.webp" alt="Widget de chat en el frontend" />
        <p>Widget flotante en el frontend — skin Braves con avatar y header personalizado.</p>
    </li>
    <li>
        <img src="' . BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-2.webp" alt="Panel de administración" />
        <p>Panel de administración — vista Dashboard con acceso rápido a todas las secciones.</p>
    </li>
    <li>
        <img src="' . BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-3.webp" alt="Ajustes del plugin" />
        <p>Ajustes — configuración del webhook de N8N, textos y comportamiento del chat.</p>
    </li>
    <li>
        <img src="' . BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-4.webp" alt="Personalización de apariencia" />
        <p>Apariencia — personalización de colores, posición, skin e icono del widget.</p>
    </li>
    <li>
        <img src="' . BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-5.webp" alt="Historial de conversaciones" />
        <p>Historial de conversaciones — visor de sesiones con exportación a CSV.</p>
    </li>
</ol>',

        );

        $plugin->screenshots = array(
            array(
                'src'     => BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-1.webp',
                'caption' => 'Widget de chat flotante en el frontend — skin Braves con avatar y header personalizado.',
            ),
            array(
                'src'     => BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-2.webp',
                'caption' => 'Panel de administración — vista Dashboard con acceso rápido a todas las secciones.',
            ),
            array(
                'src'     => BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-3.webp',
                'caption' => 'Ajustes — configuración del webhook de N8N, textos y comportamiento del chat.',
            ),
            array(
                'src'     => BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-4.webp',
                'caption' => 'Apariencia — personalización de colores, posición, skin e icono del widget.',
            ),
            array(
                'src'     => BRAVES_CHAT_PLUGIN_URL . 'assets/media/screenshot-5.webp',
                'caption' => 'Historial de conversaciones — visor de sesiones con exportación a CSV.',
            ),
        );

        return $plugin;
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