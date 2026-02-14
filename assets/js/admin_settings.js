/**
 * Admin Settings Navigation
 * JavaScript para la navegación entre secciones en la página de ajustes
 *
 * @package BravesChat
 * @version 1.2.4
 */

(function () {
    'use strict';

    /**
     * Initialize settings navigation
     */
    function init_settings_navigation() {
        const nav_links = document.querySelectorAll('.braves-settings-nav-link');
        const sections = document.querySelectorAll('.braves-settings-section');

        if (!nav_links.length || !sections.length) {
            return;
        }

        // Mostrar solo la primera sección al cargar
        show_section('general');

        // Agregar listeners a los links de navegación
        nav_links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const section_id = this.getAttribute('data-section');

                // Actualizar estado activo en sidebar
                nav_links.forEach(function (nav_link) {
                    nav_link.parentElement.classList.remove('braves-admin-nav__item--active');
                });
                this.parentElement.classList.add('braves-admin-nav__item--active');

                // Mostrar sección correspondiente
                show_section(section_id);

                // Scroll to top
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });

        // Agregar listeners a los links de subsecciones
        const sublinks = document.querySelectorAll('.braves-admin-nav__sublink');
        sublinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const field_id = this.getAttribute('data-field');
                const field_element = document.getElementById('field-' + field_id);

                if (field_element) {
                    // Primero mostrar la sección padre
                    const parent_section = field_element.closest('.braves-settings-section');
                    if (parent_section) {
                        const section_id = parent_section.getAttribute('data-section');

                        // Actualizar navegación
                        const parent_link = document.querySelector('[data-section="' + section_id + '"]');
                        if (parent_link) {
                            nav_links.forEach(function (nav_link) {
                                nav_link.parentElement.classList.remove('braves-admin-nav__item--active');
                            });
                            parent_link.parentElement.classList.add('braves-admin-nav__item--active');
                        }

                        show_section(section_id);
                    }

                    // Luego hacer scroll al campo
                    setTimeout(function () {
                        field_element.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        // Highlight del campo
                        field_element.classList.add('braves-field-highlight');
                        setTimeout(function () {
                            field_element.classList.remove('braves-field-highlight');
                        }, 2000);
                    }, 300);
                }
            });
        });

        // Manejar navegación con hash en URL
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);

            // Si es una sección
            if (document.getElementById(hash)) {
                show_section(hash);

                // Actualizar navegación
                const link = document.querySelector('[data-section="' + hash + '"]');
                if (link) {
                    nav_links.forEach(function (nav_link) {
                        nav_link.parentElement.classList.remove('braves-admin-nav__item--active');
                    });
                    link.parentElement.classList.add('braves-admin-nav__item--active');
                }
            }
        }
    }

    /**
     * Show specific section and hide others
     *
     * @param {string} section_id - ID de la sección a mostrar
     */
    function show_section(section_id) {
        const sections = document.querySelectorAll('.braves-settings-section');

        sections.forEach(function (section) {
            if (section.getAttribute('data-section') === section_id) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        // Actualizar hash en URL sin scroll
        if (history.pushState) {
            history.pushState(null, null, '#' + section_id);
        } else {
            window.location.hash = section_id;
        }
    }

    /**
     * Auto-hide notices with flip animation
     */
    function init_notice_autohide() {
        const notices = document.querySelectorAll('.braves-notice');

        if (notices.length === 0) {
            return; // No hay notificaciones
        }

        notices.forEach(function (notice) {
            setTimeout(function () {
                notice.classList.add('braves-notice--hiding');

                // Remover del DOM después de la animación
                setTimeout(function () {
                    notice.remove();
                }, 500); // Duración de la animación slide-out
            }, 3000); // 3 segundos antes de empezar a ocultar
        });
    }

    /**
     * Initialize excluded pages buttons
     */
    function init_excluded_pages_buttons() {
        const selectAllBtn = document.getElementById('braves-select-all-pages');
        const deselectAllBtn = document.getElementById('braves-deselect-all-pages');
        const selectBox = document.getElementById('braves_chat_excluded_pages');

        if (selectAllBtn && selectBox) {
            selectAllBtn.addEventListener('click', function () {
                for (let i = 0; i < selectBox.options.length; i++) {
                    selectBox.options[i].selected = true;
                }
            });
        }

        if (deselectAllBtn && selectBox) {
            deselectAllBtn.addEventListener('click', function () {
                for (let i = 0; i < selectBox.options.length; i++) {
                    selectBox.options[i].selected = false;
                }
            });
        }
    }

    /**
     * Initialize skin visibility logic
     */
    function init_skin_logic() {
        const skinSelect = document.getElementById('braves_chat_chat_skin');
        if (!skinSelect) return;

        function toggleSkinSettings() {
            const skin = skinSelect.value;
            const bravesSkinElements = document.querySelectorAll('.braves-skin-only');
            const defaultSkinElements = document.querySelectorAll('.default-skin-only');

            if (skin === 'braves') {
                // Show Braves skin elements
                bravesSkinElements.forEach(el => el.style.display = 'block');
                // Hide Default skin elements
                defaultSkinElements.forEach(el => el.style.display = 'none');
            } else {
                // Show Default skin elements
                defaultSkinElements.forEach(el => el.style.display = 'block');
                // Hide Braves skin elements
                bravesSkinElements.forEach(el => el.style.display = 'none');
            }
        }

        // Initial check
        toggleSkinSettings();

        // Listen for changes
        skinSelect.addEventListener('change', toggleSkinSettings);
    }

    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            init_settings_navigation();
            init_excluded_pages_buttons();
            init_skin_logic();
            // Pequeño delay para asegurar que todo el DOM esté listo
            setTimeout(init_notice_autohide, 100);
        });
    } else {
        init_settings_navigation();
        init_excluded_pages_buttons();
        init_skin_logic();
        // Pequeño delay para asegurar que todo el DOM esté listo
        setTimeout(init_notice_autohide, 100);
    }


})();
