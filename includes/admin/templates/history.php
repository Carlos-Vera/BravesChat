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
    wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'braveschat'));
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables, not true globals.
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
            // translators: %d is the HTTP error code number.
            $fetch_error = sprintf(__('Error HTTP %d al conectar con el servidor.', 'braveschat'), $response_code);
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
                $fetch_error = __('El webhook respondió con cuerpo vacío. Verifica que el flujo de N8N esté activo y retornando datos.', 'braveschat');
            } else {
                $fetch_error = __('La respuesta del webhook no es un JSON válido.', 'braveschat');
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

        // Preservar timestamp individual del mensaje para mostrarlo en el modal
        $msg['_ts'] = isset($row['updated_at']) ? (string)$row['updated_at'] : (isset($row['update_at']) ? (string)$row['update_at'] : '');

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
                    <h1 class="braves-page-title"><strong><?php esc_html_e('Historial', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Conversaciones registradas con tu agente de IA.', 'braveschat'); ?>
                    </p>
                </div>

                <!-- Configuration Status Section -->
                <?php if (!$config_status['is_configured']): ?>
                <div class="notice notice-warning inline">
                    <p>
                        <?php esc_html_e('¡Casi lo tienes! Conecta la URL del Webhook en Ajustes para que tu agente empiece a trabajar.', 'braveschat'); ?>
                    </p>
                </div>
                <?php endif; ?>

                <?php if (empty($stats_webhook_url)): ?>
                    <div class="notice notice-warning inline">
                        <p>
                            <strong><?php esc_html_e('Webhook no configurado:', 'braveschat'); ?></strong>
                            <?php
                            echo wp_kses_post( sprintf(
                                /* translators: %s is the URL to the plugin settings page. */
                                __('Configura la URL del webhook de historial en <a href="%s">Ajustes</a>.', 'braveschat'),
                                esc_url(admin_url('admin.php?page=braveschat-settings'))
                            ) ); ?>
                        </p>
                    </div>
                <?php elseif (!empty($fetch_error)): ?>
                    <div class="notice notice-error inline">
                        <p>
                            <strong><?php esc_html_e('No hay conversaciones registradas o hay un error de conexión.', 'braveschat'); ?></strong> 
                            <br><?php echo esc_html($fetch_error); ?>
                        </p>
                    </div>
                <?php elseif (empty($sessions)): ?>
                    <div class="notice notice-info inline">
                        <p>
                            <?php esc_html_e('No hay conversaciones registradas o hay un error de conexión.', 'braveschat'); ?>
                        </p>
                    </div>
                <?php else: ?>

                    <div class="braves-bento-card" style="margin-top: 20px;">
                        
                        <!-- Actions -->
                        <div class="braves-bento-header">
                            <h3>
                                <?php
                                echo esc_html( sprintf(
                                    // translators: %d is the number of conversations found.
                                    _n('%d conversación encontrada', '%d conversaciones encontradas', count($sessions), 'braveschat'),
                                    count($sessions)
                                ) ); ?>
                            </h3>
                            <button type="button" id="braves-history-export-csv" class="button button-primary braves-btn">
                                <?php esc_html_e('Descargar CSV', 'braveschat'); ?>
                            </button>
                        </div>
                        

                        <!-- Tabla Bentō -->
                        <table id="braves-history-table" class="braves-history-table">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 25%;"><?php esc_html_e('Contacto', 'braveschat'); ?></th>
                                    <th scope="col" style="width: 55%;"><?php esc_html_e('Extracto', 'braveschat'); ?></th>
                                    <th scope="col" style="width: 20%;"><?php esc_html_e('Fecha', 'braveschat'); ?></th>
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
                                    $snippet = __('(Sin mensajes)', 'braveschat');
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
                                            <div class="braves-history-table__contact-name"><?php echo esc_html($client_name); ?></div>
                                        <?php else: ?>
                                            <em><?php esc_html_e('Desconocido', 'braveschat'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="braves-history-table__snippet"><?php echo esc_html($snippet); ?></span>
                                    </td>
                                    <td>
                                        <div class="braves-history-table__date">
                                            <span class="dashicons dashicons-clock braves-history-table__date-icon"></span>
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
    <div class="braves-history-modal-wrapper">
        <button type="button" id="braves-history-modal-close" class="braves-history-modal__close" aria-label="<?php esc_attr_e('Cerrar', 'braveschat'); ?>">&times;</button>
        <div class="braves-history-modal" role="dialog" aria-modal="true" aria-labelledby="braves-modal-title">
            <div class="braves-history-modal__header">
                <div>
                    <h3 id="braves-modal-title" class="braves-history-modal__title"><?php esc_html_e('Conversación del Chat', 'braveschat'); ?></h3>
                    <p id="braves-modal-subtitle" class="braves-history-modal__subtitle"></p>
                </div>
            </div>
            <div id="braves-history-modal-body" class="braves-history-modal__body">
                <!-- Chat messages will be dynamically injected here by JS -->
            </div>
        </div>
    </div>
</div>

