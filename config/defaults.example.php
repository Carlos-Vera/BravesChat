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
    'header_title' => __('Chat de Soporte', 'braves-chat'),
    'header_subtitle' => __('Estamos aquí para ayudarte', 'braves-chat'),

    // Mensaje de bienvenida
    'welcome_message' => __('¡Hola! ¿En qué podemos ayudarte hoy?', 'braves-chat'),

    // Posición del widget (opciones: 'bottom-right', 'bottom-left')
    'position' => 'bottom-right',

    // Páginas excluidas (array vacío por defecto)
    'excluded_pages' => array(),

    // Configuración de disponibilidad
    'availability_enabled' => false,
    'availability_start' => '09:00',
    'availability_end' => '18:00',
    'availability_timezone' => 'America/New_York', // Ver: https://www.php.net/manual/en/timezones.php
    'availability_message' => __('Nuestro horario de atención es de 9:00 a 18:00. Déjanos tu mensaje y te responderemos lo antes posible.', 'braves-chat'),

    // Modo de visualización (opciones: 'modal', 'fullscreen')
    'display_mode' => 'modal',

    // Configuración GDPR
    'gdpr_enabled' => false,
    'gdpr_message' => __('Este sitio utiliza cookies para mejorar tu experiencia. Al continuar navegando, aceptas nuestra política de cookies.', 'braves-chat'),
    'gdpr_accept_text' => __('Aceptar', 'braves-chat'),
);
