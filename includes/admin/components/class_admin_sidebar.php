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
                'id'        => 'history',
                'label'     => __('Historial', 'braves-chat'),
                'url'       => admin_url('admin.php?page=braves-chat-history'),
                'page_slug' => 'braves-chat-history',
                'icon'      => $this->get_icon_svg('history'),
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
            'history' => '<svg width="24" height="24" viewBox="0 0 1624 1877" fill="none" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
                <path fill="currentColor" d="M739.689,1726.451c-1.782,-3.514 -2.029,-4.12 1.463,-6.23c10.367,-6.265 9.715,-6.927 20.97,-16.033c179.081,-144.9 182.61,-341.306 186.777,-345.651c3.906,-4.073 276.065,-1.103 378.067,-1.939c68.687,-0.563 62.789,-68.866 30.034,-86.247c-12.963,-6.879 -13.823,-6.039 -325.353,-6.039c-78.785,0 -83.541,2.003 -84.667,-7.748c-16.638,-144.044 -66.31,-176.948 -44.481,-176.885c33.957,0.097 320.241,0.913 424.466,0.059c71.23,-0.584 64.753,-76.499 21.278,-89.851c-16.098,-4.944 -304.083,-1.541 -504.248,-2.435c-15.807,-0.071 -13.342,-5.527 -33.654,-26.22c-193.283,-196.907 -401.893,-150.344 -411.887,-157.393c-4.201,-2.963 -1,-410.054 -1.944,-506.895c-1.093,-112.174 91.323,-139.642 112.385,-143.5c26.482,-4.851 684.177,-2.737 685.844,-1.135c3.098,2.977 0.535,327.126 1.45,353.768c0.534,15.551 11.606,34.759 28.879,40.994c24.946,9.004 363.585,-0.614 369.58,5.148c3.087,2.967 0.601,947.466 1.38,1027.514c1.048,107.764 -84.682,138.477 -112.373,143.495c-33.582,6.086 -596.164,0.122 -743.968,3.224l-112.862,-935.627c19.727,14.066 21.716,12.052 161.798,12.052c553.237,0 553.587,0.446 563.328,-3.561c46.69,-19.208 31.722,-87.212 -15.683,-88.438c-6.209,-0.161 -655.141,-0.034 -664.542,-0.074c-6.014,-0.025 -34.096,-4.717 -51.825,20.209c-19.94,28.035 3.815,56.134 6.924,59.812l112.862,935.627Z"/>
                <path fill="currentColor" d="M706.794,990.68c276.005,228.889 152.6,678.34 -207.234,731.261c-328.28,48.28 -598.934,-313.742 -411.416,-627.698c104.192,-174.445 379.932,-292.265 618.65,-103.562l-295.302,353.866c23.14,16.501 25.538,11.449 146.417,12.052c73.674,0.367 69.932,-91.489 3.081,-92.071c-56.633,-0.493 -72.114,3.741 -72.189,-7.761c-0.885,-134.197 9.316,-161.691 -35.467,-175.445c-19.322,-5.934 -56.425,6.553 -56.803,46.178c-0.031,3.286 -0.012,164.809 -0.018,166.144c-0.085,22.083 -3.373,29.195 14.979,50.904l295.302,-353.866Z"/>
                <path fill="currentColor" d="M1557.792,458.431c-20.926,-0.051 -235.221,-0.579 -261.573,0.12c-9.337,0.248 -7.602,-5.138 -7.602,-100.083c0,-175.594 -0.304,-176.501 1.792,-177.191c2.726,-0.897 2.615,0.962 175.377,173.723c28.225,28.172 56.45,56.345 84.676,84.517c7.294,7.292 24.862,20.295 7.33,18.913Z"/>
            </svg>',
        );

        return isset($icons[$icon_name]) ? $icons[$icon_name] : '';
    }
}
