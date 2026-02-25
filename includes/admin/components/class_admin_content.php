<?php
/**
 * Componente Content del Admin
 *
 * Renderiza el área de contenido con cards estilo Bentō
 *
 * @package BravesChat
 * @version 1.2.0
 */

namespace BravesChat\Admin;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use function defined;
use function wp_parse_args;
use function esc_attr;
use function esc_html;
use function wp_kses;
use function wp_kses_allowed_html;
use function esc_url;
use function wp_kses_post;
use function checked;
use function absint;

class Admin_Content {

    /**
     * Instancia única (Singleton)
     *
     * @var Admin_Content
     */
    private static $instance = null;

    /**
     * Obtener instancia única
     *
     * @return Admin_Content
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        // Inicialización si es necesaria
    }

    /**
     * Renderizar card estilo Bentō
     *
     * @param array $args Argumentos de la card
     * @return void
     */
    public function render_card($args = array()) {
        $defaults = array(
            'title' => '',
            'subtitle' => '',
            'description' => '',
            'content' => '',
            'icon' => '',
            'icon_color' => '#023e8a',
            'action_text' => '',
            'action_url' => '',
            'action_target' => '_self',
            'footer' => '',
            'custom_class' => '',
        );

        $args = wp_parse_args($args, $defaults);
        $custom_class = esc_attr($args['custom_class']);

        echo '<div class="braves-card ' . $custom_class . '">';
        
        if (!empty($args['icon'])) {
            echo '<div class="braves-card__icon">';
            echo $args['icon'];
            echo '</div>';
        }

        if (!empty($args['title'])) {
            echo '<h3 class="braves-card__title">';
            echo esc_html($args['title']);
            echo '</h3>';
        }

        if (!empty($args['subtitle'])) {
            echo '<p class="braves-card__subtitle">';
            echo esc_html($args['subtitle']);
            echo '</p>';
        }

        if (!empty($args['description'])) {
            echo '<p class="braves-card__description">';
            echo esc_html($args['description']);
            echo '</p>';
        }

        if (!empty($args['content'])) {
            echo '<div class="braves-card__content">';
            // Allow form inputs in card content
            $allowed_html = array_merge(
                wp_kses_allowed_html('post'),
                array(
                    'input' => array(
                        'type' => true,
                        'id' => true,
                        'name' => true,
                        'value' => true,
                        'class' => true,
                        'style' => true,
                        'placeholder' => true,
                        'checked' => true,
                        'autocomplete' => true,
                        'rows' => true,
                        'cols' => true,
                    ),
                    'textarea' => array(
                        'id' => true,
                        'name' => true,
                        'class' => true,
                        'style' => true,
                        'placeholder' => true,
                        'rows' => true,
                        'cols' => true,
                    ),
                    'select' => array(
                        'id' => true,
                        'name' => true,
                        'class' => true,
                        'style' => true,
                        'multiple' => true,
                        'size' => true,
                    ),
                    'option' => array(
                        'value' => true,
                        'selected' => true,
                    ),
                    'label' => array(
                        'for' => true,
                        'class' => true,
                        'style' => true,
                    ),
                    'span' => array(
                        'class' => true,
                        'style' => true,
                    ),
                    'p' => array(
                        'class' => true,
                        'style' => true,
                    ),
                    'div' => array(
                        'class' => true,
                        'style' => true,
                        'id' => true,
                    ),
                )
            );
            echo wp_kses($args['content'], $allowed_html);
            echo '</div>';
        }

        if (!empty($args['action_text']) && !empty($args['action_url'])) {
            $action_url = esc_url($args['action_url']);
            $action_target = esc_attr($args['action_target']);
            $action_text = esc_html($args['action_text']);
            
            echo '<div class="braves-card__action">';
            echo '<a href="' . $action_url . '" target="' . $action_target . '">';
            echo $action_text;
            echo '</a>';
            echo '</div>';
        }

        if (!empty($args['footer'])) {
            echo '<div class="braves-card__footer">';
            echo wp_kses_post($args['footer']);
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Renderizar quick action button
     *
     * @param array $args Argumentos del botón
     * @return void
     */
    public function render_quick_action($args = array()) {
        $defaults = array(
            'text' => '',
            'url' => '',
            'icon' => '',
            'style' => 'primary', // primary, secondary, outline
            'target' => '_self',
            'custom_class' => '',
        );

        $args = wp_parse_args($args, $defaults);

        $button_class = 'braves-button braves-button--' . $args['style'];
        if (!empty($args['custom_class'])) {
            $button_class .= ' ' . $args['custom_class'];
        }
        
        $url = esc_url($args['url']);
        $class = esc_attr($button_class);
        $target = esc_attr($args['target']);

        echo '<a href="' . $url . '" class="' . $class . '" target="' . $target . '">';
        
        if (!empty($args['icon'])) {
            echo '<span class="braves-button__icon">';
            echo $args['icon'];
            echo '</span>';
        }
        
        echo '<span class="braves-button__text">';
        echo esc_html($args['text']);
        echo '</span>';
        echo '</a>';
    }

    /**
     * Renderizar sección con header
     *
     * @param array $args Argumentos de la sección
     * @return void
     */
    public function render_section($args = array()) {
        $defaults = array(
            'title' => '',
            'description' => '',
            'content' => '',
            'custom_class' => '',
        );

        $args = wp_parse_args($args, $defaults);
        $custom_class = esc_attr($args['custom_class']);

        echo '<div class="braves-section ' . $custom_class . '">';

        if (!empty($args['title'])) {
            echo '<div class="braves-section__header">';
            echo '<h2 class="braves-section__title">';
            echo esc_html($args['title']);
            echo '</h2>';
            
            if (!empty($args['description'])) {
                echo '<p class="braves-section__description">';
                echo esc_html($args['description']);
                echo '</p>';
            }
            echo '</div>';
        }

        if (!empty($args['content'])) {
            echo '<div class="braves-section__content">';
            echo wp_kses_post($args['content']);
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Renderizar toggle/switch moderno
     *
     * @param array $args Argumentos del toggle
     * @return void
     */
    public function render_toggle($args = array()) {
        $defaults = array(
            'id' => '',
            'name' => '',
            'label' => '',
            'description' => '',
            'checked' => false,
            'value' => '1',
            'custom_class' => '',
        );

        $args = wp_parse_args($args, $defaults);

        $toggle_id = !empty($args['id']) ? $args['id'] : 'toggle-' . uniqid();
        $custom_class = esc_attr($args['custom_class']);
        $id = esc_attr($toggle_id);
        $name = esc_attr($args['name']);
        $value = esc_attr($args['value']);
        $checked = checked($args['checked'], true, false);

        echo '<div class="braves-toggle ' . $custom_class . '">';
        echo '<div class="braves-toggle__control">';
        echo '<input type="checkbox" id="' . $id . '" name="' . $name . '" value="' . $value . '" ' . $checked . ' class="braves-toggle__input" />';
        echo '<label for="' . $id . '" class="braves-toggle__label">';
        echo '<span class="braves-toggle__switch"></span>';
        
        if (!empty($args['label'])) {
            echo '<span class="braves-toggle__text">';
            echo esc_html($args['label']);
            echo '</span>';
        }
        
        echo '</label>';
        echo '</div>';

        if (!empty($args['description'])) {
            echo '<p class="braves-toggle__description">';
            echo esc_html($args['description']);
            echo '</p>';
        }
        
        echo '</div>';
    }

    /**
     * Renderizar grid de cards (layout Bento)
     *
     * @param array $cards Array de configuraciones de cards
     * @param int $columns Número de columnas (2, 3, 4)
     * @return void
     */
    public function render_card_grid($cards = array(), $columns = 3) {
        if (empty($cards)) {
            return;
        }

        $grid_class = 'braves-card-grid braves-card-grid--' . absint($columns) . '-cols';

        echo '<div class="' . esc_attr($grid_class) . '">';
        
        foreach ($cards as $card) {
            $this->render_card($card);
        }
        
        echo '</div>';
    }
}
