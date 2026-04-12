<?php
/**
 * Plantilla para modo Pantalla completa
 * 
 * @package BravesChat
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables injected by Frontend class.
// Obtener el icono seleccionado y tooltip
$chat_icon = get_option('braves_chat_chat_icon', 'robot-chat');
$icon_path = BRAVES_CHAT_PLUGIN_URL . 'assets/media/chat-icons/' . $chat_icon . '.svg';
$bubble_tooltip = get_option('braves_chat_bubble_tooltip', __('Habla con nuestro asistente IA', 'braveschat'));
?>

<script>
(function(){
    if (!document.getElementById('braves-fullscreen-overlay')) {
        var el = document.createElement('div');
        el.id = 'braves-fullscreen-overlay';
        document.body.appendChild(el);
    }
})();
</script>

<div id="<?php echo esc_attr($unique_id); ?>" class="braveslab-chat-widget-container position-fullscreen braves-skin-<?php echo esc_attr($chat_skin); ?>">
    <div id="braveslab-chat-container" class="chat-open">
        <!-- Botón flotante eliminado para el modo inmersivo tipo ChatGPT -->
        
        <div id="chat-window" class="braves-welcome-mode" style="display: flex;">
            <div id="chat-header">
                <div class="header-inner">
                    <h3><?php echo esc_html($header_title); ?></h3>
                    <p><?php echo esc_html($header_subtitle); ?></p>
                </div>
            </div>

            <!-- Pantalla de bienvenida tipo Claude (visible solo en modo welcome) -->
            <div id="chat-welcome-splash">
                <div class="welcome-splash-icon">
                    <?php if (!empty($avatar_url)): ?>
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="Bot Avatar">
                    <?php else: ?>
                        <img src="<?php echo esc_url($icon_path); ?>" alt="Bot Avatar">
                    <?php endif; ?>
                </div>
                <h2 class="welcome-splash-title"><?php echo esc_html($header_title); ?></h2>
                <?php if (!empty($header_subtitle)): ?>
                    <p class="welcome-splash-subtitle"><?php echo esc_html($header_subtitle); ?></p>
                <?php endif; ?>
            </div>

            <div id="chat-messages">
                <div class="message bot">
                    <div class="message-avatar">
                        <?php if (!empty($avatar_url)): ?>
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="Bot Avatar">
                        <?php else: ?>
                            <img src="<?php echo esc_url($icon_path); ?>" alt="Bot Avatar">
                        <?php endif; ?>
                    </div>
                    <div class="message-content">
                        <div class="message-bubble">
                            <?php echo wp_kses_post(nl2br($welcome_message)); ?>
                        </div>
                        <div class="message-time" id="welcome-time"></div>
                    </div>
                </div>

                <!-- Typing indicator dentro del área de mensajes -->
                <div class="typing-indicator" id="typing-indicator" style="display: none;">
                    <span class="typing-pulse-dot"></span>
                </div>
            </div>

            <div id="chat-input-wrapper">
                <div id="chat-input-container">
                    <textarea
                        id="chat-input"
                        rows="3"
                        placeholder="<?php esc_attr_e('Escribe tu mensaje...', 'braveschat'); ?>"
                        aria-label="<?php esc_attr_e('Escribe tu mensaje', 'braveschat'); ?>"
                        style="resize: none;"
                    ></textarea>
                    <button id="send-button" disabled aria-label="<?php esc_attr_e('Enviar mensaje', 'braveschat'); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 11L12 6L17 11M12 18V7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="chat-footer-text">
                    <?php esc_html_e( 'La IA puede cometer errores. Considera verificar la información importante.', 'braveschat' ); ?>
                </div>
            </div>
        </div>
    </div>
</div>