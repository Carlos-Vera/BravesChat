/**
 * JavaScript para el panel de administración
 *
 * @package BravesChat
 */

/* ==================== DARK MODE TOGGLE ==================== */
(function () {
    window.bravesToggleTheme = function () {
        var isDark   = document.documentElement.getAttribute('data-braves-theme') === 'dark';
        var newTheme = isDark ? '' : 'dark';

        if (newTheme === 'dark') {
            document.documentElement.setAttribute('data-braves-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-braves-theme');
        }

        var btn = document.getElementById('braves-theme-toggle');
        if (btn) {
            var span = btn.querySelector('.braves-button__text');
            if (span) {
                span.textContent = newTheme === 'dark' ? 'Modo Claro' : 'Modo Oscuro';
            }
        }

        /* Guardar preferencia en user_meta vía AJAX */
        var config = window.bravesAdminConfig;
        if (config && config.ajaxUrl && config.themeNonce) {
            var data = new FormData();
            data.append('action', 'braveschat_save_theme');
            data.append('theme', newTheme);
            data.append('nonce', config.themeNonce);
            fetch(config.ajaxUrl, { method: 'POST', body: data });
        }
    };

    /* Sincronizar texto del botón con el tema activo al cargar */
    document.addEventListener('DOMContentLoaded', function () {
        var isDark = document.documentElement.getAttribute('data-braves-theme') === 'dark';
        var btn    = document.getElementById('braves-theme-toggle');
        if (btn) {
            var span = btn.querySelector('.braves-button__text');
            if (span) {
                span.textContent = isDark ? 'Modo Claro' : 'Modo Oscuro';
            }
        }
    });
})();

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

            $('#braves-select-all-pages').on('click', function () {
                $excludedPages.find('option').prop('selected', true);
                updateCounter();
            });

            $('#braves-deselect-all-pages').on('click', function () {
                $excludedPages.find('option').prop('selected', false);
                updateCounter();
            });
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

/* ==================== TINYMCE DARK MODE ==================== */
(function () {
    var DARK_CSS = 'html,body{background:#1e1e1e!important;color:#f0f0f0!important}p,div,span,li,td,th,h1,h2,h3,h4,h5,h6{color:#f0f0f0!important}a{color:#42b9f8!important}';

    function injectDark(editor) {
        try {
            var doc = editor.getDoc();
            if (!doc || doc.getElementById('braves-tmce-dark')) return;
            var style = doc.createElement('style');
            style.id = 'braves-tmce-dark';
            style.textContent = DARK_CSS;
            (doc.head || doc.getElementsByTagName('head')[0]).appendChild(style);
        } catch (e) {}
    }

    function removeDark(editor) {
        try {
            var doc = editor.getDoc();
            if (!doc) return;
            var el = doc.getElementById('braves-tmce-dark');
            if (el) el.parentNode.removeChild(el);
        } catch (e) {}
    }

    function applyToAll(isDark) {
        if (typeof tinymce === 'undefined') return;
        tinymce.editors.forEach(function (editor) {
            isDark ? injectDark(editor) : removeDark(editor);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce === 'undefined') return;

        tinymce.on('AddEditor', function (e) {
            e.editor.on('init', function () {
                if (document.documentElement.getAttribute('data-braves-theme') === 'dark') {
                    injectDark(e.editor);
                }
            });
        });
    });

    /* Parchar el toggle para actualizar TinyMCE al cambiar de tema */
    var _originalToggle = window.bravesToggleTheme;
    window.bravesToggleTheme = function () {
        _originalToggle();
        setTimeout(function () {
            applyToAll(document.documentElement.getAttribute('data-braves-theme') === 'dark');
        }, 50);
    };
})();