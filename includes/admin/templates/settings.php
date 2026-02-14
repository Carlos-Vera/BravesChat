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
                    <h1 class="braves-page-title"><?php _e('<strong>Ajustes</strong>', 'braves-chat'); ?></h1>
                    <p class="braves-page-description">
                        <?php _e('Configura los ajustes principales del chat: habilitación global, webhook, token de autenticación y páginas excluidas.', 'braves-chat'); ?>
                    </p>
                </div>

                <!-- Configuration Status Section -->
                <?php if (!$config_status['is_configured']): ?>
                <div class="braves-section braves-section--warning">
                    <?php
                    Template_Helpers::notice(
                        '<strong>' . __('Acción requerida:', 'braves-chat') . '</strong> ' .
                        __('Para que el chat funcione, necesitas configurar la <strong>URL del webhook</strong> en la página de ajustes.', 'braves-chat'),
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

                <!-- Settings Form -->
                <form action="options.php" method="post">
                    <?php
                    settings_fields('braves_chat_settings');
                    // Preservar opciones no mostradas en este formulario
                    \BravesChat\Settings::get_instance()->render_hidden_fields(array(
                        'global_enable',
                        'webhook_url',
                        'n8n_auth_token',
                        'excluded_pages'
                    ));
                    ?>

                    <!-- Configuración General Section -->
                    <div class="braves-section">
                        <h2 class="braves-section__title">
                            <?php _e('Configuración General', 'braves-chat'); ?>
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
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Mostrar el chat en todas las páginas del sitio web', 'braves-chat'); ?>
                            </p>
                            <?php
                            $toggle_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Mostrar en toda la web', 'braves-chat'),
                                'description' => __('Habilita el chat globalmente en todas las páginas del sitio web.', 'braves-chat'),
                                'content' => $toggle_content,
                                'custom_class' => 'braves-card--full-width',
                            ));
                            ?>

                            <!-- Card: URL del Webhook -->
                            <?php
                            $webhook_url = get_option($option_prefix . 'webhook_url', 'https://flow.braveslab.com/webhook/1427244e-a23c-4184-a536-d02622f36325/chat');

                            ob_start();
                            ?>
                            <input type="url"
                                   id="<?php echo esc_attr($option_prefix . 'webhook_url'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'webhook_url'); ?>"
                                   value="<?php echo esc_attr($webhook_url); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   placeholder="https://flow.braveslab.com/webhook/...">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('URL del webhook de N8N para procesar los mensajes del chat.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $webhook_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('URL del Webhook', 'braves-chat'),
                                'description' => __('URL del endpoint de N8N donde se procesarán los mensajes.', 'braves-chat'),
                                'content' => $webhook_content,
                            ));
                            ?>

                            <!-- Card: Token de Autenticación N8N -->
                            <?php
                            $n8n_token = get_option($option_prefix . 'n8n_auth_token', '');

                            ob_start();
                            ?>
                            <input type="password"
                                   id="<?php echo esc_attr($option_prefix . 'n8n_auth_token'); ?>"
                                   name="<?php echo esc_attr($option_prefix . 'n8n_auth_token'); ?>"
                                   value="<?php echo esc_attr($n8n_token); ?>"
                                   class="braves-input"
                                   style="width: 100%;"
                                   autocomplete="new-password"
                                   placeholder="••••••••••••••••">
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Token secreto para autenticar las peticiones al webhook (Header X-N8N-Auth). Déjalo vacío si no usas autenticación.', 'braves-chat'); ?>
                            </p>
                            <?php
                            $token_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Token de Autenticación N8N', 'braves-chat'),
                                'description' => __('Token de seguridad para verificar las peticiones al webhook.', 'braves-chat'),
                                'content' => $token_content,
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
                            <p class="braves-field-help" style="margin-top: 8px; font-size: 13px; color: #666;">
                                <?php _e('Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples páginas.', 'braves-chat'); ?>
                            </p>
                            <div class="braves-button-group" style="margin-top: 10px;">
                                <button type="button" id="braves-select-all-pages" class="button button-secondary button-small">
                                    <?php _e('Seleccionar todas', 'braves-chat'); ?>
                                </button>
                                <button type="button" id="braves-deselect-all-pages" class="button button-secondary button-small">
                                    <?php _e('Deseleccionar todas', 'braves-chat'); ?>
                                </button>
                            </div>
                            <?php
                            $pages_content = ob_get_clean();

                            Template_Helpers::card(array(
                                'title' => __('Páginas Excluidas', 'braves-chat'),
                                'description' => __('Selecciona las páginas donde NO quieres que aparezca el chat.', 'braves-chat'),
                                'content' => $pages_content,
                                'custom_class' => 'braves-card--full-width',
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
