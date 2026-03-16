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
                    <h1 class="braves-page-title">
                        <strong><?php esc_html_e('Dashboard', 'braveschat'); ?></strong>
                    </h1>
                    <p class="braves-page-description">
                        <?php echo wp_kses_post( __('Una visión general de las funciones de <strong>BravesChat iA</strong> para construir y personalizar tu chat.', 'braveschat') ); ?>
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

                <!-- Status Cards Grid -->
                <div class="braves-card-grid braves-card-grid--3-cols">

                    <!-- Chat Activo Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('chat', '#0077b6'),
                        'title' => __('Chat Activo', 'braveschat'),
                        'description' => $config_status['is_configured']
                            ? __('El chat está configurado y funcionando correctamente en tu sitio web.', 'braveschat')
                            : __('El chat aún no está configurado. Configura el webhook para empezar.', 'braveschat'),
                        'action_text' => __('Ver sitio web', 'braveschat'),
                        'action_url' => home_url(),
                        'action_target' => '_blank',
                        'is_link_card' => true,
                    ));
                    ?>

                    <!-- Configuración Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('settings', '#0077b6'),
                        'title' => __('Configuración', 'braveschat'),
                        'description' => __('Personaliza la apariencia, mensajes y comportamiento del chat según tus necesidades.', 'braveschat'),
                        'action_text' => __('Ir a Ajustes', 'braveschat'),
                        'action_url' => admin_url('admin.php?page=braveschat-settings'),
                        'is_link_card' => true,
                    ));
                    ?>

                    <!-- Documentación Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('docs', '#0077b6'),
                        'title' => __('Documentación', 'braveschat'),
                        'description' => __('Aprende cómo sacar el máximo provecho del plugin con nuestra documentación completa.', 'braveschat'),
                        'action_text' => __('Ver documentación', 'braveschat'),
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
                            'text' => __('Personalizar Apariencia', 'braveschat'),
                            'url' => admin_url('customize.php'),
                            'style' => 'secondary',
                        ));

                        Template_Helpers::quick_action(array(
                            'text' => __('Configurar Webhook', 'braveschat'),
                            'url' => admin_url('admin.php?page=braveschat-settings'),
                            'style' => 'secondary',
                        ));
                        ?>
                    </div>
                </div>


            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
