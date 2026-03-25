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
    wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'braveschat'));
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
                    <h1 class="braves-page-title"><?php echo wp_kses_post( __('Acerca de <strong>BravesChat iA</strong>', 'braveschat') ); ?></h1>
                    <p class="braves-page-description">
                        <?php esc_html_e('Información del plugin e historial de cambios.', 'braveschat'); ?>
                    </p>
                </div>

                <!-- Plugin Info Section -->
                <div class="braves-section">
                    <h2 class="braves-section__title">
                        <?php esc_html_e('Información del Plugin', 'braveschat'); ?>
                    </h2>

                    <div class="braves-card-grid braves-card-grid--3-cols">

                        <!-- Card: Versión -->
                        <?php
                        Template_Helpers::card(array(
                            'icon' => Template_Helpers::get_icon('verified', '#0077b6'),
                            'title' => __('Versión', 'braveschat'),
                            'description' => 'v' . BRAVES_CHAT_VERSION,
                            'footer' => __('Last Update: <b>25/03/2026</b>', 'braveschat'),
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
                            'title' => __('Autor Principal', 'braveschat'),
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
                            'title' => __('Empresa', 'braveschat'),
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
                        <?php esc_html_e('Historial de Cambios', 'braveschat'); ?>
                    </h2>
                </div>

                    <div class="braves-timeline">

                        <div class="braves-timeline__cap">
                            <span class="braves-timeline__cap-label"><?php esc_html_e('Hoy', 'braveschat'); ?></span>
                        </div>

                    <!-- Version 2.4.0 -->
                        <div class="braves-timeline__item braves-tl-left">
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Chat nativo en móviles', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: En móviles el chat ahora ocupa toda la pantalla. Tiene su propio header con botón de cierre, respeta la muesca del iPhone y bloquea el scroll del fondo mientras está abierto.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: El widget ya no interfiere con el carrito ni el checkout de WooCommerce — z-index ajustado para coexistir sin conflictos.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Los scripts del panel se cargan con el sistema de WordPress en lugar de estar embebidos en las plantillas — mayor compatibilidad y seguridad.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.4.0</div>
                                <div class="braves-timeline__label"><?php esc_html_e('25 Mar 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__spacer"></div>
                        </div>

                    <!-- Version 2.3.8 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -17rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.3.8</div>
                                <div class="braves-timeline__label"><?php esc_html_e('18 Mar 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Versículo del día', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Cada día aparece un versículo de la Biblia (NVI) en el encabezado del panel. Cambia solo a medianoche — sin configurar nada. Se obtiene de la API de la American Bible Society con caché de 24h en transient de WordPress. Totalmente seguro y sin recopilación de datos de usuario. ', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Versions 2.3.2 → 2.3.7 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: 2rem;">
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Mejoras de diseño UX/UI', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: En móviles la burbuja ocupa menos espacio. El skin default se reduce a 48×48px; el skin Braves se contrae a avatar + botón redondo. En escritorio no cambia nada.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Los avisos de webhook y guardado suben al header. La página queda limpia.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Sidebar renombrado — Disponibilidad, Privacidad, Conversaciones. Más directo.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: El badge de versión se marca cuando estás en esta página.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Correcciones', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('FIXED: El botón para subir imagen personalizada en Apariencia ahora abre correctamente la biblioteca de medios de WordPress.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: El Plugin Check de WordPress.org ya no reporta errores de text domain. El plugin usa el slug correcto (braveschat) en todos los archivos y en el ZIP de distribución.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.3.7</div>
                                <div class="braves-timeline__label"><?php esc_html_e('18 Mar 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__spacer"></div>
                        </div>

                    <!-- Version 2.3.1 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -28rem;">
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Escribe mientras el agente responde', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: El campo de texto ya no se bloquea mientras el agente responde. Puedes escribir en cualquier momento. Si el agente está a mitad de respuesta, se cancela y tu nuevo mensaje se envía de inmediato.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.3.1</div>
                                <div class="braves-timeline__label"><?php esc_html_e('11 Mar 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__spacer"></div>
                        </div>

                    <!-- Version 2.3.0 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -10rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.3.0</div>
                                <div class="braves-timeline__label"><?php esc_html_e('10 Mar 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('El token vive en el servidor', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: El token de N8N ya no viaja al navegador. El frontend envía el mensaje a WordPress, y WordPress lo reenvía al webhook con el token añadido en el servidor — invisible para cualquier visitante.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Tres métodos de autenticación hacia N8N: cabecera personalizada, Basic Auth o sin autenticación.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: El modo de visualización activo (modal o pantalla completa) aparece ahora en la cabecera del panel.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: El bloque de Gutenberg muestra un preview rediseñado con el estilo del panel.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: Licencia GPL-2.0-or-later, alineada con los requisitos de WordPress.org.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Banners, capturas e iconos convertidos a PNG para máxima compatibilidad.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Versions 2.2.1 → 2.2.3 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: -24rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.2.3</div>
                                <div class="braves-timeline__label"><?php esc_html_e('5 Mar 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Detalles del plugin sin salir del admin', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Haz clic en "Ver detalles" en la lista de plugins de WordPress para ver la ficha completa: capturas, FAQ, instrucciones y compatibilidad.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: El mensaje del banner GDPR y el aviso de horario offline ahora usan editor visual. Puedes añadir negritas, listas o enlaces sin tocar HTML.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: El panel de BravesChat ya no muestra avisos de otros plugins. Solo ves lo tuyo, sin ruido.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: El panel detecta si tienes herramientas instaladas que puedan exportar el código del plugin y te avisa. Una capa extra de protección.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 2.2.0 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -3rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.2.0</div>
                                <div class="braves-timeline__label"><?php esc_html_e('26 Feb 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Historial de conversaciones', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php echo wp_kses_post( __('ADDED: Nueva sección <strong>Historial</strong>: accede a todas las conversaciones que tu agente ha tenido con tus visitantes.', 'braveschat') ); ?></li>
                                            <li><?php esc_html_e('ADDED: Abre cualquier conversación con un clic y lee cada mensaje tal como ocurrió, en una interfaz de chat clara.', 'braveschat'); ?></li>
                                            <li><?php echo wp_kses_post( __('ADDED: Exporta el historial completo a <strong>CSV</strong> con un clic para analizarlo en Excel, importarlo a tu CRM o compartirlo con tu equipo.', 'braveschat') ); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: Los mensajes de tus clientes se muestran exactamente como los escribieron, sin ruido que dificulte la lectura.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Cada fila agrupa todos los mensajes de la conversación para que veas el contexto completo.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 2.1.3 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: -23rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.1.3</div>
                                <div class="braves-timeline__label"><?php esc_html_e('23 Feb 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('ZIP automático en cada release', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Cada vez que publicamos una versión, el ZIP listo para instalar se genera automáticamente en GitHub. Sin pasos manuales.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: El proceso de generación automática del ZIP no se ejecutaba correctamente en versiones anteriores.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 2.1.2 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: -4rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                    <div class="braves-timeline__badge">v2.1.2</div>
                                    <div class="braves-timeline__label"><?php esc_html_e('20 Feb 2026', 'braveschat'); ?></div>
                                </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('El chat ignora los estilos de tu tema', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: El chat ya no hereda estilos del tema activo. Botones, inputs y textos se ven igual sin importar qué tema tengas instalado.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: La tipografía Montserrat se aplica en todos los elementos del chat: modal, pantalla completa y banner GDPR.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Correcciones', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('FIXED: El chat en posición central se veía desplazado en algunos temas. Ahora se centra correctamente.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: La posición inferior izquierda en modo modal quedaba mal alineada en ciertos tamaños de pantalla.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: Cada sección del panel tiene su propio título en la pestaña del navegador.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Al desinstalar el plugin, tus configuraciones se conservan por si lo reinstalás más adelante.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 2.1.1 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -28rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.1.1</div>
                                <div class="braves-timeline__label"><?php esc_html_e('16 Feb 2026', 'braveschat'); ?></div>
                            </div> 
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Markdown en tiempo real', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: Las respuestas del agente se renderizan en tiempo real con formato Markdown. Negritas, listas y enlaces aparecen tal como llegan.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: Al enviar un mensaje, el cursor vuelve automáticamente al campo de texto. Puedes seguir escribiendo sin hacer clic.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                    <!-- Version 2.1.0 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -8rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v2.1.0</div>
                                <div class="braves-timeline__label"><?php esc_html_e('16 Feb 2026', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Controla la velocidad de escritura', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Control deslizante para ajustar la velocidad a la que el agente "escribe" sus respuestas. Desde lenta y pausada hasta casi instantánea.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: El banner GDPR ahora acepta texto enriquecido. Puedes añadir negritas, listas o enlaces directamente desde el campo de texto.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: La tipografía del chat se carga desde el propio servidor, sin depender de servicios externos.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Correcciones', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('FIXED: El scroll del chat baja automáticamente al llegar un mensaje nuevo. El banner GDPR se muestra correctamente en todos los temas.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 2.0.0 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: -16rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                    <div class="braves-timeline__badge">v2.0.0</div>
                                    <div class="braves-timeline__label"><?php esc_html_e('14 Feb 2026', 'braveschat'); ?></div>
                                </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('BravesChat iA 2.0', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: El plugin se renombra como BravesChat iA con arquitectura renovada de arriba abajo.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Botón para maximizar el chat. El usuario puede expandirlo a pantalla completa en cualquier momento.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: El área de texto crece automáticamente al escribir varias líneas, sin desbordar el chat.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Tras interactuar, la burbuja del chat se minimiza en una pastilla compacta para no molestar.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: Identidad visual renovada con los colores corporativos de BravesChat.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Las burbujas de mensajes ya no cortan el texto ni muestran bordes extraños.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: El icono de enviar mensaje es ahora un diseño propio en blanco.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: El tooltip del botón flotante dice por defecto "Habla con nuestro asistente IA".', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Correcciones', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('FIXED: El botón de enviar ya no se queda bloqueado en situaciones inesperadas.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: Al guardar ajustes de Páginas Excluidas, la selección ya no se perdía.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: La integración con N8N ya no enviaba datos extra que podían confundir al agente.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: Al guardar ajustes desde cualquier sección, ya no se perdían los valores configurados en otras páginas.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.2.4 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -33rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                    <div class="braves-timeline__badge">v1.2.4</div>
                                    <div class="braves-timeline__label"><?php esc_html_e('17 Nov 2025', 'braveschat'); ?></div>
                                </div>
                            
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Tooltip personalizable', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Campo de texto para personalizar el mensaje que aparece al pasar el cursor sobre el botón flotante del chat.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Al activar el plugin, detecta automáticamente versiones anteriores instaladas, las desactiva y limpia los archivos obsoletos. Sin perder ninguna configuración.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: El color por defecto del icono del botón flotante cambia a gris claro para mejor contraste sobre fondos oscuros.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Las opciones de Apariencia están mejor organizadas. El tooltip aparece justo antes del selector de iconos.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Correcciones', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('FIXED: Error grave que impedía cargar WordPress cuando había dos versiones del plugin instaladas al mismo tiempo.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: El color del icono no se aplicaba correctamente al editar la configuración de apariencia.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.2.3 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: -3.5rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v1.2.3</div>
                                <div class="braves-timeline__label"><?php esc_html_e('26 Oct 2025', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Personalización de colores e iconos', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Sistema completo de personalización de colores desde el panel: burbuja, color primario, fondo y texto del chat.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Selector de color nativo con sincronización a campo de texto hexadecimal. Escribe el código exacto o usa el picker visual.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Paleta de colores extraída automáticamente del tema activo, más una paleta por defecto de 8 colores.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Cuatro iconos para el botón flotante: Robot, Círculo, Happy y Burbuja.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: Las paletas de colores se pueden expandir y contraer con animación suave para no saturar la pantalla.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: El selector de iconos usa un diseño de pestañas horizontal, más claro y rápido de usar.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: El panel de Apariencia es responsive. Los selectores se reorganizan en dos columnas en pantallas pequeñas.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Correcciones', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('FIXED: Eliminada la dependencia de Lottie Player que se cargaba desde un CDN externo. El botón del chat ya no falla si no hay conexión a internet.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: El botón flotante ahora usa un color sólido en lugar del gradiente que causaba inconsistencias visuales.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: La imagen del botón pasó de animación a SVG estático. Más rápido y sin errores de consola.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.2.2 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -44rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v1.2.2</div>
                                <div class="braves-timeline__label"><?php esc_html_e('25 Oct 2025', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Panel completo y funcional', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Correcciones', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('FIXED: Los campos de configuración no se mostraban en algunas páginas del panel. Ahora aparecen correctamente en todas las secciones.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: Al guardar desde cualquier página de ajustes, ya no se borraban los valores configurados en las otras secciones.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('FIXED: El icono del menú lateral mostraba gris en lugar de blanco al estar seleccionado.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras de Experiencia', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Las notificaciones de guardado exitoso desaparecen solas tras unos segundos, con animación suave.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Los iconos del sidebar se actualizaron a versiones sólidas para mejor legibilidad.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Las tarjetas de información son ahora clicables con enlaces externos.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('IMPROVED: Todos los checkboxes del panel usan el estilo toggle del diseño Bentō.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.2.1 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -7rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v1.2.1</div>
                                <div class="braves-timeline__label"><?php esc_html_e('24 Oct 2025', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Rediseño completo del panel de administración', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Panel de administración rediseñado con el estilo Bentō — limpio, modular y fácil de navegar.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Cinco secciones independientes: Resumen, Ajustes, Apariencia, Horarios y GDPR.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Sidebar compartido entre todas las páginas del panel para una navegación consistente.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.1.2 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: -8rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v1.1.2</div>
                                <div class="braves-timeline__label"><?php esc_html_e('23 Oct 2025', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Cambio de marca', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: El plugin actualiza su marca de Weblandia a BravesLab — todas las URLs y referencias se actualizaron a braveslab.com.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.1.1 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -2rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v1.1.1</div>
                                <div class="braves-timeline__label"><?php esc_html_e('16 Oct 2025', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Sistema de cookies y GDPR', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Sistema de identificación única de usuarios mediante fingerprinting del navegador, sin almacenar datos personales.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Banner de consentimiento GDPR configurable — bloquea el chat hasta que el usuario acepte.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Si las cookies están bloqueadas por el navegador, el historial de chat se guarda en memoria local como alternativa.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.1.0 -->
                        <div class="braves-timeline__item braves-tl-left" style="--braves-tl-nudge: -15rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v1.1.0</div>
                                <div class="braves-timeline__label"><?php esc_html_e('1 Oct 2025', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Horarios y páginas excluidas', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Define el horario en que el chat está disponible, con soporte de zonas horarias.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Mensaje automático fuera de horario. El agente responde con tu texto cuando no está disponible.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Selector de páginas excluidas. Elige en qué páginas de tu sitio no aparece el chat.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Token de autenticación para asegurar la comunicación con tu agente de N8N.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Mejoras', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('IMPROVED: La configuración del webhook es más flexible y valida la URL antes de guardar.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Version 1.0.0 -->
                        <div class="braves-timeline__item braves-tl-right" style="--braves-tl-nudge: -10rem;">
                            <div class="braves-timeline__spacer"></div>
                            <div class="braves-timeline__axis">
                                <div class="braves-timeline__badge">v1.0.0</div>
                                <div class="braves-timeline__label"><?php esc_html_e('15 Sep 2025', 'braveschat'); ?></div>
                            </div>
                            <div class="braves-timeline__card-side">
                                <div class="braves-changelog__version">
                                    <h3 class="braves-changelog__title">
                                        <?php esc_html_e('Lanzamiento inicial', 'braveschat'); ?>
                                    </h3>
                                    <div class="braves-changelog__section">
                                        <h4><?php esc_html_e('Nuevas Funcionalidades', 'braveschat'); ?></h4>
                                        <ul>
                                            <li><?php esc_html_e('ADDED: Widget de chat con IA integrado en WordPress mediante bloque de Gutenberg.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Conexión directa con flujos de trabajo de N8N mediante webhook configurable.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Dos modos de visualización: modal flotante y pantalla completa.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Posicionamiento configurable del botón flotante: derecha, izquierda o centro.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Mensajes de bienvenida, título y subtítulo del chat personalizables desde el panel.', 'braveschat'); ?></li>
                                            <li><?php esc_html_e('ADDED: Preparado para traducción a cualquier idioma.', 'braveschat'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div><!-- .braves-admin-content -->

        </div><!-- .braves-admin-body -->

    </div><!-- .braves-admin-container -->
</div><!-- .wrap -->

