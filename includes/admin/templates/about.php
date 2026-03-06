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
    wp_die(__('No tienes permisos para acceder a esta página.', 'braves-chat'));
}

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
                    <h1 class="braves-page-title"><?php _e('Acerca de <strong>BravesChat iA</strong>', 'braves-chat'); ?></h1>
                    <p class="braves-page-description">
                        <?php _e('Información del plugin y historial de cambios.', 'braves-chat'); ?>
                    </p>
                </div>

                <!-- Plugin Info Section -->
                <div class="braves-section">
                    <h2 class="braves-section__title">
                        <?php _e('Información del Plugin', 'braves-chat'); ?>
                    </h2>

                    <div class="braves-card-grid braves-card-grid--3-cols">

                        <!-- Card: Versión -->
                        <?php
                        Template_Helpers::card(array(
                            'icon' => Template_Helpers::get_icon('verified', '#0077b6'),
                            'title' => __('Versión', 'braves-chat'),
                            'description' => 'v' . BRAVES_CHAT_VERSION,
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
                        <?php _e('Historial de Cambios', 'braves-chat'); ?>
                    </h2>

                    <!-- Versions 2.2.1 → 2.2.3 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.2.3</span>
                                <?php _e('Admin afinado y protegido', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('5 de Marzo, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Haz clic en "Ver detalles" en la lista de plugins de WordPress para ver la ficha completa del plugin — capturas, FAQ, instrucciones y compatibilidad — sin salir del admin.', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: El mensaje del banner GDPR y el mensaje de horario offline ahora usan un editor visual — añade negritas, listas o enlaces sin escribir una sola línea de HTML.', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: El panel de BravesChat ya no muestra avisos de otros plugins instalados en tu WordPress — la interfaz queda limpia y enfocada en tu configuración.', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔧 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: BravesChat detecta herramientas de exportación de código instaladas en tu WordPress. En el momento de la detección, BravesLab LLC registra automáticamente el dominio, la IP del servidor, el identificador del administrador y el timestamp exacto — datos remitidos de forma inmediata a nuestros asesores legales. La distribución no autorizada del código de BravesChat constituye una infracción de derechos de autor protegida bajo 17 U.S.C. § 501 y los tratados internacionales de propiedad intelectual en los que EE.UU. es parte.', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.2.0 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.2.0</span>
                                <?php _e('Historial de Conversaciones:<i>conoce lo que piensan tus clientes</i>', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('26 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('✨ Nuevas Funcionalidades', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: New <strong>Historial</strong>: accede a todas las conversaciones que tu agente ha tenido con tus visitantes.', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Abre cualquier conversación con un clic y lee cada mensaje tal y como ocurrió, en una interfaz de chat clara y fácil de seguir.', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Exporta el historial completo a <strong>CSV</strong> con un clic para analizarlo en Excel, importarlo a tu CRM o compartirlo con tu equipo.', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('IMPROVED: Los mensajes de tus clientes se muestran exactamente como los escribieron, sin textos irrelevantes que dificulten la lectura.', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Cada fila agrupa en su interior todos los mensajes de la conversación, para que puedas entender el contexto completo.', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.1.3 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.1.3</span>
                                <?php _e('Fix Release Automatizado con GitHub Actions', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('23 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔧 Backend / CI', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('FIXED: El workflow de GitHub Actions no se ejecutaba porque .gitignore excluía el directorio .github/workflows/', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Eliminada regla **/.github/workflows/* del .gitignore para que el workflow quede trackeado en git', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Workflow release.yml ahora genera automáticamente braveschat.zip al hacer push de un tag v*', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.1.2 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.1.2</span>
                                <?php _e('Aislamiento CSS y Mejoras de Compatibilidad', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('20 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎨 Mejoras de Interfaz', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('IMPROVED: Sistema completo de aislamiento CSS (Isolation Layer) para prevenir conflictos con temas', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Reset exhaustivo de estilos de botones, inputs y elementos de texto en todos los modos', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Protección contra bleeding de CSS de temas externos en Modal, Fullscreen y GDPR Banner', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Forzado de tipografía Montserrat en todos los elementos del chat', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🐛 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('FIXED: Posicionamiento del chat en modo centrado (position-center)', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Ventana del chat centrada cuando la burbuja está en el centro', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Posicionamiento bottom-left corregido para modo modal', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Eliminación de cursores de escritura múltiples en streaming', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔧 Backend', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Filtro admin_title para títulos dinámicos por sección', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Títulos de pestañas personalizados: "BravesChat | Sección | Sitio"', 'braves-chat'); ?></li>
                                    <li><?php _e('CHANGED: Script de desinstalación preserva configuraciones al desinstalar', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.1.1 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v2.1.1</span>
                                <?php _e('Mejoras UX y Fixes', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('16 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎨 Mejoras de Experiencia', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('IMPROVED: Renderizado incremental de Markdown en tiempo real', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Mantenimiento del foco en el input tras enviar mensaje', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>


                    <!-- Version 2.1.0 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v2.1.0</span>
                                <?php _e('Nuevas Funcionalidades y Mejoras', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('16 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('✨ Nuevas Funcionalidades', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Slider de control de velocidad de escritura', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Soporte HTML/Markdown en banner GDPR', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Tipografía Montserrat local', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                             <div class="braves-changelog__section">
                                <h4><?php _e('🐛 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('FIXED: Scroll automático y estilos GDPR', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 2.0.0 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v2.0.0</span>
                                <?php _e('Lanzamiento Mayor - BravesChat iA 2.0', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('14 de Febrero, 2026', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🚀 Lanzamiento Mayor', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('MAJOR: Sistema reestructurado completo adoptando el nombre de "BravesChat iA"', 'braves-chat'); ?></li>
                                    <li><?php _e('MAJOR: Refactorización profunda de namespaces a BravesChat y BravesChat\Admin', 'braves-chat'); ?></li>
                                    <li><?php _e('MAJOR: Actualización de estructura de directorios y nombres de archivos principales', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('✨ Nuevas Funcionalidades UI/UX', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Funcionalidad de expansión del chat (botón de maximizar)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Auto-crecimiento del área de texto (textarea) al escribir múltiples líneas', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Enlace directo a la sección "About" desde la tarjeta de versión en el Dashboard', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Estado Minimizado de la burbuja tras interacción (pill shape)', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎨 Mejoras Visuales', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('IMPROVED: Unificación de identidad visual con colores corporativos "Braves Primary"', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Corrección de estilos en burbujas de chat (texto cortado, borders)', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Actualización de tooltips predeterminados ("Habla con nuestro asistente IA")', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Icono de enviar mensaje actualizado a diseño personalizado (blanco)', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Títulos de menú admin actualizados a "BravesChat iA"', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🐛 Correcciones y Estabilidad', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('FIXED: Lógica del botón de enviar (estado habilitado/deshabilitado)', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Guardado de configuración en sección "Páginas Excluidas"', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Depuración de salida JSON en integración con n8n', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Renderizado de campos ocultos en formularios de configuración', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 1.2.4 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v1.2.4</span>
                                <?php _e('Tooltip Personalizable y Color de Icono', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('17 de Noviembre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Campo de texto para personalizar el tooltip del botón flotante del chat', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Opción bubble_tooltip registrada en WordPress Settings API', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Card "Tooltip del Botón" en página de Apariencia (antes del selector de iconos)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Atributo title dinámico en botón flotante usando valor personalizado', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Color por defecto del icono SVG cambiado de #5B4CCC a #f2f2f2 (gris claro)', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Mejor organización de opciones en panel de Apariencia', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Tooltip ubicado estratégicamente antes del selector de iconos', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔄 Detección Automática de Versiones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Sistema automático de detección de versiones anteriores del plugin al activar', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Desactivación automática de plugins antiguos si están activos', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Eliminación automática de directorios de versiones anteriores', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Preservación de configuraciones del usuario durante la migración', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Prevención de errores fatales por funciones redeclaradas', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔧 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('FIXED: Hotfix para error fatal causado por múltiples versiones instaladas simultáneamente', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Implementación de function_exists() check para prevenir redeclaraciones', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Fallback del color del icono corregido en appearance.php', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                    <!-- Version 1.2.3 -->
                    <div class="braves-changelog">
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v1.2.3</span>
                                <?php _e('Personalización de Colores e Iconos SVG', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('26 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Sistema completo de personalización de colores desde panel de Apariencia', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: 4 campos de color personalizables: Color de la Burbuja, Color Primario, Color de Fondo y Color de Texto', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Color picker nativo HTML5 con sincronización a input de texto hexadecimal', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Paleta de colores del tema de WordPress extraída desde theme.json (colapsable)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Paleta por defecto de 8 colores cuando el tema no define colores personalizados', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Sistema de selección de iconos SVG personalizables para botón flotante', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: 4 iconos SVG optimizados (Original/Robot, Círculo, Happy, Burbuja)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Selector estilo tabs Bentō en página de Apariencia', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Toggle buttons para expandir/colapsar paletas de colores con animación suave', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Color pickers con estilo Material Design list (inline-block, vertical-align: middle)', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Diseño tabs horizontal con fondo gris claro y selección con borde morado', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Iconos optimizados 32x32px desde viewBox 460x460', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Diseño responsive (2 columnas en móvil)', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔧 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('FIXED: Eliminada dependencia de Lottie Player (CDN externo)', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Errores de consola por animaciones Lottie no cargadas', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Error JavaScript cuando wp.i18n no está disponible', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Alineación del color picker y input text usando display: inline-block con vertical-align: middle', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Configuración JavaScript duplicada entre templates y class_frontend.php', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Templates modal.php y screen.php creaban variable conflictiva bravesChatConfig', 'braves-chat'); ?></li>
                                    <li><?php _e('REMOVED: Gradiente del botón flotante - ahora usa color sólido', 'braves-chat'); ?></li>
                                    <li><?php _e('REMOVED: Borde izquierdo de las burbujas de mensajes', 'braves-chat'); ?></li>
                                    <li><?php _e('CHANGED: Templates usan img SVG en lugar de animación Lottie', 'braves-chat'); ?></li>
                                    <li><?php _e('CHANGED: Icono por defecto cambiado a "Original" (robot-chat)', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.2.2 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.2.2</span>
                                <?php _e('Correcciones y Mejoras UX', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('25 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔧 Correcciones', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('FIXED: Los inputs de formulario no se renderizaban en las páginas de configuración', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: El método Admin_Content::render_card() no soportaba el parámetro content', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: wp_kses_post() eliminaba los elementos de formulario HTML', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Los ajustes se perdían al guardar desde diferentes páginas (Settings, Appearance, Availability, GDPR)', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: El icono del menú mostraba color gris en lugar de blanco cuando estaba activo', 'braves-chat'); ?></li>
                                    <li><?php _e('FIXED: Script admin_settings.js no se cargaba en páginas Appearance, Availability y GDPR', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Sistema de auto-ocultación para notificaciones de éxito con animación slide-up', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Campos ocultos en formularios para preservar ajustes de otras secciones', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: JavaScript para mantener clase wp-has-current-submenu en páginas del plugin', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Iconos de sidebar actualizados a versiones sólidas (Horarios, GDPR)', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Iconos de página About actualizados (Version: verified, Autor: person_pin, Empresa: business_center)', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Tarjetas informativas ahora son clicables con enlaces externos', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Todos los formularios ahora funcionales con diseño Bentō consistente', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Estilos CSS unificados en todas las páginas del admin', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Toggles estilo Bentō implementados en todos los checkboxes', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.2.1 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.2.1</span>
                                <?php _e('Rediseño Completo del Admin', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('24 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('⚙️ Características', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('Implementación completa del diseño Bentō', 'braves-chat'); ?></li>
                                    <li><?php _e('Nueva arquitectura modular de componentes', 'braves-chat'); ?></li>
                                    <li><?php _e('5 páginas de administración: Resumen, Ajustes, Apariencia, Horarios, GDPR', 'braves-chat'); ?></li>
                                    <li><?php _e('Sidebar único compartido entre todas las páginas', 'braves-chat'); ?></li>
                                    <li><?php _e('Sistema de Template Helpers para renderizado consistente', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.1.2 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.1.2</span>
                                <?php _e('Cambio de Marca', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('23 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔁 Cambios', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('CHANGED: Weblandia → BravesLab', 'braves-chat'); ?></li>
                                    <li><?php _e('CHANGED: URLs actualizadas a braveslab.com', 'braves-chat'); ?></li>
                                    <li><?php _e('CHANGED: Copyright actualizado a BRAVES LAB LLC', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.1.1 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--primary">v1.1.1</span>
                                <?php _e('Sistema de Cookies y GDPR', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('16 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Sistema de cookies con fingerprinting del navegador', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Banner de consentimiento GDPR configurable', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Hash SHA-256 para identificación única', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Fallback a localStorage si cookies bloqueadas', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.1.0 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.1.0</span>
                                <?php _e('Horarios y Páginas Excluidas', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('1 de Octubre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('⚙️ Características', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Sistema de horarios de disponibilidad con zonas horarias', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Páginas excluidas configurables (selector múltiple)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Token de autenticación N8N (header X-N8N-Auth)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Mensaje personalizado fuera de horario', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🎁 Mejoras', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('IMPROVED: Configuración del webhook más flexible', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Validación de URLs de webhook', 'braves-chat'); ?></li>
                                    <li><?php _e('IMPROVED: Sanitización de inputs en Settings API', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Version 1.0.0 -->
                        <div class="braves-changelog__version">
                            <h3 class="braves-changelog__title">
                                <span class="braves-badge braves-badge--success">v1.0.0</span>
                                <?php _e('Lanzamiento Inicial', 'braves-chat'); ?>
                            </h3>
                            <p class="braves-changelog__date"><?php _e('15 de Septiembre, 2025', 'braves-chat'); ?></p>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🛠️ Funcionalidades Principales', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Integración de chat con IA mediante bloque Gutenberg', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Configuración de webhook N8N', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Sistema de mensajes personalizables', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Dos modos de visualización: Modal y Pantalla completa', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Posicionamiento configurable (derecha, izquierda, centro)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Animación Lottie en botón de chat', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🧬 Arquitectura', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Estructura OOP con namespaces PHP (BravesChat)', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: WordPress Settings API para configuración', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: WordPress Customizer API para personalización en tiempo real', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Bloque Gutenberg con opciones personalizables', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🔒 Seguridad', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Sanitización completa de inputs', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Nonces en todos los formularios', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Verificación de capacidades de usuario', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Escapado de salidas (esc_html, esc_attr, esc_url)', 'braves-chat'); ?></li>
                                </ul>
                            </div>

                            <div class="braves-changelog__section">
                                <h4><?php _e('🇻🇪 i18n', 'braves-chat'); ?></h4>
                                <ul>
                                    <li><?php _e('ADDED: Preparado para internacionalización', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Text domain: braves-chat', 'braves-chat'); ?></li>
                                    <li><?php _e('ADDED: Archivo .pot para traducciones', 'braves-chat'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->
