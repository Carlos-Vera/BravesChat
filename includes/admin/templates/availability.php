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
            $sidebar->render($current_page, array('form_id' => 'braveschat-form-availability'));
            ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title"><strong><?php esc_html_e('Availability', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Configure the chat availability schedule. When enabled, the chat will only be available during the specified hours.', 'braveschat'); ?>
                    </p>
                </div>

                <!-- Availability Form -->
                <form id="braveschat-form-availability" action="options.php" method="post">
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
                            <?php esc_html_e('Schedule Settings', 'braveschat'); ?>
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
                                <?php esc_html_e('Enable business hours restriction', 'braveschat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Enable Schedule', 'braveschat'),
                                'description' => __('Activate schedule restrictions for the chat.', 'braveschat'),
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
                                <?php esc_html_e('Reference timezone for the configured schedule.', 'braveschat'); ?>
                            </p>
                            <?php
                            $timezone_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Timezone', 'braveschat'),
                                'description' => __('Reference timezone for the schedule.', 'braveschat'),
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
                                <?php esc_html_e('Start time of the business hours (24h format).', 'braveschat'); ?>
                            </p>
                            <?php
                            $start_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Start Time', 'braveschat'),
                                'description' => __('Time when the chat becomes available.', 'braveschat'),
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
                                <?php esc_html_e('End time of the business hours (24h format).', 'braveschat'); ?>
                            </p>
                            <?php
                            $end_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('End Time', 'braveschat'),
                                'description' => __('Time when the chat stops being available.', 'braveschat'),
                                'content' => $end_content,
                            ));
                            ?>

                            <!-- Card: Mensaje Fuera de Horario -->
                            <?php
                            $availability_message = get_option($option_prefix . 'availability_message', __('Our business hours are from 9:00 to 18:00. Leave us your message and we will get back to you as soon as possible.', 'braveschat'));
                            ?>
                            <div class="braves-card braves-card--full-width">
                                <h3 class="braves-card__title"><?php esc_html_e('Off-Hours Message', 'braveschat'); ?></h3>
                                <p class="braves-card__description"><?php esc_html_e('Text users will see outside business hours.', 'braveschat'); ?></p>
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
                                        <?php esc_html_e('Message shown when the user tries to use the chat outside business hours.', 'braveschat'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                </form>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
