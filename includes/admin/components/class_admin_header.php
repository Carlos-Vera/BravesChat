<?php
/**
 * Componente Header del Admin
 *
 * Renderiza el header con logo y navegación superior
 *
 * @package BravesChat
 * @version 1.2.0
 */

namespace BravesChat\Admin;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use function defined;
use function wp_parse_args;
use function esc_attr;
use function esc_url;
use function admin_url;
use function esc_attr__;
use function esc_html;
use function file_exists;
use function file_get_contents;
use function esc_html__;

class Admin_Header {

    /**
     * Instancia única (Singleton)
     *
     * @var Admin_Header
     */
    private static $instance = null;

    /**
     * Obtener instancia única
     *
     * @return Admin_Header
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
        // Inicialización si es necesaria
    }

    /**
     * Renderizar header
     *
     * @param array $args Argumentos opcionales
     * @return void
     */
    public function render($args = array()) {
        $defaults = array(
            'show_logo' => true,
            'show_version' => true,
            'custom_class' => '',
        );

        $args = wp_parse_args($args, $defaults);
        $custom_class = esc_attr($args['custom_class']);

        echo '<div class="braves-admin-header ' . $custom_class . '">';
        echo '<div class="braves-admin-header__inner">';

        if ($args['show_logo']) {
            echo '<div class="braves-admin-header__logo">';
            $this->render_logo();
            echo '</div>';
        }

        if ($args['show_version']) {
            $admin_url = esc_url(admin_url('admin.php?page=braves-chat-about'));
            $title = esc_attr__('Ver información del plugin', 'braves-chat');
            $version = esc_html('v' . BRAVES_CHAT_VERSION);

            echo '<div class="braves-admin-header__version">';
            echo '<a href="' . $admin_url . '" class="braves-badge braves-badge--primary braves-badge--clickable" title="' . $title . '">';
            echo $version;
            echo '</a>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Renderizar logo
     *
     * @return void
     */
    private function render_logo() {
        $logo_path = BRAVES_CHAT_PLUGIN_DIR . 'assets/media/braves-logo.svg';

        if (file_exists($logo_path)) {
            // Renderizar SVG directamente
            echo file_get_contents($logo_path);
        } else {
            // Fallback a texto
            echo '<span class="braves-admin-header__logo-text">';
            echo esc_html__('BravesChat iA', 'braves-chat');
            echo '</span>';
        }
    }

    /**
     * Obtener HTML del header sin renderizarlo
     *
     * @param array $args Argumentos opcionales
     * @return string HTML del header
     */
    public function get_html($args = array()) {
        ob_start();
        $this->render($args);
        return ob_get_clean();
    }
}
