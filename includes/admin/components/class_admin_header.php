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
use function get_option;
use function gmdate;
use function count;

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
            'show_logo'    => true,
            'show_version' => true,
            'custom_class' => '',
            'notices'      => '',
        );

        $args = wp_parse_args($args, $defaults);
        $custom_class = esc_attr($args['custom_class']);

        echo '<div class="braves-admin-header ' . $custom_class . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $custom_class is esc_attr().
        echo '<div class="braves-admin-header__inner">';

        if ($args['show_logo']) {
            $dashboard_url = esc_url(admin_url('admin.php?page=braveschat'));
            echo '<a href="' . $dashboard_url . '" class="braves-admin-header__logo">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $dashboard_url is esc_url().
            $this->render_logo();
            echo '</a>';
        }

        if (!empty($args['notices'])) {
            echo '<div class="braves-admin-header__notices">';
            echo $args['notices']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- notices are built with Template_Helpers::notice() which handles escaping internally.
            echo '</div>';
        }

        if ($args['show_version']) {
            $admin_url    = esc_url(admin_url('admin.php?page=braveschat-about'));
            $title        = esc_attr__('View plugin information', 'braveschat');
            $version      = esc_html('v' . BRAVES_CHAT_VERSION);
            $display_mode = esc_html(get_option('braves_chat_display_mode', 'modal'));
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, used for styling only.
            $is_about     = isset($_GET['page']) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'braveschat-about';
            $badge_class  = 'braves-badge braves-badge--primary braves-badge--clickable' . ($is_about ? ' braves-badge--active' : '');

            $verse = self::get_daily_verse();

            echo '<div class="braves-admin-header__version">';
            echo '<div class="braves-admin-header__version-badge">';
            echo '<em class="braves-header__mode-label">' . $display_mode . '</em>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $display_mode is esc_html().
            echo '<a href="' . $admin_url . '" class="' . esc_attr($badge_class) . '" title="' . $title . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $admin_url is esc_url(); $title is esc_attr__().
            echo $version; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $version is esc_html().
            echo '</a>';
            echo '</div>';
            if ( null !== $verse ) {
                echo '<p class="braves-admin-header__verse">';
                echo '<em>' . esc_html( "\u{201c}" . $verse['text'] . "\u{201d} \u{2014} " . $verse['ref'] ) . '</em>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html() applied.
                echo '</p>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Versículo diario NVI — rotación por día del año sobre el array local de 365 versículos.
     *
     * @return array{ text: string, ref: string }
     */
    private static function get_daily_verse() {
        $locale     = get_locale();
        $is_spanish = ( strncmp( $locale, 'es', 2 ) === 0 );
        $file       = $is_spanish
            ? BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/data/bible-verses.php'
            : BRAVES_CHAT_PLUGIN_DIR . 'includes/admin/data/bible-verses-en.php';
        $verses = include $file;
        return $verses[ (int) gmdate( 'z' ) % count( $verses ) ];
    }

    /**
     * Renderizar logo
     *
     * @return void
     */
    private function render_logo() {
        $logo_light_path = BRAVES_CHAT_PLUGIN_DIR . 'assets/media/braves-logo.svg';
        $logo_dark_path  = BRAVES_CHAT_PLUGIN_DIR . 'assets/media/braves-logo-white.svg';

        if ( file_exists( $logo_light_path ) ) {
            echo '<img src="' . esc_url( BRAVES_CHAT_PLUGIN_URL . 'assets/media/braves-logo.svg' ) . '" alt="' . esc_attr__( 'BravesChat', 'braveschat' ) . '" class="braves-admin-header__logo-img logo-light" />';
        }

        if ( file_exists( $logo_dark_path ) ) {
            echo '<img src="' . esc_url( BRAVES_CHAT_PLUGIN_URL . 'assets/media/braves-logo-white.svg' ) . '" alt="' . esc_attr__( 'BravesChat', 'braveschat' ) . '" class="braves-admin-header__logo-img logo-dark" />';
        } elseif ( ! file_exists( $logo_light_path ) ) {
            // Fallback a texto si no hay ningún logo
            echo '<span class="braves-admin-header__logo-text">';
            echo esc_html__('BravesChat iA', 'braveschat');
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
