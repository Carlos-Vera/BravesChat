<?php
/**
 * Componente Sidebar del Admin
 *
 * Renderiza la navegación lateral con tabs
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
use function sanitize_text_field;
use function __;
use function admin_url;
use function apply_filters;
use function esc_url;
use function esc_attr;
use function esc_html;
use function do_action;

class Admin_Sidebar {

    /**
     * Instancia única (Singleton)
     *
     * @var Admin_Sidebar
     */
    private static $instance = null;

    /**
     * Obtener instancia única
     *
     * @return Admin_Sidebar
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
     * Renderizar sidebar
     *
     * @param string $current_page Página actual
     * @return void
     */
    public function render($current_page = '') {
        if (empty($current_page)) {
            $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'braves-chat';
        }

        $menu_items = $this->get_menu_items();

        echo '<nav class="braves-admin-sidebar">';
        echo '<div class="braves-admin-sidebar__inner">';
        
        foreach ($menu_items as $item) {
            $this->render_menu_item($item, $current_page);
        }
        
        echo '</div>'; // .braves-admin-sidebar__inner

        /**
         * Hook para agregar items adicionales al sidebar
         *
         * @param string $current_page Página actual
         */
        do_action('braves_chat_admin_sidebar_items', $current_page);
        
        echo '</nav>';
    }

    /**
     * Obtener items del menú
     *
     * @return array Items del menú
     */
    private function get_menu_items() {
        $items = array(
            array(
                'id' => 'dashboard',
                'label' => __('Dashboard', 'braves-chat'),
                'url' => admin_url('admin.php?page=braves-chat'),
                'page_slug' => 'braves-chat',
                'icon' => $this->get_icon_svg('dashboard'),
            ),
            array(
                'id' => 'settings',
                'label' => __('Ajustes', 'braves-chat'),
                'url' => admin_url('admin.php?page=braves-chat-settings'),
                'page_slug' => 'braves-chat-settings',
                'icon' => $this->get_icon_svg('settings'),
            ),
            array(
                'id' => 'appearance',
                'label' => __('Apariencia', 'braves-chat'),
                'url' => admin_url('admin.php?page=braves-chat-appearance'),
                'page_slug' => 'braves-chat-appearance',
                'icon' => $this->get_icon_svg('appearance'),
            ),
            array(
                'id' => 'availability',
                'label' => __('Horarios', 'braves-chat'),
                'url' => admin_url('admin.php?page=braves-chat-availability'),
                'page_slug' => 'braves-chat-availability',
                'icon' => $this->get_icon_svg('availability'),
            ),
            array(
                'id' => 'gdpr',
                'label' => __('GDPR', 'braves-chat'),
                'url' => admin_url('admin.php?page=braves-chat-gdpr'),
                'page_slug' => 'braves-chat-gdpr',
                'icon' => $this->get_icon_svg('gdpr'),
            ),
            array(
                'id'        => 'statistics',
                'label'     => __('Historial', 'braves-chat'),
                'url'       => admin_url('admin.php?page=braves-chat-stats'),
                'page_slug' => 'braves-chat-stats',
                'icon'      => $this->get_icon_svg('statistics'),
            ),
        );

        /**
         * Filtro para modificar items del menú
         *
         * @param array $items Items del menú
         */
        return apply_filters('braves_chat_admin_menu_items', $items);
    }

    /**
     * Renderizar un item del menú
     *
     * @param array $item Item del menú
     * @param string $current_page Página actual
     * @return void
     */
    private function render_menu_item($item, $current_page) {
        $is_active = ($item['page_slug'] === $current_page);
        $item_class = 'braves-admin-sidebar__item';

        if ($is_active) {
            $item_class .= ' braves-admin-sidebar__item--active';
        }

        $url = esc_url($item['url']);
        $class = esc_attr($item_class);
        $page_slug = esc_attr($item['page_slug']);
        $label = esc_html($item['label']);
        
        echo '<a href="' . $url . '" class="' . $class . '" data-page="' . $page_slug . '">';
        echo '<span class="braves-admin-sidebar__icon">';
        echo $item['icon'];
        echo '</span>';
        echo '<span class="braves-admin-sidebar__label">';
        echo $label;
        echo '</span>';
        echo '</a>';
    }

    /**
     * Obtener SVG de icono
     *
     * @param string $icon_name Nombre del icono
     * @return string SVG del icono
     */
    private function get_icon_svg($icon_name) {
        $icons = array(
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" fill="currentColor"/>
            </svg>',
            'settings' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" fill="currentColor"/>
            </svg>',
            'appearance' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" fill="currentColor"/>
            </svg>',
            'availability' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm3.3 14.71L11 12.41V7h2v4.59l3.71 3.71-1.42 1.41z" fill="currentColor"/>
            </svg>',
            'gdpr' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM9 8V6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9z" fill="currentColor"/>
            </svg>',
            'statistics' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z" fill="currentColor"/>
            </svg>',
        );

        return isset($icons[$icon_name]) ? $icons[$icon_name] : '';
    }
}
