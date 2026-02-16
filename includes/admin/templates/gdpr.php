<?php
/**
 * GDPR Page Template
 *
 * Página de GDPR con diseño Bentō
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
                    <h1 class="braves-page-title"><?php _e('<strong>GDPR</strong>', 'braves-chat'); ?></h1>
                    <p class="braves-page-description">
                        <?php _e('Configure el banner de consentimiento de cookies para cumplir con las regulaciones GDPR.<br/>El sistema utiliza cookies persistentes con fingerprinting del navegador.', 'braves-chat'); ?>
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

                <!-- GDPR Form -->
                <form action="options.php" method="post">
                    <?php
                    settings_fields('braves_chat_settings');
                    // Preservar opciones no mostradas en este formulario
                    \BravesChat\Settings::get_instance()->render_hidden_fields(array(
                        'gdpr_enabled',
                        'gdpr_message',
                        'gdpr_accept_text'
                    ));
                    ?>

                    <!-- GDPR Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php _e('Compliance GDPR / Cookies', 'braves-chat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: Habilitar Banner GDPR -->
                            <?php
                            $gdpr_enabled = get_option($option_prefix . 'gdpr_enabled', false);

                            ob_start();
                            ?>
                            <label class="braves-toggle-wrapper">
                                <input type="checkbox"
                                       id="<?php echo esc_attr($option_prefix . 'gdpr_enabled'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'gdpr_enabled'); ?>"
                                       value="1"
                                       <?php checked(1, $gdpr_enabled); ?>
                                       class="braves-toggle-input">
                                <span class="braves-toggle-slider"></span>
                            </label>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Mostrar banner de consentimiento de cookies. El consentimiento se guarda en localStorage.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Habilitar Banner GDPR', 'braves-chat'),
                                'description' => __('Muestra un banner de consentimiento antes de crear cookies.', 'braves-chat'),
                                'content' => $toggle_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Mensaje del Banner -->
                            <?php
                            $gdpr_message = get_option($option_prefix . 'gdpr_message', __('Este sitio utiliza cookies para mejorar tu experiencia y proporcionar un servicio de chat personalizado. Al continuar navegando, aceptas nuestra política de cookies.', 'braves-chat'));

                            ob_start();
                            ?>
                            <textarea id="<?php echo esc_attr($option_prefix . 'gdpr_message'); ?>"
                                      name="<?php echo esc_attr($option_prefix . 'gdpr_message'); ?>"
                                      rows="4"
                                      class="braves-textarea"
                                      style="width: 100%;"
                                      placeholder="<?php echo esc_attr(__('Este sitio utiliza cookies...', 'braves-chat')); ?>"><?php echo esc_textarea($gdpr_message); ?></textarea>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Mensaje que se mostrará en el banner de cookies.<br>
                                Soporta <b>HTML</b> y <b>Markdown</b> (negrita**, *cursiva*, [enlaces](url)).', 'braves-chat'); ?>
                            </p>
                            <?php
                            $message_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Mensaje del Banner', 'braves-chat'),
                                'description' => __('Texto informativo sobre el uso de cookies en el chat.', 'braves-chat'),
                                'content' => $message_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Texto del Botón de Aceptar -->
                            <?php
                            $gdpr_accept_text = get_option($option_prefix . 'gdpr_accept_text', __('Aceptar', 'braves-chat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'gdpr_accept_text'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'gdpr_accept_text'); ?>"
                                   value="<?php echo esc_attr($gdpr_accept_text); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Aceptar', 'braves-chat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Texto del botón para aceptar las cookies (ej: "Aceptar", "Entendido", "Acepto").', 'braves-chat'); ?>
                            </p>
                            <?php
                            $button_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Texto del Botón de Aceptar', 'braves-chat'),
                                'description' => __('Etiqueta del botón de aceptación de cookies.', 'braves-chat'),
                                'content' => $button_content,
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
