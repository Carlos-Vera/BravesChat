/**
 * BravesChat Redirect Handler
 *
 * Gestiona el parseo de respuestas JSON, redirecciones y acciones personalizadas.
 * Permite que el chat controle la navegación y el comportamiento del sitio.
 *
 * @package BravesChat
 * @since 1.1.0
 */

/**
 * Clase BravesRedirectHandler
 *
 * Procesa respuestas del webhook para extraer mensajes, redirecciones y acciones.
 * Ejecuta la lógica de redirección con retrasos configurables.
 */
class BravesRedirectHandler {
    /**
     * Constructor de RedirectHandler
     * @param {Object} config - Configuración del handler
     * @param {Function} config.on_message_callback - Callback para agregar mensajes al chat
     * @param {Function} config.on_error_callback - Callback para manejar errores
     */
    constructor(config) {
        this.on_message_callback = config.on_message_callback;
        this.on_error_callback = config.on_error_callback;

        // Claves de localStorage (cambiado de sessionStorage para persistencia permanente)
        this.STORAGE_KEY_CONVERSATION_PREFIX = 'braves_chat_conversation_'; // Prefijo + session_id
        this.STORAGE_KEY_REDIRECT_PENDING = 'braves_chat_redirect_pending';

        // Delay por defecto para redirecciones (3 segundos)
        this.DEFAULT_REDIRECT_DELAY = 3000;

        console.log('[BravesRedirectHandler] Inicializado');
    }

    /**
     * Obtén la clave de conversación para un session_id específico
     * @param {string} session_id - ID de la sesión  
     * @returns {string} Clave de localStorage
     */
    get_conversation_key(session_id) {
        return this.STORAGE_KEY_CONVERSATION_PREFIX + session_id;
    }

    /**
     * Procesa la respuesta del webhook
     * Intenta parsear JSON y ejecutar acciones correspondientes
     * @param {string} response_text - Respuesta en texto del webhook
     * @returns {Object} Objeto con mensaje procesado y metadata
     */
    parse_response(response_text) {
        const result = {
            message: '',
            has_redirect: false,
            redirect_url: '',
            redirect_delay: 0,
            has_action: false,
            action_type: '',
            action_data: {}
        };

        try {
            // Intentar parsear JSON
            const data = JSON.parse(response_text);
            console.log('[BravesRedirectHandler] JSON parseado correctamente:', data);

            // Extraer mensaje
            if (data.message || data.text || data.output || data.response || data.content) {
                result.message = data.message || data.text || data.output || data.response || data.content;
            } else if (data.data) {
                // Manejar casos con data.data
                if (typeof data.data === 'string') {
                    result.message = data.data;
                } else if (data.data.message || data.data.text) {
                    result.message = data.data.message || data.data.text;
                }
            } else {
                // No message found, usar JSON stringificado
                result.message = JSON.stringify(data);
            }

            // Extraer redirección
            if (data.redirect_url && data.redirect_url.trim() !== '') {
                result.has_redirect = true;
                result.redirect_url = data.redirect_url.trim();
                result.redirect_delay = parseInt(data.redirect_delay) || 0;
            }

            // Extraer acción
            if (data.action && typeof data.action === 'object') {
                result.has_action = true;
                result.action_type = data.action.type || '';
                result.action_data = data.action.data || {};
            }

            console.log('[BravesRedirectHandler] Respuesta procesada:', result);
        } catch (json_error) {
            // JSON inválido - usar respuesta como texto simple
            console.warn('[BravesRedirectHandler]JSON inválido, usando texto plano:', json_error.message);
            result.message = response_text;
        }

        return result;
    }

    /**
     * Guarda el estado de la conversación en localStorage (CAMBIADO DE sessionStorage)
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

            const key = this.get_conversation_key(session_id);
            localStorage.setItem(key, JSON.stringify(state)); // ✅ localStorage en lugar de sessionStorage
            console.log('[BravesRedirectHandler] Estado guardado en localStorage:', state);
        } catch (storage_error) {
            console.error('[BravesRedirectHandler] Error guardando estado:', storage_error);
        }
    }

    /**
     * Recupera el estado de la conversación desde localStorage (CAMBIADO DE sessionStorage)
     * @param {string} session_id - ID de la sesión actual
     * @returns {Object|null} Estado recuperado o null si no existe
     */
    restore_conversation_state(session_id) {
        try {
            const key = this.get_conversation_key(session_id);
            const state_json = localStorage.getItem(key);  // ✅ localStorage en lugar de sessionStorage

            if (!state_json) {
                console.log('[BravesRedirectHandler] No hay estado pendiente para session_id:', session_id);
                return null;
            }

            const state = JSON.parse(state_json);
            console.log('[BravesRedirectHandler] Estado recuperado de localStorage:', state);

            // NO limpiamos después de recuperar - queremos que persista
            // sessionStorage.removeItem(key); ❌ REMOVIDO

            return state;
        } catch (restore_error) {
            console.error('[BravesRedirectHandler] Error recuperando estado:', restore_error);
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
        console.log(`[BravesRedirectHandler] Redirección programada a "${url}" en ${delay}ms`);

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
            console.log('[BravesRedirectHandler] Ejecutando redirección a:', url);
            window.location.href = url;

            // Disparar evento
            const event = new CustomEvent('braves_chat_redirect_executed', {
                detail: { url: url }
            });
            document.dispatchEvent(event);
        }, delay);
    }

    /**
     * Ejecuta una acción custom
     * @param {string} action_type - Tipo de acción (open_modal, trigger_event, etc)
     * @param {Object} action_data - Datos para la acción
     * @returns {void}
     */
    execute_custom_action(action_type, action_data) {
        console.log('[BravesRedirectHandler] Ejecutando acción custom:', action_type, action_data);

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
                    console.warn(`[BravesRedirectHandler] Acción desconocida: "${action_type}"`);

                    // Disparar evento custom para que el desarrollador pueda manejarlo
                    const custom_event = new CustomEvent('braves_chat_custom_action', {
                        detail: {
                            action: action_type,
                            data: action_data
                        }
                    });
                    document.dispatchEvent(custom_event);
            }

            // Disparar evento genérico de acción ejecutada
            const event = new CustomEvent('braves_chat_action_executed', {
                detail: { type: action_type, data: action_data }
            });
            document.dispatchEvent(event);

        } catch (action_error) {
            console.error('[BravesRedirectHandler] Error ejecutando acción:', action_error);
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
            'braves_modal',
            `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
        );

        console.log('[BravesRedirectHandler] Modal abierto:', url);
    }

    /**
     * Acción: Disparar evento custom en el DOM
     * @param {Object} data - {event_name: string, detail?: Object}
     * @returns {void}
     */
    action_trigger_event(data) {
        const event_name = data?.event_name || 'braves_custom_event';
        const event_detail = data?.detail || {};

        const event = new CustomEvent(event_name, { detail: event_detail });
        document.dispatchEvent(event);

        console.log('[BravesRedirectHandler] Evento disparado:', event_name, event_detail);
    }

    /**
     * Acción: Cerrar el chat
     * @param {Object} data - Datos opcionales
     * @returns {void}
     */
    action_close_chat(data) {
        const event = new CustomEvent('braves_chat_close_requested', { detail: data });
        document.dispatchEvent(event);

        console.log('[BravesRedirectHandler] Solicitud de cierre del chat');
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

        console.log(`[BravesRedirectHandler] Página se recargará en ${delay}ms`);
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
            console.warn('[BravesRedirectHandler] scroll_to requiere un selector');
            return;
        }

        const element = document.querySelector(selector);

        if (element) {
            element.scrollIntoView({ behavior: behavior, block: 'start' });
            console.log('[BravesRedirectHandler] Scroll a:', selector);
        } else {
            console.warn(`[BravesRedirectHandler] Elemento no encontrado: ${selector}`);
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
            console.log('[BravesRedirectHandler] Se completó un redirect pendiente');
        }

        return was_pending;
    }
}

// Exportar para uso global
window.BravesRedirectHandler = BravesRedirectHandler;
