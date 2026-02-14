<?php
/**
 * Plantilla para modo Pantalla completa
 * 
 * @package BravesChat
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener el icono seleccionado y tooltip
$chat_icon = get_option('braves_chat_chat_icon', 'robot-chat');
$icon_path = BRAVES_CHAT_PLUGIN_URL . 'assets/media/chat-icons/' . $chat_icon . '.svg';
$bubble_tooltip = get_option('braves_chat_bubble_tooltip', __('Habla con nuestro asistente IA', 'braves-chat'));
?>

<div id="<?php echo esc_attr($unique_id); ?>" class="braveslab-chat-widget-container position-<?php echo esc_attr($position); ?> braves-skin-<?php echo esc_attr($chat_skin); ?>">
    <div id="braveslab-chat-container" class="chat-closed">
        <button id="chat-toggle" title="<?php echo esc_attr($bubble_tooltip); ?>">
            <?php if ($chat_skin === 'braves' && !empty($bubble_image)): ?>
                <img id="chat-bubble-image" src="<?php echo esc_url($bubble_image); ?>" alt="<?php esc_attr_e('Chat', 'braves-chat'); ?>">
            <?php else: ?>
                <img id="chat-icon" src="<?php echo esc_url($icon_path); ?>" alt="<?php esc_attr_e('Chat', 'braves-chat'); ?>">
            <?php endif; ?>
            <span id="close-icon" style="display: none;">✕</span>
        </button>
        
        <div id="chat-window">
            <div id="chat-header">
                <div>
                    <h3><?php echo esc_html($header_title); ?></h3>
                    <p><?php echo esc_html($header_subtitle); ?></p>
                </div>
                <button id="close-chat" aria-label="<?php esc_attr_e('Cerrar chat', 'braves-chat'); ?>">×</button>
            </div>
            
            <div id="chat-messages">
                <div class="message bot">
                    <div class="message-bubble">
                        <?php echo wp_kses_post(nl2br($welcome_message)); ?>
                    </div>
                    <div class="message-time" id="welcome-time"></div>
                </div>
            </div>
            
            
            <?php
            // DESHABILITADO: El indicador de escritura ahora se muestra como cursor circular dentro del mensaje
            // con el nuevo sistema de streaming. Este indicador antiguo ya no es necesario.
            /*
            <div class="typing-indicator" id="typing-indicator">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
            */
            ?>
            
            <div id="chat-input-container">
                <textarea
                    id="chat-input"
                    rows="1"
                    placeholder="<?php esc_attr_e('Escribe tu mensaje...', 'braves-chat'); ?>"
                    aria-label="<?php esc_attr_e('Escribe tu mensaje', 'braves-chat'); ?>"
                    style="resize: none;"
                ></textarea>
                <button id="send-button" disabled aria-label="<?php esc_attr_e('Enviar mensaje', 'braves-chat'); ?>">
                    <span>➤</span>
                </button>
            </div>
        </div>
    </div>
</div>