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
            $sidebar->render($current_page, array('form_id' => 'braveschat-form-gdpr'));
            ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title"><strong><?php esc_html_e('GDPR', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php echo wp_kses_post( __('Configure the cookie consent banner to comply with GDPR regulations.<br/>The system uses persistent cookies with browser fingerprinting.', 'braveschat') ); ?>
                    </p>
                </div>

                <!-- GDPR Form -->
                <form id="braveschat-form-gdpr" action="options.php" method="post">
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
                            <?php esc_html_e('GDPR / Cookie Compliance', 'braveschat'); ?>
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
                                <?php esc_html_e('Show cookie consent banner. Consent is saved in localStorage.', 'braveschat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Enable GDPR Banner', 'braveschat'),
                                'description' => __('Shows a consent banner before creating cookies.', 'braveschat'),
                                'content' => $toggle_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Mensaje del Banner -->
                            <?php
                            $gdpr_message = get_option($option_prefix . 'gdpr_message', __('This site uses cookies to improve your experience and provide a personalized chat service. By continuing to browse, you accept our cookie policy.', 'braveschat'));
                            ?>
                            <div class="braves-card braves-card--full-width">
                                <h3 class="braves-card__title"><?php esc_html_e('Banner Message', 'braveschat'); ?></h3>
                                <p class="braves-card__description"><?php esc_html_e('Informational text about cookie usage in the chat.', 'braveschat'); ?></p>
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
                                        <?php esc_html_e('Message shown in the cookie banner. You can use bold, italics and links.', 'braveschat'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Card: Texto del Botón de Aceptar -->
                            <?php
                            $gdpr_accept_text = get_option($option_prefix . 'gdpr_accept_text', __('Accept', 'braveschat'));

                            ob_start();
                            ?>
                            <input type="text"
                                   id="<?php echo esc_attr($option_prefix . 'gdpr_accept_text'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'gdpr_accept_text'); ?>"
                                   value="<?php echo esc_attr($gdpr_accept_text); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="<?php echo esc_attr(__('Accept', 'braveschat')); ?>">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php esc_html_e('Text for the cookie acceptance button (e.g.: "Accept", "Got it", "I agree").', 'braveschat'); ?>
                            </p>
                            <?php
                            $button_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Accept Button Text', 'braveschat'),
                                'description' => __('Label for the cookie acceptance button.', 'braveschat'),
                                'content' => $button_content,
                            ));
                            ?>

                        </div>
                    </div>

                </form>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
