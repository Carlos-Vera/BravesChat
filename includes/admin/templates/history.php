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
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (is_array($data)) {
                // Normalizar estructura de N8N:
                // Formato 1: {"data": [{...}]}
                // Formato 2: [[{...}]] — array doble (N8N output node)
                // Formato 3: [{...}] — array directo
                if (isset($data['data']) && is_array($data['data'])) {
                    $raw_items = $data['data'];
                } else {
                    $first = reset($data);
                    if (is_array($first) && isset($first[0]) && is_array($first[0])) {
                        // Formato doble-envuelto: [[row1, row2, ...]]
                        $raw_items = $first;
                    } else {
                        $raw_items = $data;
                    }
                }

                // N8N envuelve cada item bajo una clave "json" a veces, normalizar si es necesario
                foreach ($raw_items as $item) {
                    if (isset($item['json']) && is_array($item['json'])) {
                        $conversations[] = $item['json'];
                    } else {
                        $conversations[] = $item;
                    }
                }
            } elseif (empty($body)) {
                $fetch_error = __('El webhook respondió con cuerpo vacío. Verifica que el flujo de N8N esté activo y retornando datos.', 'braves-chat');
            } else {
                $fetch_error = __('La respuesta del webhook no es un JSON válido.', 'braves-chat');
            }
        }
    }
}

// Agrupar mensajes individuales por session_id formando conversaciones
// N8N devuelve una fila por mensaje: {session_id, chat_history: {type, content}, ...}
$sessions = array();
foreach ($conversations as $row) {
    if (!is_array($row)) continue;
    $sid = isset($row['session_id']) && is_scalar($row['session_id']) ? (string)$row['session_id'] : '';
    if (empty($sid)) continue;

    if (!isset($sessions[$sid])) {
        $sessions[$sid] = array(
            'session_id'  => $sid,
            'client_name' => null,
            'updated_at'  => null,
            'messages'    => array(),
        );
    }

    // Tomar el primer valor no-nulo para metadatos de sesión (filas y results de tools)
    foreach (array('client_name', 'updated_at', 'update_at') as $meta) {
        $target = ($meta === 'update_at') ? 'updated_at' : $meta;
        if (is_null($sessions[$sid][$target]) && isset($row[$meta]) && !is_null($row[$meta])) {
            $sessions[$sid][$target] = $row[$meta];
        }
    }

    // Agregar el mensaje al historial (filtrando y limpiando acciones internas del agente)
    if (isset($row['chat_history']) && is_array($row['chat_history'])) {
        $msg     = $row['chat_history'];
        $content = isset($msg['content']) ? trim((string)$msg['content']) : '';
        $type    = strtolower(trim(isset($msg['type']) ? (string)$msg['type'] : (isset($msg['role']) ? (string)$msg['role'] : '')));

        // 1. Omitir mensajes vacíos
        if (empty($content)) continue;

        // 2. Omitir tool call invocations: "Calling {tool} with input: ..."
        if (preg_match('/^Calling\s+\S+\s+with\s+input:/i', $content)) continue;

        // 3. Omitir mensajes de AI con tool_calls no vacíos → invocación interna de herramienta
        if (($type === 'ai' || $type === 'assistant') && !empty($msg['tool_calls'])) continue;

        // 4. Omitir razonamiento interno / instrucciones del prompt del agente
        //    Solo patrones muy específicos para no ocultar respuestas válidas
        if (preg_match('/^(Plan|Thought|Notes?|error_?message|Name|There was an error|Action Input|Observation)\s*:/i', $content)) continue;

        // 5. Manejar contenido JSON de herramientas (starts with [ o {)
        if ($content[0] === '[') {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && !empty($decoded) && is_array($decoded[0])) {
                // Extraer timestamp del CRM antes de ocultar
                if (is_null($sessions[$sid]['updated_at'])) {
                    foreach (array('update_at', 'updated_at') as $ts_field) {
                        if (!empty($decoded[0][$ts_field])) {
                            $sessions[$sid]['updated_at'] = (string)$decoded[0][$ts_field];
                            break;
                        }
                    }
                }
                if (isset($decoded[0]['response']) && is_string($decoded[0]['response'])) {
                    $msg['content'] = $decoded[0]['response'];
                } else {
                    continue; // Tool result sin respuesta → ocultar
                }
            } else {
                continue;
            }
        } elseif ($content[0] === '{') {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                if (isset($decoded['response']) && is_string($decoded['response'])) {
                    $msg['content'] = $decoded['response'];
                } else {
                    continue; // Tool result sin respuesta → ocultar
                }
            }
        }

        // 6. Limpiar mensajes del usuario: quitar prefijo N8N y session ID
        if ($type === 'human' || $type === 'user') {
            $text = (string)$msg['content'];
            // Quitar prefijo "Mensaje del usuario: "
            $text = preg_replace('/^Mensaje del usuario:\s*/i', '', $text);
            // Cortar en el primer salto de línea (real \n, literal \\n, o \r\n)
            $nl = strpos($text, "\n");
            if ($nl === false) $nl = strpos($text, '\n'); // literal backslash-n
            if ($nl === false) $nl = strpos($text, "\r");
            if ($nl !== false) $text = substr($text, 0, $nl);
            // Quitar posible ] residual al final (artefacto de serialización de N8N)
            $msg['content'] = rtrim(trim($text), ']');
        }

        // Omitir si el contenido quedó vacío tras la limpieza
        if (empty(trim((string)$msg['content']))) continue;

        $sessions[$sid]['messages'][] = $msg;
    }
}

// Ordenar sesiones de más reciente a más antigua
uasort($sessions, function ($a, $b) {
    $ta = !empty($a['updated_at']) ? strtotime($a['updated_at']) : 0;
    $tb = !empty($b['updated_at']) ? strtotime($b['updated_at']) : 0;
    return $tb - $ta;
});
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
                        <?php _e('Conversaciones registradas con tu agente de IA.', 'braves-chat'); ?>
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
                <?php elseif (empty($sessions)): ?>
                    <div class="notice notice-info inline">
                        <p>
                            <?php _e('No hay conversaciones registradas o hay un error de conexión.', 'braves-chat'); ?>
                        </p>
                    </div>
                <?php else: ?>

                    <div class="braves-bento-card" style="margin-top: 20px;">
                        
                        <!-- Actions -->
                        <div class="braves-bento-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--braves-gray-200);">
                            <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: var(--braves-gray-900);">
                                <?php printf(
                                    _n('%d conversación encontrada', '%d conversaciones encontradas', count($sessions), 'braves-chat'),
                                    count($sessions)
                                ); ?>
                            </h3>
                            <button type="button" id="braves-history-export-csv" class="button button-primary braves-btn">
                                <?php _e('Descargar CSV', 'braves-chat'); ?>
                            </button>
                        </div>
                        
                        <style>
                            .braves-bento-card {
                                background: var(--braves-white);
                                border-radius: var(--braves-radius-lg, 12px);
                                box-shadow: var(--braves-shadow, 0 1px 3px rgba(0,0,0,0.1));
                                overflow: hidden;
                                margin-bottom: 20px;
                                font-family: var(--braves-font-sans, "Montserrat", sans-serif);
                            }
                            
                            .braves-history-table {
                                width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                            }
                            
                            /* Alineación consistente */
                            .braves-history-table th, .braves-history-table td {
                                text-align: left !important;
                            }
                            
                            .braves-history-table th {
                                padding: 16px 20px;
                                font-weight: 600;
                                font-size: 14px;
                                color: var(--braves-gray-600);
                                background: var(--braves-gray-50);
                                border-bottom: 1px solid var(--braves-gray-200);
                            }
                            
                            .braves-history-table td {
                                padding: 16px 20px;
                                vertical-align: middle;
                                border-bottom: 1px solid var(--braves-gray-100);
                                color: var(--braves-gray-800);
                                font-size: 15px;
                            }
                            
                            .braves-history-table-row {
                                cursor: pointer;
                                transition: background-color var(--braves-transition-fast, 0.2s);
                            }
                            .braves-history-table-row:hover {
                                background-color: var(--braves-gray-50) !important;
                            }
                            .braves-history-table-row:last-child td {
                                border-bottom: none;
                            }
                            
                            /* Boton Descargar CSV */
                            #braves-history-export-csv.button-primary {
                                border-radius: var(--braves-radius-md, 8px);
                                font-family: var(--braves-font-sans, "Montserrat", sans-serif);
                                padding: 4px 16px;
                                background-color: var(--braves-primary, #0077b6);
                                border-color: var(--braves-primary, #0077b6);
                                color: var(--braves-white, #fff);
                                box-shadow: var(--braves-shadow-sm, 0 1px 2px rgba(0,0,0,0.05));
                                transition: background-color var(--braves-transition-fast, 0.2s);
                            }
                            #braves-history-export-csv.button-primary:hover,
                            #braves-history-export-csv.button-primary:active,
                            #braves-history-export-csv.button-primary:focus {
                                background-color: var(--braves-primary-hover, #3c3c3c) !important;
                                border-color: var(--braves-primary-hover, #3c3c3c) !important;
                                color: var(--braves-white, #fff);
                            }
                            
                            /* Modal Header y Trucate Fix */
                            .braves-history-modal__header {
                                display: flex;
                                justify-content: space-between;
                                align-items: flex-start;
                            }
                            .braves-history-modal__header > div {
                                flex: 1;
                                min-width: 0;
                                padding-right: 15px;
                            }
                            .braves-history-modal__subtitle {
                                word-break: break-all;
                                margin: 5px 0 0 0 !important;
                                font-size: 12px;
                                color: var(--braves-gray-500, #6b7280);
                            }
                            
                            /* Modal Close Button Circular Fix */
                            .braves-history-modal__close {
                                width: 32px !important;
                                height: 32px !important;
                                flex-shrink: 0;
                                display: flex !important;
                                align-items: center;
                                justify-content: center;
                                border-radius: 50% !important;
                                border: none;
                                background: transparent;
                                cursor: pointer;
                                font-size: 20px;
                                line-height: 1;
                                color: var(--braves-gray-500, #6b7280);
                                transition: background-color 0.2s, color 0.2s;
                            }
                            .braves-history-modal__close:hover {
                                background-color: var(--braves-gray-200, #e5e7eb) !important;
                                color: var(--braves-gray-900, #111827);
                            }

                            /* Bubbles Container */
                            #braves-history-modal-body {
                                display: flex;
                                flex-direction: column;
                                padding: 20px;
                                gap: 15px;
                                background: var(--braves-gray-50, #f8f9fa);
                            }

                            /* Wrapper burbuja + etiqueta */
                            .braves-history-bubble-wrap {
                                display: flex;
                                flex-direction: column;
                                width: 100%;
                            }
                            .braves-history-bubble-wrap--user {
                                align-items: flex-end;
                            }
                            .braves-history-bubble-wrap--ai {
                                align-items: flex-start;
                            }
                            /* Etiqueta remitente */
                            .braves-history-chat-sender {
                                font-size: 11px;
                                font-weight: 600;
                                color: var(--braves-gray-500, #6b7280);
                                margin-bottom: 4px;
                                padding: 0 4px;
                                text-transform: uppercase;
                                letter-spacing: 0.05em;
                            }
                            /* Burbujas */
                            .braves-history-chat-bubble {
                                max-width: 80%;
                                padding: 12px 16px;
                                border-radius: 12px;
                                font-size: 14px;
                                line-height: 1.5;
                                word-break: break-word;
                                box-shadow: var(--braves-shadow-sm, 0 1px 2px rgba(0,0,0,0.05));
                            }
                            .braves-history-chat-bubble--user {
                                background-color: var(--braves-primary, #0077b6);
                                color: var(--braves-white, #ffffff);
                                border-bottom-right-radius: 4px;
                            }
                            .braves-history-chat-bubble--user a {
                                color: inherit;
                                text-decoration: underline;
                            }
                            .braves-history-chat-bubble--ai {
                                background-color: var(--braves-white, #ffffff);
                                color: var(--braves-gray-800, #1f2937);
                                border: 1px solid var(--braves-gray-200, #e5e7eb);
                                border-bottom-left-radius: 4px;
                            }
                            .braves-history-chat-bubble--ai a {
                                color: var(--braves-primary, #0077b6);
                                text-decoration: underline;
                            }
                        </style>

                        <!-- Tabla Bentō -->
                        <table id="braves-history-table" class="braves-history-table">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 25%;"><?php _e('Contacto', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 55%;"><?php _e('Extracto', 'braves-chat'); ?></th>
                                    <th scope="col" style="width: 20%;"><?php _e('Fecha', 'braves-chat'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessions as $row):
                                    $session_id  = $row['session_id'];
                                    $client_name = !is_null($row['client_name']) ? (string)$row['client_name'] : '';
                                    $update_at   = !is_null($row['updated_at'])  ? (string)$row['updated_at']  : '';
                                    $messages    = $row['messages']; // array de {type, content}

                                    // Formatear Fecha
                                    $date_formatted = '';
                                    if (!empty($update_at)) {
                                        $ts = strtotime($update_at);
                                        $date_formatted = $ts ? date_i18n('d/m/Y H:i', $ts) : $update_at;
                                    }

                                    // Extracto: último mensaje del usuario (ya limpiado en PHP al construir sesiones)
                                    $snippet = __('(Sin mensajes)', 'braves-chat');
                                    foreach (array_reverse($messages) as $msg) {
                                        if (!is_array($msg)) continue;
                                        $role = strtolower(trim(isset($msg['type']) ? $msg['type'] : (isset($msg['role']) ? $msg['role'] : '')));
                                        if (($role === 'human' || $role === 'user') && !empty($msg['content'])) {
                                            $snippet = wp_trim_words((string)$msg['content'], 10, '...');
                                            break;
                                        }
                                    }

                                    // JSON del historial para el modal
                                    $chat_history_json = wp_json_encode($messages);
                                ?>
                                <tr class="braves-history-table-row"
                                    data-session-id="<?php echo esc_attr($session_id); ?>"
                                    data-client-name="<?php echo esc_attr($client_name); ?>"
                                    data-update-at="<?php echo esc_attr($date_formatted); ?>"
                                    data-chat-history="<?php echo esc_attr($chat_history_json); ?>">
                                    <td>
                                        <?php if (!empty($client_name)): ?>
                                            <div style="font-weight: 600; color: var(--braves-primary);"><?php echo esc_html($client_name); ?></div>
                                        <?php else: ?>
                                            <em style="color: var(--braves-gray-500);"><?php _e('Desconocido', 'braves-chat'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: var(--braves-gray-600); font-style: italic;"><?php echo esc_html($snippet); ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; color: var(--braves-gray-500); font-size: 14px;">
                                            <span class="dashicons dashicons-clock" style="margin-right: 6px; font-size: 16px; width: 16px; height: 16px;"></span>
                                            <?php echo esc_html($date_formatted); ?>
                                        </div>
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
            rows.push(['Session ID', 'Client Name', 'Updated At', 'Chat History JSON']);

            tableRows.forEach(function(tr) {
                rows.push([
                    tr.getAttribute('data-session-id') || '',
                    tr.getAttribute('data-client-name') || '',
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

                modalTitle.textContent = clientName || 'Conversación Anónima';
                modalSubtitle.textContent = 'Session ID: ' + sessionId;

                // Render Chat
                modalBody.innerHTML = ''; // Clear previous
                
                // --- Helpers ---

                // Limpieza residual (el contenido ya viene limpio desde PHP, esto es red de seguridad)
                function cleanContent(text) {
                    text = text.replace(/^Mensaje del usuario:\s*/i, '');
                    var nl = text.indexOf('\n');
                    if (nl !== -1) text = text.substring(0, nl);
                    return text.trim();
                }

                // Convierte Markdown básico a HTML seguro
                function parseMarkdown(text) {
                    // Escapar HTML para prevenir XSS
                    text = text.replace(/&/g, '&amp;')
                               .replace(/</g, '&lt;')
                               .replace(/>/g, '&gt;');
                    // Negrita
                    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                    text = text.replace(/__(.+?)__/g, '<strong>$1</strong>');
                    // Cursiva
                    text = text.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');
                    // Enlace [texto](url)
                    text = text.replace(/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
                    // URL desnuda
                    text = text.replace(/(^|[\s>])(https?:\/\/[^\s<"]+)/g, '$1<a href="$2" target="_blank" rel="noopener noreferrer">$2</a>');
                    // Saltos de línea
                    text = text.replace(/\n/g, '<br>');
                    return text;
                }

                if (chatHistoryRaw) {
                    try {
                        var chatHistory = JSON.parse(chatHistoryRaw);
                        if (Array.isArray(chatHistory) && chatHistory.length > 0) {
                            chatHistory = chatHistory.reverse(); // Orden cronológico: más antiguo arriba
                            chatHistory.forEach(function(msg) {
                                var role = (msg.role || msg.type || '').trim().toLowerCase();
                                var isUser = (role === 'human' || role === 'user');

                                // Wrapper (label + burbuja)
                                var wrapDiv = document.createElement('div');
                                wrapDiv.className = 'braves-history-bubble-wrap' + (isUser ? ' braves-history-bubble-wrap--user' : ' braves-history-bubble-wrap--ai');

                                // Etiqueta remitente
                                var labelDiv = document.createElement('div');
                                labelDiv.className = 'braves-history-chat-sender';
                                labelDiv.textContent = isUser ? (clientName || 'Usuario') : 'Agente';

                                // Burbuja con contenido limpio y Markdown renderizado
                                var msgDiv = document.createElement('div');
                                msgDiv.className = 'braves-history-chat-bubble ' + (isUser ? 'braves-history-chat-bubble--user' : 'braves-history-chat-bubble--ai');
                                msgDiv.innerHTML = parseMarkdown(cleanContent(msg.content || ''));

                                wrapDiv.appendChild(labelDiv);
                                wrapDiv.appendChild(msgDiv);
                                modalBody.appendChild(wrapDiv);
                            });
                        } else {
                            modalBody.innerHTML = '<div class="braves-history-chat-empty">' + '<?php echo esc_js(__('Chat vacío o formato inválido.', 'braves-chat')); ?>' + '</div>';
                        }
                    } catch (err) {
                        console.error('BravesChat: Error parsing chat history JSON.', err);
                        modalBody.innerHTML = '<div class="braves-history-chat-empty">' + '<?php echo esc_js(__('No se pudo visualizar el chat.', 'braves-chat')); ?>' + '</div>';
                    }
                } else {
                    modalBody.innerHTML = '<div class="braves-history-chat-empty">' + '<?php echo esc_js(__('No hay datos de historial.', 'braves-chat')); ?>' + '</div>';
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
