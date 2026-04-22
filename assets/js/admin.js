/**
 * JavaScript para el panel de administración
 *
 * @package BravesChat
 */

/* ==================== DARK MODE TOGGLE ==================== */
(function () {
    const { __ } = wp.i18n;

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
                /* translators: button label to switch to light mode */
                span.textContent = newTheme === 'dark' ? __('Light Mode', 'braveschat') : __('Dark Mode', 'braveschat');
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
                /* translators: button label to switch to light mode */
                /* translators: button label to switch to dark mode */
                span.textContent = isDark ? __('Light Mode', 'braveschat') : __('Dark Mode', 'braveschat');
            }
        }
    });
})();

(function ($) {
    'use strict';

    const { __ } = wp.i18n;

    $(document).ready(function () {

        /**
         * Validación de URL del webhook
         */
        $('input[name="braves_chat_webhook_url"]').on('blur', function () {
            var url = $(this).val();
            var urlPattern = /^https?:\/\/.+/i;

            if (url && !urlPattern.test(url)) {
                /* translators: browser alert for invalid webhook URL */
                alert( __('Please enter a valid URL starting with http:// or https://', 'braveschat') );
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
                /* translators: browser alert for invalid time format */
                alert( __('Please enter a valid time in HH:MM format (e.g. 09:00)', 'braveschat') );
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
                /* translators: %d = number of excluded pages */
                var text = count === 0 ? __('No excluded pages', 'braveschat') :
                    count === 1 ? __('1 excluded page', 'braveschat') :
                        count + ' ' + __('excluded pages', 'braveschat');
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
                /* translators: browser alert shown when saving without a webhook URL */
                alert( __('Please enter a valid webhook URL before saving.', 'braveschat') );
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
                /* translators: browser confirmation when leaving page with unsaved changes */
                return __('Are you sure you want to leave? Unsaved changes will be lost.', 'braveschat');
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
                /* translators: browser alert after copying system info */
                alert( __('System information copied to clipboard', 'braveschat') );
            }).catch(function () {
                /* translators: browser alert when clipboard copy fails */
                /* translators: browser alert when clipboard copy fails */
                alert( __('Could not copy. Please copy manually.', 'braveschat') );
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

/* ==================== CHANGELOG TIMELINE ACCORDION / ENTRANCE ==================== */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var timelineItems = document.querySelectorAll('.braves-timeline__item');
        if (!timelineItems || timelineItems.length === 0) return;

        // 1. Efecto de aparición inicial (Slide up al entrar por debajo)
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('braves-item--visible');
                    } else if (entry.boundingClientRect.top > 0) {
                        // Reset if it goes back below the screen to allow replay
                        // Remove class to animate again when scrolling back down
                        entry.target.classList.remove('braves-item--visible');
                        entry.target.style.opacity = '';
                        entry.target.style.transform = '';
                    }
                });
            }, {
                root: null,
                rootMargin: '0px 0px -50px 0px', // Animates slightly before taking up the screen
                threshold: 0.1
            });

            timelineItems.forEach(function (item) {
                observer.observe(item);
            });
        }

        // 2. Efecto de desaparición superior (Fade out y push back)
        // Aplicamos el transform SOLO al interior (.braves-timeline__card-side) 
        // para que NO rompa el position: sticky del envoltorio.
        function onScroll() {
            var viewportHeight = window.innerHeight;
            // El fade inicia un poco por encima del centro (donde está sticky el badge)
            var fadeStart = (viewportHeight * 0.25) - 20; 
            var fadeEnd = 30;    

            timelineItems.forEach(function (item) {
                if (!item.classList.contains('braves-item--visible')) return;

                var rect = item.getBoundingClientRect();
                var cardSide = item.querySelector('.braves-timeline__card-side');
                var axis = item.querySelector('.braves-timeline__axis');
                
                if (rect.top < fadeStart && rect.bottom > 0) {
                    var range = fadeStart - fadeEnd;
                    var opacity = (rect.top - fadeEnd) / range;
                    
                    if (opacity < 0) opacity = 0;
                    if (opacity > 1) opacity = 1;

                    var scale = 0.95 + (opacity * 0.05); 
                    var translateY = (1 - opacity) * -20; 

                    if (cardSide) {
                        cardSide.style.opacity = opacity;
                        cardSide.style.transform = 'scale(' + scale + ') translateY(' + translateY + 'px)';
                    }
                    if (axis) {
                        axis.style.opacity = opacity;
                    }
                } else if (rect.top >= fadeStart) {
                    if (cardSide) {
                        cardSide.style.opacity = '';
                        cardSide.style.transform = '';
                    }
                    if (axis) {
                        axis.style.opacity = '';
                    }
                }
            });
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });
})();

/* ==================== SCROLL TO TOP BUTTON ==================== */
(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var scrollTopWrapper = document.getElementById('braves-scroll-top-wrapper');
        if (!scrollTopWrapper) return;

        window.addEventListener('scroll', function () {
            if (window.scrollY > 700) {
                scrollTopWrapper.style.opacity = '1';
                scrollTopWrapper.style.visibility = 'visible';
            } else {
                scrollTopWrapper.style.opacity = '0';
                scrollTopWrapper.style.visibility = 'hidden';
            }
        }, { passive: true });
    });
})();