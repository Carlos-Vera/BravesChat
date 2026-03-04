# BravesChat Plugin - Documentación Técnica

## 📋 Información General
- **Nombre:** BravesChat
- **Versión:** 2.2.2
- **Descripción:** Plugin profesional de chat para WordPress con integración a N8N, soporte de horarios, cumplimiento GDPR, personalización avanzada y estadísticas de conversaciones.
- **Autor:** Carlos Vera (BravesLab)
- **Repositorio:** Carlos-Vera/BravesChat

## 🛠️ Stack Tecnológico
- **PHP:** 7.4+
- **WordPress:** 5.8+
- **JavaScript:** Vanilla JS (ES6+)
- **CSS:** Vanilla CSS + CSS Variables (Custom Properties)
- **Integración:** N8N Webhooks (HTTP POST)

## 🏗️ Arquitectura
El plugin sigue el patrón **Singleton** para sus clases principales, asegurando una única instancia de cada componente.

### Estructura de Clases
- `BravesChat` (Main): Inicialización, carga de dependencias, hooks globales.
- `Settings`: Gestión de opciones (Settings API), registro de campos.
- `Frontend`: Encolado de scripts/estilos, renderizado del widget (Modal/Fullscreen).
- `Admin_Controller`: Controlador del panel de administración (Diseño "Bentō").
- `BravesCookieManager`: Gestión de cookies y fingerprinting.

### Directorios Clave
- `/includes`: Lógica de negocio (PHP).
- `/includes/admin`: Lógica y templates del panel de administración.
- `/assets/css`: Estilos frontend y backend.
- `/assets/js`: Lógica frontend (chat, fingerprint, redirect) y backend.
- `/templates`: Archivos de vista del widget (`modal.php`, `screen.php`).

## ✨ Funcionalidades Principales

### 1. Integración con N8N
- **Webhook:** Envío de mensajes a flujos de trabajo en N8N.
- **Autenticación:** Soporte para Token de Seguridad (Header `X-N8N-Auth`).
- **Payload:** Incluye mensaje, historial, fingerprint del usuario, y metadatos de la página.

### 2. Modos de Visualización
- **Modal:** Widget flotante en esquina (configurable).
- **Fullscreen:** Interfaz de chat a pantalla completa.
- **Skins:** "Default" y "Braves" (diseño personalizado con header transparente y avatares).

### 3. Personalización (Apariencia)
- **Colores:** Primario, Fondo, Texto, Burbuja, Icono.
- **Textos:** Título, Subtítulo, Mensaje de Bienvenida, Estado ("Escribiendo...").
- **Posición:** Inferior Derecha, Inferior Izquierda.
- **Icono:** Selección de iconos (SVG/Dashicons) o imagen personalizada.

### 4. Disponibilidad y Horarios
- **Horario:** Configuración de hora de inicio y fin.
- **Zona Horaria:** Selección de timezone.
- **Mensaje Offline:** Respuesta automática fuera de horario.

### 5. Privacidad y GDPR
- **Banner de Consentimiento:** Bloqueo del chat hasta aceptación.
- **Fingerprinting:** Identificación única de usuario sin datos personales (`braves_fingerprint.js`).
- **Cookies:** `braves_chat_session` (Duración: 1 año).

### 6. Estadísticas (v2.1.4)
- **Webhook:** Consulta a N8N que extrae el historial de conversaciones desde Postgres.
- **Configuración:** URL del webhook + API Key propios (opciones `braves_chat_stats_webhook_url` / `braves_chat_stats_api_key`).
- **Tabla:** Columnas Session ID, Email, Último Mensaje, Fecha/Hora.
- **CSV:** Exportación con todos los campos: `session_id`, `client_mail`, `last_message`, `updated_at`, `chat_history`, `metadata`, `user_height`.

### 7. Experiencia de Usuario (UX)
- **Markdown:** Soporte para renderizado de Markdown en mensajes del bot (enlaces, negritas, listas).
- **Typing Indicator:** Animación de puntos suspensivos y velocidad de escritura configurable.
- **Persistencia:** Historial de chat guardado en `localStorage` (o transitorio si no hay consentimiento).
- **Tipografía:** Montserrat (cargada localmente).

## 🎨 Convenciones de Diseño (UX/UI)
Consultar el archivo `UX-UI-Convenciones.md` para detalles sobre:
- Paleta de Colores
- Tipografía
- Componentes UI
- Animaciones
- Estructura CSS/JS

## 💻 Comandos de Desarrollo
No se requiere compilación (Vanilla JS/CSS).

- **Logs:** Revisar `debug.log` de WordPress para errores PHP.
- **Consola:** Revisar consola del navegador para errores JS (prefijo `BravesChat:`).

## 🚀 Flujo de Trabajo
1.  **Ramas:** `main` (producción), `develop` (desarrollo).
2.  **Versiones:** SemVer (MAJOR.MINOR.PATCH).
3.  **Commits:** Conventional Commits (e.g., `feat: nueva opción de color`, `fix: error en safari`).
4.  **Release Automatizado:**
    - Al crear un tag que empiece por `v` (e.g., `v2.1.3`), un GitHub Action genera automáticamente `braveschat.zip`.
    - Este ZIP contiene la carpeta `braveschat/` en la raíz, asegurando actualizaciones limpias en WordPress.
    - El ZIP se adjunta automáticamente al Release en GitHub.

## ⚠️ Notas Importantes
- **Cache:** Al actualizar Assets, incrementar `BRAVES_CHAT_VERSION` en `braves_chat.php` para romper la caché del navegador.
- **Base de Datos:** Las opciones se guardan en `wp_options` con prefijo `braves_chat_`.
