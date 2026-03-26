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
        add_action('admin_enqueue_scripts', array($this, 'add_menu_icon_active_styles'), 20);
        add_action('admin_head', array($this, 'add_theme_restoration_script'));
        add_filter('admin_title', array($this, 'filter_admin_title'), 10, 2);
        add_action('current_screen', array($this, 'suppress_other_notices'));
        add_filter('admin_body_class', array($this, 'add_admin_body_class'));
        add_action('wp_ajax_braveschat_save_theme', array($this, 'ajax_save_theme'));
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
        wp_add_inline_style( 'braves-admin-base', '#toplevel_page_braveschat .wp-submenu { display: none !important; }' );
    }

    /**
     * Inyectar script de restauración de tema en el <head> para evitar FOUC
     *
     * @return void
     */
    public function add_theme_restoration_script() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'braveschat' ) === false ) {
            return;
        }
        $theme = get_user_meta( get_current_user_id(), 'braveschat_admin_theme', true );
        if ( 'dark' === $theme ) {
            echo '<script>document.documentElement.setAttribute("data-braves-theme","dark");</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static string, no user input
        }
    }

    /**
     * AJAX: guardar preferencia de tema en user_meta
     *
     * @return void
     */
    public function ajax_save_theme() {
        check_ajax_referer( 'braveschat_save_theme', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( -1, '', array( 'response' => 403 ) );
        }

        $theme   = isset( $_POST['theme'] ) ? sanitize_text_field( wp_unslash( $_POST['theme'] ) ) : '';
        $user_id = get_current_user_id();

        if ( 'dark' === $theme ) {
            update_user_meta( $user_id, 'braveschat_admin_theme', 'dark' );
        } else {
            delete_user_meta( $user_id, 'braveschat_admin_theme' );
        }

        wp_send_json_success();
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
            $svg_content = file_get_contents($svg_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin asset
            // Eliminar XML declaration y DOCTYPE — inválidos en data URI
            $svg_start = strpos( $svg_content, '<svg' );
            if ( false !== $svg_start ) {
                $svg_content = substr( $svg_content, $svg_start );
            }
            // width/height en % no se resuelven en background-image sin contexto padre
            $svg_content = preg_replace( '/(<svg[^>]*)\bwidth="100%"/', '$1width="20"', $svg_content );
            $svg_content = preg_replace( '/(<svg[^>]*)\bheight="100%"/', '$1height="20"', $svg_content );
            return 'data:image/svg+xml;base64,' . base64_encode( $svg_content );
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

        // History page script (previously inline in history.php template)
        if ( strpos( $hook, 'braveschat-history' ) !== false ) {
            $history_data = array(
                'agentName' => get_option( 'braves_chat_agent_name', '' ) ?: __( 'Agente', 'braveschat' ),
                'i18n'      => array(
                    'chatEmpty'  => __( 'Chat vacío o formato inválido.', 'braveschat' ),
                    'chatError'  => __( 'No se pudo visualizar el chat.', 'braveschat' ),
                    'chatNoData' => __( 'No hay datos de historial.', 'braveschat' ),
                ),
            );
            wp_add_inline_script(
                'braves-admin-settings',
                'var bravesHistoryData = ' . wp_json_encode( $history_data ) . ';',
                'before'
            );
            wp_add_inline_script(
                'braves-admin-settings',
                'var bravesAgentName = bravesHistoryData.agentName;

document.addEventListener(\'DOMContentLoaded\', function() {

    // --- CSV Export Logic ---
    var btn = document.getElementById(\'braves-history-export-csv\');
    if (btn) {
        btn.addEventListener(\'click\', function() {
            var tableRows = document.querySelectorAll(\'#braves-history-table tbody tr\');
            if (!tableRows || tableRows.length === 0) return;

            var rows = [];
            var today = new Date();
            var dateStr = today.getFullYear()
                + String(today.getMonth() + 1).padStart(2, \'0\')
                + String(today.getDate()).padStart(2, \'0\');

            rows.push([\'Session ID\', \'Client Name\', \'Updated At\', \'Chat History JSON\']);

            tableRows.forEach(function(tr) {
                rows.push([
                    tr.getAttribute(\'data-session-id\') || \'\',
                    tr.getAttribute(\'data-client-name\') || \'\',
                    tr.getAttribute(\'data-update-at\') || \'\',
                    tr.getAttribute(\'data-chat-history\') || \'\'
                ]);
            });

            var csv = rows.map(function(row) {
                return row.map(function(cell) {
                    return \'"\' + String(cell).replace(/"/g, \'""\'  ) + \'"\';
                }).join(\',\');
            }).join(\'\\r\\n\');

            var blob = new Blob([\'\\uFEFF\' + csv], { type: \'text/csv;charset=utf-8;\' });
            var url  = URL.createObjectURL(blob);
            var a    = document.createElement(\'a\');
            a.href     = url;
            a.download = \'braveschat_history_\' + dateStr + \'.csv\';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    }

    // --- Modal Logic ---
    var tableRows = document.querySelectorAll(\'.braves-history-table-row\');
    var modalOverlay = document.getElementById(\'braves-history-modal\');
    var modalClose = document.getElementById(\'braves-history-modal-close\');
    var modalTitle = document.getElementById(\'braves-modal-title\');
    var modalSubtitle = document.getElementById(\'braves-modal-subtitle\');
    var modalBody = document.getElementById(\'braves-history-modal-body\');

    if (modalOverlay && modalClose) {
        tableRows.forEach(function(row) {
            row.addEventListener(\'click\', function(e) {
                if (e.target.tagName.toLowerCase() === \'a\') return;

                var chatHistoryRaw = this.getAttribute(\'data-chat-history\');
                var sessionId      = this.getAttribute(\'data-session-id\') || \'N/A\';
                var clientName     = this.getAttribute(\'data-client-name\') || \'\';
                var updateAt       = this.getAttribute(\'data-update-at\') || \'\';

                modalTitle.textContent = clientName || \'Conversación Anónima\';

                var sessionIdShort = sessionId.length > 7 ? \'…\' + sessionId.slice(-7) : sessionId;
                var dateOnly = updateAt ? updateAt.split(\' \')[0] : \'\';
                var subtitleParts = [\'Session: \' + sessionIdShort];
                if (dateOnly) subtitleParts.push(dateOnly);
                modalSubtitle.textContent = subtitleParts.join(\' · \');

                modalBody.innerHTML = \'\';

                function cleanContent(text) {
                    text = text.replace(/^Mensaje del usuario:\\s*/i, \'\');
                    var nl = text.indexOf(\'\\n\');
                    if (nl !== -1) text = text.substring(0, nl);
                    return text.trim();
                }

                function parseMarkdown(text) {
                    text = text.replace(/&/g, \'&amp;\')
                               .replace(/</g, \'&lt;\')
                               .replace(/>/g, \'&gt;\');
                    text = text.replace(/\\*\\*(.+?)\\*\\*/g, \'<strong>$1</strong>\');
                    text = text.replace(/__(.+?)__/g, \'<strong>$1</strong>\');
                    text = text.replace(/\\*([^*\\n]+)\\*/g, \'<em>$1</em>\');
                    text = text.replace(/\\[([^\\]]+)\\]\\((https?:\\/\\/[^)]+)\\)/g, \'<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>\');
                    text = text.replace(/(^|[\\s>])(https?:\\/\\/[^\\s<"]+)/g, \'$1<a href="$2" target="_blank" rel="noopener noreferrer">$2</a>\');
                    text = text.replace(/\\n/g, \'<br>\');
                    return text;
                }

                if (chatHistoryRaw) {
                    try {
                        var chatHistory = JSON.parse(chatHistoryRaw);
                        if (Array.isArray(chatHistory) && chatHistory.length > 0) {
                            chatHistory.forEach(function(msg) {
                                var role = (msg.role || msg.type || \'\').trim().toLowerCase();
                                var isUser = (role === \'human\' || role === \'user\');

                                var wrapDiv = document.createElement(\'div\');
                                wrapDiv.className = \'braves-history-bubble-wrap\' + (isUser ? \' braves-history-bubble-wrap--user\' : \' braves-history-bubble-wrap--ai\');

                                var labelDiv = document.createElement(\'div\');
                                labelDiv.className = \'braves-history-chat-sender\';
                                labelDiv.textContent = isUser ? (clientName || \'Usuario\') : bravesAgentName;

                                var msgDiv = document.createElement(\'div\');
                                msgDiv.className = \'braves-history-chat-bubble \' + (isUser ? \'braves-history-chat-bubble--user\' : \'braves-history-chat-bubble--ai\');

                                var contentHtml = parseMarkdown(cleanContent(msg.content || \'\'));

                                var timeHtml = \'\';
                                if (msg._ts) {
                                    var tsDate = new Date(msg._ts);
                                    if (!isNaN(tsDate.getTime())) {
                                        var hh = String(tsDate.getHours()).padStart(2, \'0\');
                                        var mm = String(tsDate.getMinutes()).padStart(2, \'0\');
                                        timeHtml = \'<span class="braves-history-bubble-time">\' + hh + \':\' + mm + \'</span>\';
                                    }
                                }

                                msgDiv.innerHTML = \'<span class="braves-history-bubble-content">\' + contentHtml + \'</span>\' + timeHtml;

                                wrapDiv.appendChild(labelDiv);
                                wrapDiv.appendChild(msgDiv);
                                modalBody.appendChild(wrapDiv);
                            });
                        } else {
                            modalBody.innerHTML = \'<div class="braves-history-chat-empty">\' + bravesHistoryData.i18n.chatEmpty + \'</div>\';
                        }
                    } catch (err) {
                        console.error(\'BravesChat: Error parsing chat history JSON.\', err);
                        modalBody.innerHTML = \'<div class="braves-history-chat-empty">\' + bravesHistoryData.i18n.chatError + \'</div>\';
                    }
                } else {
                    modalBody.innerHTML = \'<div class="braves-history-chat-empty">\' + bravesHistoryData.i18n.chatNoData + \'</div>\';
                }

                modalOverlay.classList.add(\'braves-is-visible\');
                document.body.style.overflow = \'hidden\';
            });
        });

        var closeModal = function() {
            modalOverlay.classList.remove(\'braves-is-visible\');
            document.body.style.overflow = \'\';
        };

        modalClose.addEventListener(\'click\', closeModal);

        modalOverlay.addEventListener(\'click\', function(e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });

        document.addEventListener(\'keydown\', function(e) {
            if (e.key === \'Escape\' && modalOverlay.classList.contains(\'braves-is-visible\')) {
                closeModal();
            }
        });
    }

});'
            );
        }
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
            'ajaxUrl' => esc_url(admin_url('admin-ajax.php')),
            'themeNonce' => wp_create_nonce('braveschat_save_theme'),
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
