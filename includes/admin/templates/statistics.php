<?php
/**
 * Statistics Page Template
 *
 * Página de Estadísticas – historial de conversaciones desde N8N/Postgres
 *
 * @package BravesChat
 * @subpackage Admin\Templates
 * @since 2.2.0
 */

use BravesChat\Admin\Admin_Header;
use BravesChat\Admin\Admin_Sidebar;
use BravesChat\Admin\Template_Helpers;

if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos para acceder a esta página.', 'braves-chat'));
}

// Obtener instancias de componentes
$header  = Admin_Header::get_instance();
$sidebar = Admin_Sidebar::get_instance();

// Opciones de estadísticas
$stats_webhook_url = get_option('braves_chat_stats_webhook_url', '');
$stats_api_key     = get_option('braves_chat_stats_api_key', '');

// Detectar si se guardaron los ajustes
$settings_updated = isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true';

// Obtener datos del webhook si hay URL configurada
$conversations    = array();
$fetch_error      = '';

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
            $conversations = $data;
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
                    <h1 class="braves-page-title"><?php _e('<strong>Estadísticas</strong>', 'braves-chat'); ?></h1>
                    <p class="braves-page-description">
                        <?php _e('Historial de conversaciones obtenido desde tu base de datos a través de N8N.', 'braves-chat'); ?>
                    </p>
                </div>

                <!-- Success Notice -->
                <?php if ($settings_updated): ?>
                <div class="braves-section">
                    <?php
                    Template_Helpers::notice(
                        __('Configuración guardada correctamente.', 'braves-chat'),
                        'success'
                    );
                    ?>
                </div>
                <?php endif; ?>

                <!-- Configuración del Webhook de Estadísticas -->
                <form action="options.php" method="post">
                    <?php
                    settings_fields('braves_chat_settings');
                    \BravesChat\Settings::get_instance()->render_hidden_fields(array(
                        'stats_webhook_url',
                        'stats_api_key',
                    ));
                    ?>

                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php _e('Configuración', 'braves-chat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: URL Webhook de Estadísticas -->
                            <?php ob_start(); ?>
                            <input type="url"
                                   id="braves_chat_stats_webhook_url"
                                   name="braves_chat_stats_webhook_url"
                                   value="<?php echo esc_attr($stats_webhook_url); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="https://flow.braveslab.com/webhook/...">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('URL del webhook de N8N que consulta las conversaciones en Postgres.', 'braves-chat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('URL Webhook de Estadísticas', 'braves-chat'),
                                'description' => __('Endpoint de N8N para recuperar el historial de conversaciones.', 'braves-chat'),
                                'content'     => ob_get_clean(),
                            ));
                            ?>

                            <!-- Card: API Key -->
                            <?php ob_start(); ?>
                            <input type="password"
                                   id="braves_chat_stats_api_key"
                                   name="braves_chat_stats_api_key"
                                   value="<?php echo esc_attr($stats_api_key); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   autocomplete="new-password"
                                   placeholder="••••••••••••••••">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Clave de autenticación enviada en el header x-api-key al webhook.', 'braves-chat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('API Key', 'braves-chat'),
                                'description' => __('Clave para autenticar las peticiones al webhook de estadísticas.', 'braves-chat'),
                                'content'     => ob_get_clean(),
                            ));
                            ?>

                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="braves-section braves-section--actions">
                        <div class="braves-button-group">
                            <?php submit_button(
                                __('Guardar Configuración', 'braves-chat'),
                                'primary braves-button braves-button--primary',
                                'submit',
                                false
                            ); ?>
                        </div>
                    </div>

                </form>

                <!-- Tabla de Conversaciones -->
                <?php if (!empty($stats_webhook_url)): ?>
                <div class="braves-section" style="margin-top: 30px;">
                    <h2 class="braves-section__title">
                        <?php _e('Conversaciones', 'braves-chat'); ?>
                    </h2>

                    <?php if (!empty($fetch_error)): ?>
                        <div class="notice notice-error inline">
                            <p>
                                <strong><?php _e('Error al obtener los datos:', 'braves-chat'); ?></strong>
                                <?php echo esc_html($fetch_error); ?>
                            </p>
                        </div>
                    <?php elseif (empty($conversations)): ?>
                        <div class="notice notice-info inline">
                            <p><?php _e('No hay conversaciones registradas todavía.', 'braves-chat'); ?></p>
                        </div>
                    <?php else: ?>

                        <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 13px; color: #666;">
                                <?php printf(
                                    _n('%d conversación encontrada.', '%d conversaciones encontradas.', count($conversations), 'braves-chat'),
                                    count($conversations)
                                ); ?>
                            </span>
                            <button type="button" id="braves-export-csv" class="button button-secondary">
                                <?php _e('Descargar CSV', 'braves-chat'); ?>
                            </button>
                        </div>

                        <table id="braves-stats-table" class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 180px;"><?php _e('Session ID', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 200px;"><?php _e('Email', 'braves-chat'); ?></th>
                                    <th scope="col"><?php _e('Último Mensaje', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 160px;"><?php _e('Fecha / Hora', 'braves-chat'); ?></th>
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

                                    // Formatear fecha legible
                                    $date_formatted = '';
                                    if (!empty($updated_at)) {
                                        $ts = strtotime($updated_at);
                                        if ($ts) {
                                            $date_formatted = date_i18n('d/m/Y H:i', $ts);
                                        } else {
                                            $date_formatted = esc_html($updated_at);
                                        }
                                    }
                                ?>
                                <tr
                                    data-chat-history="<?php echo esc_attr($chat_history); ?>"
                                    data-metadata="<?php echo esc_attr($metadata); ?>"
                                    data-user-height="<?php echo esc_attr($user_height); ?>"
                                >
                                    <td style="word-break: break-all; font-family: monospace; font-size: 12px;">
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

                    <?php endif; ?>
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

        var rows  = [];
        var today = new Date();
        var dateStr = today.getFullYear()
            + String(today.getMonth() + 1).padStart(2, '0')
            + String(today.getDate()).padStart(2, '0');

        // Cabecera CSV (columnas visibles + extra)
        rows.push([
            'session_id',
            'client_mail',
            'last_message',
            'updated_at',
            'chat_history',
            'metadata',
            'user_height'
        ]);

        // Filas de datos
        var tbody = table.querySelector('tbody');
        var trows = tbody ? tbody.querySelectorAll('tr') : [];

        trows.forEach(function(tr) {
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

        // Convertir a CSV
        var csvContent = rows.map(function(row) {
            return row.map(function(cell) {
                var val = String(cell).replace(/"/g, '""');
                return '"' + val + '"';
            }).join(',');
        }).join('\r\n');

        // Descargar
        var blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
        var url  = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href     = url;
        link.download = 'braveschat_estadisticas_' + dateStr + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });
})();
</script>
