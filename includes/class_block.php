<?php
/**
 * Gestión del bloque de Gutenberg
 *
 * Maneja el registro y renderizado del bloque de chat para Gutenberg
 *
 * @package BravesChat
 * @since 1.0.0
 * @version 1.0.0
 */

namespace BravesChat;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Block
 *
 * Gestiona el bloque de Gutenberg del chat
 *
 * @since 1.0.0
 */
class Block {

    /**
     * Instancia única (patrón Singleton)
     *
     * @since 1.0.0
     * @var Block|null
     */
    private static $instance = null;

    /**
     * Obtener instancia única
     *
     * @since 1.0.0
     * @return Block Instancia única de la clase
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado (patrón Singleton)
     *
     * @since 1.0.0
     */
    private function __construct() {
        add_action('init', array($this, 'register_block'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    }

    /**
     * Registrar bloque de Gutenberg
     *
     * Registra el bloque 'wland/chat-widget' con sus atributos y callback
     *
     * @since 1.0.0
     * @return void
     */
    public function register_block() {
        // Asegurar que tenemos los archivos necesarios
        $this->ensure_block_files();
        
        register_block_type('braves/chat-widget', array(
            'editor_script' => 'braves-chat-block-editor',
            'editor_style' => 'braves-chat-block-editor-style',
            'style' => 'braves-chat-block-style',
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                // Solo el mensaje de bienvenida es configurable por bloque.
                // El resto de opciones (webhook, título, colores, posición)
                // se gestionan desde el panel global del plugin.
                'welcomeMessage' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
        ));
    }
    
    /**
     * Encolar assets del editor de bloques
     *
     * Carga scripts y estilos necesarios para el editor de Gutenberg
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'braves-chat-block-editor',
            BRAVES_CHAT_PLUGIN_URL . 'assets/js/block.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            BRAVES_CHAT_VERSION,
            true
        );
        
        wp_enqueue_style(
            'braves-chat-block-editor-style',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/block_editor.css',
            array(),
            BRAVES_CHAT_VERSION
        );

        wp_enqueue_style(
            'braves-chat-block-style',
            BRAVES_CHAT_PLUGIN_URL . 'assets/css/block_style.css',
            array(),
            BRAVES_CHAT_VERSION
        );
        
        // Localizar datos para el bloque
        wp_localize_script('braves-chat-block-editor', 'bravesChatBlock', array(
            'defaultWelcomeMessage' => Helpers::get_welcome_message(),
        ));
    }
    
    /**
     * Renderizar bloque en el frontend
     *
     * Callback para renderizar el HTML del bloque en la página
     *
     * @since 1.0.0
     * @param array $attributes Atributos del bloque
     * @return string HTML del bloque
     */
    public function render_block($attributes) {
        // Verificar si debe mostrarse el chat
        if (!Helpers::should_display_chat()) {
            return '';
        }
        
        // Usar configuración global del plugin. Si el bloque tiene mensaje de
        // bienvenida propio, lo sobreescribe; el resto siempre viene del panel.
        $global = Helpers::sanitize_block_attributes(array());

        $webhook_url    = $global['webhookUrl'];
        $header_title   = $global['headerTitle'];
        $header_subtitle = $global['headerSubtitle'];
        $position       = $global['position'];
        $chat_skin      = $global['chatSkin'];
        $bubble_image   = $global['bubbleImage'];
        $bubble_text    = $global['bubbleText'];
        $avatar_url     = $bubble_image; // Imagen del avatar en los mensajes del bot

        // Mensaje de bienvenida: del bloque si está definido, si no el global
        $block_message = isset($attributes['welcomeMessage']) ? trim($attributes['welcomeMessage']) : '';
        $welcome_message = $block_message !== '' ? sanitize_textarea_field($block_message) : $global['welcomeMessage'];

        // El bloque siempre renderiza en modo pantalla completa
        $display_mode = 'fullscreen';

        // Generar ID único
        $unique_id = Helpers::generate_unique_id();

        ob_start();
        include BRAVES_CHAT_PLUGIN_DIR . 'templates/screen.php';
        return ob_get_clean();
    }
    
    /**
     * Asegurar que existen los archivos del bloque
     *
     * Crea archivos JS y CSS del bloque si no existen
     *
     * @since 1.0.0
     * @return void
     */
    private function ensure_block_files() {
        $js_dir = BRAVES_CHAT_PLUGIN_DIR . 'assets/js/';
        $css_dir = BRAVES_CHAT_PLUGIN_DIR . 'assets/css/';
        
        // Crear directorios si no existen
        if (!file_exists($js_dir)) {
            wp_mkdir_p($js_dir);
        }
        
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }
        
        // Crear block.js si no existe
        $block_js_file = $js_dir . 'block.js';
        if (!file_exists($block_js_file)) {
            $this->create_block_js($block_js_file);
        }
        
        // Crear block_editor.css si no existe
        $block_editor_css = $css_dir . 'block_editor.css';
        if (!file_exists($block_editor_css)) {
            $this->create_block_editor_css($block_editor_css);
        }

        // Crear block_style.css si no existe
        $block_style_css = $css_dir . 'block_style.css';
        if (!file_exists($block_style_css)) {
            $this->create_block_style_css($block_style_css);
        }
    }
    
    /**
     * Crear archivo block.js (fallback si no existe en disco)
     * Nota: el archivo real es assets/js/block.js y tiene precedencia.
     */
    private function create_block_js($file) {
        // Copiar contenido del archivo real si existe
        $real_file = BRAVES_CHAT_PLUGIN_DIR . 'assets/js/block.js';
        if (file_exists($real_file)) {
            copy($real_file, $file);
        }
    }

    /**
     * Crear archivo block-editor.css (fallback si no existe en disco)
     */
    private function create_block_editor_css($file) {
        $real_file = BRAVES_CHAT_PLUGIN_DIR . 'assets/css/block_editor.css';
        if (file_exists($real_file)) {
            copy($real_file, $file);
        }
    }
    
    /**
     * Crear archivo block-style.css
     */
    private function create_block_style_css($file) {
        $content = "/* Estilos del bloque en el frontend */
.wp-block-braves-chat-widget {
    position: relative;
}

.braveslab-chat-widget-container {
    position: relative;
    width: 100%;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}";
        
        file_put_contents($file, $content);
    }
}