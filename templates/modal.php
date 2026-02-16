<?php
/**
 * Plantilla para modo Modal
 *
 * @package BravesChat
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<?php
// Obtener el icono seleccionado y tooltip
$chat_icon = get_option('braves_chat_chat_icon', 'robot-chat');
$icon_path = BRAVES_CHAT_PLUGIN_URL . 'assets/media/chat-icons/' . $chat_icon . '.svg';
$header_status_text = get_option('braves_chat_header_status_text', __('Chateando con Charlie', 'braves-chat'));
$bubble_tooltip = get_option('braves_chat_bubble_tooltip', __('Habla con nuestro asistente IA', 'braves-chat'));
?>
<div id="<?php echo esc_attr($unique_id); ?>" class="braveslab-chat-widget-container position-<?php echo esc_attr($position); ?>">
    <div id="braveslab-chat-container" class="chat-closed braves-skin-<?php echo esc_attr($chat_skin); ?>">
        <button id="chat-toggle" title="<?php echo esc_attr($bubble_tooltip); ?>">
            <?php if ($chat_skin === 'braves'): ?>
                <div class="braves-bubble-content">
                    <div class="braves-bubble-header">
                        <?php 
                        $img_src = !empty($bubble_image) ? $bubble_image : $icon_path;
                        // Asegurar que haya texto fallback si la opción está vacía
                        $final_bubble_text = !empty(trim($bubble_text)) ? $bubble_text : __('¿Necesitas ayuda?', 'braves-chat');
                        ?>
                        <img id="chat-bubble-image" src="<?php echo esc_url($img_src); ?>" alt="Chat" width="60" height="60">
                        <span class="braves-bubble-text"><?php echo esc_html($final_bubble_text); ?></span>
                    </div>
                    <div class="braves-bubble-cta">
                        <svg class="braves-bubble-cta-icon" width="24" height="24" viewBox="0 0 1624 1877" version="1.1" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                             <g transform="matrix(1,0,0,1,-13152.456162,-2119.869508)">
                                <g id="Enviar" transform="matrix(3.529117,0,0,4.079728,13152.456162,2119.869508)">
                                    <path d="M37.324,196.867C28.757,165.507 6.289,74.866 3.941,65.395C-6.514,23.214 32.528,-10.064 72.392,6.761C76.34,8.427 481.665,176.543 483.548,177.397C522.507,195.066 522.819,242.997 492.157,264.011C486.61,267.812 486.338,267.42 115.446,421.373C58.441,445.035 49.853,451.846 22.123,433.999C19.041,431.61 -0.897,416.158 2.285,389.479C3.062,382.963 28.587,283.19 37.506,249.531L210.054,249.531C224.588,249.531 236.387,237.732 236.387,223.199C236.387,208.666 224.588,196.867 210.054,196.867L37.324,196.867Z" style="fill: currentColor;"/>
                                </g>
                            </g>
                        </svg>
                        <span class="braves-bubble-cta-text"><?php esc_html_e('Hablemos', 'braves-chat'); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <img id="chat-icon" src="<?php echo esc_url($icon_path); ?>" alt="<?php esc_attr_e('Chat', 'braves-chat'); ?>">
            <?php endif; ?>
            <span id="close-icon" style="display: none;">✕</span>
        </button>

        <div id="chat-window">
            <div id="chat-header">
                <div class="chat-header-badge">
                    <?php if (!empty($avatar_url)): ?>
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($header_title); ?>" class="chat-avatar">
                    <?php else: ?>
                        <div class="chat-avatar" style="background: #4ECDC4; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #000; font-weight: 600; font-size: 18px;">
                            <?php echo esc_html(mb_substr($header_title, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="chat-header-info">
                        <span class="chat-title"><?php echo esc_html($header_title); ?></span>
                        <?php if (!empty($header_status_text)): ?>
                            <span class="chat-status-text"><?php echo esc_html($header_status_text); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="chat-header-actions">
                     <button id="expand-chat" title="<?php esc_attr_e('Expandir', 'braves-chat'); ?>">
                        <svg class="icon-expand" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 3H21V9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 21H3V15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 3L14 10" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 21L10 14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg class="icon-collapse" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <path d="M4 14H10V20" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 10H14V4" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 10L21 3" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 21L10 14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <!-- Close button moved to outside in CSS for Braves skin, but kept here for structure -->
                </div>
            </div>

            <div id="chat-messages">
                <div class="message bot">
                    <div class="message-bubble">
                        <?php echo wp_kses_post(nl2br($welcome_message)); ?>
                    </div>
                </div>
            </div>

            <div class="typing-indicator" id="typing-indicator" style="display: none;">
                <div class="typing-dots">
                     <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
            
            <div id="chat-input-wrapper">
                <div id="chat-input-container">
                    <textarea
                        id="chat-input"
                        rows="1"
                        placeholder="<?php esc_attr_e('Enviar un mensaje', 'braves-chat'); ?>"
                        aria-label="<?php esc_attr_e('Escribe tu mensaje', 'braves-chat'); ?>"
                        style="resize: none;"
                    ></textarea>
                    
                    <button id="send-button" disabled aria-label="<?php esc_attr_e('Enviar mensaje', 'braves-chat'); ?>">
                        <svg width="20" height="20" viewBox="0 0 1624 1877" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
                            <g transform="matrix(1,0,0,1,-11388.375,-2119.869508)">
                                <g id="Enviar" transform="matrix(3.529117,0,0,4.079728,11388.375,2119.869508)">
                                    <rect x="0" y="0" width="460" height="460" style="fill:none;"/>
                                    <g transform="matrix(0,-0.849289,0.981795,0,10.83012,449.266745)">
                                        <g id="Capa-1" serif:id="Capa 1">
                                            <path d="M17.242,441.583C16.991,441.422 16.751,441.25 16.521,441.068C12.92,438.274 -7.661,421.96 -6.908,393.246C-6.557,379.887 28.625,249.957 34.624,224.088C34.259,222.293 33.275,217.539 32.148,212.799C24.493,180.606 3.532,101.215 -4.812,67.566C-17.135,17.877 28.891,-21.389 75.899,-1.549C279.986,84.588 280.822,82.323 484.992,168.199C534.508,189.026 533.145,246.854 497.255,271.451C491.683,275.269 491.483,275.05 118.904,429.703C95.734,439.321 80.425,446.17 68.605,449.801C48.171,456.077 36.726,454.123 17.242,441.583ZM78.594,199.124C98.272,199.321 172.756,198.443 207.507,199.025C218.975,199.217 226.705,199.87 228.226,200.381C235.422,202.798 240.946,209.019 243.197,216.318C245.495,223.769 244.369,232.1 239.09,238.28C235.014,243.052 228.218,246.898 217.573,246.984C190.1,247.208 102.719,246.57 78.265,247.154C75.683,257.295 68.171,286.776 50.557,355.812C46.563,371.467 43.78,381.459 42.31,388.382C40.962,394.728 40.758,397.034 43.04,400.23C45.058,402.497 46.578,404.111 48.648,404.573C51.127,405.128 53.93,404.235 57.961,402.856C65.986,400.112 77.091,394.985 94.025,388.036C95.896,387.268 466.783,233.384 467.724,232.968C472.36,230.917 475.073,227.039 475.089,222.953C475.105,218.819 472.265,214.939 467.033,212.736C430.704,197.437 94.5,58.137 62.099,44.712C61.311,44.385 58.607,42.808 55.014,41.913C51.448,41.026 46.552,40.943 42.809,45.956C40.943,48.456 40.977,50.063 42.09,55.94C43.989,65.961 48.849,83.374 57.459,117.218C70.419,168.165 76.378,190.815 78.594,199.124Z" style="fill:#ffffff;fill-opacity:1;"/>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
            
            <button id="minimize-chat" aria-label="<?php esc_attr_e('Minimizar chat', 'braves-chat'); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 9L12 15L18 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
</div>