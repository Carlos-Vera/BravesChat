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

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos para acceder a esta página.', 'braves-chat'));
}

// Obtener instancias de componentes
$header  = Admin_Header::get_instance();
$sidebar = Admin_Sidebar::get_instance();

// Obtener estado de configuración
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
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code >= 400) {
            $fetch_error = sprintf(__('Error HTTP %d al conectar con el servidor.', 'braves-chat'), $response_code);
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (is_array($data)) {
                // N8N envuelve cada item bajo una clave "json" a veces, normalizar si es necesario
                foreach ($data as $item) {
                    if (isset($item['json']) && is_array($item['json'])) {
                        $conversations[] = $item['json'];
                    } else {
                        $conversations[] = $item;
                    }
                }
            } elseif (!empty($body)) {
                $fetch_error = __('La respuesta del webhook no es un JSON válido.', 'braves-chat');
            }
        }
    }
}
?>

<div class="wrap braves-admin-wrap">
    <div class="braves-admin-container">

        <?php
        // Renderizar header
        $header->render(array(
            'show_logo'    => true,
            'show_version' => true,
        ));
        ?>

        <div class="braves-admin-body">

            <?php
            // Renderizar sidebar
            $sidebar->render($current_page);
            ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title"><?php _e('<strong>Historial</strong>', 'braves-chat'); ?></h1>
                    <p class="braves-page-description">
                        <?php _e('Historial de conversaciones registradas en la base de datos de Postgres.', 'braves-chat'); ?>
                    </p>
                </div>

                <!-- Configuration Status Section -->
                <?php if (!$config_status['is_configured']): ?>
                <div class="notice notice-warning inline">
                    <p>
                        <strong><?php _e('Acción requerida:', 'braves-chat'); ?></strong>
                        <?php _e('Para que el chat funcione, necesitas configurar la URL del webhook en la página de ajustes.', 'braves-chat'); ?>
                    </p>
                </div>
                <?php endif; ?>

                <?php if (empty($stats_webhook_url)): ?>
                    <div class="notice notice-warning inline">
                        <p>
                            <strong><?php _e('Webhook no configurado:', 'braves-chat'); ?></strong>
                            <?php printf(
                                __('Configura la URL del webhook de historial en <a href="%s">Ajustes</a>.', 'braves-chat'),
                                esc_url(admin_url('admin.php?page=braves-chat-settings'))
                            ); ?>
                        </p>
                    </div>
                <?php elseif (!empty($fetch_error)): ?>
                    <div class="notice notice-error inline">
                        <p>
                            <strong><?php _e('No hay conversaciones registradas o hay un error de conexión.', 'braves-chat'); ?></strong> 
                            <br><?php echo esc_html($fetch_error); ?>
                        </p>
                    </div>
                <?php elseif (empty($conversations)): ?>
                    <div class="notice notice-info inline">
                        <p>
                            <?php _e('No hay conversaciones registradas o hay un error de conexión.', 'braves-chat'); ?>
                        </p>
                    </div>
                <?php else: ?>

                    <!-- Tabla de conversaciones -->
                    <div class="braves-section" style="margin-top: 20px;">
                        
                        <!-- Actions -->
                        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; font-size: 16px;">
                                <?php printf(
                                    _n('%d conversación encontrada', '%d conversaciones encontradas', count($conversations), 'braves-chat'),
                                    count($conversations)
                                ); ?>
                            </h3>
                            <button type="button" id="braves-history-export-csv" class="button button-primary">
                                <?php _e('Descargar CSV', 'braves-chat'); ?>
                            </button>
                        </div>
                        
                        <style>
                            .braves-history-table-row {
                                cursor: pointer;
                                transition: background-color 0.2s;
                            }
                            .braves-history-table-row:hover {
                                background-color: #f0f0f1 !important;
                            }
                        </style>

                        <!-- Tabla NATIVA WP -->
                        <table id="braves-history-table" class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 25%;"><?php _e('Session ID', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 25%;"><?php _e('Contacto', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 30%;"><?php _e('Extracto', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 20%;"><?php _e('Fecha', 'braves-chat'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conversations as $row):
                                    $session_id       = isset($row['session_id'])       ? $row['session_id']       : '';
                                    $client_name      = isset($row['client_name'])      ? $row['client_name']      : '';
                                    $client_email     = isset($row['client_email'])     ? $row['client_email']     : '';
                                    $chat_history     = isset($row['chat_history'])     ? $row['chat_history']     : '';
                                    $user_height      = isset($row['user_height'])      ? $row['user_height']      : '';
                                    $intent           = isset($row['intent'])           ? $row['intent']           : '';
                                    $lead_score       = isset($row['lead_score'])       ? $row['lead_score']       : '';
                                    $product_interest = isset($row['product_interest']) ? $row['product_interest'] : '';
                                    $update_at        = isset($row['update_at'])        ? $row['update_at']        : '';

                                    // Formatear Fecha
                                    $date_formatted = '';
                                    if (!empty($update_at)) {
                                        $ts = strtotime($update_at);
                                        $date_formatted = $ts ? date_i18n('d/m/Y H:i', $ts) : $update_at;
                                    }

                                    // Extraer extracto del chat
                                    $snippet = __('(Sin mensajes)', 'braves-chat');
                                    $chat_history_json = is_array($chat_history) ? wp_json_encode($chat_history) : $chat_history;
                                    
                                    if (!empty($chat_history)) {
                                        $decoded_chat = is_array($chat_history) ? $chat_history : json_decode($chat_history, true);
                                        if (is_array($decoded_chat) && count($decoded_chat) > 0) {
                                            $last_msg_obj = end($decoded_chat);
                                            // Buscar el último mensaje del usuario si es posible, o el último en general
                                            foreach (array_reverse($decoded_chat) as $msg) {
                                                $role = isset($msg['role']) ? $msg['role'] : (isset($msg['type']) ? $msg['type'] : '');
                                                if ($role === 'human' || $role === 'user') {
                                                    $last_msg_obj = $msg;
                                                    break;
                                                }
                                            }
                                            if (isset($last_msg_obj['content'])) {
                                                $snippet = wp_trim_words($last_msg_obj['content'], 10, '...');
                                            }
                                        }
                                    }
                                ?>
                                <tr class="braves-history-table-row"
                                    data-session-id="<?php echo esc_attr($session_id); ?>"
                                    data-client-name="<?php echo esc_attr($client_name); ?>"
                                    data-client-email="<?php echo esc_attr($client_email); ?>"
                                    data-user-height="<?php echo esc_attr($user_height); ?>"
                                    data-intent="<?php echo esc_attr($intent); ?>"
                                    data-lead-score="<?php echo esc_attr($lead_score); ?>"
                                    data-product-interest="<?php echo esc_attr($product_interest); ?>"
                                    data-update-at="<?php echo esc_attr($date_formatted); ?>"
                                    data-chat-history="<?php echo esc_attr($chat_history_json); ?>">
                                    <td>
                                        <strong><?php echo esc_html(wp_trim_words($session_id, 3, '...')); ?></strong>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo esc_html($client_name); ?></div>
                                        <?php if (!empty($client_email)): ?>
                                            <div><a href="mailto:<?php echo esc_attr($client_email); ?>"><?php echo esc_html($client_email); ?></a></div>
                                        <?php endif; ?>
                                        <?php if (empty($client_name) && empty($client_email)): ?>
                                            <em><?php _e('Desconocido', 'braves-chat'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: #666; font-style: italic;"><?php echo esc_html($snippet); ?></span>
                                    </td>
                                    <td>
                                        <span class="dashicons dashicons-clock" style="color: #888; margin-right: 4px; font-size: 16px; margin-top: 3px;"></span>
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

<!-- Chat History Viewer Modal -->
<div id="braves-history-modal" class="braves-history-modal-overlay">
    <div class="braves-history-modal" role="dialog" aria-modal="true" aria-labelledby="braves-modal-title">
        <div class="braves-history-modal__header">
            <div>
                <h3 id="braves-modal-title" class="braves-history-modal__title"><?php _e('Conversación del Chat', 'braves-chat'); ?></h3>
                <p id="braves-modal-subtitle" class="braves-history-modal__subtitle"></p>
            </div>
            <button type="button" id="braves-history-modal-close" class="braves-history-modal__close" aria-label="<?php _e('Cerrar', 'braves-chat'); ?>">&times;</button>
        </div>
        <div id="braves-history-modal-body" class="braves-history-modal__body">
            <!-- Chat messages will be dynamically injected here by JS -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- CSV Export Logic ---
    var btn = document.getElementById('braves-history-export-csv');
    if (btn) {
        btn.addEventListener('click', function() {
            var tableRows = document.querySelectorAll('#braves-history-table tbody tr');
            if (!tableRows || tableRows.length === 0) return;

            var rows = [];
            var today = new Date();
            var dateStr = today.getFullYear()
                + String(today.getMonth() + 1).padStart(2, '0')
                + String(today.getDate()).padStart(2, '0');

            // Header Row
            rows.push([
                'Session ID', 
                'Client Name', 
                'Client Email', 
                'User Height', 
                'Intent', 
                'Lead Score', 
                'Product Interest', 
                'Update At', 
                'Chat History JSON'
            ]);

            tableRows.forEach(function(tr) {
                rows.push([
                    tr.getAttribute('data-session-id') || '',
                    tr.getAttribute('data-client-name') || '',
                    tr.getAttribute('data-client-email') || '',
                    tr.getAttribute('data-user-height') || '',
                    tr.getAttribute('data-intent') || '',
                    tr.getAttribute('data-lead-score') || '',
                    tr.getAttribute('data-product-interest') || '',
                    tr.getAttribute('data-update-at') || '',
                    tr.getAttribute('data-chat-history') || ''
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
            a.download = 'braveschat_history_' + dateStr + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    }

    // --- Modal Logic ---
    var tableRows = document.querySelectorAll('.braves-history-table-row');
    var modalOverlay = document.getElementById('braves-history-modal');
    var modalClose = document.getElementById('braves-history-modal-close');
    var modalTitle = document.getElementById('braves-modal-title');
    var modalSubtitle = document.getElementById('braves-modal-subtitle');
    var modalBody = document.getElementById('braves-history-modal-body');

    if (modalOverlay && modalClose) {
        // Open Modal Event
        tableRows.forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Ignore clicks on links inside the row to allow mailto links
                if (e.target.tagName.toLowerCase() === 'a') return;

                var chatHistoryRaw = this.getAttribute('data-chat-history');
                var sessionId      = this.getAttribute('data-session-id') || 'N/A';
                var clientName     = this.getAttribute('data-client-name') || '';
                var clientEmail    = this.getAttribute('data-client-email') || '';
                
                var displayName = [clientName, clientEmail].filter(Boolean).join(' - ');
                if (!displayName) displayName = 'Conversación Anónima';
                
                modalTitle.textContent = displayName;
                modalSubtitle.textContent = 'Session ID: ' + sessionId;

                // Render Chat
                modalBody.innerHTML = ''; // Clear previous
                
                if (chatHistoryRaw) {
                    try {
                        var chatHistory = JSON.parse(chatHistoryRaw);
                        if (Array.isArray(chatHistory) && chatHistory.length > 0) {
                            chatHistory.forEach(function(msg) {
                                var msgDiv = document.createElement('div');
                                msgDiv.className = 'braves-history-chat-bubble';
                                
                                // Determine role
                                var role = msg.role || msg.type || '';
                                if (role === 'human' || role === 'user') {
                                    msgDiv.classList.add('braves-history-chat-bubble--user');
                                } else {
                                    msgDiv.classList.add('braves-history-chat-bubble--ai');
                                }

                                // We use textContent for security (prevents XSS if content has HTML elements unintentionally)
                                msgDiv.textContent = msg.content || '';
                                modalBody.appendChild(msgDiv);
                            });
                        } else {
                            modalBody.innerHTML = '<div class="braves-history-chat-empty">' + '<?php esc_js(_e('Chat vacío o formato inválido.', 'braves-chat')); ?>' + '</div>';
                        }
                    } catch (err) {
                        console.error('BravesChat: Error parsing chat history JSON.', err);
                        modalBody.innerHTML = '<div class="braves-history-chat-empty">' + '<?php esc_js(_e('No se pudo visualizar el chat.', 'braves-chat')); ?>' + '</div>';
                    }
                } else {
                    modalBody.innerHTML = '<div class="braves-history-chat-empty">' + '<?php esc_js(_e('No hay datos de historial.', 'braves-chat')); ?>' + '</div>';
                }

                // Show modal & prevent body scroll
                modalOverlay.classList.add('braves-is-visible');
                document.body.style.overflow = 'hidden';
            });
        });

        // Close Modal Event
        var closeModal = function() {
            modalOverlay.classList.remove('braves-is-visible');
            document.body.style.overflow = '';
        };

        modalClose.addEventListener('click', closeModal);
        
        // Close on overlay click
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });

        // Close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalOverlay.classList.contains('braves-is-visible')) {
                closeModal();
            }
        });
    }

});
</script>
