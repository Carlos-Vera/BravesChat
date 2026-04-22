<?php
/**
 * Appearance Page Template
 *
 * Página de Apariencia moderno con diseño Bentō
 *
 * @package BravesChat
 * @subpackage Admin\Templates
 * @since 1.2.1
 */

use BravesChat\Admin\Admin_Header;
use BravesChat\Admin\Admin_Sidebar;
use BravesChat\Admin\Template_Helpers;

if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have permission to access this page.', 'braveschat'));
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables, not true globals.
// Obtener instancias de componentes
$header = Admin_Header::get_instance();
$sidebar = Admin_Sidebar::get_instance();

// Obtener estado de configuración
$config_status = Template_Helpers::get_config_status();

// Detectar si se guardaron los ajustes
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Settings API sets this query arg; nonce is verified by options.php.
$settings_updated = isset($_GET['settings-updated']) && sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) === 'true';

// Prefijo de opciones
$option_prefix = 'braves_chat_';

// Obtener colores del tema de WordPress
$theme_colors = array();

// Intentar obtener colores desde el editor de bloques (theme.json)
if (function_exists('wp_get_global_settings')) {
    $global_settings = wp_get_global_settings();
    if (isset($global_settings['color']['palette']['theme'])) {
        foreach ($global_settings['color']['palette']['theme'] as $color) {
            $theme_colors[] = array(
                'name' => $color['name'],
                'color' => $color['color']
            );
        }
    }
}

// Si no hay colores del tema, agregar paleta por defecto
if (empty($theme_colors)) {
    $theme_colors = array(
        array('name' => 'Turquesa', 'color' => '#01B7AF'),
        array('name' => 'Azul', 'color' => '#3B82F6'),
        array('name' => 'Violeta', 'color' => '#8B5CF6'),
        array('name' => 'Rosa', 'color' => '#EC4899'),
        array('name' => 'Naranja', 'color' => '#F59E0B'),
        array('name' => 'Verde', 'color' => '#10B981'),
        array('name' => 'Rojo', 'color' => '#EF4444'),
        array('name' => 'Gris', 'color' => '#6B7280'),
    );
}
?>

<div class="wrap braves-admin-wrap">
    <div class="braves-admin-container">

        <?php
        // Construir notices para el header
        $notices_html = '';
        if (!$config_status['is_configured']) {
            ob_start();
            Template_Helpers::notice(
                __('Almost there! Connect the Webhook URL in Settings so your agent can start working.', 'braveschat'),
                'warning'
            );
            $notices_html .= ob_get_clean();
        }
        if ($settings_updated) {
            ob_start();
            Template_Helpers::notice(
                __('Settings saved successfully.', 'braveschat'),
                'success'
            );
            $notices_html .= ob_get_clean();
        }

        // Renderizar header
        $header->render(array(
            'show_logo'    => true,
            'show_version' => true,
            'notices'      => $notices_html,
        ));
        ?>

        <div class="braves-admin-body">

            <?php
            // Renderizar sidebar
            $sidebar->render($current_page, array('form_id' => 'braveschat-form-appearance'));
            ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title"><strong><?php esc_html_e('Appearance', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Customize the visual appearance of the chat: titles, messages, position and display mode.', 'braveschat'); ?>
                    </p>
                </div>

                <!-- Appearance Form -->
                <form id="braveschat-form-appearance" action="options.php" method="post">
                    <?php
                    settings_fields('braves_chat_settings');
                    // Preservar opciones no mostradas en este formulario
                    \BravesChat\Settings::get_instance()->render_hidden_fields(array(
                        'header_title',
                        'header_status_text',
                        'header_subtitle',
                        'agent_name',
                        'welcome_message',
                        'position',
                        'display_mode',
                        'chat_icon',
                        'icon_color',
                        'bubble_tooltip',
                        'bubble_color',
                        'primary_color',
                        'background_color',
                        'text_color',
                        'chat_skin',
                        'bubble_image',
                        'bubble_text'
                    ));
                    ?>

                    <!-- Apariencia del Chat Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php esc_html_e('Chat Appearance', 'braveschat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: Modo de Visualización -->
                            <?php
                            $current_mode = get_option($option_prefix . 'display_mode', 'modal');
                            $modes = array(
                                'modal' => __('Floating Bubble', 'braveschat'),
                                'fullscreen' => __('Full Screen', 'braveschat'),
                                'mixed' => __('Mixed Display', 'braveschat'),
                            );

                            ob_start();
                            ?>
                            <select name="<?php echo esc_attr($option_prefix . 'display_mode'); ?>"
                                    id="<?php echo esc_attr($option_prefix . 'display_mode'); ?>"
                                    class="braves-select"
                                    style="width: 100%;">
                                <?php foreach ($modes as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>"
                                            <?php selected($current_mode, $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Select the visual behavior that best fits your website design.', 'braveschat'); ?>
                                <?php if ($current_mode === 'mixed'): ?>
                                    <br><?php esc_html_e('Mixed Mode: floating bubble on the whole website, full screen on pages with the Gutenberg block.', 'braveschat'); ?>
                                <?php endif; ?>
                            </p>
                            <?php
                            $mode_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Display Mode', 'braveschat'),
                                'description' => __('Defines how the window is presented to the visitor.', 'braveschat'),
                                'content' => $mode_content,
                            ));
                            ?>

                            <!-- Card: Skin del Chat -->
                            <?php
                            $chat_skin = get_option($option_prefix . 'chat_skin', 'default');
                            
                            ob_start();
                            ?>
                            <select name="<?php echo esc_attr($option_prefix . 'chat_skin'); ?>"
                                    id="<?php echo esc_attr($option_prefix . 'chat_skin'); ?>"
                                    class="braves-select"
                                    style="width: 100%;">
                                <option value="default" <?php selected('default', $chat_skin); ?>><?php esc_html_e('Basic Skin', 'braveschat'); ?></option>
                                <option value="braves" <?php selected('braves', $chat_skin); ?>><?php esc_html_e('Braves Skin', 'braveschat'); ?></option>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Choose the design template that best fits your brand.', 'braveschat'); ?>
                            </p>
                            <?php
                            $skin_content = ob_get_clean();
                            
                            Template_Helpers::card(array(
                                'title' => __('Chat Design', 'braveschat'),
                                'description' => __('Customize the visual style of the Agent.', 'braveschat'),
                                'content' => $skin_content,
                            ));
                            ?>

                            <!-- Card: Imagen de Burbuja Personalizada -->
                            <?php
                            $bubble_image = get_option($option_prefix . 'bubble_image', '');
                            
                            ob_start();
                            ?>
                            <div class="braves-media-uploader">
                                <input type="hidden" 
                                       name="<?php echo esc_attr($option_prefix . 'bubble_image'); ?>" 
                                       id="<?php echo esc_attr($option_prefix . 'bubble_image'); ?>" 
                                       value="<?php echo esc_attr($bubble_image); ?>" 
                                       class="braves-media-url">
                                
                                <div class="braves-media-preview-wrapper<?php echo esc_attr( empty($bubble_image) ? ' braves-hidden' : '' ); ?>">
                                    <img src="<?php echo esc_url($bubble_image); ?>"
                                         class="braves-media-preview">
                                </div>

                                <button type="button" class="button braves-upload-media" data-title="<?php esc_attr_e('Select image', 'braveschat'); ?>" data-button="<?php esc_attr_e('Use image', 'braveschat'); ?>">
                                    <?php esc_html_e('Upload image', 'braveschat'); ?>
                                </button>
                                <button type="button" class="button braves-remove-media braves-ml-sm"><?php esc_html_e('Remove image', 'braveschat'); ?></button>
                            </div>
                            <p class="braves-field-help braves-mt-sm">
                                <?php echo wp_kses_post( sprintf(
                                    /* translators: %s: line break tag */
                                    __('Upload a custom image (1:1) for the floating button.%sOnly visible in the Braves skin.', 'braveschat'),
                                    '<br/>'
                                ) ); ?>
                            </p>
                            <?php
                            $bubble_content = ob_get_clean();
                            
                            Template_Helpers::card(array(
                                'title' => __('Bubble Image', 'braveschat'),
                                'description' => __('Replace the default icon with your own image.', 'braveschat'),
                                'content' => $bubble_content,
                            ));
                            ?>

                            <!-- Card: Texto de Burbuja -->
                            <?php
                            $bubble_text = get_option($option_prefix . 'bubble_text', __('Need help?', 'braveschat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'bubble_text'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'bubble_text'); ?>"
                                   value="<?php echo esc_attr($bubble_text); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Need help?', 'braveschat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Text that appears next to the bubble image (only for Braves skin).', 'braveschat'); ?>
                            </p>
                            <?php
                            $bubble_text_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Bubble Text', 'braveschat'),
                                'description' => __('Message that accompanies the chat button.', 'braveschat'),
                                'content' => $bubble_text_content,
                                //'custom_class' => 'braves-skin-only',
                            ));
                            ?>

                            <!-- Card: Título de la Cabecera -->
                            <?php
                            $header_title = get_option($option_prefix . 'header_title', __('BravesLab AI Assistant', 'braveschat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'header_title'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'header_title'); ?>"
                                   value="<?php echo esc_attr($header_title); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('BravesLab AI Assistant', 'braveschat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Main title shown in the chat header.', 'braveschat'); ?>
                            </p>
                            <?php
                            $header_title_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Header Title', 'braveschat'),
                                'description' => __('The main title users will see in the chat.', 'braveschat'),
                                'content' => $header_title_content,
                            ));
                            ?>

                            <!-- Card: Texto de Estado del Header -->
                            <?php
                            $header_status_text = get_option($option_prefix . 'header_status_text', __('Chatting with Charlie', 'braveschat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'header_status_text'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'header_status_text'); ?>"
                                   value="<?php echo esc_attr($header_status_text); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Chatting with Charlie', 'braveschat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Text that appears with animation next to the avatar when the chat opens.', 'braveschat'); ?>
                            </p>
                            <?php
                            $header_status_text_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Status Text (Animated)', 'braveschat'),
                                'description' => __('Text that unfolds next to the avatar.', 'braveschat'),
                                'content' => $header_status_text_content,
                            ));
                            ?>

                            <!-- Card: Subtítulo de la Cabecera -->
                            <?php
                            $header_subtitle = get_option($option_prefix . 'header_subtitle', __('Artificial Intelligence Marketing Agency', 'braveschat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'header_subtitle'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'header_subtitle'); ?>"
                                   value="<?php echo esc_attr($header_subtitle); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Artificial Intelligence Marketing Agency', 'braveschat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Subtitle shown below the main title.', 'braveschat'); ?>
                            </p>
                            <?php
                            $header_subtitle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Header Subtitle', 'braveschat'),
                                'description' => __('Descriptive text that complements the title.', 'braveschat'),
                                'content' => $header_subtitle_content,
                            ));
                            ?>

                            <!-- Card: Nombre del Agente -->
                            <?php
                            $agent_name = get_option($option_prefix . 'agent_name', '');
                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'agent_name'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'agent_name'); ?>"
                                   value="<?php echo esc_attr($agent_name); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php esc_attr_e('E.g.: Charlie', 'braveschat'); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('This name will be used in the history to organize and filter conversations.', 'braveschat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('Agent Name', 'braveschat'),
                                'description' => __('Defines how you will identify your agent in your records.', 'braveschat'),
                                'content'     => ob_get_clean(),
                            ));
                            ?>

                            <!-- Card: Mensaje de Bienvenida -->
                            <?php
                            $welcome_message = get_option($option_prefix . 'welcome_message', __('Hello! I\'m the BravesLab assistant, your Artificial Intelligence Marketing Agency. We integrate AI into businesses to multiply results. How can we help you?', 'braveschat'));

                            ob_start();
                            ?>
                            <textarea id="<?php echo esc_attr($option_prefix . 'welcome_message'); ?>"
                                      name="<?php echo esc_attr($option_prefix . 'welcome_message'); ?>"
                                      rows="4"
                                      class="braves-textarea"
                                      style="width: 100%;"
                                      placeholder="<?php echo esc_attr(__('Hello! How can we help you?', 'braveschat')); ?>"><?php echo esc_textarea($welcome_message); ?></textarea>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Initial message the user will see when opening the chat.', 'braveschat'); ?>
                            </p>
                            <?php
                            $welcome_message_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Welcome Message', 'braveschat'),
                                'description' => __('The first message the user will see in the chat.', 'braveschat'),
                                'content' => $welcome_message_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Posición del Chat -->
                            <?php
                            $current_position = get_option($option_prefix . 'position', 'bottom-right');
                            $positions = array(
                                'bottom-right' => __('Bottom right', 'braveschat'),
                                'bottom-left' => __('Bottom left', 'braveschat'),
                                'center' => __('Center', 'braveschat'),
                            );

                            ob_start();
                            ?>
                            <select name="<?php echo esc_attr($option_prefix . 'position'); ?>"
                                    id="<?php echo esc_attr($option_prefix . 'position'); ?>"
                                    class="braves-select"
                                    style="width: 100%;">
                                <?php foreach ($positions as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>"
                                            <?php selected($current_position, $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Position of the chat widget on the screen.', 'braveschat'); ?>
                            </p>
                            <?php
                            $position_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Chat Position', 'braveschat'),
                                'description' => __('Where the chat button will appear on the screen.', 'braveschat'),
                                'content' => $position_content,
                            ));
                            ?>

                            <!-- Card: Tooltip del Botón -->
                            <?php
                            $bubble_tooltip = get_option($option_prefix . 'bubble_tooltip', __('Talk to our AI assistant', 'braveschat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'bubble_tooltip'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'bubble_tooltip'); ?>"
                                   value="<?php echo esc_attr($bubble_tooltip); ?>"
                                   class="braves-input"
                                   placeholder="<?php esc_attr_e('Talk to our AI assistant', 'braveschat'); ?>"
                                   style="width: 100%;"
                            />
                            <p class="braves-field-help" style="margin-top: 12px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Text that will appear when hovering over the floating button.', 'braveschat'); ?>
                            </p>
                            <?php
                            $tooltip_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Button Tooltip', 'braveschat'),
                                'description' => __('Message shown when hovering over the button.', 'braveschat'),
                                'content' => $tooltip_content,
                            ));
                            ?>

                            <!-- Card: Icono del Botón de Chat -->
                            <?php
                            $current_icon = get_option($option_prefix . 'chat_icon', 'robot-chat');
                            $available_icons = array(
                                'robot-chat' => __('Original', 'braveschat'),
                                'chat-circle' => __('Circle', 'braveschat'),
                                'chat-happy' => __('Happy', 'braveschat'),
                                'chat-burbble' => __('Bubble', 'braveschat'),
                            );

                            ob_start();
                            ?>
<div class="braves-icon-tabs">
                                <?php foreach ($available_icons as $icon_key => $icon_label): ?>
                                    <label class="braves-icon-tab">
                                        <input type="radio"
                                               name="<?php echo esc_attr($option_prefix . 'chat_icon'); ?>"
                                               value="<?php echo esc_attr($icon_key); ?>"
                                               <?php checked($current_icon, $icon_key); ?>>
                                        <div class="braves-icon-tab__content">
                                            <img src="<?php echo esc_url(BRAVES_CHAT_PLUGIN_URL . 'assets/media/chat-icons/' . $icon_key . '.svg'); ?>"
                                                 alt="<?php echo esc_attr($icon_label); ?>"
                                                 width="32"
                                                 height="32">
                                            <span><?php echo esc_html($icon_label); ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php
                            $icon_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Button Icon', 'braveschat'),
                                'description' => __('Icon that will appear in the floating chat button.', 'braveschat'),
                                'content' => $icon_content,
                                'custom_class' => 'braves-card--full-width default-skin-only',
                            ));
                            ?>

                            <!-- Card: Color del Icono -->
                            <?php
                            $icon_color = get_option($option_prefix . 'icon_color', '#f2f2f2');

                            ob_start();
                            ?>
                            <!-- Color Picker Principal - Material Design List Style -->
                            <div style="margin-bottom: 16px;">
                                <input type="color"
                                       id="<?php echo esc_attr($option_prefix . 'icon_color'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'icon_color'); ?>"
                                       value="<?php echo esc_attr($icon_color); ?>"
                                       class="braves-color-picker"
                                       title="<?php esc_attr_e('Select custom color', 'braveschat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($icon_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'icon_color'); ?>"
                                       readonly
                                       >
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="icon-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php esc_html_e('Theme colors', 'braveschat'); ?></span>
                                </button>
                            </div>

                            <!-- Paleta de colores predefinidos (oculta por defecto) -->
                            <div class="braves-color-palette braves-color-palette--collapsed" id="icon-color-palette">
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php foreach ($theme_colors as $preset): ?>
                                        <button type="button"
                                                class="braves-color-preset"
                                                data-color="<?php echo esc_attr($preset['color']); ?>"
                                                data-target="<?php echo esc_attr($option_prefix . 'icon_color'); ?>"
                                                title="<?php echo esc_attr($preset['name']); ?>"
                                                style="background: <?php echo esc_attr($preset['color']); ?>;">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <p class="braves-field-help" style="margin-top: 12px; font-size: 13px; color: #666;">
                                <?php esc_html_e('SVG icon color in the floating chat button.', 'braveschat'); ?>
                            </p>
                            <?php
                            $icon_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Icon Color', 'braveschat'),
                                'description' => __('Color that will be applied to the button SVG icon.', 'braveschat'),
                                'content' => $icon_color_content,
                            ));
                            ?>

                        </div>
                    </div>

                    <!-- Personalización de Colores Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php esc_html_e('Custom Colors', 'braveschat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: Color de la Burbuja -->
                            <?php
                            $bubble_color = get_option($option_prefix . 'bubble_color', '#01B7AF');

                            ob_start();
                            ?>
                            <!-- Color Picker Principal - Material Design List Style -->
                            <div style="margin-bottom: 16px;">
                                <input type="color"
                                       id="<?php echo esc_attr($option_prefix . 'bubble_color'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'bubble_color'); ?>"
                                       value="<?php echo esc_attr($bubble_color); ?>"
                                       class="braves-color-picker"
                                       title="<?php esc_attr_e('Select custom color', 'braveschat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($bubble_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'bubble_color'); ?>"
                                       readonly
                                       >
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="bubble-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php esc_html_e('Theme colors', 'braveschat'); ?></span>
                                </button>
                            </div>

                            <!-- Paleta de colores predefinidos (oculta por defecto) -->
                            <div class="braves-color-palette braves-color-palette--collapsed" id="bubble-color-palette">
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php foreach ($theme_colors as $preset): ?>
                                        <button type="button"
                                                class="braves-color-preset"
                                                data-color="<?php echo esc_attr($preset['color']); ?>"
                                                data-target="<?php echo esc_attr($option_prefix . 'bubble_color'); ?>"
                                                title="<?php echo esc_attr($preset['name']); ?>"
                                                style="background: <?php echo esc_attr($preset['color']); ?>;">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <p class="braves-field-help" style="margin-top: 12px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Color of the floating button (bubble) of the chat.', 'braveschat'); ?>
                            </p>
                            <?php
                            $bubble_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Bubble Color', 'braveschat'),
                                'description' => __('Color of the floating button that opens the chat.', 'braveschat'),
                                'content' => $bubble_color_content,
                            ));
                            ?>

                            <!-- Card: Color Primario -->
                            <?php
                            $primary_color = get_option($option_prefix . 'primary_color', '#01B7AF');

                            ob_start();
                            ?>
                            <!-- Color Picker Principal - Material Design List Style -->
                            <div style="margin-bottom: 16px;">
                                <input type="color"
                                       id="<?php echo esc_attr($option_prefix . 'primary_color'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'primary_color'); ?>"
                                       value="<?php echo esc_attr($primary_color); ?>"
                                       class="braves-color-picker"
                                       title="<?php esc_attr_e('Select custom color', 'braveschat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($primary_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'primary_color'); ?>"
                                       readonly
                                       >
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="primary-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php esc_html_e('Theme colors', 'braveschat'); ?></span>
                                </button>
                            </div>

                            <!-- Paleta de colores predefinidos (oculta por defecto) -->
                            <div class="braves-color-palette braves-color-palette--collapsed" id="primary-color-palette">
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php foreach ($theme_colors as $preset): ?>
                                        <button type="button"
                                                class="braves-color-preset"
                                                data-color="<?php echo esc_attr($preset['color']); ?>"
                                                data-target="<?php echo esc_attr($option_prefix . 'primary_color'); ?>"
                                                title="<?php echo esc_attr($preset['name']); ?>"
                                                style="background: <?php echo esc_attr($preset['color']); ?>;">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <p class="braves-field-help" style="margin-top: 12px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Color of the header and assistant messages.', 'braveschat'); ?>
                            </p>
                            <?php
                            $primary_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Primary Color', 'braveschat'),
                                'description' => __('Main color used in the chat header.', 'braveschat'),
                                'content' => $primary_color_content,
                            ));
                            ?>

                            <!-- Card: Color de Fondo -->
                            <?php
                            $background_color = get_option($option_prefix . 'background_color', '#FFFFFF');

                            ob_start();
                            ?>
                            <!-- Color Picker Principal - Material Design List Style -->
                            <div style="margin-bottom: 16px;">
                                <input type="color"
                                       id="<?php echo esc_attr($option_prefix . 'background_color'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'background_color'); ?>"
                                       value="<?php echo esc_attr($background_color); ?>"
                                       class="braves-color-picker"
                                       title="<?php esc_attr_e('Select custom color', 'braveschat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($background_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'background_color'); ?>"
                                       readonly
                                       >
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="background-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php esc_html_e('Theme colors', 'braveschat'); ?></span>
                                </button>
                            </div>

                            <!-- Paleta de colores predefinidos (oculta por defecto) -->
                            <div class="braves-color-palette braves-color-palette--collapsed" id="background-color-palette">
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php
                                    // Para fondo, agregar blanco y negro
                                    $background_presets = array_merge(
                                        array(
                                            array('name' => 'Blanco', 'color' => '#FFFFFF'),
                                            array('name' => 'Gris claro', 'color' => '#F3F4F6'),
                                            array('name' => 'Negro', 'color' => '#1F2937'),
                                        ),
                                        $theme_colors
                                    );
                                    foreach ($background_presets as $preset): ?>
                                        <button type="button"
                                                class="braves-color-preset"
                                                data-color="<?php echo esc_attr($preset['color']); ?>"
                                                data-target="<?php echo esc_attr($option_prefix . 'background_color'); ?>"
                                                title="<?php echo esc_attr($preset['name']); ?>"
                                                style="background: <?php echo esc_attr($preset['color']); ?>;">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <p class="braves-field-help" style="margin-top: 12px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Background color of the chat window.', 'braveschat'); ?>
                            </p>
                            <?php
                            $background_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Background Color', 'braveschat'),
                                'description' => __('Background color of the message area.', 'braveschat'),
                                'content' => $background_color_content,
                            ));
                            ?>

                            <!-- Card: Color de Texto -->
                            <?php
                            $text_color = get_option($option_prefix . 'text_color', '#333333');

                            ob_start();
                            ?>
                            <!-- Color Picker Principal - Material Design List Style -->
                            <div style="margin-bottom: 16px;">
                                <input type="color"
                                       id="<?php echo esc_attr($option_prefix . 'text_color'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'text_color'); ?>"
                                       value="<?php echo esc_attr($text_color); ?>"
                                       class="braves-color-picker"
                                       title="<?php esc_attr_e('Select custom color', 'braveschat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($text_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'text_color'); ?>"
                                       readonly
                                       >
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="text-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php esc_html_e('Theme colors', 'braveschat'); ?></span>
                                </button>
                            </div>

                            <!-- Paleta de colores predefinidos (oculta por defecto) -->
                            <div class="braves-color-palette braves-color-palette--collapsed" id="text-color-palette">
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php
                                    // Para texto, agregar tonos oscuros
                                    $text_presets = array_merge(
                                        array(
                                            array('name' => 'Negro', 'color' => '#1F2937'),
                                            array('name' => 'Gris oscuro', 'color' => '#4B5563'),
                                            array('name' => 'Gris', 'color' => '#6B7280'),
                                            array('name' => 'Blanco', 'color' => '#FFFFFF'),
                                        ),
                                        $theme_colors
                                    );
                                    foreach ($text_presets as $preset): ?>
                                        <button type="button"
                                                class="braves-color-preset"
                                                data-color="<?php echo esc_attr($preset['color']); ?>"
                                                data-target="<?php echo esc_attr($option_prefix . 'text_color'); ?>"
                                                title="<?php echo esc_attr($preset['name']); ?>"
                                                style="background: <?php echo esc_attr($preset['color']); ?>;">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <p class="braves-field-help" style="margin-top: 12px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Color of the message text.', 'braveschat'); ?>
                            </p>
                            <?php
                            $text_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Text Color', 'braveschat'),
                                'description' => __('Color of the main text in messages.', 'braveschat'),
                                'content' => $text_color_content,
                            ));
                            ?>

                        </div>
                    </div>

                </form>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
