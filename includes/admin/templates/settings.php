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
        // Construir notices para el header
        $notices_html = '';
        if (!$config_status['is_configured']) {
            ob_start();
            Template_Helpers::notice(
                __('¡Casi lo tienes! Conecta la URL del Webhook en Ajustes para que tu agente empiece a trabajar.', 'braveschat'),
                'warning'
            );
            $notices_html .= ob_get_clean();
        }
        if ($settings_updated) {
            ob_start();
            Template_Helpers::notice(
                __('Configuración guardada correctamente.', 'braveschat'),
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
                    <h1 class="braves-page-title"><strong><?php esc_html_e('Configuración del Agente', 'braveschat'); ?></strong></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Gestiona la conexión, visibilidad y comportamiento de tu Agente de IA.', 'braveschat'); ?>
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
                            <?php esc_html_e('Configuración General', 'braveschat'); ?>
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
                                <?php esc_html_e('Mostrar el chat en todas las páginas del sitio web', 'braveschat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Activación Global', 'braveschat'),
                                'description' => __('Habilita el agente en todas las páginas de la web.', 'braveschat'),
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
                                <?php esc_html_e('URL del webhook de N8N para procesar los mensajes del chat.', 'braveschat'); ?>
                            </p>
                            <?php
                            $webhook_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('URL del Webhook', 'braveschat'),
                                'description' => __('El puente entre tu web y tu sistema N8N.', 'braveschat'),
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
                                <option value="none"   <?php selected($n8n_auth_type, 'none'); ?>><?php esc_html_e('Sin autenticación', 'braveschat'); ?></option>
                                <option value="header" <?php selected($n8n_auth_type, 'header'); ?>><?php esc_html_e('Header personalizado (Header Auth)', 'braveschat'); ?></option>
                                <option value="basic"  <?php selected($n8n_auth_type, 'basic'); ?>><?php esc_html_e('Basic Auth (usuario + contraseña)', 'braveschat'); ?></option>
                            </select>
                            <p class="braves-field-help" style="margin-top: 8px;">
                                <?php esc_html_e('Elige el tipo de autenticación configurado en tu nodo Webhook o Chat Trigger de N8N.', 'braveschat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('Tipo de Autenticación', 'braveschat'),
                                'description' => __('Método de seguridad para proteger el acceso al webhook.', 'braveschat'),
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
                                'title'       => __('Credenciales', 'braveschat'),
                                'description' => __('Identidad del Agente (Usuario) <br/>Token de Seguridad (Contraseña).', 'braveschat'),
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
                                <?php esc_html_e('Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples páginas.', 'braveschat'); ?>
                            </p>
                            <div class="braves-button-group" style="margin-top: 10px;">
                                <button type="button" id="braves-select-all-pages" class="braves-button braves-button--secondary">
                                    <?php esc_html_e('Seleccionar todas', 'braveschat'); ?>
                                </button>
                                <button type="button" id="braves-deselect-all-pages" class="braves-button braves-button--secondary">
                                    <?php esc_html_e('Deseleccionar todas', 'braveschat'); ?>
                                </button>
                            </div>
                            <?php
                            $pages_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Páginas Excluidas', 'braveschat'),
                                'description' => __('Selecciona las páginas donde NO aparecerá el chat.', 'braveschat'),
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
                                        <span><?php esc_html_e('Rápido (10ms)', 'braveschat'); ?></span>
                                        <span><?php esc_html_e('Lento (100ms)', 'braveschat'); ?></span>
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
                                <?php echo wp_kses_post(__('Ajusta la velocidad con la que el asistente "escribe" el mensaje.<br/>Un valor de 30-40ms se siente natural.', 'braveschat')); ?>
                            </p>
                            <?php
                            $speed_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Velocidad de Escritura', 'braveschat'),
                                'description' => __('Velocidad de la animación de texto (ms por carácter).', 'braveschat'),
                                'content' => $speed_content,
                            ));
                            ?>

                        </div>
                    </div>

                    <!-- Configuración de Historial Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php esc_html_e('Configuración de Historial', 'braveschat'); ?>
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
                                <?php esc_html_e('URL del webhook de N8N que consulta el historial de conversaciones en Postgres.', 'braveschat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('URL Webhook de Historial', 'braveschat'),
                                'description' => __('Endpoint de N8N para recuperar las conversaciones registradas.', 'braveschat'),
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
                                <?php esc_html_e('Clave de autenticación enviada en el header x-api-key al webhook de historial.', 'braveschat'); ?>
                            </p>
                            <?php
                            Template_Helpers::card(array(
                                'title'       => __('API Key de Historial', 'braveschat'),
                                'description' => __('Clave para autenticar las peticiones al webhook de historial.', 'braveschat'),
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
