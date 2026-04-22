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

        // Velocidad de escritura desde configuración PHP (milisegundos por carácter)
        this.typing_speed = (window.BravesChatConfig && window.BravesChatConfig.typing_speed)
            ? parseInt(window.BravesChatConfig.typing_speed)
            : 30;

        // Estado de pantalla de bienvenida (tipo Claude)
        this.is_welcome_mode = true;

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
        console.log('Auth Token:', this.auth_token ? 'Configurado' : 'No configurado');

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

        // SAFE RENDER: Verificar GDPR al inicio y ocultar contenido si es necesario
        // Esto previene que se vea el contenido "detrás" del aviso
        if (window.bravesFingerprint &&
            window.bravesFingerprint.gdpr_config &&
            window.bravesFingerprint.gdpr_config.enabled &&
            !window.bravesFingerprint.has_gdpr_consent()) {

            console.log('[BravesChat Screen] Init: GDPR activo y sin consentimiento. Bloqueando UI preventivamente.');
            if (this.chat_window) this.chat_window.classList.add('braves-gdpr-locked');
            if (this.chat_messages) this.chat_messages.style.display = 'none';
            const inputWrapper = document.getElementById('chat-input-wrapper');
            if (inputWrapper) inputWrapper.style.display = 'none';

            // Mostrar el aviso directamente ya que en fullscreen el chat siempre está expuesto
            this.show_in_chat_gdpr();
        }

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
                // this.toggle_chat(); // Eliminado en modo inmersivo
            });
        }

        if (this.close_button) {
            this.close_button.addEventListener('click', (e) => {
                e.stopPropagation();
                // En modo inmersivo tal vez no se quiera cerrar pero lo mantenemos si existe
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
     * Crea el dot de latido persistente al final del área de mensajes.
     * Se llama una sola vez al salir del modo bienvenida.
     * @returns {void}
     */
    create_heartbeat_dot() {
        if (document.getElementById('braves-heartbeat-dot')) return;
        const wrapper = document.createElement('div');
        wrapper.id = 'braves-heartbeat-dot';
        wrapper.className = 'braves-heartbeat-dot';
        const inner = document.createElement('span');
        inner.className = 'braves-heartbeat-dot__inner';
        wrapper.appendChild(inner);
        this.chat_messages.appendChild(wrapper);
    }

    /**
     * Inserta un elemento de mensaje justo antes del typing-indicator,
     * manteniendo el indicador y el heartbeat dot siempre al final.
     * @param {HTMLElement} element
     * @returns {void}
     */
    insert_message_to_chat(element) {
        const typing_indicator = document.getElementById('typing-indicator');
        if (typing_indicator && typing_indicator.parentNode === this.chat_messages) {
            this.chat_messages.insertBefore(element, typing_indicator);
        } else {
            this.chat_messages.appendChild(element);
        }
    }

    /**
     * Verifica si hay una conversación pendiente y la restaura
     * @returns {void}
     */
    check_and_restore_conversation() {
        // ✅ Pasar session_id para obtener el historial correcto de localStorage
        const restored_state = this.redirect_handler.restore_conversation_state(this.session_id);

        if (restored_state && restored_state.history && restored_state.history.length > 0) {
            console.log('[BravesChat Screen] Restaurando conversación:', restored_state);

            // Restaurar historial
            this.conversation_history = restored_state.history;

            // Restaurar mensajes en el DOM
            this.conversation_history.forEach(msg => {
                const type = msg.role === 'user' ? 'user' : 'bot';
                this.add_message(msg.content, type, false);
            });

            // Saltar welcome mode si hay mensajes previos
            this.is_welcome_mode = false;
            if (this.chat_window) {
                this.chat_window.classList.remove('braves-welcome-mode');
            }
            const splash = document.getElementById('chat-welcome-splash');
            if (splash) splash.style.display = 'none';

            // Crear el dot de latido (no hay welcome mode)
            this.create_heartbeat_dot();

            console.log(`✅ Conversación restaurada (${this.conversation_history.length} mensajes)`);
        }
    }

    /**
     * Transiciona desde la pantalla de bienvenida al modo chat
     * Anima la salida del splash y la entrada de los mensajes
     * @returns {void}
     */
    activate_chat_mode() {
        if (!this.is_welcome_mode) return;
        this.is_welcome_mode = false;

        const splash = document.getElementById('chat-welcome-splash');

        // Fase 1: animar salida del splash
        if (splash) {
            splash.classList.add('braves-splash-exit');
        }

        // Fase 2: tras la animación, cambiar al layout de chat
        setTimeout(() => {
            if (this.chat_window) {
                this.chat_window.classList.remove('braves-welcome-mode');
            }
            if (splash) {
                splash.style.display = 'none';
            }
            if (this.chat_messages) {
                this.chat_messages.classList.add('braves-messages-appear');
            }
            // Crear el dot de latido al entrar en modo chat
            this.create_heartbeat_dot();
        }, 300);
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

        // Verificar si bravesFingerprint existe
        if (!window.bravesFingerprint) {
            console.error('[BravesChat Screen] window.bravesFingerprint no está definido');
            // Focus en el input
            setTimeout(() => {
                if (this.chat_input) this.chat_input.focus();
            }, 300);
            return;
        }

        // Verificar GDPR antes de permitir interacción si se abre el modo
        if (window.bravesFingerprint.gdpr_config.enabled && !window.bravesFingerprint.has_gdpr_consent()) {
            console.log('[BravesChat Screen] GDPR activo y sin consentimiento. Mostrando aviso.');
            this.show_in_chat_gdpr();
        } else {
            console.log('[BravesChat Screen] GDPR inactivo o ya con consentimiento.');
            // Asegurarnos de que la UI esté desbloqueada
            if (this.chat_window) this.chat_window.classList.remove('braves-gdpr-locked');
            if (this.chat_messages) this.chat_messages.style.display = '';
            const inputWrapper = document.getElementById('chat-input-wrapper');
            if (inputWrapper) inputWrapper.style.display = '';

            // Focus en el input
            setTimeout(() => {
                if (this.chat_input) this.chat_input.focus();
            }, 300);
        }
    }

    /**
     * Muestra el aviso GDPR dentro del chat
     * Bloquea el input hasta que se acepte
     */
    show_in_chat_gdpr() {
        // Bloquear UI añadiendo clase al contenedor principal
        if (this.chat_window) {
            this.chat_window.classList.add('braves-gdpr-locked');
        }
        // Force hide content via JS to ensure no flash
        if (this.chat_messages) this.chat_messages.style.display = 'none';
        const inputWrapper = document.getElementById('chat-input-wrapper');
        if (inputWrapper) inputWrapper.style.display = 'none';

        // Crear elemento del mensaje GDPR
        const gdprId = 'braves-chat-gdpr-notice';
        if (document.getElementById(gdprId)) return;

        const notice = document.createElement('div');
        notice.id = gdprId;
        notice.className = 'braves-chat-gdpr-notice braves-gdpr-global-overlay';
        notice.innerHTML = `
            <div class="braves-gdpr-card">
                <h3>${__('Terms and conditions', 'braveschat')}</h3>
                <p>${window.bravesFingerprint.gdpr_config.message || __('By clicking "Accept" and each time I interact with this AI agent, I consent to my communications being recorded, stored and shared with third-party service providers, as described in the Privacy Policy. If you do not wish your conversations to be recorded, please refrain from using this service.', 'braveschat')}</p>
                <div class="braves-gdpr-actions">
                    <button class="braves-btn-cancel" id="braves-gdpr-cancel-btn">${__('Cancel', 'braveschat')}</button>
                    <button class="braves-btn-accept" id="braves-gdpr-accept-btn">${window.bravesFingerprint.gdpr_config.accept_text || __('Accept', 'braveschat')}</button>
                </div>
            </div>
        `;

        // Insertar en document.body para que sea un overlay independiente del chat
        document.body.appendChild(notice);

        // Bind events
        setTimeout(() => {
            const acceptBtn = notice.querySelector('#braves-gdpr-accept-btn');
            const cancelBtn = notice.querySelector('#braves-gdpr-cancel-btn');

            if (acceptBtn) {
                acceptBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handle_gdpr_consent(true, notice);
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handle_gdpr_consent(false, notice);
                });
            }
        }, 100);
    }

    /**
     * Maneja la respuesta al aviso GDPR
     * @param {boolean} accepted - True si el usuario aceptó
     * @param {HTMLElement} noticeElement - El elemento del aviso
     */
    handle_gdpr_consent(accepted, noticeElement) {
        if (accepted) {
            // Guardar consentimiento
            if (window.bravesFingerprint) {
                window.bravesFingerprint.save_gdpr_consent();

                // Inicializar sesión ahora que tenemos consentimiento
                window.bravesFingerprint.get_or_create_session().then(sessionId => {
                    this.session_id = sessionId;
                    console.log('Sesión iniciada tras consentimiento GDPR:', this.session_id);
                });
            }

            // Quitar aviso
            if (noticeElement) noticeElement.remove();

            // Desbloquear UI
            if (this.chat_window) {
                this.chat_window.classList.remove('braves-gdpr-locked');
            }
            if (this.chat_messages) this.chat_messages.style.display = '';
            const inputWrapper = document.getElementById('chat-input-wrapper');
            if (inputWrapper) inputWrapper.style.display = '';

            // Desbloquear input y focus
            if (this.chat_input) {
                this.chat_input.disabled = false;
                this.chat_input.focus();
            }
            if (this.send_button) {
                this.send_button.disabled = false;
            }
            // Revisar estado del botón de enviar
            this.toggle_send_button();

        } else {
            // Usuario canceló - en pantalla completa no tiene forma de cerrar, se bloquea el input
            if (this.chat_input) {
                this.chat_input.disabled = true;
            }
            if (this.send_button) {
                this.send_button.disabled = true;
            }
        }
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

        textarea.style.height = 'auto';
        const newHeight = Math.max(textarea.scrollHeight, 72); // mínimo 3 líneas
        textarea.style.height = newHeight + 'px';

        // Habilitar scroll interno solo cuando supera el máximo
        if (newHeight > 200) {
            textarea.style.setProperty('overflow-y', 'auto', 'important');
        } else {
            textarea.style.setProperty('overflow-y', 'hidden', 'important');
        }
    }

    /**
     * Convierte texto con Markdown básico a HTML seguro
     * @param {string} text - Texto con posible formato Markdown
     * @returns {string} HTML sanitizado
     */
    parse_markdown(text) {
        if (!text) return '';

        // Escapar HTML para prevenir XSS
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        // Enlaces: [texto](url) — solo acepta https?:// para prevenir javascript: XSS
        html = html.replace(
            /\[([^\]]+)\]\((https?:\/\/[^)]+)\)/g,
            '<a href="$2" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">$1</a>'
        );

        // Negrita: **texto**
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // Cursiva: *texto*
        html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

        // Saltos de línea
        html = html.replace(/\n/g, '<br>');

        return html;
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

            // Activar modo chat si está en pantalla de bienvenida
            if (this.is_welcome_mode) {
                this.activate_chat_mode();
            }

            // Cancelar streaming si está activo
            if (this.streaming_active) {
                this.cancel_streaming();
            }

            console.log('Enviando mensaje:', message);

            // Registrar posición del dot ANTES de añadir el mensaje (para animación FLIP)
            const heartbeat_dot = document.getElementById('braves-heartbeat-dot');
            const dot_pre_offset = heartbeat_dot ? heartbeat_dot.offsetTop : null;

            // Agregar mensaje del usuario al chat
            this.add_message(message, 'user');

            // Animar el dot viajando hacia abajo (técnica FLIP)
            if (heartbeat_dot && dot_pre_offset !== null) {
                requestAnimationFrame(() => {
                    const delta = dot_pre_offset - heartbeat_dot.offsetTop;
                    if (Math.abs(delta) > 10) {
                        const travel = Math.max(delta, -500); // cap de viaje
                        heartbeat_dot.style.transition = 'none';
                        heartbeat_dot.style.transform = `translateY(${travel}px)`;
                        heartbeat_dot.offsetHeight; // forzar reflow
                        heartbeat_dot.style.transition = 'transform 1.1s cubic-bezier(0.4, 0, 0.2, 1)';
                        heartbeat_dot.style.transform = 'translateY(0)';
                        setTimeout(() => {
                            heartbeat_dot.style.transition = '';
                            heartbeat_dot.style.transform = '';
                        }, 1100);
                    }
                });
            }

            this.chat_input.value = '';

            // Resetear altura del textarea después de enviar
            this.chat_input.style.height = 'auto';
            this.auto_resize_textarea(this.chat_input);
            this.chat_input.focus();

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
                // Enviar mensaje via AJAX de WordPress (el token se gestiona en el servidor)
                const response = await fetch(BravesChatConfig.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'braves_chat_send_message',
                        nonce: BravesChatConfig.nonce,
                        chatInput: message,
                        sessionId: this.session_id
                    })
                });

                if (!response.ok) {
                    throw new Error(`ERROR HTTP ${response.status}: ${response.statusText}`);
                }

                const wp_result = await response.json();

                if (!wp_result.success) {
                    throw new Error(wp_result.data?.message || 'Error al conectar con el webhook.');
                }

                // Extraer la respuesta de N8N del resultado AJAX
                const data = wp_result.data;

                // Ocultar indicador de escritura
                this.hide_typing_indicator();

                // Usar RedirectHandler para parsear la respuesta
                const parsed = this.redirect_handler.parse_response(typeof data === 'string' ? data : JSON.stringify(data));

                // Adaptarse a diferentes formatos de respuesta de N8N (fallback)
                let bot_message = parsed.message;

                // Si el parser no encontró mensaje, intentar formatos legacy y específicos de N8N Stream
                if (!bot_message) {
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

                // Mostrar mensaje con efecto de streaming visual (velocidad desde config)
                await this.stream_message(bot_message, 'bot');

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
                let user_message = __('Error processing your message:', 'braveschat') + '\n\n';
                let technical_details = '';

                if (error.message.includes('Failed to fetch')) {
                    user_message += __('**Could not connect to the server**', 'braveschat') + '\n\n';
                    user_message += __('Possible causes:', 'braveschat') + '\n';
                    user_message += __('• No internet connection', 'braveschat') + '\n';
                    user_message += __('• The N8N server is down', 'braveschat') + '\n';
                    user_message += __('• CORS problem', 'braveschat') + '\n';
                    user_message += __('• Incorrect webhook URL', 'braveschat') + '\n\n';
                    technical_details = `URL: ${this.webhook_url}\nError: ${error.message}`;
                } else if (error.message.includes('WEBHOOK_NOT_CONFIGURED')) {
                    user_message += __('**Webhook not configured**', 'braveschat') + '\n\n';
                    user_message += __('The administrator must configure the webhook URL at:', 'braveschat') + '\n';
                    user_message += __('WordPress Admin > Settings > BravesChat iA', 'braveschat') + '\n\n';
                    technical_details = error.message;
                } else if (error.message.includes('401') || error.message.includes('403')) {
                    user_message += __('**Authentication error**', 'braveschat') + '\n\n';
                    user_message += __('The authentication token is invalid or has expired.', 'braveschat') + '\n';
                    user_message += __('Contact the administrator to verify the N8N token.', 'braveschat') + '\n\n';
                    technical_details = error.message;
                } else if (error.message.includes('404')) {
                    user_message += __('**Webhook not found**', 'braveschat') + '\n\n';
                    user_message += __('The webhook URL does not exist or is incorrect.', 'braveschat') + '\n';
                    user_message += __('Check the URL in the plugin settings.', 'braveschat') + '\n\n';
                    technical_details = `URL: ${this.webhook_url}\n${error.message}`;
                } else if (error.message.includes('JSON_PARSE_ERROR')) {
                    user_message += __('**Invalid server response**', 'braveschat') + '\n\n';
                    user_message += __('The N8N server did not return a valid JSON.', 'braveschat') + '\n';
                    user_message += __('Check the workflow configuration in N8N.', 'braveschat') + '\n\n';
                    technical_details = error.message;
                } else if (error.message.includes('RESPONSE_FORMAT_ERROR')) {
                    user_message += __('**Incorrect response format**', 'braveschat') + '\n\n';
                    user_message += __('The server returned a response but without the expected field.', 'braveschat') + '\n';
                    user_message += __('The webhook must return: {output: "message"} or {response: "message"}', 'braveschat') + '\n\n';
                    technical_details = error.message;
                } else if (error.message.includes('500') || error.message.includes('502') || error.message.includes('503')) {
                    user_message += __('**Server error**', 'braveschat') + '\n\n';
                    user_message += __('The N8N server has an internal problem.', 'braveschat') + '\n';
                    user_message += __('Contact the server administrator.', 'braveschat') + '\n\n';
                    technical_details = error.message;
                } else {
                    user_message += __('**Unknown error**', 'braveschat') + '\n\n';
                    user_message += __('An unexpected error occurred. Please try again.', 'braveschat') + '\n\n';
                    technical_details = `${error.message}\n\nStack: ${error.stack}`;
                }

                user_message += __('**Technical details:**', 'braveschat') + '\n';
                user_message += '```\n' + technical_details + '\n```\n\n';
                user_message += `${new Date().toLocaleString()}`;

                this.add_message(user_message, 'bot');

                // Log adicional para el administrador
                console.log('📊 INFORMACIÓN DE DEBUG:');
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
                        const current_text = bubble.textContent;
                        bubble.innerHTML = this.parse_markdown(current_text + text);
                        this.scroll_to_bottom();
                        return;
                    }
                }
            }

            // Crear nuevo mensaje
            const message_div = document.createElement('div');
            message_div.className = `message ${type}`;

            // Contenedor de avatar
            const avatar_div = document.createElement('div');
            avatar_div.className = 'message-avatar';

            if (type === 'bot') {
                const img = document.createElement('img');
                // Obtener el source del avatar del header/config
                img.src = window.BravesChatConfig?.bubbleImage || document.querySelector('#chat-icon')?.src || '';
                if (!img.src && document.querySelector('.message.bot img')) {
                    img.src = document.querySelector('.message.bot img').src;
                }
                img.alt = 'Bot Avatar';
                avatar_div.appendChild(img);
            } else {
                avatar_div.textContent = 'U';
            }

            // Contenedor de contenido
            const content_div = document.createElement('div');
            content_div.className = 'message-content';

            const bubble_div = document.createElement('div');
            bubble_div.className = 'message-bubble';

            bubble_div.innerHTML = this.parse_markdown(text);

            const time_div = document.createElement('div');
            time_div.className = 'message-time';
            const now = new Date();
            time_div.textContent = now.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });

            content_div.appendChild(bubble_div);
            content_div.appendChild(time_div);

            message_div.appendChild(avatar_div);
            message_div.appendChild(content_div);

            this.insert_message_to_chat(message_div);

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
     * Muestra un mensaje con efecto de typing carácter a carácter
     * Usa typing_speed (ms/carácter), pausas naturales en puntuación,
     * variación aleatoria y renderizado de Markdown en tiempo real.
     * @param {string} text - Texto completo a mostrar
     * @param {string} type - Tipo de mensaje ('bot' o 'user')
     * @returns {Promise<void>}
     */
    async stream_message(text, type = 'bot') {
        // Cancelar streaming anterior si existe
        this.cancel_streaming();

        // Estructura del mensaje con avatar
        const message_div = document.createElement('div');
        message_div.className = `message ${type}`;

        const avatar_div = document.createElement('div');
        avatar_div.className = 'message-avatar';

        if (type === 'bot') {
            const img = document.createElement('img');
            img.src = window.BravesChatConfig?.bubbleImage || '';
            img.alt = 'Bot Avatar';
            avatar_div.appendChild(img);
        } else {
            avatar_div.textContent = 'U';
        }

        const content_div = document.createElement('div');
        content_div.className = 'message-content';

        const bubble_div = document.createElement('div');
        bubble_div.className = 'message-bubble';

        const time_div = document.createElement('div');
        time_div.className = 'message-time';
        const now = new Date();
        time_div.textContent = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

        content_div.appendChild(bubble_div);
        content_div.appendChild(time_div);
        message_div.appendChild(avatar_div);
        message_div.appendChild(content_div);
        this.insert_message_to_chat(message_div);

        this.current_stream_message_element = bubble_div;
        this.streaming_active = true;

        // Streaming carácter a carácter con markdown en tiempo real
        let visible_content = '';

        for (let i = 0; i < text.length; i++) {
            if (!this.streaming_active) {
                // Cancelado: volcar el resto de una vez
                visible_content = text;
                break;
            }

            const char = text[i];
            visible_content += char;

            // Solo actualizar el bubble — el cursor_span nunca se toca (no reinicia animación)
            bubble_div.innerHTML = this.parse_markdown(visible_content);

            this.scroll_to_bottom();

            // Calcular delay con variación humana (±30%) y pausas en puntuación
            let delay = Math.round(this.typing_speed * (0.7 + Math.random() * 0.6));
            if ('.!?\n'.includes(char)) {
                delay += 150;
            } else if (',;:'.includes(char)) {
                delay += 70;
            }

            await new Promise(resolve => {
                this.streaming_timeout_id = setTimeout(resolve, delay);
            });
        }

        // Render final completo con markdown
        bubble_div.innerHTML = this.parse_markdown(visible_content);
        // El heartbeat dot persiste independientemente — siempre visible

        if (this.chat_input) this.chat_input.disabled = false;
        if (this.send_button) this.send_button.disabled = false;
        this.toggle_send_button();

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
            // El cursor permanece visible — el dot es el latido del agente
        }
    }

    /**
     * Inicializa el chat fullscreen cuando el DOM está listo
     */
    if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new BravesChatScreen();
    });
} else {
    new BravesChatScreen();
}
