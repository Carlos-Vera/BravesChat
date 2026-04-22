<?php
/**
 * BravesChat - Configuración de Valores por Defecto (TEMPLATE)
 *
 * Este es un archivo de ejemplo. Copia este archivo como 'defaults.php'
 * y edita los valores según tu instalación.
 *
 * INSTRUCCIONES:
 * 1. Copia este archivo: cp defaults.example.php defaults.php
 * 2. Edita defaults.php con tus valores personalizados
 * 3. NO edites este archivo (defaults.example.php)
 *
 * @package BravesChat
 * @since 2.1.1
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

return array(
    // URL del webhook de N8N
    // Ejemplo: 'https://tu-dominio.com/webhook/tu-id/chat'
    'webhook_url' => '',

    // Textos del encabezado
    'header_title' => __('Support Chat', 'braveschat'),
    'header_subtitle' => __('We are here to help you', 'braveschat'),

    // Mensaje de bienvenida
    'welcome_message' => __('Hello! How can we help you today?', 'braveschat'),

    // Posición del widget (opciones: 'bottom-right', 'bottom-left')
    'position' => 'bottom-right',

    // Páginas excluidas (array vacío por defecto)
    'excluded_pages' => array(),

    // Configuración de disponibilidad
    'availability_enabled' => false,
    'availability_start' => '09:00',
    'availability_end' => '18:00',
    'availability_timezone' => 'America/New_York', // Ver: https://www.php.net/manual/en/timezones.php
    'availability_message' => __('Our business hours are from 9:00 to 18:00. Leave us your message and we will get back to you as soon as possible.', 'braveschat'),

    // Modo de visualización (opciones: 'modal', 'fullscreen')
    'display_mode' => 'modal',

    // Configuración GDPR
    'gdpr_enabled' => false,
    'gdpr_message' => __('This site uses cookies to improve your experience. By continuing to browse, you accept our cookie policy.', 'braveschat'),
    'gdpr_accept_text' => __('Accept', 'braveschat'),
);
