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
    wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'braveschat'));
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
                '<strong>' . __('Falta el último cable.', 'braveschat') . '</strong> ' .
                __('Sin la URL del Webhook, el chat no puede comunicarse con N8N. Pásate por Ajustes y vincula tu flujo para activar la IA.', 'braveschat'),
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
                        <?php echo wp_kses_post( __('El centro de control de tu agente. Desde aquí gestionas cómo interactúa tu IA con el mundo.', 'braveschat') ); ?>
                    </p>
                </div>

                <!-- Status Cards Grid -->
                <div class="braves-card-grid braves-card-grid--3-cols">

                    <!-- Chat Activo Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('chat'),
                        'title' => $config_status['global_enabled'] ? __('Chat Activo', 'braveschat') : __('Chat Offline', 'braveschat'),
                        'description' => !$config_status['global_enabled']
                            ? __('Chat desactivado — actívalo en Ajustes para que tus visitas puedan usarlo.', 'braveschat')
                            : ($config_status['is_configured']
                                ? __('Tu agente está online. Todo en orden, la IA ya está lista para atender a tus clientes.', 'braveschat')
                                : __('Tu agente aún no está configurado. Configura el webhook para empezar.', 'braveschat')
                            ),
                        'action_text' => $config_status['global_enabled'] ? __('Ver en vivo', 'braveschat') : __('Ir a Configuración General', 'braveschat'),
                        'action_url' => $config_status['global_enabled'] ? home_url() : admin_url('admin.php?page=braveschat-settings'),
                        'action_target' => $config_status['global_enabled'] ? '_blank' : '_self',
                        'is_link_card' => true,
                    ));
                    ?>

                    <!-- Apariencia Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('appearance'),
                        'title' => __('Apariencia', 'braveschat'),
                        'description' => __('Cerebro y estilo. Ajusta qué dice el chat, cómo lo dice y qué aspecto tiene.', 'braveschat'),
                        'action_text' => __('Personalizar', 'braveschat'),
                        'action_url' => admin_url('admin.php?page=braveschat-appearance'),
                        'is_link_card' => true,
                    ));
                    ?>

                    <!-- Documentación Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('docs'),
                        'title' => __('Documentación', 'braveschat'),
                        'description' => __('¿Necesitas ayuda? Guía paso a paso para exprimir todas las funciones de BravesChat.', 'braveschat'),
                        'action_text' => __('Abrir manual', 'braveschat'),
                        'action_url' => 'https://github.com/Carlos-Vera/braveschat',
                        'action_target' => '_blank',
                        'is_link_card' => true,
                    ));
                    ?>

                </div>

                <!-- Quick Actions Section -->
                <div class="braves-section braves-section--actions">
                    <h2 class="braves-section__title">
                        <?php esc_html_e('Acciones Rápidas', 'braveschat'); ?>
                    </h2>

                    <div class="braves-button-group">
                        <?php
                        Template_Helpers::quick_action(array(
                            'text' => __('Cambiar Diseño', 'braveschat'),
                            'url' => admin_url('customize.php'),
                            'style' => 'secondary',
                        ));

                        Template_Helpers::quick_action(array(
                            'text' => __('Vincular con N8N', 'braveschat'),
                            'url' => admin_url('admin.php?page=braveschat-settings'),
                            'style' => 'secondary',
                        ));
                        ?>
                        <button type="button" id="braves-theme-toggle" class="braves-button braves-button--secondary" onclick="bravesToggleTheme()">
                            <span class="braves-button__text"><?php esc_html_e('Modo Oscuro', 'braveschat'); ?></span>
                        </button>
                    </div>
                </div>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
