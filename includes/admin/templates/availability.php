<?php
/**
 * Availability Page Template
 *
 * Página de Horarios con diseño Bentō
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
                    <h1 class="braves-page-title"><strong><?php esc_html_e('Horarios', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Configura los horarios de disponibilidad del chat. Cuando está activado, el chat solo estará disponible durante el horario especificado.', 'braveschat'); ?>
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

                <!-- Availability Form -->
                <form action="options.php" method="post">
                    <?php
                    settings_fields('braves_chat_settings');
                    // Preservar opciones no mostradas en este formulario
                    \BravesChat\Settings::get_instance()->render_hidden_fields(array(
                        'availability_enabled',
                        'availability_start',
                        'availability_end',
                        'availability_timezone',
                        'availability_message'
                    ));
                    ?>

                    <!-- Horarios Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php esc_html_e('Configuración de Horarios', 'braveschat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: Habilitar Horarios -->
                            <?php
                            $availability_enabled = get_option($option_prefix . 'availability_enabled', false);

                            ob_start();
                            ?>
                            <label class="braves-toggle-wrapper">
                                <input type="checkbox"
                                       id="<?php echo esc_attr($option_prefix . 'availability_enabled'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'availability_enabled'); ?>"
                                       value="1"
                                       <?php checked(1, $availability_enabled); ?>
                                       class="braves-toggle-input">
                                <span class="braves-toggle-slider"></span>
                            </label>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Activar restricción por horarios de atención', 'braveschat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Habilitar Horarios', 'braveschat'),
                                'description' => __('Activa las restricciones de horario para el chat.', 'braveschat'),
                                'content' => $toggle_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Zona Horaria -->
                            <?php
                            $current_timezone = get_option($option_prefix . 'availability_timezone', 'Europe/Madrid');
                            $timezones = timezone_identifiers_list();

                            ob_start();
                            ?>
                            <select name="<?php echo esc_attr($option_prefix . 'availability_timezone'); ?>"
                                    id="<?php echo esc_attr($option_prefix . 'availability_timezone'); ?>"
                                    class="braves-select"
                                    style="width: 100%;">
                                <?php foreach ($timezones as $timezone): ?>
                                    <option value="<?php echo esc_attr($timezone); ?>"
                                            <?php selected($current_timezone, $timezone); ?>>
                                        <?php echo esc_html(str_replace('_', ' ', $timezone)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Zona horaria de referencia para los horarios configurados.', 'braveschat'); ?>
                            </p>
                            <?php
                            $timezone_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Zona Horaria', 'braveschat'),
                                'description' => __('Zona horaria de referencia para el horario.', 'braveschat'),
                                'content' => $timezone_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Hora de Inicio -->
                            <?php
                            $availability_start = get_option($option_prefix . 'availability_start', '09:00');

                            ob_start();
                            ?>
                            <input type="time"
                                   id="<?php echo esc_attr($option_prefix . 'availability_start'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'availability_start'); ?>"
                                   value="<?php echo esc_attr($availability_start); ?>"
                                   class="braves-input"
                                   style="width: 100%;">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Hora de inicio del horario de atención (formato 24h).', 'braveschat'); ?>
                            </p>
                            <?php
                            $start_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Hora de Inicio', 'braveschat'),
                                'description' => __('Hora en que el chat comienza a estar disponible.', 'braveschat'),
                                'content' => $start_content,
                            ));
                            ?>

                            <!-- Card: Hora de Fin -->
                            <?php
                            $availability_end = get_option($option_prefix . 'availability_end', '18:00');

                            ob_start();
                            ?>
                            <input type="time"
                                   id="<?php echo esc_attr($option_prefix . 'availability_end'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'availability_end'); ?>"
                                   value="<?php echo esc_attr($availability_end); ?>"
                                   class="braves-input"
                                   style="width: 100%;">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Hora de fin del horario de atención (formato 24h).', 'braveschat'); ?>
                            </p>
                            <?php
                            $end_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Hora de Fin', 'braveschat'),
                                'description' => __('Hora en que el chat deja de estar disponible.', 'braveschat'),
                                'content' => $end_content,
                            ));
                            ?>

                            <!-- Card: Mensaje Fuera de Horario -->
                            <?php
                            $availability_message = get_option($option_prefix . 'availability_message', __('Nuestro horario de atención es de 9:00 a 18:00. Déjanos tu mensaje y te responderemos lo antes posible.', 'braveschat'));
                            ?>
                            <div class="braves-card braves-card--full-width">
                                <h3 class="braves-card__title"><?php esc_html_e('Mensaje Fuera de Horario', 'braveschat'); ?></h3>
                                <p class="braves-card__description"><?php esc_html_e('Texto que verán los usuarios fuera del horario de atención.', 'braveschat'); ?></p>
                                <div class="braves-card__content">
                                    <?php
                                    wp_editor($availability_message, 'braves_chat_availability_message', array(
                                        'textarea_name' => $option_prefix . 'availability_message',
                                        'media_buttons' => false,
                                        'teeny'         => true,
                                        'textarea_rows' => 6,
                                        'quicktags'     => true,
                                    ));
                                    ?>
                                    <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                        <?php esc_html_e('Mensaje que se mostrará cuando el usuario intente usar el chat fuera del horario de atención.', 'braveschat'); ?>
                                    </p>
                                </div>
                            </div>

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
