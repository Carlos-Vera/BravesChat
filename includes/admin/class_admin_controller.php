<?php
/**
 * Controlador principal del panel de administración
 *
 * Clase base que coordina todos los componentes del admin
 * Diseño Bentō moderno para panel de administración
 *
 * @package BravesChat
 * @version 1.2.0
 */

namespace BravesChat\Admin;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Admin_Controller {

    /**
     * Instancia única (Singleton)
     *
     * @var Admin_Controller
     */
    private static $instance = null;

    /**
     * Componentes del admin
     *
     * @var array
     */
    private $components = array();

    /**
     * Obtener instancia única
     *
     * @return Admin_Controller
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
        $this->init_components();
    }

    /**
     * Cargar archivos de dependencias
     *
     * @return void
     */
    private function load_dependencies() {
        // Componentes principales
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/components/class_admin_header.php';
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/components/class_admin_sidebar.php';
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/components/class_admin_content.php';

        // Templates helper
        require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/class_template_helpers.php';
    }

    /**
     * Inicializar hooks de WordPress
     *
     * @return void
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_head', array($this, 'add_menu_icon_active_styles'));
        add_filter('admin_title', array($this, 'filter_admin_title'), 10, 2);
        add_action('current_screen', array($this, 'suppress_other_notices'));
        add_filter('admin_body_class', array($this, 'add_admin_body_class'));
    }

    /**
     * Agregar estilos y script para icono de menú activo en páginas sin parent_slug
     *
     * @return void
     */
    public function add_menu_icon_active_styles() {
        $screen = get_current_screen();

        if (!$screen || strpos($screen->id, 'braveschat') === false) {
            return;
        }

        // Ocultar los ítems del submenú nativo de WordPress.
        // La navegación se gestiona con el sidebar personalizado del plugin.
        // Los submenús se registran con parent_slug = 'braveschat' para que WordPress
        // active el resaltado del menú principal de forma nativa.
        ?>
        <style>
            #toplevel_page_braveschat .wp-submenu {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Inicializar componentes
     *
     * @return void
     */
    private function init_components() {
        // Asegurar que las clases estén cargadas si el autoloader falla
        if (!class_exists('\\BravesChat\\Admin\\Admin_Header')) {
            require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/components/class_admin_header.php';
        }
        if (!class_exists('\\BravesChat\\Admin\\Admin_Sidebar')) {
            require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/components/class_admin_sidebar.php';
        }
        if (!class_exists('\\BravesChat\\Admin\\Admin_Content')) {
            require_once BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/components/class_admin_content.php';
        }

        $this->components['header'] = \BravesChat\Admin\Admin_Header::get_instance();
        $this->components['sidebar'] = \BravesChat\Admin\Admin_Sidebar::get_instance();
        $this->components['content'] = \BravesChat\Admin\Admin_Content::get_instance();
    }

    /**
     * Registrar menú de administración
     *
     * @return void
     */
    public function register_admin_menu() {
        // Obtener icono SVG
        $icon_svg = $this->get_menu_icon_svg();

        // Menú principal
        add_menu_page(
            __('BravesChat iA', 'braveschat'),
            __('BravesChat iA', 'braveschat'),
            'manage_options',
            'braveschat',
            array($this, 'render_dashboard_page'),
            $icon_svg,
            58
        );

        // Ocultar el submenu por defecto de WordPress
        // Solo se muestra "Braves Chat" en el menu principal
        // La navegación se hace por el sidebar personalizado

        // Registrar páginas sin mostrarlas en el submenu
        /*
        add_submenu_page(
            null, // parent_slug = null = página oculta en menu
            __('Resumen', 'braveschat'),
            __('Resumen', 'braveschat'),
            'manage_options',
            'braveschat',
            array($this, 'render_dashboard_page')
        );
        */

        add_submenu_page(
            'braveschat',
            __('Ajustes', 'braveschat'),
            __('Ajustes', 'braveschat'),
            'manage_options',
            'braveschat-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'braveschat',
            __('Apariencia', 'braveschat'),
            __('Apariencia', 'braveschat'),
            'manage_options',
            'braveschat-appearance',
            array($this, 'render_appearance_page')
        );

        add_submenu_page(
            'braveschat',
            __('Horarios', 'braveschat'),
            __('Horarios', 'braveschat'),
            'manage_options',
            'braveschat-availability',
            array($this, 'render_availability_page')
        );

        add_submenu_page(
            'braveschat',
            __('GDPR', 'braveschat'),
            __('GDPR', 'braveschat'),
            'manage_options',
            'braveschat-gdpr',
            array($this, 'render_gdpr_page')
        );

        add_submenu_page(
            'braveschat',
            __('Acerca de', 'braveschat'),
            __('Acerca de', 'braveschat'),
            'manage_options',
            'braveschat-about',
            array($this, 'render_about_page')
        );

        add_submenu_page(
            'braveschat',
            __('Historial', 'braveschat'),
            __('Historial', 'braveschat'),
            'manage_options',
            'braveschat-history',
            array($this, 'render_history_page')
        );
    }

    /**
     * Añadir clase CSS personalizada al body en páginas del plugin
     *
     * @param string $classes Clases actuales del body
     * @return string
     */
    public function add_admin_body_class($classes) {
        $screen = get_current_screen();
        if (!$screen) {
            return $classes;
        }
        if ($screen->id === 'toplevel_page_braveschat'
            || strpos($screen->id, '_page_braveschat-') !== false
        ) {
            $classes .= ' braves-chat-admin-page';
        }
        return $classes;
    }

    /**
     * Obtener icono SVG del menú
     *
     * @return string Data URI del SVG o dashicon
     */
    private function get_menu_icon_svg() {
        $svg_path = BRAVES_CHAT_PLUGIN_DIR . 'assets/media/menu_icon_solid.svg';

        if (file_exists($svg_path)) {
            $svg_content = file_get_contents($svg_path);
            return 'data:image/svg+xml;base64,' . base64_encode($svg_content);
        }

        // Fallback a dashicon
        return 'dashicons-format-chat';
    }

    /**
     * Renderizar página de Dashboard
     *
     * @return void
     */
    public function render_dashboard_page() {
        $current_page = 'braveschat';

        // Template del dashboard
        include BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/templates/dashboard.php';
    }

    /**
     * Renderizar página de Settings (Configuración General)
     *
     * @return void
     */
    public function render_settings_page() {
        $current_page = 'braveschat-settings';
        include BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/templates/settings.php';
    }

    /**
     * Renderizar página de Apariencia
     *
     * @return void
     */
    public function render_appearance_page() {
        $current_page = 'braveschat-appearance';
        include BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/templates/appearance.php';
    }

    /**
     * Renderizar página de Horarios
     *
     * @return void
     */
    public function render_availability_page() {
        $current_page = 'braveschat-availability';
        include BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/templates/availability.php';
    }

    /**
     * Renderizar página de GDPR
     *
     * @return void
     */
    public function render_gdpr_page() {
        $current_page = 'braveschat-gdpr';
        include BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/templates/gdpr.php';
    }

    /**
     * Renderizar página de Acerca de
     *
     * @return void
     */
    public function render_about_page() {
        $current_page = 'braveschat-about';
        include BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/templates/about.php';
    }

    /**
     * Renderizar página de Historial
     *
     * @return void
     */
    public function render_history_page() {
        $current_page = 'braveschat-history';
        include BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/templates/history.php';
    }

    /**
     * Encolar assets del admin
     *
     * @param string $hook Página actual del admin
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        // Solo cargar en páginas de Braves Chat
        if (!$this->is_braves_admin_page($hook)) {
            return;
        }

        // WordPress Components
        wp_enqueue_style('wp-components');

        // Estilos del admin
        wp_enqueue_style(
            'braves-admin-variables',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/admin/variables.css',
            array(),
            BRAVES_CHAT_VERSION
        );

        wp_enqueue_style(
            'braves-admin-base',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/admin/base.css',
            array('braves-admin-variables'),
            BRAVES_CHAT_VERSION
        );

        // Fuente Montserrat (Local) - Misma que frontend
        wp_enqueue_style(
            'braves-chat-fonts',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/braves_fonts.css',
            array(),
            BRAVES_CHAT_VERSION
        );

        wp_enqueue_style(
            'braves-admin-components',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/admin/components.css',
            array('braves-admin-base'),
            BRAVES_CHAT_VERSION
        );

        wp_enqueue_style(
            'braves-admin-dashboard',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/admin/dashboard.css',
            array('braves-admin-components'),
            BRAVES_CHAT_VERSION
        );

        wp_enqueue_style(
            'braves-admin-settings',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/admin/settings.css',
            array('braves-admin-dashboard'),
            BRAVES_CHAT_VERSION
        );

        // JavaScript del admin settings (para auto-hide de notificaciones)
        wp_enqueue_script(
            'braves-admin-settings',
            BRAVES_CHAT_PLUGIN_URL . 'assets/js/admin_settings.js',
            array(),
            BRAVES_CHAT_VERSION,
            true
        );

        // Script de selector de iconos y color picker (solo en página de Apariencia)
        if (strpos($hook, '_page_braveschat-appearance') !== false) {
            wp_enqueue_script(
                'braves-icon-selector',
                BRAVES_CHAT_PLUGIN_URL . 'assets/js/icon_selector.js',
                array(),
                BRAVES_CHAT_VERSION,
                true
            );

            wp_enqueue_script(
                'braves-color-picker',
                BRAVES_CHAT_PLUGIN_URL . 'assets/js/color_picker.js',
                array(),
                BRAVES_CHAT_VERSION,
                true
            );
        }

        // JavaScript del admin (si existe)
        if (file_exists(BRAVES_CHAT_PLUGIN_DIR . 'assets/js/admin/dashboard.js')) {
            wp_enqueue_script(
                'braves-admin-dashboard',
                BRAVES_CHAT_PLUGIN_URL . 'assets/js/admin/dashboard.js',
                array('jquery'),
                BRAVES_CHAT_VERSION,
                true
            );
        }

        // Localizar datos
        $this->localize_admin_data();
    }

    /**
     * Verificar si estamos en una página del admin de Braves
     *
     * @param string $hook Página actual
     * @return bool
     */
    /**
     * Suprimir notices de otros plugins en páginas de BravesChat
     *
     * @param WP_Screen $screen
     * @return void
     */
    public function suppress_other_notices($screen) {
        if ($screen->id === 'toplevel_page_braveschat'
            || strpos($screen->id, '_page_braveschat-') !== false
        ) {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }

    private function is_braves_admin_page($hook) {
        return $hook === 'toplevel_page_braveschat'
            || strpos($hook, '_page_braveschat-') !== false;
    }

    /**
     * Pasar datos a JavaScript
     *
     * @return void
     */
    private function localize_admin_data() {
        $config = array(
            'siteUrl' => esc_url(home_url()),
            'adminUrl' => esc_url(admin_url()),
            'settingsUrl' => esc_url(admin_url('admin.php?page=braveschat-settings')),
            'dashboardUrl' => esc_url(admin_url('admin.php?page=braveschat')),
            'customizeUrl' => esc_url(admin_url('customize.php')),
            'pluginVersion' => BRAVES_CHAT_VERSION,
            'isConfigured' => !empty(get_option('braves_chat_webhook_url')),
            'nonce' => wp_create_nonce('braves_chat_admin'),
        );

        wp_localize_script('wp-components', 'bravesAdminConfig', $config);
    }

    /**
     * Obtener componente específico
     *
     * @param string $name Nombre del componente
     * @return object|null
     */
    public function get_component($name) {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }

    /**
     * Filtrar el título de admin para agregar títulos dinámicos por sección
     *
     * @param string $admin_title Título actual del admin
     * @param string $title Título de la página
     * @return string Título filtrado
     */
    public function filter_admin_title($admin_title, $title) {
        $screen = get_current_screen();

        // Solo aplicar en páginas de BravesChat
        if (!$screen || strpos($screen->id, 'braveschat') === false) {
            return $admin_title;
        }

        // Mapeo de páginas a títulos
        $page_titles = array(
            'toplevel_page_braveschat' => __('Resumen', 'braveschat'),
            'braveschat_page_braveschat-settings' => __('Ajustes', 'braveschat'),
            'braveschat_page_braveschat-appearance' => __('Apariencia', 'braveschat'),
            'braveschat_page_braveschat-availability' => __('Horarios', 'braveschat'),
            'braveschat_page_braveschat-gdpr' => __('GDPR', 'braveschat'),
            'braveschat_page_braveschat-about' => __('Acerca de', 'braveschat'),
            'braveschat_page_braveschat-history' => __('Historial', 'braveschat'),
        );

        // Obtener título de la sección actual
        $section_title = isset($page_titles[$screen->id]) ? $page_titles[$screen->id] : '';

        if ($section_title) {
            // Obtener el nombre del sitio
            $site_name = get_bloginfo('name', 'display');

            // Formato: "BravesChat | Sección | Nombre del Sitio"
            return sprintf('BravesChat | %s | %s', $section_title, $site_name);
        }

        // Limpiar cualquier carácter HTML o símbolo no deseado del inicio
        $admin_title = preg_replace('/^[<>\s]+/', '', $admin_title);

        return $admin_title;
    }
}

// Inicializar el controlador
Admin_Controller::get_instance();
