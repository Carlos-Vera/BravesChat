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
    wp_die(__('No tienes permisos para acceder a esta página.', 'braves-chat'));
}

// Obtener instancias de componentes
$header = Admin_Header::get_instance();
$sidebar = Admin_Sidebar::get_instance();

// Obtener estado de configuración
$config_status = Template_Helpers::get_config_status();

// Detectar si se guardaron los ajustes
$settings_updated = isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true';

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
        // Renderizar header
        $header->render(array(
            'show_logo' => true,
            'show_version' => true,
        ));
        ?>

        <div class="braves-admin-body">

            <?php
            // Renderizar sidebar
            $sidebar->render($current_page);
            ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title"><?php _e('<strong>Apariencia</strong>', 'braves-chat'); ?></h1>
                    <p class="braves-page-description">
                        <?php _e('Personaliza el aspecto visual del chat: títulos, mensajes, posición y modo de visualización.', 'braves-chat'); ?>
                    </p>
                </div>

                <!-- Configuration Status Section -->
                <?php if (!$config_status['is_configured']): ?>
                <div class="braves-section braves-section--warning">
                    <?php
                    Template_Helpers::notice(
                        '<strong>' . __('Acción requerida:', 'braves-chat') . '</strong> ' .
                        __('Para que el chat funcione, necesitas configurar la URL del webhook en la página de ajustes.', 'braves-chat'),
                        'warning'
                    );
                    ?>
                </div>
                <?php endif; ?>

                <!-- Success Notice -->
                <?php if ($settings_updated): ?>
                <div class="braves-section">
                    <?php
                    Template_Helpers::notice(
                        __('Configuración guardada correctamente.', 'braves-chat'),
                        'success'
                    );
                    ?>
                </div>
                <?php endif; ?>

                <!-- Appearance Form -->
                <form action="options.php" method="post">
                    <?php
                    settings_fields('braves_chat_settings');
                    // Preservar opciones no mostradas en este formulario
                    \BravesChat\Settings::get_instance()->render_hidden_fields(array(
                        'header_title',
                        'header_status_text',
                        'header_subtitle',
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
                            <?php _e('Apariencia del Chat', 'braves-chat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: Skin del Chat -->
                            <?php
                            $chat_skin = get_option($option_prefix . 'chat_skin', 'default');
                            
                            ob_start();
                            ?>
                            <select name="<?php echo esc_attr($option_prefix . 'chat_skin'); ?>"
                                    id="<?php echo esc_attr($option_prefix . 'chat_skin'); ?>"
                                    class="braves-select"
                                    style="width: 100%;">
                                <option value="default" <?php selected('default', $chat_skin); ?>><?php esc_html_e('Default (Color)', 'braves-chat'); ?></option>
                                <option value="braves" <?php selected('braves', $chat_skin); ?>><?php esc_html_e('Braves (B&W Minimal)', 'braves-chat'); ?></option>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Selecciona el diseño visual del chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $skin_content = ob_get_clean();
                            
                            Template_Helpers::card(array(
                                'title' => __('Diseño del Chat', 'braves-chat'),
                                'description' => __('Personaliza la apariencia del widget.', 'braves-chat'),
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
                                
                                <div class="braves-media-preview-wrapper" style="margin-bottom: 10px; <?php echo empty($bubble_image) ? 'display: none;' : ''; ?>">
                                    <img src="<?php echo esc_attr($bubble_image); ?>" 
                                         class="braves-media-preview" 
                                         style="max-width: 100px; max-height: 100px; border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                                
                                <button type="button" class="button braves-upload-media" data-title="<?php esc_attr_e('Seleccionar imagen', 'braves-chat'); ?>" data-button="<?php esc_attr_e('Usar imagen', 'braves-chat'); ?>">
                                    <?php _e('Subir imagen', 'braves-chat'); ?>
                                </button>
                                <button type="button" class="button braves-remove-media" style="margin-left: 5px;"><?php _e('Quitar imagen', 'braves-chat'); ?></button>
                            </div>
                            <p class="braves-field-help" style="margin-top: 5px;">
                                <?php _e('Sube una imagen personalizada (1:1) para el botón flotante.<br/>
                                Solo visible en el skin Braves.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $bubble_content = ob_get_clean();
                            
                            Template_Helpers::card(array(
                                'title' => __('Imagen de Burbuja', 'braves-chat'),
                                'description' => __('Reemplaza el icono por defecto con tu propia imagen.', 'braves-chat'),
                                'content' => $bubble_content,
                                'custom_class' => 'braves-skin-only',
                            ));
                            ?>

                            <!-- Card: Texto de Burbuja -->
                            <?php
                            $bubble_text = get_option($option_prefix . 'bubble_text', __('Chat de voz', 'braves-chat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'bubble_text'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'bubble_text'); ?>"
                                   value="<?php echo esc_attr($bubble_text); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Chat de voz', 'braves-chat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Texto que aparece junto a la imagen de la burbuja (solo para skin Braves).', 'braves-chat'); ?>
                            </p>
                            <?php
                            $bubble_text_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Texto de Burbuja', 'braves-chat'),
                                'description' => __('Mensaje que acompaña el botón del chat.', 'braves-chat'),
                                'content' => $bubble_text_content,
                                'custom_class' => 'braves-skin-only',
                            ));
                            ?>

                            <!-- Card: Título de la Cabecera -->
                            <?php
                            $header_title = get_option($option_prefix . 'header_title', __('BravesLab AI Assistant', 'braves-chat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'header_title'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'header_title'); ?>"
                                   value="<?php echo esc_attr($header_title); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('BravesLab AI Assistant', 'braves-chat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Título principal que aparece en la cabecera del chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $header_title_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Título de la Cabecera', 'braves-chat'),
                                'description' => __('El título principal que verán los usuarios en el chat.', 'braves-chat'),
                                'content' => $header_title_content,
                            ));
                            ?>

                            <!-- Card: Texto de Estado del Header -->
                            <?php
                            $header_status_text = get_option($option_prefix . 'header_status_text', __('Chateando con Charlie', 'braves-chat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'header_status_text'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'header_status_text'); ?>"
                                   value="<?php echo esc_attr($header_status_text); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Chateando con Charlie', 'braves-chat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Texto que aparece con animación al lado del avatar cuando se abre el chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $header_status_text_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Texto de Estado (Animado)', 'braves-chat'),
                                'description' => __('Texto que se despliega junto al avatar.', 'braves-chat'),
                                'content' => $header_status_text_content,
                            ));
                            ?>

                            <!-- Card: Subtítulo de la Cabecera -->
                            <?php
                            $header_subtitle = get_option($option_prefix . 'header_subtitle', __('Artificial Intelligence Marketing Agency', 'braves-chat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'header_subtitle'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'header_subtitle'); ?>"
                                   value="<?php echo esc_attr($header_subtitle); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Artificial Intelligence Marketing Agency', 'braves-chat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Subtítulo que aparece debajo del título principal.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $header_subtitle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Subtítulo de la Cabecera', 'braves-chat'),
                                'description' => __('Texto descriptivo que complementa el título.', 'braves-chat'),
                                'content' => $header_subtitle_content,
                            ));
                            ?>

                            <!-- Card: Mensaje de Bienvenida -->
                            <?php
                            $welcome_message = get_option($option_prefix . 'welcome_message', __('¡Hola! Soy el asistente de BravesLab, tu Artificial Intelligence Marketing Agency. Integramos IA en empresas para multiplicar resultados. ¿Cómo podemos ayudarte?', 'braves-chat'));

                            ob_start();
                            ?>
                            <textarea id="<?php echo esc_attr($option_prefix . 'welcome_message'); ?>"
                                      name="<?php echo esc_attr($option_prefix . 'welcome_message'); ?>"
                                      rows="4"
                                      class="braves-textarea"
                                      style="width: 100%;"
                                      placeholder="<?php echo esc_attr(__('¡Hola! ¿Cómo podemos ayudarte?', 'braves-chat')); ?>"><?php echo esc_textarea($welcome_message); ?></textarea>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Mensaje inicial que verá el usuario al abrir el chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $welcome_message_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Mensaje de Bienvenida', 'braves-chat'),
                                'description' => __('El primer mensaje que verá el usuario en el chat.', 'braves-chat'),
                                'content' => $welcome_message_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Posición del Chat -->
                            <?php
                            $current_position = get_option($option_prefix . 'position', 'bottom-right');
                            $positions = array(
                                'bottom-right' => __('Abajo a la derecha', 'braves-chat'),
                                'bottom-left' => __('Abajo a la izquierda', 'braves-chat'),
                                'center' => __('Centro', 'braves-chat'),
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
                                <?php _e('Posición del widget de chat en la pantalla.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $position_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Posición del Chat', 'braves-chat'),
                                'description' => __('Dónde aparecerá el botón del chat en la pantalla.', 'braves-chat'),
                                'content' => $position_content,
                            ));
                            ?>

                            <!-- Card: Modo de Visualización -->
                            <?php
                            $current_mode = get_option($option_prefix . 'display_mode', 'modal');
                            $modes = array(
                                'modal' => __('Modal (ventana flotante)', 'braves-chat'),
                                'fullscreen' => __('Pantalla completa', 'braves-chat'),
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
                                <?php _e('Modo de presentación del chat al abrirse.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $mode_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Modo de Visualización', 'braves-chat'),
                                'description' => __('Cómo se mostrará el chat al abrirse.', 'braves-chat'),
                                'content' => $mode_content,
                            ));
                            ?>

                            <!-- Card: Tooltip del Botón -->
                            <?php
                            $bubble_tooltip = get_option($option_prefix . 'bubble_tooltip', __('Habla con nuestro asistente IA', 'braves-chat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'bubble_tooltip'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'bubble_tooltip'); ?>"
                                   value="<?php echo esc_attr($bubble_tooltip); ?>"
                                   class="braves-input"
                                   placeholder="<?php esc_attr_e('Habla con nuestro asistente IA', 'braves-chat'); ?>"
                                   style="width: 100%;"
                            />
                            <p class="braves-field-help" style="margin-top: 12px; font-size: 13px; color: #666;">
                                <?php _e('Texto que aparecerá al pasar el cursor sobre el botón flotante.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $tooltip_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Tooltip del Botón', 'braves-chat'),
                                'description' => __('Mensaje que se muestra al pasar el cursor sobre el botón.', 'braves-chat'),
                                'content' => $tooltip_content,
                            ));
                            ?>

                            <!-- Card: Icono del Botón de Chat -->
                            <?php
                            $current_icon = get_option($option_prefix . 'chat_icon', 'robot-chat');
                            $available_icons = array(
                                'robot-chat' => __('Original', 'braves-chat'),
                                'chat-circle' => __('Círculo', 'braves-chat'),
                                'chat-happy' => __('Happy', 'braves-chat'),
                                'chat-burbble' => __('Burbuja', 'braves-chat'),
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
                                'title' => __('Icono del Botón', 'braves-chat'),
                                'description' => __('Icono que aparecerá en el botón flotante del chat.', 'braves-chat'),
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
                                       title="<?php esc_attr_e('Seleccionar color personalizado', 'braves-chat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($icon_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'icon_color'); ?>"
                                       readonly
                                       style="display: inline-block; vertical-align: middle; width: 200px; height: 40px; font-family: monospace; text-transform: uppercase; font-size: 13px; color: #6B7280; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; background: #F9FAFB; margin: 0 0 0 12px; box-sizing: border-box;">
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="icon-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php _e('Colores del tema', 'braves-chat'); ?></span>
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
                                <?php _e('Color del icono SVG en el botón flotante del chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $icon_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Color del Icono', 'braves-chat'),
                                'description' => __('Color que se aplicará al icono SVG del botón.', 'braves-chat'),
                                'content' => $icon_color_content,
                            ));
                            ?>

                        </div>
                    </div>

                    <!-- Personalización de Colores Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php _e('Colores Personalizados', 'braves-chat'); ?>
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
                                       title="<?php esc_attr_e('Seleccionar color personalizado', 'braves-chat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($bubble_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'bubble_color'); ?>"
                                       readonly
                                       style="display: inline-block; vertical-align: middle; width: 200px; height: 40px; font-family: monospace; text-transform: uppercase; font-size: 13px; color: #6B7280; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; background: #F9FAFB; margin: 0 0 0 12px; box-sizing: border-box;">
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="bubble-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php _e('Colores del tema', 'braves-chat'); ?></span>
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
                                <?php _e('Color del botón flotante (burbuja) del chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $bubble_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Color de la Burbuja', 'braves-chat'),
                                'description' => __('Color del botón flotante que abre el chat.', 'braves-chat'),
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
                                       title="<?php esc_attr_e('Seleccionar color personalizado', 'braves-chat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($primary_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'primary_color'); ?>"
                                       readonly
                                       style="display: inline-block; vertical-align: middle; width: 200px; height: 40px; font-family: monospace; text-transform: uppercase; font-size: 13px; color: #6B7280; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; background: #F9FAFB; margin: 0 0 0 12px; box-sizing: border-box;">
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="primary-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php _e('Colores del tema', 'braves-chat'); ?></span>
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
                                <?php _e('Color del header y mensajes del asistente.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $primary_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Color Primario', 'braves-chat'),
                                'description' => __('Color principal usado en el header del chat.', 'braves-chat'),
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
                                       title="<?php esc_attr_e('Seleccionar color personalizado', 'braves-chat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($background_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'background_color'); ?>"
                                       readonly
                                       style="display: inline-block; vertical-align: middle; width: 200px; height: 40px; font-family: monospace; text-transform: uppercase; font-size: 13px; color: #6B7280; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; background: #F9FAFB; margin: 0 0 0 12px; box-sizing: border-box;">
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="background-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php _e('Colores del tema', 'braves-chat'); ?></span>
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
                                <?php _e('Color de fondo de la ventana del chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $background_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Color de Fondo', 'braves-chat'),
                                'description' => __('Color de fondo del área de mensajes.', 'braves-chat'),
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
                                       title="<?php esc_attr_e('Seleccionar color personalizado', 'braves-chat'); ?>"
                                       style="display: inline-block; vertical-align: middle; margin: 0;">
                                <input type="text"
                                       value="<?php echo esc_attr($text_color); ?>"
                                       class="braves-color-text"
                                       data-color-input="<?php echo esc_attr($option_prefix . 'text_color'); ?>"
                                       readonly
                                       style="display: inline-block; vertical-align: middle; width: 200px; height: 40px; font-family: monospace; text-transform: uppercase; font-size: 13px; color: #6B7280; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; background: #F9FAFB; margin: 0 0 0 12px; box-sizing: border-box;">
                            </div>

                            <!-- Toggle para mostrar paleta -->
                            <div style="margin-bottom: 12px;">
                                <button type="button" class="braves-palette-toggle" data-palette-target="text-color-palette">
                                    <span class="braves-palette-toggle-icon">▶</span>
                                    <span><?php _e('Colores del tema', 'braves-chat'); ?></span>
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
                                <?php _e('Color del texto de los mensajes.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $text_color_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Color de Texto', 'braves-chat'),
                                'description' => __('Color del texto principal en los mensajes.', 'braves-chat'),
                                'content' => $text_color_content,
                            ));
                            ?>

                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="braves-section braves-section--actions">
                        <div class="braves-button-group">
                            <?php submit_button(
                                __('Guardar', 'braves-chat'),
                                'primary braves-button braves-button--primary',
                                'submit',
                                false
                            ); ?>
                        </div>
                    </div>

                </form>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
