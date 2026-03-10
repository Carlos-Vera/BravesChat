/**
 * BravesChat - Modal Mode
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

class BravesChatModal {
    /**
     * Constructor de la clase BravesChatModal
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

        // CONFIGURABLE: Delay entre fragmentos (en milisegundos)
        // Ajusta este valor para controlar la velocidad de aparición del texto
        // Valores recomendados: 30-100ms
        this.messages = [];
        this.typing_speed = (window.BravesChatConfig && window.BravesChatConfig.typing_speed) ? parseInt(window.BravesChatConfig.typing_speed) : 30;

        // Inicializar session_id de forma asíncrona
        this.generate_session_id().then(session_id => {
            this.session_id = session_id;
            // Verificar si hay conversación pendiente de recuperación
            this.check_and_restore_conversation();
        });

        this.init();
    }

    /**
     * Inicializa el chat modal
     * Configura elementos del DOM, animaciones y event listeners
     * @returns {void}
     */
    init() {
        console.log('Inicializando BravesChat Modal...');
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
        this.expand_button = document.getElementById('expand-chat');

        if (!this.chat_container) {
            console.error('No se encontró el contenedor del chat');
            return;
        }

        // VERIFICAR ESTADO MINIMIZADO
        // Si el usuario ya interactuó previamente, cargar en estado minimizado
        if (localStorage.getItem('braves_chat_interacted') === 'true') {
            this.chat_container.classList.add('braves-chat-minimized');
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

            console.log('[BravesChat Modal] Init: GDPR activo y sin consentimiento. Bloqueando UI preventivamente.');
            if (this.chat_window) this.chat_window.classList.add('braves-gdpr-locked');
            if (this.chat_messages) this.chat_messages.style.display = 'none';
            const inputWrapper = document.getElementById('chat-input-wrapper');
            if (inputWrapper) inputWrapper.style.display = 'none';
        }

        console.log('Chat Modal inicializado correctamente');
    }

    /**
     * Configura todos los event listeners del chat
     * @returns {void}
     */
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

        // Delegación de eventos para el botón expandir (por si se reemplaza el HTML o es dinámico)
        if (this.chat_container) {
            this.chat_container.addEventListener('click', (e) => {
                const expandBtn = e.target.closest('#expand-chat');
                if (expandBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggle_expand();
                }
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

        // Cerrar al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (this.is_open &&
                this.chat_window && !this.chat_window.contains(e.target) &&
                this.chat_toggle && !this.chat_toggle.contains(e.target) &&
                !e.target.closest('.braves-chat-gdpr-notice')) {
                this.close_window();
            }
        });
    }


    /**
     * Verifica si hay una conversación pendiente y la restaura
     * @returns {void}
     */
    check_and_restore_conversation() {
        // ✅ Pasar session_id para obtener el historial correcto de localStorage
        const restored_state = this.redirect_handler.restore_conversation_state(this.session_id);

        if (restored_state) {
            console.log('[BravesChat Modal] Restaurando conversación:', restored_state);

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
                        console.log('[BravesChat Modal] Usando session_id con fingerprinting:', fingerprint_session);
                        return fingerprint_session;
                    }
                } catch (error) {
                    console.error('[BravesChat Modal] Error obteniendo fingerprint:', error);
                }
            }
        }

        // Fallback: generar ID temporal si el sistema de fingerprinting no está disponible
        const temp_session = 'temp_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
        console.warn('[BravesChat Modal] Sistema de fingerprinting no disponible, usando session_id temporal:', temp_session);
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
     * Abre la ventana del chat
     * @returns {void}
     */
    /**
     * Abre la ventana del chat
     * @returns {void}
     */
    open_window() {
        this.chat_container.classList.remove('chat-closed');
        this.chat_container.classList.add('chat-open');
        this.is_open = true;

        console.log('[BravesChat Modal] Intentando abrir ventana de chat...');

        // Verificar si bravesFingerprint existe
        if (!window.bravesFingerprint) {
            console.error('[BravesChat Modal] window.bravesFingerprint no está definido');
            // Si no está definido, permitir abrir por seguridad
            setTimeout(() => {
                if (this.chat_input) this.chat_input.focus();
            }, 300);
            return;
        }

        console.log('[BravesChat Modal] GDPR Config:', window.bravesFingerprint.gdpr_config);
        console.log('[BravesChat Modal] Has Consent:', window.bravesFingerprint.has_gdpr_consent());

        // Verificar GDPR antes de permitir interacción
        if (window.bravesFingerprint.gdpr_config.enabled && !window.bravesFingerprint.has_gdpr_consent()) {
            console.log('[BravesChat Modal] GDPR activo y sin consentimiento. Mostrando aviso.');
            this.show_in_chat_gdpr();
        } else {
            console.log('[BravesChat Modal] GDPR inactivo o ya con consentimiento.');
            // Asegurarnos de que la UI esté desbloqueada
            if (this.chat_window) this.chat_window.classList.remove('braves-gdpr-locked');
            if (this.chat_messages) this.chat_messages.style.display = '';
            const inputWrapper = document.getElementById('chat-input-wrapper');
            if (inputWrapper) inputWrapper.style.display = '';

            // Focus en el input si ya hay consentimiento
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
                <h3>${__('Términos y condiciones', 'braves-chat')}</h3>
                <p>${window.bravesFingerprint.gdpr_config.message || __('Al hacer clic en «Aceptar» y cada vez que interactúo con este agente de IA, doy mi consentimiento para que se graben, almacenen y compartan mis comunicaciones con terceros proveedores de servicios, tal y como se describe en la Política de privacidad. Si no desea que se graben sus conversaciones, le rogamos que se abstenga de utilizar este servicio.', 'braves-chat')}</p>
                <div class="braves-gdpr-actions">
                    <button class="braves-btn-cancel" id="braves-gdpr-cancel-btn">${__('Cancelar', 'braves-chat')}</button>
                    <button class="braves-btn-accept" id="braves-gdpr-accept-btn">${window.bravesFingerprint.gdpr_config.accept_text || __('Aceptar', 'braves-chat')}</button>
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
            // Usuario canceló - cerrar chat
            this.close_window();
            // Opcional: mostrar mensaje de que es necesario aceptar para usar el chat
        }
    }

    /**
     * Cierra la ventana del chat
     * @returns {void}
     */
    close_window() {
        this.chat_container.classList.remove('chat-open');
        this.chat_container.classList.remove('braves-expanded'); // Reset expansion on close
        this.chat_container.classList.add('chat-closed');
        this.is_open = false;
    }

    /**
     * Alterna entre modo normal y expandido
     * @returns {void}
     */
    toggle_expand() {
        if (this.chat_container.classList.contains('braves-expanded')) {
            this.chat_container.classList.remove('braves-expanded');
        } else {
            this.chat_container.classList.add('braves-expanded');
        }
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

        // Toggle overflow based on whether content exceeds max-height (250px)
        if (newHeight > 250) {
            textarea.style.setProperty('overflow-y', 'auto', 'important');
        } else {
            textarea.style.setProperty('overflow-y', 'hidden', 'important');
        }
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

        // Focus inmediato para permitir seguir escribiendo si se desea
        this.chat_input.focus();

        this.toggle_send_button();

        // Guardar en historial
        this.conversation_history.push({
            role: 'user',
            content: message
        });

        // ✅ Guardar automáticamente después de cada mensaje del usuario
        this.redirect_handler.save_conversation_state(this.conversation_history, this.session_id);

        // ✅ MARCAR COMO INTERACTUADO
        // El usuario ha enviado un mensaje, así que la próxima vez que cargue la página,
        // la burbuja aparecerá minimizada.
        localStorage.setItem('braves_chat_interacted', 'true');

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

            // Ocultar indicador de escritura
            this.hide_typing_indicator();

            // Extraer la respuesta de N8N del resultado AJAX
            const data = wp_result.data;
            let bot_message;

            {
                // Extraer mensaje del objeto de respuesta de N8N

                if (data.content) {
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

                if (!bot_message) {
                    console.error('No se encontró mensaje en la respuesta');
                    console.error('   Estructura recibida:', data);
                    console.error('   Tipo de dato:', typeof data);
                    throw new Error(`RESPONSE_FORMAT_ERROR: No se encontró el mensaje en la respuesta.\n\nCampos disponibles: ${Object.keys(data).join(', ')}\n\nRespuesta completa: ${JSON.stringify(data).substring(0, 200)}`);
                }

                // Mostrar mensaje con efecto de escritura simulado
                await this.stream_message_simulated(bot_message);
            }
            // FIN de bifurcación (streaming vs fallback)

            // Guardar en historial
            this.conversation_history.push({
                role: 'assistant',
                content: bot_message
            });

            // ✅ Guardar automáticamente después de cada respuesta del bot
            this.redirect_handler.save_conversation_state(this.conversation_history, this.session_id);

            console.log('Mensaje procesado correctamente');

            // Parsear respuesta para detectar redirecciones/acciones (funciona para ambos casos)
            const parsed = this.redirect_handler.parse_response(bot_message);

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
                    // Obtener texto actual, añadir nuevo y re-renderizar markdown
                    // Esto asegura que formatos que cruzan chunks se rendericen bien
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

        const bubble_div = document.createElement('div');
        bubble_div.className = 'message-bubble';
        // Usar innerHTML con parseo de Markdown para permitir enlaces
        bubble_div.innerHTML = this.parse_markdown(text);

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
     * Procesa streaming real de n8n usando ReadableStream
     * Muestra fragmentos NDJSON a medida que llegan (sin delays artificiales)
     * @param {ReadableStream} stream - El response.body stream de n8n
     * @returns {Promise<string>} - Contenido completo al finalizar
     */
    async stream_from_reader(stream) {
        const reader = stream.getReader();
        const decoder = new TextDecoder();

        console.log('📡 Iniciando streaming real de n8n...');

        // REMOVED: Eliminar cursores anteriores para que solo el último mensaje tenga el dot palpitante
        if (this.chat_messages) {
            const existing_cursors = this.chat_messages.querySelectorAll('.typing-cursor');
            existing_cursors.forEach(cursor => cursor.remove());
        }

        // Crear mensaje vacío con cursor circular
        const message_div = document.createElement('div');
        message_div.className = 'message bot';

        const bubble_div = document.createElement('div');
        bubble_div.className = 'message-bubble';

        // Añadir cursor de typing (círculo con CSS)
        const cursor_span = document.createElement('span');
        cursor_span.className = 'typing-cursor';
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

        let buffer = '';
        let full_content = ''; // Contenido total recibido (para logs y final)
        let visible_content = ''; // Contenido actualmente visible (para typing)
        let fragment_count = 0;

        try {
            while (true) {
                const { value, done } = await reader.read();

                if (done) {
                    console.log('✅ Streaming completado');
                    break;
                }

                if (!this.streaming_active) {
                    console.log('⚠️ Streaming cancelado por el usuario');
                    break;
                }

                // Decodificar chunk recibido
                const chunk = decoder.decode(value, { stream: true });
                buffer += chunk;

                console.log(`📦 Chunk recibido (${chunk.length} chars)`);

                // Intentar parsear objetos JSON completos del buffer
                const lines = buffer.split('\n');
                buffer = lines.pop() || ''; // Guardar última línea (puede estar incompleta)

                for (const line of lines) {
                    if (!line.trim()) continue;

                    try {
                        const obj = JSON.parse(line);
                        fragment_count++;

                        console.log(`   ✓ Fragmento #${fragment_count}:`, obj);

                        // Extraer contenido relevante
                        let content = '';

                        // FORMATO N8N CHAT: {"type":"message","content":"..."}
                        if (obj.type) {
                            console.log(`      📋 Tipo de fragmento: "${obj.type}"`);

                            // Ignorar fragmentos de control
                            if (obj.type === 'begin' || obj.type === 'end' || obj.type === 'metadata') {
                                console.log(`      ⏭️ Ignorando fragmento de control`);
                                continue;
                            }

                            // Extraer contenido de fragmentos tipo "message"
                            if (obj.type === 'message' || obj.type === 'chunk' || obj.type === 'text') {
                                if (obj.content) {
                                    content = obj.content;
                                } else if (obj.text) {
                                    content = obj.text;
                                } else if (obj.data) {
                                    content = typeof obj.data === 'string' ? obj.data : obj.data.content || obj.data.text || '';
                                }
                            }
                        }

                        // FALLBACK: Formatos sin campo "type"
                        if (!content) {
                            if (obj.content) {
                                content = obj.content;
                            } else if (obj.output) {
                                content = obj.output;
                            } else if (obj.text) {
                                content = obj.text;
                            } else if (obj.message) {
                                content = obj.message;
                            } else if (obj.response) {
                                content = obj.response;
                            } else if (obj.data) {
                                if (typeof obj.data === 'string') {
                                    content = obj.data;
                                } else if (obj.data.text) {
                                    content = obj.data.text;
                                } else if (obj.data.content) {
                                    content = obj.data.content;
                                }
                            }
                        }

                        if (content) {
                            full_content += content;

                            // EFECTO DE ESCRITURA CARÁCTER POR CARÁCTER
                            // Iterar sobre cada letra del contenido recibido
                            for (let i = 0; i < content.length; i++) {
                                const char = content[i];

                                // ACUMULAR TEXTO VISIBLE Y ACTUALIZAR DOM EN TIEMPO REAL
                                // Esto permite que el markdown se "transforme" a medida que se escribe
                                visible_content += char;
                                const parsed_html = this.parse_markdown(visible_content);

                                bubble_div.innerHTML = parsed_html;
                                bubble_div.appendChild(cursor_span);

                                // Scroll suave
                                this.scroll_to_bottom();

                                // Calcular delay basado en la configuración + variación aleatoria
                                // Variación de ±30% para dar efecto humano
                                const random_variation = (Math.random() * 0.6) + 0.7; // 0.7 a 1.3
                                let delay = this.typing_speed * random_variation;

                                // Pausas naturales en puntuación
                                if (['.', '!', '?', '\n'].includes(char)) {
                                    delay += 150; // Pausa más larga al finalizar frases
                                } else if ([',', ';', ':'].includes(char)) {
                                    delay += 70;  // Pausa media en comas
                                }

                                await new Promise(resolve => setTimeout(resolve, delay));

                                // Verificar si el usuario canceló durante la escritura
                                if (!this.streaming_active) break;
                            }
                        } else {
                            console.log(`      ⚠️ No se encontró contenido en este fragmento`);
                        }
                    } catch (e) {
                        // JSON incompleto, seguir acumulando
                        console.log('   ⏳ JSON incompleto, esperando más datos...');
                    }
                }
            }

            console.log(`✅ Total fragmentos procesados: ${fragment_count}`);
            console.log(`📝 Contenido completo (${full_content.length} chars):`, full_content.substring(0, 100) + '...');

        } catch (error) {
            console.error('❌ Error durante streaming:', error);
            throw error;
        } finally {
            // ✨ MANTENER CURSOR VISIBLE: El cursor se queda parpadeando al final del mensaje
            // para dar la sensación de que el agente está "vivo" y esperando respuesta
            // NO eliminamos cursor_span.remove() - se queda visible

            console.log('✅ Streaming finalizado - cursor permanece visible');

            // Renderizar Markdown final para asegurar enlaces y formato correcto
            if (this.current_stream_message_element && full_content) {
                try {
                    const parsed_html = this.parse_markdown(full_content);
                    // Mantener el cursor
                    const cursor = this.current_stream_message_element.querySelector('.typing-cursor');
                    this.current_stream_message_element.innerHTML = parsed_html;
                    if (cursor) {
                        this.current_stream_message_element.appendChild(cursor);
                    }
                } catch (e) {
                    console.error('Error al renderizar Markdown final:', e);
                }
            }

            if (this.chat_input) {
                this.chat_input.disabled = false;
            }
            if (this.send_button) {
                this.send_button.disabled = false;
            }

            // Focus al terminar el streaming para que el usuario pueda escribir inmediatamente
            if (this.chat_input) {
                this.chat_input.focus();
            }
            this.toggle_send_button();

            this.streaming_active = false;
            this.current_stream_message_element = null;
            this.scroll_to_bottom();
        }

        return full_content;
    }

    /**
     * Parsea texto Markdown simple a HTML para el chat
     * Soporta: enlaces [text](url), negrita **text**, cursiva *text*, saltos de línea
     * Escapa caracteres HTML para prevenir XSS antes de parsear
     * @param {string} text - Texto en formato Markdown
     * @returns {string} - HTML seguro y formateado
     */
    parse_markdown(text) {
        if (!text) return '';

        // 1. Escapar HTML base para prevenir XSS (seguridad)
        // Convertimos caracteres peligrosos en entidades HTML
        let html = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

        // 2. Parsear enlaces: [texto](url)
        // Renderiza como <a href="url" target="_blank">texto</a>
        html = html.replace(
            /\[([^\]]+)\]\(([^)]+)\)/g,
            '<a href="$2" style="color: inherit; text-decoration: underline;">$1</a>'
        );

        // 3. Parsear negrita: **texto**
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // 4. Parsear cursiva: *texto*
        html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

        // 5. Convertir saltos de línea (newlines) en <br>
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    /**
     * Cancela el streaming actual
     * @returns {void}
     */
    /**
     * Muestra un mensaje con efecto de escritura carácter a carácter (streaming simulado).
     * Replica el comportamiento visual de stream_from_reader() pero con el texto
     * ya completo recibido desde el proxy AJAX, sin necesidad de un ReadableStream.
     *
     * @param {string} text - Texto completo a mostrar con efecto de escritura.
     * @returns {Promise<void>}
     */
    async stream_message_simulated(text) {
        // Cancelar streaming anterior si existe
        this.cancel_streaming();

        // Limpiar cursores anteriores para que sólo el mensaje actual tenga el cursor
        if (this.chat_messages) {
            this.chat_messages.querySelectorAll('.typing-cursor').forEach(c => c.remove());
        }

        // Crear burbuja vacía con cursor pulsante
        const message_div = document.createElement('div');
        message_div.className = 'message bot';

        const bubble_div = document.createElement('div');
        bubble_div.className = 'message-bubble';

        const cursor_span = document.createElement('span');
        cursor_span.className = 'typing-cursor';
        bubble_div.appendChild(cursor_span);

        const time_div = document.createElement('div');
        time_div.className = 'message-time';
        const now = new Date();
        time_div.textContent = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

        message_div.appendChild(bubble_div);
        message_div.appendChild(time_div);
        this.chat_messages.appendChild(message_div);

        this.current_stream_message_element = bubble_div;
        this.streaming_active = true;

        if (this.chat_input)  this.chat_input.disabled  = true;
        if (this.send_button) this.send_button.disabled = true;

        let visible_content = '';

        try {
            for (let i = 0; i < text.length; i++) {
                if (!this.streaming_active) {
                    // Usuario canceló — mostrar el resto de golpe
                    visible_content = text;
                    bubble_div.innerHTML = this.parse_markdown(visible_content);
                    bubble_div.appendChild(cursor_span);
                    break;
                }

                const char = text[i];
                visible_content += char;

                // Renderizar Markdown en tiempo real
                bubble_div.innerHTML = this.parse_markdown(visible_content);
                bubble_div.appendChild(cursor_span);

                this.scroll_to_bottom();

                // Delay con variación aleatoria ±30% para efecto humano
                const random_variation = (Math.random() * 0.6) + 0.7;
                let delay = this.typing_speed * random_variation;

                // Pausas naturales en puntuación
                if (['.', '!', '?', '\n'].includes(char)) {
                    delay += 150;
                } else if ([',', ';', ':'].includes(char)) {
                    delay += 70;
                }

                await new Promise(resolve => {
                    this.streaming_timeout_id = setTimeout(resolve, delay);
                });
            }
        } finally {
            // Renderizar Markdown final completo para asegurar formato correcto
            if (this.current_stream_message_element) {
                bubble_div.innerHTML = this.parse_markdown(text);
                // Mantener cursor visible al final (el agente sigue "activo")
                bubble_div.appendChild(cursor_span);
            }

            if (this.chat_input)  this.chat_input.disabled  = false;
            if (this.send_button) this.send_button.disabled = false;
            if (this.chat_input)  this.chat_input.focus();
            this.toggle_send_button();

            this.streaming_active = false;
            this.current_stream_message_element = null;
            this.scroll_to_bottom();
        }
    }

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
 * Inicializa el chat modal cuando el DOM está listo
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new BravesChatModal();
    });
} else {
    new BravesChatModal();
}

