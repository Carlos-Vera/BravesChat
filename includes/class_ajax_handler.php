<?php
/**
 * Manejador de peticiones AJAX
 *
 * Hace de proxy entre el frontend y el webhook N8N,
 * añadiendo el token de autenticación en el servidor.
 * El token nunca se expone al navegador del visitante.
 *
 * @package BravesChat
 * @since 2.3.0
 */

namespace BravesChat;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Ajax_Handler
 *
 * @since 2.3.0
 */
class Ajax_Handler {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_braves_chat_send_message',        array($this, 'handle_message'));
        add_action('wp_ajax_nopriv_braves_chat_send_message', array($this, 'handle_message'));
    }

    /**
     * Recibe el mensaje del frontend, lo reenvía al webhook de N8N
     * con el token de autenticación añadido en el servidor y devuelve
     * la respuesta al navegador.
     *
     * @since 2.3.0
     * @return void
     */
    public function handle_message() {
        // Verificar nonce
        check_ajax_referer('braves_chat_nonce', 'nonce');

        // Sanitizar input
        $chat_input = isset($_POST['chatInput'])
            ? sanitize_text_field(wp_unslash($_POST['chatInput']))
            : '';
        $session_id = isset($_POST['sessionId'])
            ? sanitize_text_field(wp_unslash($_POST['sessionId']))
            : '';

        if (empty($chat_input)) {
            wp_send_json_error(array('message' => __('El mensaje no puede estar vacío.', 'braves-chat')));
        }

        // Obtener configuración desde el servidor (nunca desde el cliente)
        $webhook_url = get_option('braves_chat_webhook_url', '');
        $auth_type   = sanitize_text_field(get_option('braves_chat_n8n_auth_type', 'header'));
        $auth_header = sanitize_text_field(get_option('braves_chat_n8n_auth_header', 'X-N8N-Auth'));
        $auth_token  = get_option('braves_chat_n8n_auth_token', '');

        if (empty($webhook_url)) {
            wp_send_json_error(array('message' => __('El webhook de N8N no está configurado.', 'braves-chat')));
        }

        // Construir cabeceras según el tipo de autenticación configurado
        $headers = array('Content-Type' => 'application/json');

        if ('basic' === $auth_type && !empty($auth_header) && !empty($auth_token)) {
            // Basic Auth: Authorization: Basic base64(usuario:contraseña)
            $headers['Authorization'] = 'Basic ' . base64_encode($auth_header . ':' . $auth_token);
        } elseif ('header' === $auth_type && !empty($auth_header) && !empty($auth_token)) {
            // Header personalizado: X-N8N-Auth: token
            $headers[ $auth_header ] = $auth_token;
        }
        // 'none': sin cabecera de autenticación

        // Reenviar al webhook con el mismo payload que espera N8N Chat Trigger
        $response = wp_remote_post(
            esc_url_raw($webhook_url),
            array(
                'headers' => $headers,
                'body'    => wp_json_encode(array(
                    'chatInput' => $chat_input,
                    'sessionId' => $session_id,
                )),
                'timeout' => 60,
            )
        );

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body      = wp_remote_retrieve_body($response);
        $data      = json_decode($body, true);

        if ($http_code < 200 || $http_code >= 300) {
            wp_send_json_error(array(
                'message' => sprintf(
                    /* translators: %d: HTTP response code */
                    __('Error del webhook (HTTP %d).', 'braves-chat'),
                    $http_code
                ),
            ));
        }

        if (null === $data) {
            // Puede ser NDJSON (streaming de N8N): una línea JSON por token.
            // Intentar ensamblar el mensaje concatenando los campos "content"
            // de cada evento de tipo "item".
            $assembled = $this->parse_ndjson($body);
            if (null !== $assembled) {
                wp_send_json_success(array('output' => $assembled));
            }

            // Formato desconocido — devolver como string para que el JS lo muestre
            wp_send_json_success(array('output' => $body));
        }

        wp_send_json_success($data);
    }

    /**
     * Parsea una respuesta NDJSON de N8N y ensambla el mensaje completo.
     *
     * N8N puede devolver el texto del agente como un stream de objetos JSON,
     * uno por línea, donde cada evento tiene la forma:
     *   {"type":"item","content":"token","metadata":{...}}
     *
     * Este método concatena todos los campos "content" de eventos tipo "item"
     * y devuelve el mensaje completo como string. Devuelve null si el body
     * no tiene ningún evento "item" reconocible.
     *
     * @since 2.3.0
     * @param string $body Cuerpo de la respuesta de N8N.
     * @return string|null Mensaje ensamblado, o null si no se pudo parsear.
     */
    private function parse_ndjson($body) {
        $lines     = explode("\n", trim($body));
        $assembled = '';
        $found     = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ('' === $line) {
                continue;
            }

            $event = json_decode($line, true);
            if (!is_array($event)) {
                continue;
            }

            if (isset($event['type']) && 'item' === $event['type'] && isset($event['content'])) {
                $assembled .= $event['content'];
                $found      = true;
            }
        }

        return $found ? $assembled : null;
    }
}
