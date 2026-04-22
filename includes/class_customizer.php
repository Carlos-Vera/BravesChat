<?php
/**
 * Integración con WordPress Customizer
 *
 * Registra opciones del chat en el Customizer de WordPress
 *
 * @package BravesChat
 * @since 1.0.0
 * @version 1.0.0
 */

namespace BravesChat;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase Customizer
 *
 * Integra el plugin con el Customizer de WordPress
 *
 * @since 1.0.0
 */
class Customizer {

    /**
     * Instancia única (patrón Singleton)
     *
     * @since 1.0.0
     * @var Customizer|null
     */
    private static $instance = null;

    /**
     * Obtener instancia única
     *
     * @since 1.0.0
     * @return Customizer Instancia única de la clase
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado (patrón Singleton)
     *
     * @since 1.0.0
     */
    private function __construct() {
        add_action('customize_register', array($this, 'register_customizer_settings'));
    }

    /**
     * Registrar configuraciones en el Customizer
     *
     * Añade panel, secciones y controles del chat al Customizer
     *
     * @since 1.0.0
     * @param WP_Customize_Manager $wp_customize Objeto del Customizer
     * @return void
     */
    public function register_customizer_settings($wp_customize) {
        // Agregar panel
        $wp_customize->add_panel('braves_chat_panel', array(
            'title' => __('BravesChat', 'braveschat'),
            'description' => __('Customize the appearance and behavior of the AI chat.', 'braveschat'),
            'priority' => 160,
        ));
        
        // Sección de Apariencia
        $wp_customize->add_section('braves_chat_appearance', array(
            'title' => __('Appearance', 'braveschat'),
            'panel' => 'braves_chat_panel',
            'priority' => 10,
        ));
        
        // Título del header
        $wp_customize->add_setting('braves_chat_header_title', array(
            'default' => __('BravesLab AI Assistant', 'braveschat'),
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control('braves_chat_header_title', array(
            'label' => __('Header Title', 'braveschat'),
            'section' => 'braves_chat_appearance',
            'type' => 'text',
        ));
        
        // Subtítulo del header
        $wp_customize->add_setting('braves_chat_header_subtitle', array(
            'default' => __('Artificial Intelligence Marketing Agency', 'braveschat'),
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control('braves_chat_header_subtitle', array(
            'label' => __('Header Subtitle', 'braveschat'),
            'section' => 'braves_chat_appearance',
            'type' => 'text',
        ));
        
        // Mensaje de bienvenida
        $wp_customize->add_setting('braves_chat_welcome_message', array(
            'default' => __("Hello! I'm the BravesLab assistant, your Artificial Intelligence Marketing Agency. We integrate AI into businesses to multiply results. How can we help you?", 'braveschat'),
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport' => 'postMessage',
        ));
        
        $wp_customize->add_control('braves_chat_welcome_message', array(
            'label' => __('Welcome Message', 'braveschat'),
            'section' => 'braves_chat_appearance',
            'type' => 'textarea',
        ));
        
        // Posición
        $wp_customize->add_setting('braves_chat_position', array(
            'default' => 'bottom-right',
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => array($this, 'sanitize_position'),
        ));
        
        $wp_customize->add_control('braves_chat_position', array(
            'label' => __('Chat Position', 'braveschat'),
            'section' => 'braves_chat_appearance',
            'type' => 'select',
            'choices' => array(
                'bottom-right' => __('Bottom Right', 'braveschat'),
                'bottom-left' => __('Bottom Left', 'braveschat'),
                'center' => __('Center', 'braveschat'),
            ),
        ));
        
        // Modo de visualización
        $wp_customize->add_setting('braves_chat_display_mode', array(
            'default' => 'modal',
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => array($this, 'sanitize_display_mode'),
        ));
        
        $wp_customize->add_control('braves_chat_display_mode', array(
            'label' => __('Display Mode', 'braveschat'),
            'section' => 'braves_chat_appearance',
            'type' => 'select',
            'choices' => array(
                'modal' => __('Modal (Popup window)', 'braveschat'),
                'fullscreen' => __('Full screen', 'braveschat'),
            ),
        ));
        
        // Sección de Comportamiento
        $wp_customize->add_section('braves_chat_behavior', array(
            'title' => __('Behavior', 'braveschat'),
            'panel' => 'braves_chat_panel',
            'priority' => 20,
        ));
        
        // Webhook URL
        $wp_customize->add_setting('braves_chat_webhook_url', array(
            'default' => 'https://flow.braveslab.com/webhook/1427244e-a23c-4184-a536-d02622f36325/chat',
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'esc_url_raw',
        ));
        
        $wp_customize->add_control('braves_chat_webhook_url', array(
            'label' => __('Webhook URL', 'braveschat'),
            'description' => __('N8N webhook URL to process messages.', 'braveschat'),
            'section' => 'braves_chat_behavior',
            'type' => 'url',
        ));
        
        // Habilitar horarios
        $wp_customize->add_setting('braves_chat_availability_enabled', array(
            'default' => false,
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
        ));
        
        $wp_customize->add_control('braves_chat_availability_enabled', array(
            'label' => __('Enable Availability Schedules', 'braveschat'),
            'section' => 'braves_chat_behavior',
            'type' => 'checkbox',
        ));
        
        // Hora de inicio
        $wp_customize->add_setting('braves_chat_availability_start', array(
            'default' => '09:00',
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => array($this, 'sanitize_time'),
        ));
        
        $wp_customize->add_control('braves_chat_availability_start', array(
            'label' => __('Start Time', 'braveschat'),
            'section' => 'braves_chat_behavior',
            'type' => 'text',
            'input_attrs' => array(
                'placeholder' => '09:00',
            ),
        ));
        
        // Hora de fin
        $wp_customize->add_setting('braves_chat_availability_end', array(
            'default' => '18:00',
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => array($this, 'sanitize_time'),
        ));
        
        $wp_customize->add_control('braves_chat_availability_end', array(
            'label' => __('End Time', 'braveschat'),
            'section' => 'braves_chat_behavior',
            'type' => 'text',
            'input_attrs' => array(
                'placeholder' => '18:00',
            ),
        ));
        
        // Mensaje fuera de horario
        $wp_customize->add_setting('braves_chat_availability_message', array(
            'default' => __('Our business hours are from 9:00 to 18:00. Leave us your message and we will get back to you as soon as possible.', 'braveschat'),
            'type' => 'option',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_textarea_field',
        ));
        
        $wp_customize->add_control('braves_chat_availability_message', array(
            'label' => __('Out of Hours Message', 'braveschat'),
            'section' => 'braves_chat_behavior',
            'type' => 'textarea',
        ));
        
        // Registrar preview
        if ($wp_customize->is_preview()) {
            add_action('wp_enqueue_scripts', array($this, 'customizer_preview_script'));
        }
    }
    
    /**
     * Script para vista previa en vivo en el Customizer
     *
     * Actualiza elementos del chat en tiempo real mientras se edita
     *
     * @since 1.0.0
     * @return void
     */
    public function customizer_preview_script() {
        $js = '(function($) {
            wp.customize(\'braves_chat_header_title\', function(value) {
                value.bind(function(newval) {
                    $(\'#chat-header h3\').text(newval);
                });
            });
            wp.customize(\'braves_chat_header_subtitle\', function(value) {
                value.bind(function(newval) {
                    $(\'#chat-header p\').text(newval);
                });
            });
            wp.customize(\'braves_chat_welcome_message\', function(value) {
                value.bind(function(newval) {
                    $(\'#chat-messages .message.bot .message-bubble\').first().text(newval);
                });
            });
        })(jQuery);';
        wp_add_inline_script( 'jquery', $js );
    }
    
    /**
     * Sanitizar campo de posición
     *
     * @since 1.0.0
     * @param string $value Valor a sanitizar
     * @return string Valor sanitizado
     */
    public function sanitize_position($value) {
        $allowed = array('bottom-right', 'bottom-left', 'center');
        return in_array($value, $allowed) ? $value : 'bottom-right';
    }

    /**
     * Sanitizar modo de visualización
     *
     * @since 1.0.0
     * @param string $value Valor a sanitizar
     * @return string Valor sanitizado
     */
    public function sanitize_display_mode($value) {
        $allowed = array('modal', 'fullscreen');
        return in_array($value, $allowed) ? $value : 'modal';
    }

    /**
     * Sanitizar checkbox
     *
     * @since 1.0.0
     * @param mixed $value Valor a sanitizar
     * @return int 1 si está marcado, 0 en caso contrario
     */
    public function sanitize_checkbox($value) {
        return $value == 1 ? 1 : 0;
    }

    /**
     * Sanitizar hora en formato HH:MM
     *
     * @since 1.0.0
     * @param string $value Hora a sanitizar
     * @return string Hora sanitizada
     */
    public function sanitize_time($value) {
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
            return $value;
        }
        return '09:00';
    }
}