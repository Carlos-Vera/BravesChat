<?php
/**
 * Settings Page Template
 *
 * Página de Ajustes con diseño Bentō
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
            $sidebar->render($current_page, array('form_id' => 'braveschat-form-settings'));
            ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title"><strong><?php esc_html_e('Agent Settings', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Manage the connection, visibility and behavior of your AI Agent.', 'braveschat'); ?>
                    </p>
                </div>

                <!-- Settings Form -->
                <form id="braveschat-form-settings" action="options.php" method="post">
                    <?php
                    settings_fields('braves_chat_settings');
                    // Preservar opciones no mostradas en este formulario
                    \BravesChat\Settings::get_instance()->render_hidden_fields(array(
                        'global_enable',
                        'webhook_url',
                        'n8n_auth_type',
                        'n8n_auth_token',
                        'n8n_auth_header',
                        'excluded_pages',
                        'typing_speed',
                        'stats_webhook_url',
                        'stats_api_key',
                    ));
                    ?>

                    <!-- Configuración General Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php esc_html_e('General Settings', 'braveschat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: Mostrar en toda la web -->
                            <?php
                            $global_enable = get_option($option_prefix . 'global_enable', false);

                            ob_start();
                            ?>
                            <label class="braves-toggle-wrapper">
                                <input type="checkbox"
                                       id="<?php echo esc_attr($option_prefix . 'global_enable'); ?>"
                                       name="<?php echo esc_attr($option_prefix . 'global_enable'); ?>"
                                       value="1"
                                       <?php checked(1, $global_enable); ?>
                                       class="braves-toggle-input">
                                <span class="braves-toggle-slider"></span>
                            </label>
                            <p class="braves-field-help" style="margin-top: 8px;">
                                <?php esc_html_e('Show the chat on all pages of the website', 'braveschat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Global Activation', 'braveschat'),
                                'description' => __('Enable the agent on all pages of the website.', 'braveschat'),
                                'content' => $toggle_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: URL del Webhook -->
                            <?php
                            $webhook_url = get_option($option_prefix . 'webhook_url', '');

                            ob_start();
                            ?>
                            <input type="url"
                                   id="<?php echo esc_attr($option_prefix . 'webhook_url'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'webhook_url'); ?>"
                                   value="<?php echo esc_attr($webhook_url); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="https://tudominio.com/webhook/...">
                            <p class="braves-field-help" style="margin-top: 8px;">
                                <?php esc_html_e('N8N webhook URL to process chat messages.', 'braveschat'); ?>
                            </p>
                            <?php
                            $webhook_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Webhook URL', 'braveschat'),
                                'description' => __('The bridge between your website and your N8N system.', 'braveschat'),
                                'content' => $webhook_content,
                            ));
                            ?>

                            <!-- Card: Tipo de Autenticación -->
                            <?php
                            $n8n_auth_type   = get_option($option_prefix . 'n8n_auth_type', 'header');
                            $n8n_auth_header = get_option($option_prefix . 'n8n_auth_header', 'X-N8N-Auth');
                            $n8n_token       = get_option($option_prefix . 'n8n_auth_token', '');

                            ob_start();
                            ?>
                            <select id="<?php echo esc_attr($option_prefix . 'n8n_auth_type'); ?>"
                                    name="<?php echo esc_attr($option_prefix . 'n8n_auth_type'); ?>"
                                    class="braves-input"
                                    style="width: 100%;"
                                    onchange="bravesUpdateAuthLabels(this.value)">
                                <option value="none"   <?php selected($n8n_auth_type, 'none'); ?>><?php esc_html_e('No authentication', 'braveschat'); ?></option>
                                <option value="header" <?php selected($n8n_auth_type, 'header'); ?>><?php esc_html_e('Custom header (Header Auth)', 'braveschat'); ?></option>
                                <option value="basic"  <?php selected($n8n_auth_type, 'basic'); ?>><?php esc_html_e('Basic Auth (username + password)', 'braveschat'); ?></option>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px;">
                                <?php esc_html_e('Choose the authentication type configured in your N8N Webhook or Chat Trigger node.', 'braveschat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('Authentication Type', 'braveschat'),
                                'description' => __('Security method to protect access to the webhook.', 'braveschat'),
                                'content'     => ob_get_clean(),
                            ));
                            ?>

                            <!-- Card: Credenciales (Header/Usuario + Token/Contraseña en una sola tarjeta) -->
                            <?php ob_start(); ?>
                            <div id="braves-auth-credentials-card" style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label for="<?php echo esc_attr($option_prefix . 'n8n_auth_header'); ?>"
                                           id="braves-auth-header-label"
                                           style="display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px;">
                                    </label>
                                    <input type="text"
                                           id="<?php echo esc_attr($option_prefix . 'n8n_auth_header'); ?>"
                                           name="<?php echo esc_attr($option_prefix . 'n8n_auth_header'); ?>"
                                           value="<?php echo esc_attr($n8n_auth_header); ?>"
                                           class="braves-input"
                                           style="width: 100%;"
                                           placeholder="X-N8N-Auth">
                                    <p id="braves-auth-header-help" class="braves-field-help" style="margin-top: 6px; font-size: 12px; color: #888;"></p>
                                </div>
                                <div>
                                    <label for="<?php echo esc_attr($option_prefix . 'n8n_auth_token'); ?>"
                                           id="braves-auth-token-label"
                                           style="display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px;">
                                    </label>
                                    <input type="password"
                                           id="<?php echo esc_attr($option_prefix . 'n8n_auth_token'); ?>"
                                           name="<?php echo esc_attr($option_prefix . 'n8n_auth_token'); ?>"
                                           value="<?php echo esc_attr($n8n_token); ?>"
                                           class="braves-input"
                                           style="width: 100%;"
                                           autocomplete="new-password"
                                           placeholder="••••••••••••••••">
                                    <p id="braves-auth-token-help" class="braves-field-help" style="margin-top: 6px; font-size: 12px; color: #888;"></p>
                                </div>
                            </div>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('Credentials', 'braveschat'),
                                'description' => __('Agent Identity (User) <br/>Security Token (Password).', 'braveschat'),
                                'content'     => ob_get_clean(),
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Páginas Excluidas -->
                            <?php
                            $excluded_pages = get_option($option_prefix . 'excluded_pages', array());
                            $all_pages = get_pages();

                            ob_start();
                            ?>
                            <select name="<?php echo esc_attr($option_prefix . 'excluded_pages'); ?>[]"
                                    id="<?php echo esc_attr($option_prefix . 'excluded_pages'); ?>"
                                    multiple
                                    size="8"
                                    class="braves-select"
                                    style="width: 100%; height: auto;">
                                <?php foreach ($all_pages as $page): ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>"
                                            <?php echo in_array($page->ID, (array)$excluded_pages) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px;">
                                <?php esc_html_e('Hold Ctrl (Cmd on Mac) to select multiple pages.', 'braveschat'); ?>
                            </p>
                            <div class="braves-button-group" style="margin-top: 10px;">
                                <button type="button" id="braves-select-all-pages" class="braves-button braves-button--secondary">
                                    <?php esc_html_e('Select all', 'braveschat'); ?>
                                </button>
                                <button type="button" id="braves-deselect-all-pages" class="braves-button braves-button--secondary">
                                    <?php esc_html_e('Deselect all', 'braveschat'); ?>
                                </button>
                            </div>
                            <?php
                            $pages_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Excluded Pages', 'braveschat'),
                                'description' => __('Select the pages where the chat will NOT appear.', 'braveschat'),
                                'content' => $pages_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: Velocidad de Escritura -->
                            <?php
                            $typing_speed = get_option($option_prefix . 'typing_speed', 30);

                            ob_start();
                            ?>
                            <div class="braves-range-wrapper" style="display: flex; align-items: center; gap: 15px;">
                                <div style="flex-grow: 1;">
                                    <div class="braves-range-container">
                                        <input type="range"
                                               id="<?php echo esc_attr($option_prefix . 'typing_speed_range'); ?>"
                                               min="10"
                                               max="100"
                                               step="5"
                                               value="<?php echo esc_attr($typing_speed); ?>"
                                               class="braves-range">
                                        <div class="braves-range-tooltip" id="braves-range-tooltip" style="display: none;">
                                            <span class="braves-range-tooltip-value"><?php echo esc_html($typing_speed); ?></span> ms
                                        </div>
                                    </div>
                                    <div class="braves-range-labels">
                                        <span><?php esc_html_e('Fast (10ms)', 'braveschat'); ?></span>
                                        <span><?php esc_html_e('Slow (100ms)', 'braveschat'); ?></span>
                                    </div>
                                </div>
                                <div style="flex-shrink: 0; display: flex; align-items: center; gap: 5px;">
                                    <input type="number"
                                           id="<?php echo esc_attr($option_prefix . 'typing_speed'); ?>"
                                           name="<?php echo esc_attr($option_prefix . 'typing_speed'); ?>"
                                           value="<?php echo esc_attr($typing_speed); ?>"
                                           min="10"
                                           max="100"
                                           class="braves-input small-text"
                                           style="width: 70px; text-align: center;"
                                           oninput="document.getElementById('<?php echo esc_attr($option_prefix . 'typing_speed_range'); ?>').value = this.value">
                                    <span class="braves-range-unit">ms</span>
                                </div>
                            </div>
                            <p class="braves-field-help">
                                <?php echo wp_kses_post(__('Adjust the speed at which the assistant "types" the message.<br/>A value of 30-40ms feels natural.', 'braveschat')); ?>
                            </p>
                            <?php
                            $speed_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Typing Speed', 'braveschat'),
                                'description' => __('Text animation speed (ms per character).', 'braveschat'),
                                'content' => $speed_content,
                            ));
                            ?>

                        </div>
                    </div>

                    <!-- Configuración de Historial Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php esc_html_e('History Settings', 'braveschat'); ?>
                        </h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <!-- Card: URL Webhook de Historial -->
                            <?php
                            $stats_webhook_url = get_option($option_prefix . 'stats_webhook_url', '');
                            ob_start();
                            ?>
                            <input type="url"
                                   id="<?php echo esc_attr($option_prefix . 'stats_webhook_url'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'stats_webhook_url'); ?>"
                                   value="<?php echo esc_attr($stats_webhook_url); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="https://tudominio.com/webhook/...">
                            <p class="braves-field-help" style="margin-top: 8px;">
                                <?php esc_html_e('N8N webhook URL that queries the conversation history in Postgres.', 'braveschat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('History Webhook URL', 'braveschat'),
                                'description' => __('N8N endpoint to retrieve recorded conversations.', 'braveschat'),
                                'content'     => ob_get_clean(),
                            ));
                            ?>

                            <!-- Card: API Key del Historial -->
                            <?php
                            $stats_api_key = get_option($option_prefix . 'stats_api_key', '');
                            ob_start();
                            ?>
                            <input type="password"
                                   id="<?php echo esc_attr($option_prefix . 'stats_api_key'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'stats_api_key'); ?>"
                                   value="<?php echo esc_attr($stats_api_key); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   autocomplete="new-password"
                                   placeholder="••••••••••••••••">
                            <p class="braves-field-help" style="margin-top: 8px;">
                                <?php esc_html_e('Authentication key sent in the x-api-key header to the history webhook.', 'braveschat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('History API Key', 'braveschat'),
                                'description' => __('Key to authenticate requests to the history webhook.', 'braveschat'),
                                'content'     => ob_get_clean(),
                            ));
                            ?>

                        </div>
                    </div>

                </form>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
