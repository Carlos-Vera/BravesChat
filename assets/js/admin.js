/**
 * JavaScript para el panel de administración
 *
 * @package BravesChat
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        /**
         * Validación de URL del webhook
         */
        $('input[name="braves_chat_webhook_url"]').on('blur', function () {
            var url = $(this).val();
            var urlPattern = /^https?:\/\/.+/i;

            if (url && !urlPattern.test(url)) {
                alert('Por favor, introduce una URL válida que comience con http:// o https://');
                $(this).focus();
            }
        });

        /**
         * Validación de horarios
         */
        function validateTimeFormat(timeString) {
            var timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
            return timePattern.test(timeString);
        }

        $('input[name="braves_chat_availability_start"], input[name="braves_chat_availability_end"]').on('blur', function () {
            var time = $(this).val();

            if (time && !validateTimeFormat(time)) {
                alert('Por favor, introduce un horario válido en formato HH:MM (ejemplo: 09:00)');
                $(this).val('09:00');
                $(this).focus();
            }
        });

        /**
         * Mostrar/ocultar campos de disponibilidad
         */
        var $availabilityEnabled = $('input[name="braves_chat_availability_enabled"]');
        var $availabilityFields = $availabilityEnabled.closest('tr').nextAll('tr').slice(0, 4);

        function toggleAvailabilityFields() {
            if ($availabilityEnabled.is(':checked')) {
                $availabilityFields.fadeIn();
            } else {
                $availabilityFields.fadeOut();
            }
        }

        toggleAvailabilityFields();
        $availabilityEnabled.on('change', toggleAvailabilityFields);

        /**
         * Selector múltiple mejorado para páginas excluidas
         */
        var $excludedPages = $('select[name="braves_chat_excluded_pages[]"]');

        if ($excludedPages.length) {
            // Agregar contador de selección
            var $counter = $('<p class="description"></p>');
            $excludedPages.after($counter);

            function updateCounter() {
                var count = $excludedPages.find('option:selected').length;
                var text = count === 0 ? 'No hay páginas excluidas' :
                    count === 1 ? '1 página excluida' :
                        count + ' páginas excluidas';
                $counter.text(text);
            }

            updateCounter();
            $excludedPages.on('change', updateCounter);
        }

        /**
         * Confirmación antes de guardar
         */
        $('form').on('submit', function (e) {
            var webhookUrl = $('input[name="braves_chat_webhook_url"]').val();

            if (!webhookUrl) {
                e.preventDefault();
                alert('Por favor, introduce una URL de webhook válida antes de guardar.');
                $('input[name="braves_chat_webhook_url"]').focus();
                return false;
            }
        });

        /**
         * Vista previa del mensaje de bienvenida
         */
        var $welcomeMessage = $('textarea[name="braves_chat_welcome_message"]');

        if ($welcomeMessage.length) {
            var $preview = $('<div class="braves-message-preview"></div>');
            $preview.css({
                'background': 'white',
                'border': '1px solid #ddd',
                'border-radius': '8px',
                'padding': '15px',
                'margin-top': '10px',
                'font-size': '14px',
                'line-height': '1.5',
                'color': '#242424'
            });

            $welcomeMessage.after($preview);

            function updatePreview() {
                var message = $welcomeMessage.val();
                $preview.text(message);
            }

            updatePreview();
            $welcomeMessage.on('input', updatePreview);
        }

        /**
         * Indicador de cambios no guardados
         */
        var formChanged = false;

        $('form input, form textarea, form select').on('change', function () {
            formChanged = true;
        });

        $(window).on('beforeunload', function () {
            if (formChanged) {
                return '¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.';
            }
        });

        $('form').on('submit', function () {
            formChanged = false;
        });

        /**
         * Ayuda contextual
         */
        $('.braves-help-toggle').on('click', function (e) {
            e.preventDefault();
            $(this).next('.braves-help-content').slideToggle();
        });

        /**
         * Copiar información del sistema
         */
        $('#braves-copy-system-info').on('click', function (e) {
            e.preventDefault();
            var systemInfo = $(this).prev('textarea').val();

            navigator.clipboard.writeText(systemInfo).then(function () {
                alert('Información del sistema copiada al portapapeles');
            }).catch(function () {
                alert('No se pudo copiar. Por favor, copia manualmente.');
            });
        });

    });

})(jQuery);