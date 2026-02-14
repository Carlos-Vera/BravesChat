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
    wp_die(__('No tienes permisos para acceder a esta página.', 'braves-chat'));
}

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
                        <?php _e('<strong>Dashboard</strong>', 'braves-chat'); ?>
                    </h1>
                    <p class="braves-page-description">
                        <?php _e('Una visión general de las funciones de <strong>BravesChat iA</strong> para construir y personalizar tu chat.', 'braves-chat'); ?>
                    </p>
                </div>

                <!-- Configuration Status Section -->
                <?php if (!$config_status['is_configured']): ?>
                <div class="braves-section braves-section--warning">
                    <?php
                    Template_Helpers::notice(
                        '<strong>' . __('Acción requerida:', 'braves-chat') . '</strong> ' .
                        __('Para que el chat funcione, necesitas configurar la URL del webhook en la página de ajustes.', 'braves-chat'),
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
                        'title' => __('Chat Activo', 'braves-chat'),
                        'description' => $config_status['is_configured']
                            ? __('El chat está configurado y funcionando correctamente en tu sitio web.', 'braves-chat')
                            : __('El chat aún no está configurado. Configura el webhook para empezar.', 'braves-chat'),
                        'action_text' => __('Ver sitio web', 'braves-chat'),
                        'action_url' => home_url(),
                        'action_target' => '_blank',
                    ));
                    ?>

                    <!-- Configuración Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('settings', '#0077b6'),
                        'title' => __('Configuración', 'braves-chat'),
                        'description' => __('Personaliza la apariencia, mensajes y comportamiento del chat según tus necesidades.', 'braves-chat'),
                        'action_text' => __('Ir a Ajustes', 'braves-chat'),
                        'action_url' => admin_url('admin.php?page=braves-chat-settings'),
                    ));
                    ?>

                    <!-- Documentación Card -->
                    <?php
                    Template_Helpers::card(array(
                        'icon' => Template_Helpers::get_icon('docs', '#0077b6'),
                        'title' => __('Documentación', 'braves-chat'),
                        'description' => __('Aprende cómo sacar el máximo provecho del plugin con nuestra documentación completa.', 'braves-chat'),
                        'action_text' => __('Ver documentación', 'braves-chat'),
                        'action_url' => 'https://github.com/Carlos-Vera/braveschat',
                        'action_target' => '_blank',
                    ));
                    ?>

                </div>

                <!-- Quick Actions Section -->
                <div class="braves-section braves-section--actions">
                    <h2 class="braves-section__title">
                        <?php _e('Acciones Rápidas', 'braves-chat'); ?>
                    </h2>

                    <div class="braves-button-group">
                        <?php
                        Template_Helpers::quick_action(array(
                            'text' => __('Personalizar Apariencia', 'braves-chat'),
                            'url' => admin_url('customize.php'),
                            'style' => 'secondary',
                        ));

                        Template_Helpers::quick_action(array(
                            'text' => __('Configurar Webhook', 'braves-chat'),
                            'url' => admin_url('admin.php?page=braves-chat-settings'),
                            'style' => 'secondary',
                        ));
                        ?>
                    </div>
                </div>

                <!-- System Info Cards -->
                <div class="braves-section braves-section--info">
                    <h2 class="braves-section__title">
                        <?php _e('Estado del Sistema', 'braves-chat'); ?>
                    </h2>

                    <div class="braves-card-grid braves-card-grid--2-cols">

                        <!-- Configuración General -->
                        <?php
                        $global_enabled_text = $config_status['global_enabled']
                            ? __('Activo en todo el sitio', 'braves-chat')
                            : __('Usar bloque Gutenberg', 'braves-chat');

                        Template_Helpers::card(array(
                            'title' => __('Modo de visualización', 'braves-chat'),
                            'description' => sprintf(
                                __('Modo: %s', 'braves-chat'),
                                ucfirst($config_status['display_mode'])
                            ),
                            'footer' => $global_enabled_text,
                            'custom_class' => 'braves-card--compact',
                        ));
                        ?>

                        <!-- Versión del Plugin -->
                        <a href="<?php echo admin_url('admin.php?page=braves-chat-about'); ?>" class="braves-card-link">
                            <?php
                            Template_Helpers::card(array(
                                'title' => __('Versión del Plugin', 'braves-chat'),
                                'description' => 'BravesChat iA v' . BRAVES_CHAT_VERSION,
                                'footer' => sprintf(
                                    __('Última actualización: %s', 'braves-chat'),
                                    date_i18n(get_option('date_format'))
                                ),
                                'custom_class' => 'braves-card--compact',
                            ));
                            ?>
                        </a>

                    </div>
                </div>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
