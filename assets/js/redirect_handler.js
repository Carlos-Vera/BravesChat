/**
 * Wland Chat iA - Redirect Handler
 * Version: 1.0.0
 * FUNCIONALIDAD: Parser JSON y sistema de redirecciones con contexto
 * CONVENCIONES: snake_case y JSDoc
 * PATRÓN: Clase reutilizable para Modal y Screen
 */

/**
 * Clase para manejar respuestas JSON del webhook con soporte para redirecciones
 * y acciones custom manteniendo el contexto de la conversación.
 *
 * Estructura esperada del JSON:
 * {
 *   message: string,          // Mensaje a mostrar (requerido)
 *   redirect?: string,         // URL de redirección (opcional)
 *   delay?: number,           // Milisegundos de espera antes del redirect (default: 3000)
 *   action?: string,          // Acción custom a ejecutar (opcional)
 *   data?: object             // Datos adicionales para la acción (opcional)
 * }
 */
class WlandRedirectHandler {
    /**
     * Constructor de RedirectHandler
     * @param {Object} config - Configuración del handler
     * @param {Function} config.on_message_callback - Callback para agregar mensajes al chat
     * @param {Function} config.on_error_callback - Callback para manejar errores
     */
    constructor(config) {
        this.on_message_callback = config.on_message_callback;
        this.on_error_callback = config.on_error_callback;

        // Claves de sessionStorage
        this.STORAGE_KEY_CONVERSATION = 'wland_chat_conversation_state';
        this.STORAGE_KEY_REDIRECT_PENDING = 'wland_chat_redirect_pending';

        // Delay por defecto para redirecciones (3 segundos)
        this.DEFAULT_REDIRECT_DELAY = 3000;

        console.log('[Wland Redirect Handler] Inicializado');
    }

    /**
     * Procesa la respuesta del webhook
     * Intenta parsear JSON y ejecutar acciones correspondientes
     * @param {string} response_text - Respuesta en texto del webhook
     * @returns {Object} Objeto con mensaje procesado y metadata
     */
    parse_response(response_text) {
        try {
            // Intentar parsear JSON
            const data = JSON.parse(response_text);
            console.log('[Wland Redirect Handler] JSON parseado correctamente:', data);

            // Validar estructura mínima
            if (!data.message || typeof data.message !== 'string') {
                console.warn('[Wland Redirect Handler] JSON válido pero sin campo "message", usando fallback');
                return {
                    message: response_text,
                    is_json: false,
                    has_redirect: false,
                    has_action: false
                };
            }

            // Extraer campos del JSON
            const result = {
                message: data.message,
                is_json: true,
                has_redirect: !!(data.redirect && typeof data.redirect === 'string'),
                has_action: !!(data.action && typeof data.action === 'string'),
                redirect_url: data.redirect || null,
                redirect_delay: data.delay || this.DEFAULT_REDIRECT_DELAY,
                action_type: data.action || null,
                action_data: data.data || null
            };

            console.log('[Wland Redirect Handler] Respuesta procesada:', result);
            return result;

        } catch (json_error) {
            // JSON inválido - usar respuesta como texto simple
            console.warn('[Wland Redirect Handler] JSON inválido, usando texto plano:', json_error.message);
            return {
                message: response_text,
                is_json: false,
                has_redirect: false,
                has_action: false
            };
        }
    }

    /**
     * Guarda el estado de la conversación en sessionStorage
     * @param {Array} conversation_history - Historial de mensajes
     * @param {string} session_id - ID de la sesión actual
     * @returns {void}
     */
    save_conversation_state(conversation_history, session_id) {
        try {
            const state = {
                history: conversation_history,
                session_id: session_id,
                timestamp: Date.now(),
                url: window.location.href
            };

            sessionStorage.setItem(this.STORAGE_KEY_CONVERSATION, JSON.stringify(state));
            console.log('[Wland Redirect Handler] Estado guardado:', state);
        } catch (storage_error) {
            console.error('[Wland Redirect Handler] Error guardando estado:', storage_error);
        }
    }

    /**
     * Recupera el estado de la conversación desde sessionStorage
     * @returns {Object|null} Estado recuperado o null si no existe
     */
    restore_conversation_state() {
        try {
            const state_json = sessionStorage.getItem(this.STORAGE_KEY_CONVERSATION);

            if (!state_json) {
                console.log('[Wland Redirect Handler] No hay estado pendiente de recuperación');
                return null;
            }

            const state = JSON.parse(state_json);
            console.log('[Wland Redirect Handler] Estado recuperado:', state);

            // Limpiar después de recuperar
            sessionStorage.removeItem(this.STORAGE_KEY_CONVERSATION);

            return state;
        } catch (restore_error) {
            console.error('[Wland Redirect Handler] Error recuperando estado:', restore_error);
            return null;
        }
    }

    /**
     * Ejecuta una redirección con delay
     * @param {string} url - URL de destino
     * @param {number} delay - Milisegundos de espera
     * @param {Array} conversation_history - Historial a guardar
     * @param {string} session_id - ID de sesión
     * @returns {void}
     */
    execute_redirect(url, delay, conversation_history, session_id) {
        console.log(`[Wland Redirect Handler] Redirección programada a "${url}" en ${delay}ms`);

        // Guardar estado antes de redirigir
        this.save_conversation_state(conversation_history, session_id);

        // Marcar que hay un redirect pendiente
        sessionStorage.setItem(this.STORAGE_KEY_REDIRECT_PENDING, 'true');

        // Mostrar mensaje al usuario
        const seconds = Math.ceil(delay / 1000);
        const redirect_message = `\n\n🔄 Redirigiendo en ${seconds} segundo${seconds !== 1 ? 's' : ''}...`;

        if (this.on_message_callback) {
            // Agregar el mensaje de redirección al último mensaje
            this.on_message_callback(redirect_message, 'bot', true);
        }

        // Ejecutar redirección después del delay
        setTimeout(() => {
            console.log('[Wland Redirect Handler] Ejecutando redirección a:', url);
            window.location.href = url;
        }, delay);
    }

    /**
     * Ejecuta una acción custom
     * @param {string} action_type - Tipo de acción (open_modal, trigger_event, etc)
     * @param {Object} action_data - Datos para la acción
     * @returns {void}
     */
    execute_custom_action(action_type, action_data) {
        console.log('[Wland Redirect Handler] Ejecutando acción custom:', action_type, action_data);

        try {
            switch (action_type) {
                case 'open_modal':
                    this.action_open_modal(action_data);
                    break;

                case 'trigger_event':
                    this.action_trigger_event(action_data);
                    break;

                case 'close_chat':
                    this.action_close_chat(action_data);
                    break;

                case 'reload_page':
                    this.action_reload_page(action_data);
                    break;

                case 'scroll_to':
                    this.action_scroll_to(action_data);
                    break;

                default:
                    console.warn(`[Wland Redirect Handler] Acción desconocida: "${action_type}"`);

                    // Disparar evento custom para que el desarrollador pueda manejarlo
                    const custom_event = new CustomEvent('wland_chat_custom_action', {
                        detail: {
                            action: action_type,
                            data: action_data
                        }
                    });
                    document.dispatchEvent(custom_event);
            }
        } catch (action_error) {
            console.error('[Wland Redirect Handler] Error ejecutando acción:', action_error);
            if (this.on_error_callback) {
                this.on_error_callback(`Error ejecutando acción "${action_type}": ${action_error.message}`);
            }
        }
    }

    /**
     * Acción: Abrir modal/popup
     * @param {Object} data - {url: string, width?: number, height?: number}
     * @returns {void}
     */
    action_open_modal(data) {
        const url = data?.url || '#';
        const width = data?.width || 600;
        const height = data?.height || 400;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;

        window.open(
            url,
            'wland_modal',
            `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
        );

        console.log('[Wland Redirect Handler] Modal abierto:', url);
    }

    /**
     * Acción: Disparar evento custom en el DOM
     * @param {Object} data - {event_name: string, detail?: Object}
     * @returns {void}
     */
    action_trigger_event(data) {
        const event_name = data?.event_name || 'wland_custom_event';
        const event_detail = data?.detail || {};

        const event = new CustomEvent(event_name, { detail: event_detail });
        document.dispatchEvent(event);

        console.log('[Wland Redirect Handler] Evento disparado:', event_name, event_detail);
    }

    /**
     * Acción: Cerrar el chat
     * @param {Object} data - Datos opcionales
     * @returns {void}
     */
    action_close_chat(data) {
        const event = new CustomEvent('wland_chat_close_requested', { detail: data });
        document.dispatchEvent(event);

        console.log('[Wland Redirect Handler] Solicitud de cierre del chat');
    }

    /**
     * Acción: Recargar página
     * @param {Object} data - {delay?: number}
     * @returns {void}
     */
    action_reload_page(data) {
        const delay = data?.delay || 1000;

        setTimeout(() => {
            window.location.reload();
        }, delay);

        console.log(`[Wland Redirect Handler] Página se recargará en ${delay}ms`);
    }

    /**
     * Acción: Hacer scroll a elemento
     * @param {Object} data - {selector: string, behavior?: string}
     * @returns {void}
     */
    action_scroll_to(data) {
        const selector = data?.selector;
        const behavior = data?.behavior || 'smooth';

        if (!selector) {
            console.warn('[Wland Redirect Handler] scroll_to requiere un selector');
            return;
        }

        const element = document.querySelector(selector);

        if (element) {
            element.scrollIntoView({ behavior: behavior, block: 'start' });
            console.log('[Wland Redirect Handler] Scroll a:', selector);
        } else {
            console.warn(`[Wland Redirect Handler] Elemento no encontrado: ${selector}`);
        }
    }

    /**
     * Verifica si hay un redirect pendiente al cargar la página
     * @returns {boolean} True si había un redirect pendiente
     */
    check_pending_redirect() {
        const was_pending = sessionStorage.getItem(this.STORAGE_KEY_REDIRECT_PENDING) === 'true';

        if (was_pending) {
            sessionStorage.removeItem(this.STORAGE_KEY_REDIRECT_PENDING);
            console.log('[Wland Redirect Handler] Se completó un redirect pendiente');
        }

        return was_pending;
    }
}

// Exportar para uso global
window.WlandRedirectHandler = WlandRedirectHandler;
