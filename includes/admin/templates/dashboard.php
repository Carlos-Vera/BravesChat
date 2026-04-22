<?php
/**
 * Template del Dashboard Admin
 *
 * Página principal moderno con diseño Bentō
 *
 * @package BravesChat
 * @version 1.2.0
 */

use BravesChat\Admin\Admin_Header;
use BravesChat\Admin\Admin_Sidebar;
use BravesChat\Admin\Template_Helpers;

// Exit if accessed directly
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

?>

<div class="wrap braves-admin-wrap">
    <div class="braves-admin-container">

        <?php
        // Construir notices para el header
        $notices_html = '';
        if (!$config_status['is_configured']) {
            ob_start();
            Template_Helpers::notice(
                '<strong>' . __('Almost there.', 'braveschat') . '</strong> ' .
                __('Without the Webhook URL, the chat cannot communicate with N8N. Go to Settings and link your flow to activate the AI.', 'braveschat'),
                'warning'
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
            $sidebar->render($current_page);
            ?>

            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title">
                        <strong><?php esc_html_e('Dashboard', 'braveschat'); ?></strong>
                    </h1>
                    <p class="braves-page-description">
                        <?php echo wp_kses_post( __('Your agent\'s control center. From here you manage how your AI interacts with the world.', 'braveschat') ); ?>
                    </p>
                </div>

                <!-- Status Cards Grid -->
                <div class="braves-card-grid braves-card-grid--3-cols">

                    <!-- Chat Activo Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('chat'),
                        'title' => $config_status['global_enabled'] ? __('Chat Active', 'braveschat') : __('Chat Offline', 'braveschat'),
                        'description' => !$config_status['global_enabled']
                            ? __('Chat disabled — enable it in Settings so your visitors can use it.', 'braveschat')
                            : ($config_status['is_configured']
                                ? __('Your agent is online. Everything is in order, the AI is ready to assist your customers.', 'braveschat')
                                : __('Your agent is not configured yet. Set up the webhook to get started.', 'braveschat')
                            ),
                        'action_text' => $config_status['global_enabled'] ? __('View live', 'braveschat') : __('Go to General Settings', 'braveschat'),
                        'action_url' => $config_status['global_enabled'] ? home_url() : admin_url('admin.php?page=braveschat-settings'),
                        'action_target' => $config_status['global_enabled'] ? '_blank' : '_self',
                        'is_link_card' => true,
                    ));
                    ?>

                    <!-- Apariencia Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('appearance'),
                        'title' => __('Appearance', 'braveschat'),
                        'description' => __('Brain and style. Adjust what the chat says, how it says it, and what it looks like.', 'braveschat'),
                        'action_text' => __('Customize', 'braveschat'),
                        'action_url' => admin_url('admin.php?page=braveschat-appearance'),
                        'is_link_card' => true,
                    ));
                    ?>

                    <!-- Documentación Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('docs'),
                        'title' => __('Documentation', 'braveschat'),
                        'description' => __('Need help? Step-by-step guide to get the most out of all BravesChat features.', 'braveschat'),
                        'action_text' => __('Open manual', 'braveschat'),
                        'action_url' => 'https://github.com/Carlos-Vera/braveschat',
                        'action_target' => '_blank',
                        'is_link_card' => true,
                    ));
                    ?>

                </div>

                <!-- Quick Actions Section -->
                <div class="braves-section braves-section--actions">
                    <h2 class="braves-section__title">
                        <?php esc_html_e('Quick Actions', 'braveschat'); ?>
                    </h2>

                    <div class="braves-button-group">
                        <?php
                        Template_Helpers::quick_action(array(
                            'text' => __('Change Design', 'braveschat'),
                            'url' => admin_url('customize.php'),
                            'style' => 'secondary',
                        ));

                        Template_Helpers::quick_action(array(
                            'text' => __('Connect to N8N', 'braveschat'),
                            'url' => admin_url('admin.php?page=braveschat-settings'),
                            'style' => 'secondary',
                        ));
                        ?>
                        <button type="button" id="braves-theme-toggle" class="braves-button braves-button--secondary" onclick="bravesToggleTheme()">
                            <span class="braves-button__text"><?php esc_html_e('Dark Mode', 'braveschat'); ?></span>
                        </button>
                    </div>
                </div>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
