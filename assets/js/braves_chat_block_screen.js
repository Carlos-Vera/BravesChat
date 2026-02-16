/**
 * BravesChat - Fullscreen Mode
 * Version: 1.0.0
 * MODIFICADO: Implementada autenticación N8N con header X-N8N-Auth
 * REFACTORIZADO: snake_case y JSDoc
 * REFACTORIZADO: Añadido soporte i18n con wp.i18n
 */

// Importar funciones de traducción de WordPress con fallback
const { __, _x, _n, sprintf } = window.wp && window.wp.i18n ? window.wp.i18n : {
    __: (text) => text,
    _x: (text) => text,
    _n: (single, plural, number) => number === 1 ? single : plural,
    sprintf: (format, ...args) => format
};

class BravesChatScreen {
    /**
     * Constructor de la clase BravesChatScreen
     * Inicializa las propiedades y llama a init()
     */
    constructor() {
        this.is_open = false;

        this.conversation_history = [];
        this.session_id = null; // Se inicializará de forma asíncrona

        // Obtener configuración desde PHP (BravesChatConfig es pasado desde class_frontend.php)
        this.webhook_url = window.BravesChatConfig?.webhook_url || '';
        this.auth_token = window.BravesChatConfig?.auth_token || ''; // Token de autenticación
        this.is_available = window.BravesChatConfig?.isAvailable !== undefined ? window.BravesChatConfig.isAvailable : true;

        // Inicializar RedirectHandler
        this.redirect_handler = new BravesRedirectHandler({
            on_message_callback: (text, type, append) => this.add_message(text, type, append),
            on_error_callback: (error_msg) => this.add_message(error_msg, 'bot')
        });

        // Propiedades para streaming visual
        this.streaming_active = false;
        this.streaming_timeout_id = null;
        this.current_stream_message_element = null;

        // Inicializar session_id de forma asíncrona
        this.generate_session_id().then(session_id => {
            this.session_id = session_id;
            // Verificar si hay conversación pendiente de recuperación
            this.check_and_restore_conversation();
        });

        this.init();
    }

    /**
     * Inicializa el chat en modo fullscreen
     * Configura elementos del DOM, animaciones y event listeners
     * @returns {void}
     */
    init() {
        console.log('Inicializando BravesChat Fullscreen...');
        console.log('BravesChatConfig completo:', window.BravesChatConfig);
        console.log('Webhook URL:', this.webhook_url);
        console.log('Auth Token:', this.auth_token ? 'Configurado' : 'No configurado');
        console.log('Auth Token value:', this.auth_token);

        // Elementos del DOM
        this.chat_container = document.getElementById('braveslab-chat-container');
        this.chat_toggle = document.getElementById('chat-toggle');
        this.chat_window = document.getElementById('chat-window');
        this.close_button = document.getElementById('close-chat');
        this.chat_messages = document.getElementById('chat-messages');
        this.chat_input = document.getElementById('chat-input');
        this.send_button = document.getElementById('send-button');
        this.typing_indicator = document.getElementById('typing-indicator');
        this.chat_icon = document.getElementById('chat-icon');

        if (!this.chat_container) {
            console.error('No se encontró el contenedor del chat');
            return;
        }

        // Mostrar hora del mensaje de bienvenida
        this.display_welcome_time();

        // Event Listeners
        this.setup_event_listeners();

        // Listener para acción close_chat desde RedirectHandler
        document.addEventListener('braves_chat_close_requested', () => {
            this.close_window();
        });

        console.log('Chat Fullscreen inicializado correctamente');
    }

    /**
     * Configura todos los event listeners del chat
     * @returns {void}
     */
    setup_event_listeners() {
        if (this.chat_toggle) {
            this.chat_toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle_chat();
            });
        }

        if (this.close_button) {
            this.close_button.addEventListener('click', (e) => {
                e.stopPropagation();
                this.close_window();
            });
        }

        if (this.chat_input) {
            // Manejo de Enter: Enter envía, Shift+Enter nueva línea
            this.chat_input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    if (e.shiftKey) {
                        // Shift+Enter: permitir nueva línea (comportamiento default)
                        // Ajustar altura después de que el texto se inserte
                        setTimeout(() => this.auto_resize_textarea(this.chat_input), 0);
                    } else {
                        // Enter solo: enviar mensaje
                        e.preventDefault();
                        this.send_message();
                    }
                }
            });

            // Auto-resize durante escritura
            ['input', 'paste'].forEach(event => {
                this.chat_input.addEventListener(event, () => {
                    this.auto_resize_textarea(this.chat_input);
                    this.toggle_send_button();
                });
            });

            // Ejecutar auto-resize inicial
            this.auto_resize_textarea(this.chat_input);
        }

        if (this.send_button) {
            this.send_button.addEventListener('click', () => {
                this.send_message();
            });
        }
    }


    /**
     * Verifica si hay una conversación pendiente y la restaura
     * @returns {void}
     */
    check_and_restore_conversation() {
        // ✅ Pasar session_id para obtener el historial correcto de localStorage
        const restored_state = this.redirect_handler.restore_conversation_state(this.session_id);

        if (restored_state) {
            console.log('[BravesChat Screen] Restaurando conversación:', restored_state);

            // Restaurar historial
            this.conversation_history = restored_state.history || [];

            // Restaurar mensajes en el DOM
            this.conversation_history.forEach(msg => {
                const type = msg.role === 'user' ? 'user' : 'bot';
                this.add_message(msg.content, type, false);
            });

            // ❌ NO abrir el chat automáticamente - debe permanecer cerrado
            // El usuario lo abrirá manualmente si quiere continuar la conversación
            // if (this.conversation_history.length > 0) {
            //     this.open_window();
            // }

            console.log(`✅ Conversación restaurada (${this.conversation_history.length} mensajes) - chat permanece cerrado`);
        }
    }

    /**
     * Genera un ID único para la sesión del chat usando fingerprinting
     * Intenta obtener el session_id del sistema de cookies con fingerprinting.
     * Si no está disponible, genera uno temporal.
     * @returns {Promise<string>} ID de sesión único
     */
    async generate_session_id() {
        // Intentar obtener session_id del sistema de fingerprinting
        if (window.bravesFingerprint) {
            // Esperar a que el fingerprinting se complete si está en proceso
            if (typeof window.bravesFingerprint.get_or_create_session === 'function') {
                try {
                    const fingerprint_session = await window.bravesFingerprint.get_or_create_session();
                    if (fingerprint_session) {
                        console.log('[BravesChat Screen] Usando session_id con fingerprinting:', fingerprint_session);
                        return fingerprint_session;
                    }
                } catch (error) {
                    console.error('[BravesChat Screen] Error obteniendo fingerprint:', error);
                }
            }
        }

        // Fallback: generar ID temporal si el sistema de fingerprinting no está disponible
        const temp_session = 'temp_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
        console.warn('[BravesChat Screen] Sistema de fingerprinting no disponible, usando session_id temporal:', temp_session);
        return temp_session;
    }

    /**
     * Muestra la hora actual en el mensaje de bienvenida
     * @returns {void}
     */
    display_welcome_time() {
        const welcome_time = document.getElementById('welcome-time');
        if (welcome_time) {
            const now = new Date();
            welcome_time.textContent = now.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }

    /**
     * Alterna entre abrir y cerrar el chat
     * @returns {void}
     */
    toggle_chat() {
        if (this.is_open) {
            this.close_window();
        } else {
            this.open_window();
        }
    }

    /**
     * Abre la ventana del chat en modo fullscreen
     * Bloquea el scroll del body
     * @returns {void}
     */
    open_window() {
        this.chat_container.classList.remove('chat-closed');
        this.chat_container.classList.add('chat-open');
        this.is_open = true;

        // Bloquear scroll del body en modo fullscreen
        document.body.style.overflow = 'hidden';

        // Focus en el input
        setTimeout(() => {
            this.chat_input.focus();
        }, 300);
    }

    /**
     * Cierra la ventana del chat
     * Restaura el scroll del body
     * @returns {void}
     */
    close_window() {
        this.chat_container.classList.remove('chat-open');
        this.chat_container.classList.add('chat-closed');
        this.is_open = false;

        // Restaurar scroll del body
        document.body.style.overflow = '';
    }

    /**
     * Habilita o deshabilita el botón de enviar según el contenido del input
     * @returns {void}
     */
    toggle_send_button() {
        const has_text = this.chat_input.value.trim().length > 0;
        this.send_button.disabled = !has_text;
    }

    /**
     * Ajusta automáticamente la altura del textarea según su contenido
     * @param {HTMLTextAreaElement} textarea - El elemento textarea a ajustar
     * @returns {void}
     */
    auto_resize_textarea(textarea) {
        if (!textarea) return;

        // Reset height to minimum to get correct scrollHeight
        textarea.style.height = 'auto';

        // Calculate new height based on scrollHeight
        const newHeight = textarea.scrollHeight;

        // Apply new height (CSS max-height will limit it)
        textarea.style.height = newHeight + 'px';
    }

    /**
     * Envía un mensaje al webhook de N8N con autenticación
     * Gestiona el historial de conversación y muestra respuestas
     * @async
     * @returns {Promise<void>}
     */
    async send_message() {
        const message = this.chat_input.value.trim();

        if (!message) {
            console.warn('Mensaje vacío, no se envía');
            return;
        }

        // Cancelar streaming si está activo
        if (this.streaming_active) {
            this.cancel_streaming();
        }

        console.log('Enviando mensaje:', message);

        // Agregar mensaje del usuario al chat
        this.add_message(message, 'user');
        this.chat_input.value = '';

        // Resetear altura del textarea después de enviar
        this.chat_input.style.height = 'auto';
        this.auto_resize_textarea(this.chat_input);

        this.toggle_send_button();

        // Guardar en historial
        this.conversation_history.push({
            role: 'user',
            content: message
        });

        // ✅ Guardar automáticamente después de cada mensaje del usuario
        this.redirect_handler.save_conversation_state(this.conversation_history, this.session_id);

        // Mostrar indicador de escritura
        this.show_typing_indicator();

        try {
            // Validar webhook URL
            if (!this.webhook_url || this.webhook_url.trim() === '') {
                throw new Error('WEBHOOK_NOT_CONFIGURED: La URL del webhook no está configurada en los ajustes del plugin.');
            }

            // Preparar headers con autenticación
            const headers = {
                'Content-Type': 'application/json',
            };

            // Solo añadir header de autenticación si existe el token
            if (this.auth_token && this.auth_token.trim() !== '') {
                headers['X-N8N-Auth'] = this.auth_token;
                console.log('Header de autenticación añadido');
            } else {
                console.log('No se añadió header de autenticación (token vacío)');
            }

            // Preparar payload para N8N Chat (espera chatInput)
            const payload = {
                chatInput: message,
                sessionId: this.session_id
            };

            console.log('Payload enviado:', payload);
            console.log('🌐 Enviando petición a:', this.webhook_url);
            console.log('Headers:', headers);

            // Enviar al webhook con autenticación
            const response = await fetch(this.webhook_url, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload),
                mode: 'cors'
            });

            console.log('Respuesta recibida:');
            console.log('   - Status:', response.status);
            console.log('   - Status Text:', response.statusText);
            console.log('   - Headers:', Object.fromEntries(response.headers.entries()));

            // Capturar el cuerpo de la respuesta antes de verificar
            let response_text = '';
            try {
                response_text = await response.text();
                console.log('   - Body (raw):', response_text);
            } catch (text_error) {
                console.error('Error al leer el cuerpo de la respuesta:', text_error);
            }

            if (!response.ok) {
                // Error del servidor - construir mensaje descriptivo
                let error_details = `ERROR HTTP ${response.status}: ${response.statusText}`;

                if (response.status === 401) {
                    error_details = 'ERROR 401 UNAUTHORIZED: Token de autenticación inválido o expirado.';
                } else if (response.status === 403) {
                    error_details = 'ERROR 403 FORBIDDEN: Acceso denegado. Verifica el token de autenticación.';
                } else if (response.status === 404) {
                    error_details = 'ERROR 404 NOT FOUND: La URL del webhook no existe.';
                } else if (response.status === 500) {
                    error_details = 'ERROR 500 INTERNAL SERVER ERROR: Error en el servidor N8N.';
                } else if (response.status === 502) {
                    error_details = 'ERROR 502 BAD GATEWAY: El servidor N8N no responde.';
                } else if (response.status === 503) {
                    error_details = 'ERROR 503 SERVICE UNAVAILABLE: El servidor N8N está temporalmente no disponible.';
                }

                console.error('❌', error_details);
                console.error('   Respuesta del servidor:', response_text);

                throw new Error(error_details + (response_text ? `\n\nRespuesta: ${response_text.substring(0, 200)}` : ''));
            }

            // Intentar parsear JSON (Single object or Streamed/NDJSON)
            let data;
            try {
                // First attempt: Standard JSON parse
                data = JSON.parse(response_text);
                console.log('JSON parseado correctamente (Single object):', data);
            } catch (json_error) {
                console.warn('JSON.parse falló, intentando parsear como Streamed/NDJSON...');

                // Fallback: Handle concatenated JSON objects (e.g. {"type":"begin"}...{"type":"item"})
                try {
                    // Extract all potential JSON objects using regex
                    // Matches { ... } blocks, non-greedy
                    const json_objects = [];
                    let match;
                    let brace_count = 0;
                    let start_index = -1;

                    for (let i = 0; i < response_text.length; i++) {
                        const char = response_text[i];
                        if (char === '{') {
                            if (brace_count === 0) start_index = i;
                            brace_count++;
                        } else if (char === '}') {
                            brace_count--;
                            if (brace_count === 0 && start_index !== -1) {
                                const json_str = response_text.substring(start_index, i + 1);
                                try {
                                    const obj = JSON.parse(json_str);
                                    json_objects.push(obj);
                                } catch (e) {
                                    // Ignore invalid segments
                                }
                                start_index = -1;
                            }
                        }
                    }

                    if (json_objects.length > 0) {
                        console.log(`Se encontraron ${json_objects.length} objetos JSON en la respuesta.`);

                        // Strategy: Concatenar TODOS los fragmentos con contenido
                        // Prioritize objects with 'content', 'output', 'text', 'message', 'response'

                        // Filter for relevant items if it's an N8N stream
                        const relevant_items = json_objects.filter(obj =>
                            obj.content || obj.output || obj.text || obj.message || obj.response ||
                            (obj.data && (typeof obj.data === 'string' || obj.data.text))
                        );

                        if (relevant_items.length > 0) {
                            // Concatenar TODOS los fragmentos de contenido
                            let full_content = '';
                            relevant_items.forEach(obj => {
                                if (obj.content) full_content += obj.content;
                                else if (obj.output) full_content += obj.output;
                                else if (obj.text) full_content += obj.text;
                                else if (obj.message) full_content += obj.message;
                                else if (obj.response) full_content += obj.response;
                                else if (obj.data && typeof obj.data === 'string') full_content += obj.data;
                                else if (obj.data && obj.data.text) full_content += obj.data.text;
                            });

                            console.log(`✅ Se concatenaron ${relevant_items.length} fragmentos de contenido`);
                            console.log(`📝 Longitud total: ${full_content.length} caracteres`);
                            console.log(`📄 Vista previa: ${full_content.substring(0, 150)}...`);

                            // Crear objeto con el contenido concatenado
                            // Preservar otros campos del último objeto (redirect, action, etc.)
                            data = {
                                ...relevant_items[relevant_items.length - 1],
                                content: full_content
                            };
                        } else {
                            // Fallback to the very last object if no specific content fields found
                            data = json_objects[json_objects.length - 1];
                        }

                        console.log('Objeto JSON final seleccionado:', data);
                    } else {
                        throw new Error('No se pudieron extraer objetos JSON válidos.');
                    }
                } catch (stream_error) {
                    console.error('Error al parsear Stream/NDJSON:', stream_error);
                    console.error('   Respuesta recibida:', response_text);
                    throw new Error(`JSON_PARSE_ERROR: La respuesta del servidor no es JSON válido.\n\nRespuesta: ${response_text.substring(0, 200)}`);
                }
            }

            // Ocultar indicador de escritura
            this.hide_typing_indicator();

            // Usar RedirectHandler para parsear la respuesta
            const parsed = this.redirect_handler.parse_response(typeof data === 'string' ? data : JSON.stringify(data));

            // Adaptarse a diferentes formatos de respuesta de N8N (fallback)
            let bot_message = parsed.message;

            // Si el parser no encontró mensaje, intentar formatos legacy y específicos de N8N Stream
            if (!bot_message || bot_message === response_text) {
                if (data.content) { // Common in N8N streams {"type":"item", "content":"..."}
                    bot_message = data.content;
                } else if (data.output) {
                    bot_message = data.output;
                } else if (data.response) {
                    bot_message = data.response;
                } else if (data.message) {
                    bot_message = data.message;
                } else if (data.text) {
                    bot_message = data.text;
                } else if (typeof data === 'string') {
                    bot_message = data;
                } else if (data.data) {
                    if (typeof data.data === 'string') {
                        bot_message = data.data;
                    } else if (data.data.text) {
                        bot_message = data.data.text;
                    }
                }
            }

            if (!bot_message) {
                console.error('No se encontró mensaje en la respuesta');
                console.error('   Estructura recibida:', data);
                console.error('   Tipo de dato:', typeof data);
                throw new Error(`RESPONSE_FORMAT_ERROR: No se encontró el mensaje en la respuesta.\n\nCampos disponibles: ${Object.keys(data).join(', ')}\n\nRespuesta completa: ${JSON.stringify(data).substring(0, 200)}`);
            }

            // Mostrar mensaje con efecto de streaming visual
            await this.stream_message(bot_message, 'bot', 300); // 300 palabras por minuto

            // Guardar en historial
            this.conversation_history.push({
                role: 'assistant',
                content: bot_message
            });

            // ✅ Guardar automáticamente después de cada respuesta del bot
            this.redirect_handler.save_conversation_state(this.conversation_history, this.session_id);

            console.log('Mensaje procesado correctamente');

            // Procesar redirecciones si existen
            if (parsed.has_redirect && parsed.redirect_url) {
                this.redirect_handler.execute_redirect(
                    parsed.redirect_url,
                    parsed.redirect_delay,
                    this.conversation_history,
                    this.session_id
                );
            }

            // Procesar acciones custom si existen
            if (parsed.has_action && parsed.action_type) {
                this.redirect_handler.execute_custom_action(
                    parsed.action_type,
                    parsed.action_data
                );
            }

        } catch (error) {
            console.error('ERROR COMPLETO:', error);
            console.error('   Stack:', error.stack);
            this.hide_typing_indicator();

            // Construir mensaje de error descriptivo
            let user_message = __('Error al procesar tu mensaje:', 'braves-chat') + '\n\n';
            let technical_details = '';

            if (error.message.includes('Failed to fetch')) {
                user_message += __('**No se pudo conectar con el servidor**', 'braves-chat') + '\n\n';
                user_message += __('Posibles causas:', 'braves-chat') + '\n';
                user_message += __('• Sin conexión a internet', 'braves-chat') + '\n';
                user_message += __('• El servidor N8N está caído', 'braves-chat') + '\n';
                user_message += __('• Problema de CORS', 'braves-chat') + '\n';
                user_message += __('• URL del webhook incorrecta', 'braves-chat') + '\n\n';
                technical_details = `URL: ${this.webhook_url}\nError: ${error.message}`;
            } else if (error.message.includes('WEBHOOK_NOT_CONFIGURED')) {
                user_message += __('**Webhook no configurado**', 'braves-chat') + '\n\n';
                user_message += __('El administrador debe configurar la URL del webhook en:', 'braves-chat') + '\n';
                user_message += __('WordPress Admin > Ajustes > BravesChat iA', 'braves-chat') + '\n\n';
                technical_details = error.message;
            } else if (error.message.includes('401') || error.message.includes('403')) {
                user_message += __('**Error de autenticación**', 'braves-chat') + '\n\n';
                user_message += __('El token de autenticación es inválido o ha expirado.', 'braves-chat') + '\n';
                user_message += __('Contacta al administrador para verificar el token N8N.', 'braves-chat') + '\n\n';
                technical_details = error.message;
            } else if (error.message.includes('404')) {
                user_message += __('**Webhook no encontrado**', 'braves-chat') + '\n\n';
                user_message += __('La URL del webhook no existe o es incorrecta.', 'braves-chat') + '\n';
                user_message += __('Verifica la URL en los ajustes del plugin.', 'braves-chat') + '\n\n';
                technical_details = `URL: ${this.webhook_url}\n${error.message}`;
            } else if (error.message.includes('JSON_PARSE_ERROR')) {
                user_message += __('**Respuesta inválida del servidor**', 'braves-chat') + '\n\n';
                user_message += __('El servidor N8N no devolvió un JSON válido.', 'braves-chat') + '\n';
                user_message += __('Verifica la configuración del workflow en N8N.', 'braves-chat') + '\n\n';
                technical_details = error.message;
            } else if (error.message.includes('RESPONSE_FORMAT_ERROR')) {
                user_message += __('**Formato de respuesta incorrecto**', 'braves-chat') + '\n\n';
                user_message += __('El servidor devolvió una respuesta pero sin el campo esperado.', 'braves-chat') + '\n';
                user_message += __('El webhook debe devolver: {output: "mensaje"} o {response: "mensaje"}', 'braves-chat') + '\n\n';
                technical_details = error.message;
            } else if (error.message.includes('500') || error.message.includes('502') || error.message.includes('503')) {
                user_message += __('**Error del servidor**', 'braves-chat') + '\n\n';
                user_message += __('El servidor N8N tiene un problema interno.', 'braves-chat') + '\n';
                user_message += __('Contacta al administrador del servidor.', 'braves-chat') + '\n\n';
                technical_details = error.message;
            } else {
                user_message += __('**Error desconocido**', 'braves-chat') + '\n\n';
                user_message += __('Ocurrió un error inesperado. Por favor, intenta de nuevo.', 'braves-chat') + '\n\n';
                technical_details = `${error.message}\n\nStack: ${error.stack}`;
            }

            user_message += __('**Detalles técnicos:**', 'braves-chat') + '\n';
            user_message += '```\n' + technical_details + '\n```\n\n';
            user_message += `${new Date().toLocaleString('es-ES')}`;

            this.add_message(user_message, 'bot');

            // Log adicional para el administrador
            console.log('📊 INFORMACIÓN DE DEBUG:');
            console.log('   - Webhook URL:', this.webhook_url);
            console.log('   - Auth Token configurado:', this.auth_token ? 'Sí' : 'No');
            console.log('   - Session ID:', this.session_id);
            console.log('   - Historial (mensajes):', this.conversation_history.length);
        }
    }

    /**
     * Añade un mensaje al área de chat
     * @param {string} text - Texto del mensaje
     * @param {string} type - Tipo de mensaje ('user' o 'bot')
     * @param {boolean} append - Si es true, agrega el texto al último mensaje en lugar de crear uno nuevo
     * @returns {void}
     */
    add_message(text, type, append = false) {
        // Si append es true, agregar al último mensaje del mismo tipo
        if (append) {
            const messages = this.chat_messages.querySelectorAll(`.message.${type}`);
            if (messages.length > 0) {
                const last_message = messages[messages.length - 1];
                const bubble = last_message.querySelector('.message-bubble');
                if (bubble) {
                    bubble.textContent += text;
                    this.scroll_to_bottom();
                    return;
                }
            }
        }

        // Crear nuevo mensaje
        const message_div = document.createElement('div');
        message_div.className = `message ${type}`;

        const bubble_div = document.createElement('div');
        bubble_div.className = 'message-bubble';
        bubble_div.textContent = text;

        const time_div = document.createElement('div');
        time_div.className = 'message-time';
        const now = new Date();
        time_div.textContent = now.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });

        message_div.appendChild(bubble_div);
        message_div.appendChild(time_div);

        this.chat_messages.appendChild(message_div);

        // Scroll al final
        this.scroll_to_bottom();
    }

    /**
     * Muestra el indicador de escritura
     * @returns {void}
     */
    show_typing_indicator() {
        if (this.typing_indicator) {
            this.typing_indicator.style.display = 'flex';
            this.scroll_to_bottom();
        }
    }

    /**
     * Oculta el indicador de escritura
     * @returns {void}
     */
    hide_typing_indicator() {
        if (this.typing_indicator) {
            this.typing_indicator.style.display = 'none';
        }
    }

    /**
     * Hace scroll hasta el final del área de mensajes
     * Versión robusta con múltiples intentos para asegurar el scroll tras renderizado
     * @returns {void}
     */
    scroll_to_bottom() {
        if (!this.chat_messages) return;

        const perform_scroll = () => {
            this.chat_messages.scrollTop = this.chat_messages.scrollHeight;
        };

        // Intento inmediato
        perform_scroll();

        // Intento con requestAnimationFrame (siguiente frame de renderizado)
        requestAnimationFrame(() => {
            perform_scroll();

            // Intentos adicionales para cubrir asincronía de renderizado/teclado
            setTimeout(perform_scroll, 50);
            setTimeout(perform_scroll, 150);
            setTimeout(perform_scroll, 300);
        });
    }

    /**
     * Muestra un mensaje con efecto de typing palabra por palabra
     * @param {string} text - Texto completo a mostrar
     * @param {string} type - Tipo de mensaje ('bot' o 'user')
     * @param {number} words_per_minute - Velocidad de typing (default: 300)
     * @returns {Promise<void>}
     */
    async stream_message(text, type = 'bot', words_per_minute = 300) {
        // Cancelar streaming anterior si existe
        this.cancel_streaming();

        // Crear elemento de mensaje vacío
        const message_div = document.createElement('div');
        message_div.className = `message ${type}`;

        const bubble_div = document.createElement('div');
        bubble_div.className = 'message-bubble';
        bubble_div.textContent = ''; // Empezar vacío

        // Añadir cursor de typing
        const cursor_span = document.createElement('span');
        cursor_span.className = 'typing-cursor';
        cursor_span.textContent = '▊';
        bubble_div.appendChild(cursor_span);

        const time_div = document.createElement('div');
        time_div.className = 'message-time';
        const now = new Date();
        time_div.textContent = now.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });

        message_div.appendChild(bubble_div);
        message_div.appendChild(time_div);
        this.chat_messages.appendChild(message_div);

        // Guardar referencia
        this.current_stream_message_element = bubble_div;
        this.streaming_active = true;

        // Deshabilitar input durante streaming
        if (this.chat_input) {
            this.chat_input.disabled = true;
        }
        if (this.send_button) {
            this.send_button.disabled = true;
        }

        // Dividir texto en palabras (mantener espacios)
        const words = text.split(/(\s+)/);
        const delay_per_word = (60 / words_per_minute) * 1000; // ms por palabra

        // Mostrar palabras progresivamente
        for (let i = 0; i < words.length; i++) {
            if (!this.streaming_active) {
                // Streaming fue cancelado - mostrar resto del texto de una vez
                const remaining_text = words.slice(i).join('');
                const text_node = document.createTextNode(remaining_text);
                bubble_div.insertBefore(text_node, cursor_span);
                break;
            }

            // Añadir palabra antes del cursor
            const text_node = document.createTextNode(words[i]);
            bubble_div.insertBefore(text_node, cursor_span);

            // Scroll suave
            this.scroll_to_bottom();

            // Esperar antes de la siguiente palabra
            if (i < words.length - 1) {
                await new Promise(resolve => {
                    this.streaming_timeout_id = setTimeout(resolve, delay_per_word);
                });
            }
        }

        // Remover cursor al finalizar
        if (cursor_span.parentNode) {
            cursor_span.remove();
        }

        // Re-habilitar input
        if (this.chat_input) {
            this.chat_input.disabled = false;
        }
        if (this.send_button) {
            this.send_button.disabled = false;
        }
        this.toggle_send_button(); // Actualizar estado del botón

        this.streaming_active = false;
        this.current_stream_message_element = null;
        this.scroll_to_bottom();
    }

    /**
     * Cancela el streaming actual
     * @returns {void}
     */
    cancel_streaming() {
        if (this.streaming_timeout_id) {
            clearTimeout(this.streaming_timeout_id);
            this.streaming_timeout_id = null;
        }

        this.streaming_active = false;

        // Remover cursor si existe
        if (this.current_stream_message_element) {
            const cursor = this.current_stream_message_element.querySelector('.typing-cursor');
            if (cursor) {
                cursor.remove();
            }
        }
    }
}

/**
 * Inicializa el chat fullscreen cuando el DOM está listo
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new BravesChatScreen();
    });
} else {
    new BravesChatScreen();
}
