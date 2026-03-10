<?php
/**
 * Limpieza al desinstalar el plugin
 *
 * @package BravesChat
 */

// Si uninstall no es llamado desde WordPress, salir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Eliminar todas las opciones del plugin
 */
function braves_chat_delete_plugin_options() {
    $options = array(
        // Ajustes generales
        'braves_chat_global_enable',
        'braves_chat_webhook_url',
        'braves_chat_n8n_auth_type',
        'braves_chat_n8n_auth_token',
        'braves_chat_n8n_auth_header',
        'braves_chat_typing_speed',
        'braves_chat_header_title',
        'braves_chat_header_subtitle',
        'braves_chat_header_status_text',
        'braves_chat_welcome_message',
        'braves_chat_position',
        'braves_chat_display_mode',
        'braves_chat_excluded_pages',
        // Apariencia
        'braves_chat_chat_icon',
        'braves_chat_icon_color',
        'braves_chat_bubble_tooltip',
        'braves_chat_bubble_color',
        'braves_chat_bubble_image',
        'braves_chat_bubble_text',
        'braves_chat_primary_color',
        'braves_chat_background_color',
        'braves_chat_text_color',
        'braves_chat_chat_skin',
        // Disponibilidad / Horarios
        'braves_chat_availability_enabled',
        'braves_chat_availability_start',
        'braves_chat_availability_end',
        'braves_chat_availability_timezone',
        'braves_chat_availability_message',
        // GDPR
        'braves_chat_gdpr_enabled',
        'braves_chat_gdpr_message',
        'braves_chat_gdpr_accept_text',
        // Estadísticas / Historial
        'braves_chat_stats_webhook_url',
        'braves_chat_stats_api_key',
        // Metadatos internos
        'braves_chat_version',
        'braves_chat_settings',
    );

    foreach ($options as $option) {
        delete_option($option);
    }

    // Para sitios multisitio
    if (is_multisite()) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall context: direct query required to enumerate all multisite blogs; caching is unnecessary during cleanup.
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        $original_blog_id = get_current_blog_id();

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            foreach ($options as $option) {
                delete_option($option);
            }
        }

        switch_to_blog($original_blog_id);
    }
}

/**
 * Eliminar archivos y directorios creados
 */
function braves_chat_delete_plugin_files() {
    $upload_dir = wp_upload_dir();
    $braves_dir = $upload_dir['basedir'] . '/braves-chat';

    if (file_exists($braves_dir)) {
        braves_chat_recursive_delete($braves_dir);
    }
}

/**
 * Eliminar directorio recursivamente
 */
function braves_chat_recursive_delete($dir) {
    if (!file_exists($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? braves_chat_recursive_delete($path) : wp_delete_file($path);
    }

    // Use WP_Filesystem to remove directory
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
    }
    return $wp_filesystem->rmdir($dir);
}

/**
 * Limpiar metadatos de posts relacionados
 */
function braves_chat_delete_post_meta() {
    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall context: bulk delete of plugin post meta; no caching needed during cleanup.
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'braves_chat_%'"
    );
}

/**
 * Limpiar opciones de usuario
 */
function braves_chat_delete_user_meta() {
    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall context: bulk delete of plugin user meta; no caching needed during cleanup.
    $wpdb->query(
        "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'braves_chat_%'"
    );
}

// Ejecutar limpieza
braves_chat_delete_plugin_options();
braves_chat_delete_plugin_files();
braves_chat_delete_post_meta();
braves_chat_delete_user_meta();

// Limpiar caché
wp_cache_flush();
