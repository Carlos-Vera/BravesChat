/**
 * Bloque de Gutenberg para BravesChat
 *
 * Bloque de pantalla completa: se coloca en la página donde se quiere
 * mostrar el chat embebido. Usa la configuración global del plugin.
 *
 * @package BravesChat
 */

(function (blocks, element, blockEditor, components, i18n) {
    'use strict';

    var el = element.createElement;
    var __ = i18n.__;
    var InspectorControls = blockEditor.InspectorControls || wp.editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextareaControl = components.TextareaControl;

    blocks.registerBlockType('braves/chat-widget', {
        title: __('BravesChat — Pantalla Completa', 'braveschat'),
        description: __('Muestra el chat de BravesChat en pantalla completa en esta página. Configura el webhook, colores y título desde el panel del plugin.', 'braveschat'),
        icon: 'format-chat',
        category: 'widgets',
        keywords: [
            __('chat', 'braveschat'),
            __('ia', 'braveschat'),
            __('asistente', 'braveschat'),
            __('braveslab', 'braveschat'),
            __('fullscreen', 'braveschat')
        ],
        supports: {
            html: false,
            multiple: false,
            reusable: true
        },

        attributes: {
            welcomeMessage: {
                type: 'string',
                default: window.bravesChatBlock ? window.bravesChatBlock.defaultWelcomeMessage : ''
            }
        },

        edit: function (props) {
            var welcomeMessage = props.attributes.welcomeMessage;
            var setAttributes = props.setAttributes;

            var previewText = welcomeMessage
                ? (welcomeMessage.length > 160 ? welcomeMessage.substring(0, 160) + '...' : welcomeMessage)
                : __('(Usa el mensaje configurado en el panel del plugin)', 'braveschat');

            return el('div', { className: 'wp-block-braves-chat-widget' },

                // Sidebar — solo mensaje de bienvenida
                el(InspectorControls, {},
                    el(PanelBody, {
                        title: __('Mensaje de bienvenida', 'braveschat'),
                        initialOpen: true
                    },
                        el(TextareaControl, {
                            label: __('Mensaje de bienvenida', 'braveschat'),
                            value: welcomeMessage,
                            onChange: function (value) {
                                setAttributes({ welcomeMessage: value });
                            },
                            rows: 5,
                            help: __('Primer mensaje que verá el usuario en esta página. Si lo dejas vacío se usará el mensaje global del plugin.', 'braveschat')
                        })
                    )
                ),

                // Preview card en el editor
                el('div', { className: 'braves-block-card' },

                    // Header
                    el('div', { className: 'braves-block-card__header' },
                        el('div', { className: 'braves-block-card__brand' },
                            el('div', { className: 'braves-block-card__icon' },
                                el('svg', {
                                    width: '16', height: '16',
                                    viewBox: '0 0 24 24', fill: 'none',
                                    stroke: 'currentColor', strokeWidth: '2',
                                    strokeLinecap: 'round', strokeLinejoin: 'round'
                                },
                                    el('path', { d: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z' })
                                )
                            ),
                            el('span', { className: 'braves-block-card__name' }, 'BravesChat')
                        ),
                        el('span', { className: 'braves-block-card__badge' },
                            __('Pantalla Completa', 'braveschat')
                        )
                    ),

                    // Body — burbuja con mensaje de bienvenida
                    el('div', { className: 'braves-block-card__body' },
                        el('div', { className: 'braves-block-card__message-wrap' },
                            el('div', { className: 'braves-block-card__avatar' },
                                el('svg', {
                                    width: '16', height: '16',
                                    viewBox: '0 0 24 24', fill: 'none',
                                    stroke: '#ffffff', strokeWidth: '2',
                                    strokeLinecap: 'round', strokeLinejoin: 'round'
                                },
                                    el('path', { d: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z' })
                                )
                            ),
                            el('div', { className: 'braves-block-card__bubble' },
                                el('p', { className: 'braves-block-card__bubble-text' }, previewText)
                            )
                        )
                    ),

                    // Footer — nota informativa
                    el('div', { className: 'braves-block-card__footer' },
                        el('svg', {
                            width: '13', height: '13',
                            viewBox: '0 0 24 24', fill: 'none',
                            stroke: 'currentColor', strokeWidth: '2',
                            strokeLinecap: 'round', strokeLinejoin: 'round',
                            style: { flexShrink: 0 }
                        },
                            el('circle', { cx: '12', cy: '12', r: '10' }),
                            el('line', { x1: '12', y1: '8', x2: '12', y2: '12' }),
                            el('line', { x1: '12', y1: '16', x2: '12.01', y2: '16' })
                        ),
                        el('span', {},
                            __('Webhook, título, colores y posición se configuran desde el panel de BravesChat.', 'braveschat')
                        )
                    )
                )
            );
        },

        save: function () {
            return null;
        }
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor || window.wp.editor,
    window.wp.components,
    window.wp.i18n
);
