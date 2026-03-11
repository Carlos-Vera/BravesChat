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
    wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'braveschat'));
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
                    <h1 class="braves-page-title"><strong><?php esc_html_e('GDPR', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php echo wp_kses_post( __('Configure el banner de consentimiento de cookies para cumplir con las regulaciones GDPR.<br/>El sistema utiliza cookies persistentes con fingerprinting del navegador.', 'braveschat') ); ?>
                    </p>
                </div>

                <!-- Configuration Status Section -->
                <?php if (!$config_status['is_configured']): ?>
                <div class="braves-section braves-section--warning">
                    <?php
                    Template_Helpers::notice(
                        '<strong>' . __('Acción requerida:', 'braveschat') . '</strong> ' .
                        __('Para que el chat funcione, necesitas configurar la URL del webhook en la página de ajustes.', 'braveschat'),
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
                        __('Configuración guardada correctamente.', 'braveschat'),
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
                            <?php esc_html_e('Compliance GDPR / Cookies', 'braveschat'); ?>
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
                                <?php esc_html_e('Mostrar banner de consentimiento de cookies. El consentimiento se guarda en localStorage.', 'braveschat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Habilitar Banner GDPR', 'braveschat'),
                                'description' => __('Muestra un banner de consentimiento antes de crear cookies.', 'braveschat'),
                                'content' => $toggle_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Mensaje del Banner -->
                            <?php
                            $gdpr_message = get_option($option_prefix . 'gdpr_message', __('Este sitio utiliza cookies para mejorar tu experiencia y proporcionar un servicio de chat personalizado. Al continuar navegando, aceptas nuestra política de cookies.', 'braveschat'));
                            ?>
                            <div class="braves-card braves-card--full-width">
                                <h3 class="braves-card__title"><?php esc_html_e('Mensaje del Banner', 'braveschat'); ?></h3>
                                <p class="braves-card__description"><?php esc_html_e('Texto informativo sobre el uso de cookies en el chat.', 'braveschat'); ?></p>
                                <div class="braves-card__content">
                                    <?php
                                    wp_editor($gdpr_message, 'braves_chat_gdpr_message', array(
                                        'textarea_name' => $option_prefix . 'gdpr_message',
                                        'media_buttons' => false,
                                        'teeny'         => true,
                                        'textarea_rows' => 6,
                                        'quicktags'     => true,
                                    ));
                                    ?>
                                    <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                        <?php esc_html_e('Mensaje que se mostrará en el banner de cookies. Puedes usar negritas, cursivas y enlaces.', 'braveschat'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Card: Texto del Botón de Aceptar -->
                            <?php
                            $gdpr_accept_text = get_option($option_prefix . 'gdpr_accept_text', __('Aceptar', 'braveschat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'gdpr_accept_text'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'gdpr_accept_text'); ?>"
                                   value="<?php echo esc_attr($gdpr_accept_text); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Aceptar', 'braveschat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Texto del botón para aceptar las cookies (ej: "Aceptar", "Entendido", "Acepto").', 'braveschat'); ?>
                            </p>
                            <?php
                            $button_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Texto del Botón de Aceptar', 'braveschat'),
                                'description' => __('Etiqueta del botón de aceptación de cookies.', 'braveschat'),
                                'content' => $button_content,
                            ));
                            ?>

                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="braves-section braves-section--actions">
                        <div class="braves-button-group">
                            <?php submit_button(
                                __('Guardar', 'braveschat'),
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
