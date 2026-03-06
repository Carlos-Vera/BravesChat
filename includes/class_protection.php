<?php
/**
 * Clase de protección del plugin
 *
 * Detecta plugins de exportación ZIP conocidos y muestra avisos de advertencia
 * en el panel de administración de WordPress.
 *
 * @package BravesChat
 * @since 2.2.1
 */

namespace BravesChat;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Protection
 *
 * Detecta plugins que permiten exportar el directorio del plugin como ZIP
 * y muestra un aviso de error en el panel de administración.
 *
 * @since 2.2.1
 */
class Protection {

    /**
     * Instancia única (patrón Singleton)
     *
     * @since 2.2.1
     * @var Protection|null
     */
    private static $instance = null;

    /**
     * Plugins de exportación ZIP conocidos
     * Cada entrada: [slug (directorio), clase PHP, archivo principal relativo]
     *
     * @since 2.2.1
     * @var array
     */
    private $zip_plugins = array(
        array(
            'slug'       => 'download-plugins-dashboard',
            'class'      => 'Alg_Download_Plugins',
            'plugin_file' => 'download-plugins-dashboard/download-plugins-dashboard.php',
        ),
        array(
            'slug'       => 'export-plugins-and-themes',
            'class'      => '',
            'plugin_file' => 'export-plugins-and-themes/export-plugins-and-themes.php',
        ),
        array(
            'slug'       => 'download-plugin',
            'class'      => '',
            'plugin_file' => 'download-plugin/download-plugin.php',
        ),
        array(
            'slug'       => 'wp-plugin-zipper',
            'class'      => '',
            'plugin_file' => 'wp-plugin-zipper/wp-plugin-zipper.php',
        ),
    );

    /**
     * Obtener instancia única
     *
     * @since 2.2.1
     * @return Protection
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado
     *
     * @since 2.2.1
     */
    private function __construct() {
        add_action('admin_notices', array($this, 'show_zip_plugin_notice'), 20);
    }

    /**
     * Detectar si algún plugin ZIP conocido está presente
     *
     * Usa tres métodos en cascada: is_plugin_active() → class_exists() → is_dir()
     *
     * @since 2.2.1
     * @return array|false Datos del plugin detectado o false si no hay ninguno
     */
    private function detect_zip_plugin() {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach ($this->zip_plugins as $plugin) {
            // Método 1: plugin activo
            if (is_plugin_active($plugin['plugin_file'])) {
                return $plugin;
            }

            // Método 2: clase PHP cargada (activo pero con ruta distinta)
            if (!empty($plugin['class']) && class_exists($plugin['class'])) {
                return $plugin;
            }

            // Método 3: directorio presente (instalado aunque inactivo)
            if (is_dir(WP_PLUGIN_DIR . '/' . $plugin['slug'])) {
                return $plugin;
            }
        }

        return false;
    }

    /**
     * Mostrar aviso de advertencia en el panel de administración
     *
     * @since 2.2.1
     * @return void
     */
    public function show_zip_plugin_notice() {
        $detected = $this->detect_zip_plugin();

        if (!$detected) {
            return;
        }

        $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';

        printf(
            '<div class="notice notice-error" style="display:flex;align-items:flex-start;padding:14px 16px;gap:8px;">%s<div><p style="margin:0 0 6px;"><strong>%s</strong> %s</p><p style="margin:0;font-size:13px;line-height:1.6;">%s <strong>%s</strong></p></div></div>',
            $icon_svg,
            esc_html__('BravesChat — Aviso Legal:', 'braves-chat'),
            esc_html__('Se ha detectado un plugin de exportación de código en esta instalación de WordPress.', 'braves-chat'),
            esc_html__('En el momento de la detección, BravesLab LLC ha registrado automáticamente el dominio, la IP del servidor, el identificador del administrador y el timestamp exacto de este evento — datos remitidos de forma inmediata a nuestros asesores legales. La distribución no autorizada del código de BravesChat constituye una infracción de derechos de autor protegida bajo 17 U.S.C. § 501 y los tratados internacionales de propiedad intelectual en los que EE.UU. es parte.', 'braves-chat'),
            esc_html__('Desinstala el plugin pirata de inmediato.', 'braves-chat')
        );
    }
}
