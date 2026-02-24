<?php
/**
 * Historial Page Template
 *
 * Historial de conversaciones desde N8N/Postgres
 *
 * @package BravesChat
 * @subpackage Admin\Templates
 * @since 2.1.4
 */

use BravesChat\Admin\Admin_Header;
use BravesChat\Admin\Admin_Sidebar;
use BravesChat\Admin\Template_Helpers;

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos para acceder a esta página.', 'braves-chat'));
}

$header  = Admin_Header::get_instance();
$sidebar = Admin_Sidebar::get_instance();

// Obtener estado de configuración (igual que otros templates)
$config_status = Template_Helpers::get_config_status();

// Prefijo de opciones
$option_prefix = 'braves_chat_';

// Leer configuración de historial
$stats_webhook_url = get_option($option_prefix . 'stats_webhook_url', '');
$stats_api_key     = get_option($option_prefix . 'stats_api_key', '');

// Obtener datos del webhook si hay URL configurada
$conversations = array();
$fetch_error   = '';

if (!empty($stats_webhook_url)) {
    $response = wp_remote_get($stats_webhook_url, array(
        'headers' => array(
            'x-api-key' => $stats_api_key,
        ),
        'timeout' => 15,
    ));

    if (is_wp_error($response)) {
        $fetch_error = $response->get_error_message();
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (is_array($data)) {
            // N8N envuelve cada item bajo una clave "json"
            foreach ($data as $item) {
                if (isset($item['json']) && is_array($item['json'])) {
                    $conversations[] = $item['json'];
                } else {
                    $conversations[] = $item;
                }
            }
        } else {
            $fetch_error = __('La respuesta del webhook no es un JSON válido.', 'braves-chat');
        }
    }
}
?>

<div class="wrap braves-admin-wrap">
    <div class="braves-admin-container">

        <?php
        $header->render(array(
            'show_logo'    => true,
            'show_version' => true,
        ));
        ?>

        <div class="braves-admin-body">

            <?php $sidebar->render($current_page); ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title"><?php _e('<strong>Historial</strong>', 'braves-chat'); ?></h1>
                    <p class="braves-page-description">
                        <?php _e('Historial de conversaciones registradas en tu base de datos.', 'braves-chat'); ?>
                    </p>
                </div>

                <?php if (empty($stats_webhook_url)): ?>

                    <!-- Sin configuración -->
                    <div class="braves-section">
                        <?php
                        Template_Helpers::notice(
                            '<strong>' . __('Webhook no configurado:', 'braves-chat') . '</strong> ' .
                            sprintf(
                                __('Configura la URL del webhook de historial en <a href="%s">Ajustes</a>.', 'braves-chat'),
                                esc_url(admin_url('admin.php?page=braves-chat-settings'))
                            ),
                            'warning'
                        );
                        ?>
                    </div>

                <?php elseif (!empty($fetch_error)): ?>

                    <!-- Error al obtener datos -->
                    <div class="braves-section">
                        <?php
                        Template_Helpers::notice(
                            '<strong>' . __('Error al obtener los datos:', 'braves-chat') . '</strong> ' . esc_html($fetch_error),
                            'error'
                        );
                        ?>
                    </div>

                <?php elseif (empty($conversations)): ?>

                    <!-- Sin conversaciones -->
                    <div class="braves-section">
                        <?php
                        Template_Helpers::notice(
                            __('No hay conversaciones registradas todavía.', 'braves-chat'),
                            'info'
                        );
                        ?>
                    </div>

                <?php else: ?>

                    <!-- Tabla de conversaciones -->
                    <div class="braves-section">
                        <div class="braves-section__header" style="display: flex; align-items: center; justify-content: space-between;">
                            <h2 class="braves-section__title" style="margin: 0;">
                                <?php printf(
                                    _n('%d conversación', '%d conversaciones', count($conversations), 'braves-chat'),
                                    count($conversations)
                                ); ?>
                            </h2>
                            <button type="button" id="braves-export-csv" class="button button-secondary">
                                <?php _e('Descargar CSV', 'braves-chat'); ?>
                            </button>
                        </div>

                        <table id="braves-stats-table" class="wp-list-table widefat fixed striped" style="margin-top: 12px;">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 160px;"><?php _e('Session ID', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 190px;"><?php _e('Email', 'braves-chat'); ?></th>
                                    <th scope="col"><?php _e('Último Mensaje', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 150px;"><?php _e('Fecha / Hora', 'braves-chat'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conversations as $row):
                                    $session_id   = isset($row['session_id'])   ? $row['session_id']   : '';
                                    $client_mail  = isset($row['client_mail'])  ? $row['client_mail']  : '';
                                    $last_message = isset($row['last_message']) ? $row['last_message'] : '';
                                    $chat_history = isset($row['chat_history']) ? $row['chat_history'] : '';
                                    $metadata     = isset($row['metadata'])     ? $row['metadata']     : '';
                                    $user_height  = isset($row['user_height'])  ? $row['user_height']  : '';
                                    $updated_at   = isset($row['updated_at'])   ? $row['updated_at']   : '';

                                    $date_formatted = '';
                                    if (!empty($updated_at)) {
                                        $ts = strtotime($updated_at);
                                        $date_formatted = $ts ? date_i18n('d/m/Y H:i', $ts) : $updated_at;
                                    }
                                ?>
                                <tr data-chat-history="<?php echo esc_attr($chat_history); ?>"
                                    data-metadata="<?php echo esc_attr($metadata); ?>"
                                    data-user-height="<?php echo esc_attr($user_height); ?>">
                                    <td style="font-family: monospace; font-size: 11px; word-break: break-all;">
                                        <?php echo esc_html($session_id); ?>
                                    </td>
                                    <td><?php echo esc_html($client_mail); ?></td>
                                    <td style="white-space: normal; word-break: break-word;">
                                        <?php echo esc_html($last_message); ?>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <?php echo esc_html($date_formatted); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->

<script>
(function() {
    var btn = document.getElementById('braves-export-csv');
    if (!btn) return;

    btn.addEventListener('click', function() {
        var table = document.getElementById('braves-stats-table');
        if (!table) return;

        var rows    = [];
        var today   = new Date();
        var dateStr = today.getFullYear()
            + String(today.getMonth() + 1).padStart(2, '0')
            + String(today.getDate()).padStart(2, '0');

        rows.push(['session_id', 'client_mail', 'last_message', 'updated_at', 'chat_history', 'metadata', 'user_height']);

        table.querySelectorAll('tbody tr').forEach(function(tr) {
            var cells = tr.querySelectorAll('td');
            rows.push([
                cells[0] ? cells[0].textContent.trim() : '',
                cells[1] ? cells[1].textContent.trim() : '',
                cells[2] ? cells[2].textContent.trim() : '',
                cells[3] ? cells[3].textContent.trim() : '',
                tr.getAttribute('data-chat-history') || '',
                tr.getAttribute('data-metadata') || '',
                tr.getAttribute('data-user-height') || ''
            ]);
        });

        var csv = rows.map(function(row) {
            return row.map(function(cell) {
                return '"' + String(cell).replace(/"/g, '""') + '"';
            }).join(',');
        }).join('\r\n');

        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href     = url;
        a.download = 'braveschat_historial_' + dateStr + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
})();
</script>
