# Braves Chat iA - Documentación Técnica para Claude Code

> **Plugin**: Braves Chat iA
> **Versión**: 1.2.4
> **Autor**: Carlos Vera (BravesLab)
> **Diseño**: Bentō moderno
> **Patrón**: Singleton + Componentes modulares

Este archivo proporciona orientación técnica completa a Claude Code al trabajar en este repositorio.

---

## 📍 Entorno de Desarrollo

Este es un plugin de WordPress ubicado dentro de una instalación XAMPP:

- **Ruta del plugin**: `/Applications/MAMP/htdocs/wordpress/wp-content/plugins/braveschat`
- **Raíz de WordPress**: `/Applications/XAMPP/xamppfiles/htdocs/wordpress`
- **URL de prueba**: `http://localhost/wordpress/wp-admin`
- **PHP**: `/Applications/XAMPP/xamppfiles/bin/php` (usado para linting)
- **Actualización**: solo se actualizará versión cuando el usuario confirme que los cambios hechos funcionan correctamente, entonces se utilizará la siguiente estructura, se actualizan los archivos en este orden: 
    1. CLAUDE.md: agrega los cambios realizados, actualiza la estructura de archivos, incluye aclaraciones que puedan servir para un mejor desarrollo de parte de Claude en el futuro. 
    2. README.md: Agrega los cambios de funciones, estructura de archivos, mejoras, documentación, etc.
    3. CHANGELOG.md: Actualiza con las funciones, mejoras, implementaciones y demas datos importantes que deban estar aquí.
    4. braves_chat.php: actualiza la versión del plugin.
    5. about.php: agrega la nueva versión en la lista de changelog de la página siguiendo las reglas abajo establecidas para actualizar la sección de about.

### Convenciones de Código

- **Nomenclatura**: `snake_case` para todas las variables, funciones y archivos
- **NO usar camelCase** intenta evitar el uso a menos que la sintaxis lo exija
- **JSDoc**: Cada función nueva debe incluir comentarios JSDoc
- **Namespace**: Todas las clases PHP usan `BravesChat\Admin`
- **Patrón Singleton**: Todos los componentes admin usan instancia única

### Comunicación con el Usuario

- **Respuestas concisas**: Al finalizar tareas, solo dar resúmenes breves
- **Sin información excesiva**: Evitar emojis, listas largas y detalles innecesarios a menos que realmente aporten valor
- **Optimización de tokens**: Priorizar respuestas directas y eficientes
- **NO usar TodoWrite**: Evitar usar la herramienta TodoWrite a menos que sea estrictamente necesario
- **Menos herramientas**: Minimizar llamadas a herramientas innecesarias

---

## 📁 Estructura de Archivos

```
braves-chat-ia/
├── braves_chat.php                            # Plugin principal (v1.2.4)
├── includes/
│   ├── admin/                                 # Sistema de administración Bentō
│   │   ├── class_admin_controller.php         # Controlador principal
│   │   ├── class_template_helpers.php         # Helpers estáticos
│   │   ├── components/                        # Componentes reutilizables
│   │   │   ├── class_admin_header.php         # Header compartido
│   │   │   ├── class_admin_sidebar.php        # Sidebar compartido (5 secciones)
│   │   │   └── class_admin_content.php        # Content + Cards Bentō
│   │   └── templates/                         # Plantillas de páginas
│   │       ├── dashboard.php                  # Resumen
│   │       ├── settings.php                   # Ajustes
│   │       ├── appearance.php                 # Apariencia
│   │       ├── availability.php               # Horarios
│   │       └── gdpr.php                       # GDPR
│   ├── class_settings.php                     # WordPress Settings API
│   ├── class_chat_widget.php                  # Widget frontend
│   ├── class_gutenberg_block.php              # Bloque Gutenberg
│   └── ...
├── assets/
│   ├── css/
│   │   └── admin/
│   │       ├── variables.css                  # Variables CSS Bentō
│   │       ├── base.css                       # Estilos base
│   │       ├── components.css                 # Componentes (cards, toggles)
│   │       └── dashboard.css                  # Estilos específicos
│   ├── js/
│   │   ├── admin.js                           # Scripts admin
│   │   └── chat_widget.js                     # Widget frontend
│   └── media/
│       ├── braves-logo.svg                    # Logo del plugin
│       └── menu-icon.svg                      # Icono del menú WP
└── languages/                                 # Traducciones (i18n)
```

---

## 🏗️ Patrón de Arquitectura

### Principios de Diseño

1. **Singleton Pattern**: Todos los componentes admin usan instancia única
2. **Separación de Responsabilidades**: Cada clase tiene un propósito específico
3. **Componentes Reutilizables**: Header, Sidebar y Content compartidos
4. **Template Helpers**: Métodos estáticos para renderizado rápido
5. **Namespace**: `BravesChat\Admin` para organización

### Flujo de Ejecución

```
WordPress Admin Menu
    ↓
Admin_Controller::register_admin_menu()
    ↓
Admin_Controller::render_*_page()
    ↓
Template (settings.php, appearance.php, etc.)
    ↓
┌─────────────────────────────────────┐
│ Admin_Header::render()              │
│ Admin_Sidebar::render($current_page)│
│ Template_Helpers::card()            │
│   └→ Admin_Content::render_card()   │
└─────────────────────────────────────┘
```

---

## 🧩 Componentes del Sistema

### 1. Admin_Controller

**Archivo**: `includes/admin/class_admin_controller.php`

**Responsabilidad**: Controlador principal que coordina todo el sistema admin.

**Métodos clave**:
```php
- register_admin_menu()          // Registra páginas en WordPress
- render_dashboard_page()        // Renderiza Resumen
- render_settings_page()         // Renderiza Ajustes
- render_appearance_page()       // Renderiza Apariencia
- render_availability_page()     // Renderiza Horarios
- render_gdpr_page()             // Renderiza GDPR
- enqueue_admin_assets()         // Carga CSS/JS
- is_braves_admin_page()         // Detecta páginas del plugin
```

**Registro de páginas**:
```php
// WordPress solo muestra "Braves Chat iA" en el menú
add_menu_page('Braves Chat iA', ...);

// Todas las demás páginas están ocultas (parent_slug = null)
add_submenu_page(null, 'Resumen', ...);
add_submenu_page(null, 'Ajustes', ...);
add_submenu_page(null, 'Apariencia', ...);
add_submenu_page(null, 'Horarios', ...);
add_submenu_page(null, 'GDPR', ...);
```

---

### 2. Admin_Header

**Archivo**: `includes/admin/components/class_admin_header.php`

**Responsabilidad**: Renderizar la cabecera con logo y versión.

**Uso**:
```php
$header = Admin_Header::get_instance();
$header->render(array(
    'show_logo' => true,
    'show_version' => true,
));
```

**Salida HTML**:
```html
<header class="braves-admin-header">
    <div class="braves-admin-header__logo">
        <img src="assets/media/braves-logo.svg" alt="Braves Chat iA">
        <span class="braves-admin-header__version">v1.2.4</span>
    </div>
</header>
```

---

### 3. Admin_Sidebar

**Archivo**: `includes/admin/components/class_admin_sidebar.php`

**Responsabilidad**: Navegación lateral compartida entre todas las páginas.

**Características**:
- 5 secciones con iconos SVG
- Estado activo automático
- Hook `braves_chat_admin_menu_items` para extensibilidad

**Uso**:
```php
$sidebar = Admin_Sidebar::get_instance();
$sidebar->render($current_page);
```

**Estructura de menú**:
```php
array(
    array('id' => 'dashboard',   'label' => 'Resumen',    'page_slug' => 'braves-chat-ia'),
    array('id' => 'settings',    'label' => 'Ajustes',    'page_slug' => 'braves-chat-settings'),
    array('id' => 'appearance',  'label' => 'Apariencia', 'page_slug' => 'braves-chat-appearance'),
    array('id' => 'availability','label' => 'Horarios',   'page_slug' => 'braves-chat-availability'),
    array('id' => 'gdpr',        'label' => 'GDPR',       'page_slug' => 'braves-chat-gdpr'),
)
```

---

### 4. Admin_Content

**Archivo**: `includes/admin/components/class_admin_content.php`

**Responsabilidad**: Renderizar cards Bentō y componentes de contenido.

**Métodos**:
```php
- render_card($args)           // Card Bentō (CON SOPORTE PARA 'content')
- render_section($args)        // Sección con header
- render_toggle($args)         // Toggle moderno
- render_quick_action($args)   // Botón de acción rápida
- render_card_grid($cards)     // Grid de cards
```

**Uso de Cards (v1.2.2 - FIXED)**:
```php
Template_Helpers::card(array(
    'title' => 'Título del Card',
    'description' => 'Descripción breve',
    'content' => '<input type="text" name="field" class="braves-input">', // ✅ AHORA FUNCIONA
    'custom_class' => 'braves-card--full-width',
));
```

**Parámetros soportados**:
- `title` - Título del card (h3)
- `subtitle` - Subtítulo opcional
- `description` - Descripción (p)
- **`content`** - HTML personalizado (inputs, selects, textareas) ✅ v1.2.2
- `icon` - Icono SVG
- `action_text` / `action_url` - Botón de acción
- `footer` - Pie del card
- `custom_class` - Clases CSS adicionales

---

### 5. Template_Helpers

**Archivo**: `includes/admin/class_template_helpers.php`

**Responsabilidad**: Helpers estáticos para renderizado rápido.

**Métodos disponibles**:
```php
Template_Helpers::card($args)           // Renderiza card
Template_Helpers::section($args)        // Renderiza sección
Template_Helpers::toggle($args)         // Renderiza toggle
Template_Helpers::quick_action($args)   // Renderiza botón
Template_Helpers::card_grid($cards)     // Renderiza grid
Template_Helpers::notice($msg, $type)   // Renderiza notice
Template_Helpers::get_icon($name)       // Obtiene SVG
Template_Helpers::get_config_status()   // Estado del plugin
```

**Ejemplo de uso en templates**:
```php
<?php
Template_Helpers::notice('Configuración guardada correctamente.', 'success');

Template_Helpers::card(array(
    'title' => 'URL del Webhook',
    'description' => 'Endpoint de N8N',
    'content' => '<input type="url" name="braves_chat_webhook_url" value="..." class="braves-input">',
));
?>
```

---

## 📄 Estructura de Templates

### Anatomía de un Template

Todos los templates siguen la misma estructura:

```php
<?php
// 1. Imports
use BravesChat\Admin\Admin_Header;
use BravesChat\Admin\Admin_Sidebar;
use BravesChat\Admin\Template_Helpers;

// 2. Seguridad
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) wp_die('...');

// 3. Variables
$header = Admin_Header::get_instance();
$sidebar = Admin_Sidebar::get_instance();
$settings_updated = isset($_GET['settings-updated']);
$option_prefix = 'braves_chat_';
?>

<!-- 4. Layout -->
<div class="wrap braves-admin-wrap">
    <div class="braves-admin-container">

        <!-- Header -->
        <?php $header->render(array('show_logo' => true, 'show_version' => true)); ?>

        <div class="braves-admin-body">

            <!-- Sidebar -->
            <?php $sidebar->render($current_page); ?>

            <!-- Content -->
            <div class="braves-admin-content">

                <!-- Page Header -->
                <div class="braves-page-header">
                    <h1 class="braves-page-title">Título</h1>
                    <p class="braves-page-description">Descripción</p>
                </div>

                <!-- Success Notice -->
                <?php if ($settings_updated): ?>
                    <?php Template_Helpers::notice('Guardado correctamente.', 'success'); ?>
                <?php endif; ?>

                <!-- Form -->
                <form action="options.php" method="post">
                    <?php settings_fields('braves_chat_settings'); ?>

                    <div class="braves-section">
                        <h2 class="braves-section__title">Sección</h2>

                        <div class="braves-card-grid braves-card-grid--2-cols">

                            <?php
                            // Renderizar campos con Template_Helpers::card()
                            ?>

                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="braves-section braves-section--actions">
                        <?php submit_button('Guardar cambios', 'primary braves-button'); ?>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
```

---

## 🎨 Sistema de Estilos CSS

### Cascada de CSS

```
variables.css         → Tokens de diseño (colores, tipografía, espaciado)
    ↓
base.css             → Reset + estilos base
    ↓
components.css       → Cards, toggles, buttons, inputs, notices
    ↓
dashboard.css        → Estilos específicos de páginas
```

### Clases CSS Principales

**Layout**:
```css
.braves-admin-wrap          /* Wrapper principal */
.braves-admin-container     /* Container con padding */
.braves-admin-header        /* Header con logo */
.braves-admin-body          /* Body con sidebar + content */
.braves-admin-sidebar       /* Sidebar de navegación */
.braves-admin-content       /* Área de contenido */
```

**Componentes**:
```css
.braves-card                     /* Card Bentō */
.braves-card__title              /* Título del card */
.braves-card__description        /* Descripción del card */
.braves-card__content            /* ✅ Contenido del card (v1.2.2) */
.braves-card--full-width         /* Card ancho completo */

.braves-card-grid                /* Grid de cards */
.braves-card-grid--2-cols        /* Grid de 2 columnas */
.braves-card-grid--3-cols        /* Grid de 3 columnas */

.braves-toggle-wrapper           /* Wrapper del toggle */
.braves-toggle-input             /* Input checkbox */
.braves-toggle-slider            /* Slider visual */

.braves-input                    /* Input text/url/password */
.braves-textarea                 /* Textarea */
.braves-select                   /* Select */
.braves-button                   /* Botón */
.braves-button--primary          /* Botón primario */

.braves-notice                   /* Notice/alert */
.braves-notice--success          /* Success message */
.braves-notice--error            /* Error message */
```

---

## 🔧 WordPress Settings API

### Registro de Opciones

**Archivo**: `includes/class_settings.php`

Todas las opciones se registran con prefijo `braves_chat_`:

```php
// Ajustes
braves_chat_global_enable         // boolean
braves_chat_webhook_url           // string (URL)
braves_chat_n8n_auth_token        // string
braves_chat_excluded_pages        // array (IDs)

// Apariencia
braves_chat_header_title          // string
braves_chat_header_subtitle       // string
braves_chat_welcome_message       // string (textarea)
braves_chat_position              // string (bottom-right|bottom-left|center)
braves_chat_display_mode          // string (modal|fullscreen)

// Horarios
braves_chat_availability_enabled  // boolean
braves_chat_availability_start    // string (time)
braves_chat_availability_end      // string (time)
braves_chat_availability_timezone // string
braves_chat_availability_message  // string (textarea)

// GDPR
braves_chat_gdpr_enabled          // boolean
braves_chat_gdpr_message          // string (textarea)
braves_chat_gdpr_accept_text      // string
```

### Guardar Datos

Los formularios usan el Settings API nativo de WordPress:

```php
<form action="options.php" method="post">
    <?php settings_fields('braves_chat_settings'); ?>

    <!-- Campos aquí -->

    <?php submit_button(); ?>
</form>
```

---

## 🚀 Extensibilidad

### Hooks Disponibles

```php
// Sidebar: Agregar items de navegación
add_filter('braves_chat_admin_menu_items', function($items) {
    $items[] = array(
        'id' => 'custom',
        'label' => 'Mi Sección',
        'url' => admin_url('admin.php?page=custom'),
        'page_slug' => 'custom',
        'icon' => '<svg>...</svg>',
    );
    return $items;
});

// Sidebar: Agregar contenido extra
add_action('braves_chat_admin_sidebar_items', function($current_page) {
    echo '<div class="custom-sidebar-content">...</div>';
});
```

---

## 📦 Cambios en v1.2.4

### 🎨 Personalización del Tooltip y Color de Icono

**Nueva funcionalidad**: Tooltip personalizable para el botón flotante y nuevo color por defecto del icono.

**Características**:
- Campo de texto para personalizar el mensaje del tooltip del botón flotante
- Color por defecto del icono SVG cambiado a `#f2f2f2` (gris claro)
- Tooltip ubicado estratégicamente antes del selector de iconos en la UI
- Input text con `width: 100%` para consistencia visual

**Implementación**:
- Input text con `width: 100%` para consistencia visual
- Opción `braves_chat_bubble_tooltip` registrada en Settings API
- Default: "Habla con nuestro asistente IA" (traducible)
- Atributo `title` en botón flotante usa el valor personalizado

**Archivos modificados**:
- `class_settings.php` - Registro opción `bubble_tooltip` y actualización `icon_color` default a `#f2f2f2`
- `appearance.php` - Card "Tooltip del Botón" agregada antes de "Icono del Botón", fallback actualizado
- `modal.php` / `screen.php` - Variable `$bubble_tooltip` obtenida y usada en atributo `title`

**Opciones registradas**:
```php
braves_chat_bubble_tooltip  // Tooltip del botón flotante (default: "Habla con nuestro asistente IA")
braves_chat_icon_color      // Color del icono SVG (default: #f2f2f2)
```

### 🔄 Detección y Reemplazo Automático de Versiones Antiguas

**Nueva funcionalidad**: El plugin ahora detecta automáticamente versiones anteriores instaladas y las reemplaza al activar una nueva versión.

**Características**:
- Escaneo automático del directorio de plugins en busca de versiones antiguas del plugin
- Desactivación automática de plugins antiguos si están activos
- Eliminación automática de directorios de versiones anteriores
- Preservación de configuraciones del usuario durante la migración

**Implementación**:
- Método `detect_and_replace_old_versions()` en `BravesChat::activate()`
- Uso de WordPress Filesystem API para eliminación segura de directorios
- Patrón de búsqueda para detectar todas las versiones antiguas del plugin anterior
- Exclusión del directorio actual para evitar auto-eliminación

**Archivos modificados**:
- `braves_chat.php` - Nuevo método `detect_and_replace_old_versions()` agregado al hook de activación

**Beneficios**:
- ✅ Evita conflictos de versiones múltiples instaladas simultáneamente
- ✅ Previene errores fatales de "function already declared"
- ✅ Mantiene la instalación limpia y actualizada
- ✅ Experiencia de actualización fluida para usuarios

---

## 📦 Cambios en v1.2.3

### 🎨 Sistema de Personalización de Colores

**Nueva funcionalidad**: Selector de colores para personalizar el aspecto visual del chat desde el panel de Apariencia.

**Características**:
- 4 campos de color personalizables: Burbuja, Primario, Fondo y Texto
- Color picker nativo HTML5 (40x40px) con input de texto hexadecimal
- Paleta de colores del tema de WordPress (colapsable)
- Paleta por defecto de 8 colores cuando el tema no tiene colores personalizados
- CSS inyectado dinámicamente en el frontend con `!important` rules
- Alineación horizontal usando `display: inline-block` con `vertical-align: middle`

**Implementación**:
- Color picker de 40x40px con border-radius 6px
- Input text readonly mostrando código hexadecimal en mayúsculas
- Toggle button para expandir/colapsar paleta de colores del tema
- Botones de color preset de 32x32px con efecto hover
- Helpers PHP para aclarar/oscurecer colores: `lighten_color()` y `darken_color()`

**Archivos nuevos**:
- `assets/js/color_picker.js` - Sincronización color picker con input text

**Archivos modificados**:
- `class_settings.php` - Registro de 4 opciones de color (default: #01B7AF, #FFFFFF, #333333)
- `appearance.php` - Cards Bentō con color pickers y paletas colapsables
- `class_frontend.php` - Método `inject_custom_colors()` con CSS inline
- `components.css` - Estilos para `.braves-color-picker`, `.braves-palette-toggle`, `.braves-color-preset`
- `class_admin_controller.php` - Enqueue color_picker.js

**Opciones registradas**:
```php
braves_chat_bubble_color      // Color del botón flotante (default: #01B7AF)
braves_chat_primary_color     // Color del header y mensajes IA (default: #01B7AF)
braves_chat_background_color  // Color de fondo del chat (default: #FFFFFF)
braves_chat_text_color        // Color del texto de mensajes (default: #333333)
```

**CSS inyectado**:
El método `inject_custom_colors()` en `class_frontend.php` aplica los colores a:
- Botón flotante del chat (`#chat-toggle`)
- Header del modal y fullscreen (`#chat-header`)
- Mensajes del asistente (`.message.assistant`)
- Fondo del área de mensajes (`#chat-messages`)
- Color de texto de los mensajes (`.message-text`)
- Input box de escritura (`#message-input`, `#send-button`)

### ✨ Sistema de Iconos SVG Personalizables

**Nueva funcionalidad**: Selector de iconos para el botón flotante del chat.

**Características**:
- 4 iconos SVG optimizados: Original (robot), Círculo, Happy, Burbuja
- Selector estilo tabs Bentō en página de Apariencia
- Iconos con `width="48" height="48"` desde viewBox 460x460
- Opción `braves_chat_chat_icon` registrada en Settings API
- Icono por defecto: "Original" (robot-chat)

**Implementación**:
- Tabs horizontales con fondo gris claro `#f9fafb`
- Tab seleccionado con borde morado `#5B4CCC`
- Responsive: 2 columnas en móvil (max-width: 782px)
- JavaScript interactivo en `icon_selector.js`

**Archivos nuevos**:
- `assets/media/chat-icons/*.svg` - 4 iconos SVG
- `assets/js/icon_selector.js` - Selector tabs

**Archivos modificados**:
- `class_settings.php` - Registro opción `chat_icon` (default: robot-chat)
- `appearance.php` - Selector tabs Bentō
- `components.css` - Estilos `.braves-icon-tabs`
- `class_admin_controller.php` - Enqueue icon_selector.js
- `modal.php` / `screen.php` - `<img>` SVG en botón flotante
- `class_frontend.php` - Eliminada dependencia Lottie

### 🐛 Eliminación de Lottie Player

**Problema**: Dependencia externa CDN causaba errores de consola.

**Solución**:
1. ✅ Eliminado `lottie-player` de wp_enqueue_script
2. ✅ Removido `animationPath` de configuración JS
3. ✅ Templates usan `<img id="chat-icon">` en lugar de `<div id="chat-lottie">`
4. ✅ JavaScript maneja `this.chat_icon` con show/hide

**Archivos modificados**:
- `braves_chat_block_modal.js` - Eliminado init_lottie_animation()
- `braves_chat_block_screen.js` - Eliminado init_lottie_animation()
- `class_frontend.php` - Eliminado wp_dequeue_script('lottie-player')

### 🔧 Fallback wp.i18n

**Mejora**: Compatibilidad cuando traducciones no están disponibles.

**Implementación**:
```javascript
const { __, _x, _n, sprintf } = window.wp && window.wp.i18n ? window.wp.i18n : {
    __: (text) => text,
    _x: (text) => text,
    _n: (single, plural, number) => number === 1 ? single : plural,
    sprintf: (format, ...args) => format
};
```

---

## 📦 Cambios en v1.2.2

### 🐛 Corrección Crítica

**Problema**: Los inputs no se renderizaban en las tarjetas Bentō.

**Causa**: `Admin_Content::render_card()` no tenía soporte para el parámetro `content`.

**Solución**:
1. ✅ Agregado `'content' => ''` a defaults
2. ✅ Agregado bloque de renderizado con `<div class="braves-card__content">`
3. ✅ Configurado `wp_kses()` con whitelist completa para inputs

**Archivos modificados**:
- `includes/admin/components/class_admin_content.php` (líneas 95-152)
- `includes/admin/templates/settings.php` (reescrito con ob_start)
- `includes/admin/templates/appearance.php` (reescrito con ob_start)
- `includes/admin/templates/availability.php` (reescrito con ob_start)
- `includes/admin/templates/gdpr.php` (reescrito con ob_start)

### 🎨 Corrección de Estilos Inconsistentes (v1.2.2.1)

**Problema**: El Dashboard se veía diferente a las páginas de Ajustes/Apariencia/Horarios/GDPR.
- Background color diferente
- Menú lateral de WordPress con colores inconsistentes
- Variables CSS no aplicadas en todas las páginas

**Causa**: Los selectores CSS en `dashboard.css` solo aplicaban a `.toplevel_page_braves-chat-ia`, pero las subpáginas tienen identificadores diferentes (`.admin_page_braves-chat-settings`, etc.).

**Solución**:
1. ✅ Extendido todos los selectores CSS para incluir las 5 páginas del plugin
2. ✅ Agregado estilos del menú lateral de WordPress para mantener consistencia
3. ✅ Agregado carga de `settings.css` en el controlador
4. ✅ Aplicado background `#f3f6fc` a todas las páginas
5. ✅ Forzado estado activo del menú "Braves Chat iA" en todas las subpáginas

**Archivos modificados**:
- `assets/css/admin/dashboard.css` (líneas 13-64, 362-382)
- `includes/admin/class_admin_controller.php` (líneas 276-281)

**Selectores CSS agregados**:
```css
/* Ahora aplican a TODAS las páginas del plugin */
.toplevel_page_braves-chat-ia,
.admin_page_braves-chat-settings,
.admin_page_braves-chat-appearance,
.admin_page_braves-chat-availability,
.admin_page_braves-chat-gdpr {
    background-color: #f3f6fc;
    --wp-components-color-accent: #3858e9;
    /* ... */
}
```

### 🎨 Mejora de Toggles Estilo Bentō (v1.2.2.2)

**Mejora**: Todos los checkboxes ahora usan toggles estilo Bentō para una apariencia más moderna y consistente.

**Implementación**: Agregados estilos CSS simplificados para toggles que funcionan con la estructura HTML existente.

**Archivos modificados**:
- `assets/css/admin/components.css` (líneas 287-341)

**Uso en templates**:
```php
<label class="braves-toggle-wrapper">
    <input type="checkbox"
           id="option_name"
           name="option_name"
           value="1"
           <?php checked(1, $value); ?>
           class="braves-toggle-input">
    <span class="braves-toggle-slider"></span>
</label>
```

**Características del toggle**:
- ✅ Ancho: 48px, Alto: 24px
- ✅ Color inactivo: gris (`--braves-gray-300`)
- ✅ Color activo: azul primario (`--braves-primary`)
- ✅ Animación suave de transición
- ✅ Focus state accesible
- ✅ Estado disabled con opacidad reducida

### 📄 Nueva Página "Acerca de" (v1.2.2.3)

**Nueva funcionalidad**: Página oculta accesible desde el badge de versión que muestra información del plugin, changelog y créditos del equipo.

**Características**:
- No aparece en el sidebar de navegación
- Accesible haciendo clic en el badge de versión en el header
- Muestra información del plugin, equipo de desarrollo y historial de cambios
- Diseño Bentō consistente con el resto del admin

**Archivos creados**:
- `includes/admin/templates/about.php` - Template de la página

**Archivos modificados**:
- `includes/admin/class_admin_controller.php` - Registro de página oculta y método render_about_page()
- `includes/admin/components/class_admin_header.php` - Badge de versión clickeable
- `assets/css/admin/components.css` - Estilos para badges clickeables, equipo y changelog
- `assets/css/admin/dashboard.css` - Selectores CSS para incluir la nueva página

**Secciones de la página About**:
1. **Información del Plugin**: Versión, autor y empresa
2. **Equipo de Desarrollo**: Carlos Vera, Mikel Marqués, Claude
3. **Historial de Cambios**: Changelog completo con versiones 1.2.2, 1.2.1, 1.1.2, 1.1.1
4. **Enlaces Útiles**: GitHub, BravesLab Website, Soporte

### 🔧 Correcciones Críticas y Mejoras UX (v1.2.2 - Actualización Final)

**Problemas corregidos**:

1. **Pérdida de ajustes al guardar desde diferentes páginas**
   - **Problema**: Al guardar desde Settings, se perdían los ajustes de Appearance. Al guardar desde Appearance, se perdían Settings, etc.
   - **Causa**: WordPress Settings API sobrescribe TODAS las opciones en un grupo cuando se guarda, pero cada formulario solo enviaba sus propios campos visibles
   - **Solución**: Creado método `render_hidden_fields()` en `class_settings.php` que incluye campos ocultos con valores de otras secciones
   - **Archivos modificados**:
     - `includes/class_settings.php` - Nuevo método render_hidden_fields()
     - `includes/admin/templates/settings.php` - Campos ocultos agregados
     - `includes/admin/templates/appearance.php` - Campos ocultos agregados
     - `includes/admin/templates/availability.php` - Campos ocultos agregados
     - `includes/admin/templates/gdpr.php` - Campos ocultos agregados

2. **Icono del menú mostraba color gris en vez de blanco cuando estaba activo**
   - **Problema**: En páginas sin parent_slug (Settings, Appearance, etc.), el icono del menú no se mostraba blanco
   - **Solución**: JavaScript añade dinámicamente las clases `wp-has-current-submenu` y `wp-menu-open` al elemento del menú
   - **Archivos modificados**: `includes/admin/class_admin_controller.php` - Método add_menu_icon_active_styles()

3. **Script admin_settings.js no se cargaba en todas las páginas**
   - **Problema**: Las notificaciones de éxito no desaparecían automáticamente en páginas Appearance, Availability y GDPR
   - **Causa**: Script solo se encolaba en Settings page
   - **Solución**: Movido enqueue de script a `class_admin_controller.php` para todas las páginas del plugin
   - **Archivos modificados**: `includes/admin/class_admin_controller.php` - Método enqueue_admin_assets()

**Mejoras de UX implementadas**:

1. **Auto-ocultación de notificaciones de éxito**
   - **Implementación**: Sistema de auto-hide con animación slide-up después de 3 segundos
   - **Animación**: Transición suave con `translateY(-20px)` y fade-out
   - **Archivos modificados**:
     - `assets/js/admin_settings.js` - Función init_notice_autohide()
     - `assets/css/admin/components.css` - Keyframe braves-notice-slide-out

2. **Actualización de iconos de sidebar a versiones sólidas**
