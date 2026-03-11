<?php
/**
 * About Page Template
 *
 * Página informativa con changelog y créditos del plugin
 *
 * @package BravesChat
 * @subpackage Admin\Templates
 * @since 1.2.2
 */

use BravesChat\Admin\Admin_Header;
use BravesChat\Admin\Admin_Sidebar;
use BravesChat\Admin\Template_Helpers;

if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'braves-chat'));
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables, not true globals.
// Obtener instancias de componentes
$header = Admin_Header::get_instance();
$sidebar = Admin_Sidebar::get_instance();
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
                    <h1 class="braves-page-title"><?php echo wp_kses_post( __('Acerca de <strong>BravesChat iA</strong>', 'braves-chat') ); ?></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Información del plugin y historial de cambios.', 'braves-chat'); ?>
                    </p>
                </div>

                <!-- Plugin Info Section -->
                <div class="braves-section">
                    <h2 class="braves-section__title">
                        <?php esc_html_e('Información del Plugin', 'braves-chat'); ?>
                    </h2>

                    <div class="braves-card-grid braves-card-grid--3-cols">

                        <!-- Card: Versión -->
                        <?php
                        Template_Helpers::card(array(
                            'icon' => Template_Helpers::get_icon('verified', '#0077b6'),
                            'title' => __('Versión', 'braves-chat'),
                            'description' => 'v' . BRAVES_CHAT_VERSION,
                            'footer' => __('Última actualización: 11 de Marzo, 2026', 'braves-chat'),
                            'action_text' => 'GitHub Repository',
                            'action_url' => 'https://github.com/Carlos-Vera/braveschat',
                            'action_target' => '_blank',
                            'is_link_card' => true,
                        ));
                        ?>

                        <!-- Card: Autor -->
                        <?php
                        Template_Helpers::card(array(
                            'icon' => Template_Helpers::get_icon('logo_dev', '#0077b6'),
                            'title' => __('Autor Principal', 'braves-chat'),
                            'description' => 'Carlos Vera',
                            'action_text' => 'carlos@braveslab.com',
                            'action_url' => 'mailto:carlos@braveslab.com',
                            'action_target' => '_blank',
                            'is_link_card' => true,
                        ));
                        ?>

                        <!-- Card: Empresa -->
                        <?php
                        Template_Helpers::card(array(
                            'icon' => Template_Helpers::get_icon('business_center', '#0077b6'),
                            'title' => __('Empresa', 'braves-chat'),
                            'description' => 'BRAVES LAB LLC',
                            'action_text' => 'braveslab.com',
                            'action_url' => 'https://braveslab.com',
                            'action_target' => '_blank',
                            'is_link_card' => true,
                        ));
                        ?>

                    </div>
                </div>

                <!-- Changelog Section -->
                <div class="braves-section">
                    <h2 class="braves-section__title">
                        <?php esc_html_e('Historial de Cambios', 'braves-chat'); ?>
                    </h2>

                    <!-- Version 2.3.1 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.3.1</span>
                                <?php echo wp_kses_post( __('Escribe mientras el agente responde: <i>interrumpe cuando quieras</i>', 'braves-chat') ); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('11 de Marzo, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: El chat ya no bloquea la caja de texto mientras el agente responde — escribe tu siguiente pregunta en cualquier momento. Si el agente está a mitad de respuesta, se cancela y tu nuevo mensaje se envía de inmediato.', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Version 2.3.0 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.3.0</span>
                                <?php echo wp_kses_post( __('Seguridad reforzada', 'braves-chat') ); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('10 de Marzo, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('✨ Nuevas Funcionalidades', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: El token de autenticación de N8N ya no se expone en el HTML — viaja siempre en el servidor, invisible para cualquier visitante.', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Soporte para tres métodos de autenticación hacia N8N: cabecera personalizada, Basic Auth o sin autenticación.', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: El modo de visualización activo (modal o pantalla completa) ahora aparece en la cabecera del panel para referencia rápida.', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: El bloque de Gutenberg muestra un preview rediseñado con el estilo del panel: cabecera, cuerpo y pie de página con branding del chat.', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔧 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: El plugin ahora se distribuye con licencia GPL-2.0, compatible con WordPress.org.', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Las imágenes del plugin (banners, capturas) ahora son PNG para máxima compatibilidad.', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Versions 2.2.1 → 2.2.3 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.2.3</span>
                                <?php esc_html_e('Admin afinado y protegido', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('5 de Marzo, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Haz clic en "Ver detalles" en la lista de plugins de WordPress para ver la ficha completa del plugin — capturas, FAQ, instrucciones y compatibilidad — sin salir del admin.', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: El mensaje del banner GDPR y el mensaje de horario offline ahora usan un editor visual — añade negritas, listas o enlaces sin escribir una sola línea de HTML.', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: El panel de BravesChat ya no muestra avisos de otros plugins instalados en tu WordPress — la interfaz queda limpia y enfocada en tu configuración.', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔧 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: El panel te avisa si detecta alguna herramienta instalada capaz de exportar el código del plugin — una capa extra de protección para tu instalación.', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.2.0 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.2.0</span>
                                <?php echo wp_kses_post( __('Historial de Conversaciones:<i>conoce lo que piensan tus clientes</i>', 'braves-chat') ); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('26 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('✨ Nuevas Funcionalidades', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php echo wp_kses_post( __('ADDED: New <strong>Historial</strong>: accede a todas las conversaciones que tu agente ha tenido con tus visitantes.', 'braves-chat') ); ?></li>
                                    <li><?php esc_html_e('ADDED: Abre cualquier conversación con un clic y lee cada mensaje tal y como ocurrió, en una interfaz de chat clara y fácil de seguir.', 'braves-chat'); ?></li>
                                    <li><?php echo wp_kses_post( __('ADDED: Exporta el historial completo a <strong>CSV</strong> con un clic para analizarlo en Excel, importarlo a tu CRM o compartirlo con tu equipo.', 'braves-chat') ); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: Los mensajes de tus clientes se muestran exactamente como los escribieron, sin textos irrelevantes que dificulten la lectura.', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Cada fila agrupa en su interior todos los mensajes de la conversación, para que puedas entender el contexto completo.', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.1.3 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.1.3</span>
                                <?php esc_html_e('Fix Release Automatizado con GitHub Actions', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('23 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔧 Backend / CI', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('FIXED: El workflow de GitHub Actions no se ejecutaba porque .gitignore excluía el directorio .github/workflows/', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Eliminada regla **/.github/workflows/* del .gitignore para que el workflow quede trackeado en git', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Workflow release.yml ahora genera automáticamente braveschat.zip al hacer push de un tag v*', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.1.2 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.1.2</span>
                                <?php esc_html_e('Aislamiento CSS y Mejoras de Compatibilidad', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('20 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎨 Mejoras de Interfaz', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: Sistema completo de aislamiento CSS (Isolation Layer) para prevenir conflictos con temas', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Reset exhaustivo de estilos de botones, inputs y elementos de texto en todos los modos', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Protección contra bleeding de CSS de temas externos en Modal, Fullscreen y GDPR Banner', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Forzado de tipografía Montserrat en todos los elementos del chat', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🐛 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('FIXED: Posicionamiento del chat en modo centrado (position-center)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Ventana del chat centrada cuando la burbuja está en el centro', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Posicionamiento bottom-left corregido para modo modal', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Eliminación de cursores de escritura múltiples en streaming', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔧 Backend', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Filtro admin_title para títulos dinámicos por sección', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Títulos de pestañas personalizados: "BravesChat | Sección | Sitio"', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('CHANGED: Script de desinstalación preserva configuraciones al desinstalar', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.1.1 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.1.1</span>
                                <?php esc_html_e('Mejoras UX y Fixes', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('16 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: Renderizado incremental de Markdown en tiempo real', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Mantenimiento del foco en el input tras enviar mensaje', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>


                    <!-- Version 2.1.0 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v2.1.0</span>
                                <?php esc_html_e('Nuevas Funcionalidades y Mejoras', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('16 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('✨ Nuevas Funcionalidades', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Slider de control de velocidad de escritura', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Soporte HTML/Markdown en banner GDPR', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Tipografía Montserrat local', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                             <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🐛 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('FIXED: Scroll automático y estilos GDPR', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.0.0 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v2.0.0</span>
                                <?php esc_html_e('Lanzamiento Mayor - BravesChat iA 2.0', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('14 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🚀 Lanzamiento Mayor', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('MAJOR: Sistema reestructurado completo adoptando el nombre de "BravesChat iA"', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('MAJOR: Refactorización profunda de namespaces a BravesChat y BravesChat\Admin', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('MAJOR: Actualización de estructura de directorios y nombres de archivos principales', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('✨ Nuevas Funcionalidades UI/UX', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Funcionalidad de expansión del chat (botón de maximizar)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Auto-crecimiento del área de texto (textarea) al escribir múltiples líneas', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Enlace directo a la sección "About" desde la tarjeta de versión en el Dashboard', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Estado Minimizado de la burbuja tras interacción (pill shape)', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎨 Mejoras Visuales', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: Unificación de identidad visual con colores corporativos "Braves Primary"', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Corrección de estilos en burbujas de chat (texto cortado, borders)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Actualización de tooltips predeterminados ("Habla con nuestro asistente IA")', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Icono de enviar mensaje actualizado a diseño personalizado (blanco)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Títulos de menú admin actualizados a "BravesChat iA"', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🐛 Correcciones y Estabilidad', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('FIXED: Lógica del botón de enviar (estado habilitado/deshabilitado)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Guardado de configuración en sección "Páginas Excluidas"', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Depuración de salida JSON en integración con n8n', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Renderizado de campos ocultos en formularios de configuración', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 1.2.4 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v1.2.4</span>
                                <?php esc_html_e('Tooltip Personalizable y Color de Icono', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('17 de Noviembre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Campo de texto para personalizar el tooltip del botón flotante del chat', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Opción bubble_tooltip registrada en WordPress Settings API', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Card "Tooltip del Botón" en página de Apariencia (antes del selector de iconos)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Atributo title dinámico en botón flotante usando valor personalizado', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Color por defecto del icono SVG cambiado de #5B4CCC a #f2f2f2 (gris claro)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Mejor organización de opciones en panel de Apariencia', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Tooltip ubicado estratégicamente antes del selector de iconos', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔄 Detección Automática de Versiones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Sistema automático de detección de versiones anteriores del plugin al activar', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Desactivación automática de plugins antiguos si están activos', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Eliminación automática de directorios de versiones anteriores', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Preservación de configuraciones del usuario durante la migración', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Prevención de errores fatales por funciones redeclaradas', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔧 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('FIXED: Hotfix para error fatal causado por múltiples versiones instaladas simultáneamente', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Implementación de function_exists() check para prevenir redeclaraciones', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Fallback del color del icono corregido en appearance.php', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 1.2.3 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v1.2.3</span>
                                <?php esc_html_e('Personalización de Colores e Iconos SVG', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('26 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Sistema completo de personalización de colores desde panel de Apariencia', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: 4 campos de color personalizables: Color de la Burbuja, Color Primario, Color de Fondo y Color de Texto', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Color picker nativo HTML5 con sincronización a input de texto hexadecimal', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Paleta de colores del tema de WordPress extraída desde theme.json (colapsable)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Paleta por defecto de 8 colores cuando el tema no define colores personalizados', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Sistema de selección de iconos SVG personalizables para botón flotante', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: 4 iconos SVG optimizados (Original/Robot, Círculo, Happy, Burbuja)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Selector estilo tabs Bentō en página de Apariencia', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Toggle buttons para expandir/colapsar paletas de colores con animación suave', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Color pickers con estilo Material Design list (inline-block, vertical-align: middle)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Diseño tabs horizontal con fondo gris claro y selección con borde morado', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Iconos optimizados 32x32px desde viewBox 460x460', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Diseño responsive (2 columnas en móvil)', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔧 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('FIXED: Eliminada dependencia de Lottie Player (CDN externo)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Errores de consola por animaciones Lottie no cargadas', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Error JavaScript cuando wp.i18n no está disponible', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Alineación del color picker y input text usando display: inline-block con vertical-align: middle', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Configuración JavaScript duplicada entre templates y class_frontend.php', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Templates modal.php y screen.php creaban variable conflictiva bravesChatConfig', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('REMOVED: Gradiente del botón flotante - ahora usa color sólido', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('REMOVED: Borde izquierdo de las burbujas de mensajes', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('CHANGED: Templates usan img SVG en lugar de animación Lottie', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('CHANGED: Icono por defecto cambiado a "Original" (robot-chat)', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.2.2 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.2.2</span>
                                <?php esc_html_e('Correcciones y Mejoras UX', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('25 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔧 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('FIXED: Los inputs de formulario no se renderizaban en las páginas de configuración', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: El método Admin_Content::render_card() no soportaba el parámetro content', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: wp_kses_post() eliminaba los elementos de formulario HTML', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Los ajustes se perdían al guardar desde diferentes páginas (Settings, Appearance, Availability, GDPR)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: El icono del menú mostraba color gris en lugar de blanco cuando estaba activo', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('FIXED: Script admin_settings.js no se cargaba en páginas Appearance, Availability y GDPR', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Sistema de auto-ocultación para notificaciones de éxito con animación slide-up', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Campos ocultos en formularios para preservar ajustes de otras secciones', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: JavaScript para mantener clase wp-has-current-submenu en páginas del plugin', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Iconos de sidebar actualizados a versiones sólidas (Horarios, GDPR)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Iconos de página About actualizados (Version: verified, Autor: person_pin, Empresa: business_center)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Tarjetas informativas ahora son clicables con enlaces externos', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Todos los formularios ahora funcionales con diseño Bentō consistente', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Estilos CSS unificados en todas las páginas del admin', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Toggles estilo Bentō implementados en todos los checkboxes', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.2.1 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.2.1</span>
                                <?php esc_html_e('Rediseño Completo del Admin', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('24 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('⚙️ Características', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('Implementación completa del diseño Bentō', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('Nueva arquitectura modular de componentes', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('5 páginas de administración: Resumen, Ajustes, Apariencia, Horarios, GDPR', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('Sidebar único compartido entre todas las páginas', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('Sistema de Template Helpers para renderizado consistente', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.1.2 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.1.2</span>
                                <?php esc_html_e('Cambio de Marca', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('23 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔁 Cambios', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('CHANGED: Weblandia → BravesLab', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('CHANGED: URLs actualizadas a braveslab.com', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('CHANGED: Copyright actualizado a BRAVES LAB LLC', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.1.1 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v1.1.1</span>
                                <?php esc_html_e('Sistema de Cookies y GDPR', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('16 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Sistema de cookies con fingerprinting del navegador', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Banner de consentimiento GDPR configurable', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Hash SHA-256 para identificación única', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Fallback a localStorage si cookies bloqueadas', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.1.0 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.1.0</span>
                                <?php esc_html_e('Horarios y Páginas Excluidas', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('1 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('⚙️ Características', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Sistema de horarios de disponibilidad con zonas horarias', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Páginas excluidas configurables (selector múltiple)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Token de autenticación N8N (header X-N8N-Auth)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Mensaje personalizado fuera de horario', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('IMPROVED: Configuración del webhook más flexible', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Validación de URLs de webhook', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('IMPROVED: Sanitización de inputs en Settings API', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.0.0 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.0.0</span>
                                <?php esc_html_e('Lanzamiento Inicial', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php esc_html_e('15 de Septiembre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🛠️ Funcionalidades Principales', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Integración de chat con IA mediante bloque Gutenberg', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Configuración de webhook N8N', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Sistema de mensajes personalizables', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Dos modos de visualización: Modal y Pantalla completa', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Posicionamiento configurable (derecha, izquierda, centro)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Animación Lottie en botón de chat', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🧬 Arquitectura', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Estructura OOP con namespaces PHP (BravesChat)', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: WordPress Settings API para configuración', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: WordPress Customizer API para personalización en tiempo real', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Bloque Gutenberg con opciones personalizables', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🔒 Seguridad', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Sanitización completa de inputs', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Nonces en todos los formularios', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Verificación de capacidades de usuario', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Escapado de salidas (esc_html, esc_attr, esc_url)', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php esc_html_e('🇻🇪 i18n', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('ADDED: Preparado para internacionalización', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Text domain: braves-chat', 'braves-chat'); ?></li>
                                    <li><?php esc_html_e('ADDED: Archivo .pot para traducciones', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
