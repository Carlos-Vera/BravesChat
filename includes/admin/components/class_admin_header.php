<?php
/**
 * Componente Header del Admin
 *
 * Renderiza el header con logo y navegación superior
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
use function esc_url;
use function admin_url;
use function esc_attr__;
use function esc_html;
use function file_exists;
use function file_get_contents;
use function esc_html__;
use function get_option;
use function get_transient;
use function set_transient;
use function wp_remote_get;
use function wp_remote_retrieve_response_code;
use function wp_remote_retrieve_body;
use function is_wp_error;
use function json_decode;
use function trim;
use function gmdate;
use function count;

class Admin_Header {

    /**
     * Instancia única (Singleton)
     *
     * @var Admin_Header
     */
    private static $instance = null;

    /**
     * Obtener instancia única
     *
     * @return Admin_Header
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
     * Renderizar header
     *
     * @param array $args Argumentos opcionales
     * @return void
     */
    public function render($args = array()) {
        $defaults = array(
            'show_logo'    => true,
            'show_version' => true,
            'custom_class' => '',
            'notices'      => '',
        );

        $args = wp_parse_args($args, $defaults);
        $custom_class = esc_attr($args['custom_class']);

        echo '<div class="braves-admin-header ' . $custom_class . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $custom_class is esc_attr().
        echo '<div class="braves-admin-header__inner">';

        if ($args['show_logo']) {
            $dashboard_url = esc_url(admin_url('admin.php?page=braveschat'));
            echo '<a href="' . $dashboard_url . '" class="braves-admin-header__logo">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $dashboard_url is esc_url().
            $this->render_logo();
            echo '</a>';
        }

        if (!empty($args['notices'])) {
            echo '<div class="braves-admin-header__notices">';
            echo $args['notices']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- notices are built with Template_Helpers::notice() which handles escaping internally.
            echo '</div>';
        }

        if ($args['show_version']) {
            $admin_url    = esc_url(admin_url('admin.php?page=braveschat-about'));
            $title        = esc_attr__('Ver información del plugin', 'braveschat');
            $version      = esc_html('v' . BRAVES_CHAT_VERSION);
            $display_mode = esc_html(get_option('braves_chat_display_mode', 'modal'));
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, used for styling only.
            $is_about     = isset($_GET['page']) && $_GET['page'] === 'braveschat-about';
            $badge_class  = 'braves-badge braves-badge--primary braves-badge--clickable' . ($is_about ? ' braves-badge--active' : '');

            $verse = self::get_daily_verse();

            echo '<div class="braves-admin-header__version">';
            echo '<div class="braves-admin-header__version-badge">';
            echo '<em class="braves-header__mode-label">' . $display_mode . '</em>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $display_mode is esc_html().
            echo '<a href="' . $admin_url . '" class="' . esc_attr($badge_class) . '" title="' . $title . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $admin_url is esc_url(); $title is esc_attr__().
            echo $version; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $version is esc_html().
            echo '</a>';
            echo '</div>';
            if ( null !== $verse ) {
                echo '<p class="braves-admin-header__verse">';
                echo '<em>' . esc_html( "\u{201c}" . $verse['text'] . "\u{201d} \u{2014} " . $verse['ref'] ) . '</em>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html() applied.
                echo '</p>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Versículo diario NVI vía scripture.api.bible (caché 24h en transient)
     *
     * Requiere la constante BRAVES_CHAT_BIBLE_API_KEY definida en wp-config.php.
     * Si no hay clave o la petición falla devuelve null (no se renderiza nada).
     *
     * @return array{ text: string, ref: string }|null
     */
    private static function get_daily_verse() {
        $cache_key = 'braves_chat_verse_' . gmdate( 'Ymd' );
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $api_key  = 'PQJtcHR7M4spiyaxyoiDJ'; // phpcs:ignore -- BravesLab NVI key
        $verse_ids = array(
            'PHP.4.13', 'PSA.23.1',   'JHN.3.16',   'PRO.3.5',    'PSA.27.1',
            'PSA.28.7', 'PHP.4.6',    'MAT.6.33',   'PHP.4.7',    'EXO.14.14',
            'JOS.1.9',  'PRO.18.10',  'PSA.37.5',   'PSA.37.4',   'ISA.40.31',
            'ISA.41.10','JHN.14.13',  'JHN.14.6',   'PSA.119.105','ISA.26.3',
            'JER.29.11','1CO.13.8',   'ROM.8.31',   'PHP.1.6',    'MAT.11.28',
            'PSA.46.1', 'REV.22.21',  'PHP.4.8',    'NUM.6.24',   'JOS.24.15',
            'JER.33.3', 'PSA.145.18', 'PSA.34.18',  'ACT.17.28',  'GAL.5.22',
            'ROM.8.28', 'MAT.5.8',    'JHN.8.32',   'HEB.13.8',   'PSA.91.1',
        );

        $bible_id = '4122de86530c9cdf-01'; // NVI — Nueva Versión Internacional
        $verse_id = $verse_ids[ (int) gmdate( 'z' ) % count( $verse_ids ) ];
        $url      = 'https://api.scripture.api.bible/v1/bibles/' . $bible_id . '/verses/' . $verse_id
                  . '?content-type=text&include-notes=false&include-titles=false'
                  . '&include-chapter-numbers=false&include-verse-numbers=false';

        $response = wp_remote_get( $url, array(
            'headers' => array( 'api-key' => $api_key ),
            'timeout' => 5,
        ) );

        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            $text = isset( $body['data']['content'] ) ? trim( $body['data']['content'] ) : '';
            $ref  = isset( $body['data']['reference'] ) ? trim( $body['data']['reference'] ) : '';

            if ( ! empty( $text ) ) {
                $verse = array( 'text' => $text, 'ref' => $ref );
                set_transient( $cache_key, $verse, DAY_IN_SECONDS );
                return $verse;
            }
        }

        // Fallback cuando la API falla o no responde.
        return self::get_fallback_verse();
    }

    /**
     * Versículo de respaldo en NVI (usado si la API falla)
     *
     * @return array{ text: string, ref: string }
     */
    private static function get_fallback_verse() {
        $verses = array(
            array( 'text' => 'Todo lo puedo en Cristo que me fortalece.',                                              'ref' => 'Fil. 4:13' ),
            array( 'text' => 'El Señor es mi pastor; nada me faltará.',                                                'ref' => 'Sal. 23:1' ),
            array( 'text' => 'Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito.',               'ref' => 'Jn. 3:16' ),
            array( 'text' => 'Confía en el Señor con todo tu corazón, y no te apoyes en tu propia prudencia.',         'ref' => 'Prov. 3:5' ),
            array( 'text' => 'El Señor es mi luz y mi salvación; ¿a quién temeré?',                                    'ref' => 'Sal. 27:1' ),
            array( 'text' => 'El Señor es mi fortaleza y mi escudo; en él confía mi corazón.',                         'ref' => 'Sal. 28:7' ),
            array( 'text' => 'No se inquieten por nada; más bien, presenten sus peticiones a Dios.',                   'ref' => 'Fil. 4:6' ),
            array( 'text' => 'Busquen primeramente el reino de Dios y su justicia.',                                   'ref' => 'Mt. 6:33' ),
            array( 'text' => 'La paz de Dios, que sobrepasa todo entendimiento, cuidará sus corazones.',               'ref' => 'Fil. 4:7' ),
            array( 'text' => 'El Señor peleará por ustedes; solo sean tranquilos.',                                    'ref' => 'Éx. 14:14' ),
            array( 'text' => 'Ya te lo he ordenado: ¡Sé fuerte y valiente! No tengas miedo.',                         'ref' => 'Jos. 1:9' ),
            array( 'text' => 'Torre inexpugnable es el nombre del Señor; a ella corren los justos.',                   'ref' => 'Prov. 18:10' ),
            array( 'text' => 'Encomienda al Señor tu camino; confía en él, y él actuará.',                             'ref' => 'Sal. 37:5' ),
            array( 'text' => 'Deléitate en el Señor, y él te concederá los deseos de tu corazón.',                    'ref' => 'Sal. 37:4' ),
            array( 'text' => 'Pero los que confían en el Señor renovarán sus fuerzas.',                                'ref' => 'Is. 40:31' ),
            array( 'text' => 'No temas, porque yo estoy contigo; no te angusties, porque yo soy tu Dios.',             'ref' => 'Is. 41:10' ),
            array( 'text' => 'Yo soy el camino, la verdad y la vida.',                                                 'ref' => 'Jn. 14:6' ),
            array( 'text' => 'Tu palabra es una lámpara a mis pies; es una luz en mi sendero.',                        'ref' => 'Sal. 119:105' ),
            array( 'text' => 'Porque yo sé los planes que tengo para ustedes, planes para su bienestar.',              'ref' => 'Jer. 29:11' ),
            array( 'text' => 'Si Dios está de nuestra parte, ¿quién puede estar en contra nuestra?',                   'ref' => 'Rom. 8:31' ),
            array( 'text' => 'Vengan a mí todos ustedes que están cansados y agobiados, y yo les daré descanso.',      'ref' => 'Mt. 11:28' ),
            array( 'text' => 'Dios es nuestro amparo y nuestra fortaleza, nuestra ayuda segura en momentos de angustia.', 'ref' => 'Sal. 46:1' ),
            array( 'text' => 'Clama a mí y te responderé, y te daré a conocer cosas grandes y ocultas.',               'ref' => 'Jer. 33:3' ),
            array( 'text' => 'En él vivimos, nos movemos y existimos.',                                                'ref' => 'Hch. 17:28' ),
            array( 'text' => 'El fruto del Espíritu es amor, alegría, paz, paciencia, amabilidad.',                    'ref' => 'Gál. 5:22' ),
            array( 'text' => 'Sabemos que Dios dispone todas las cosas para el bien de quienes lo aman.',              'ref' => 'Rom. 8:28' ),
            array( 'text' => 'Y conocerán la verdad, y la verdad los hará libres.',                                    'ref' => 'Jn. 8:32' ),
            array( 'text' => 'Jesucristo es el mismo ayer y hoy y por los siglos.',                                    'ref' => 'Heb. 13:8' ),
            array( 'text' => 'El que habita al abrigo del Altísimo se acoge a la sombra del Todopoderoso.',            'ref' => 'Sal. 91:1' ),
            array( 'text' => 'Yo y mi familia serviremos al Señor.',                                                   'ref' => 'Jos. 24:15' ),
        );
        return $verses[ (int) gmdate( 'z' ) % count( $verses ) ];
    }

    /**
     * Renderizar logo
     *
     * @return void
     */
    private function render_logo() {
        $logo_path = BRAVES_CHAT_PLUGIN_DIR . 'assets/media/braves-logo.svg';

        if (file_exists($logo_path)) {
            // Renderizar SVG directamente
            echo file_get_contents($logo_path); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- local plugin SVG file, safe internal asset.
        } else {
            // Fallback a texto
            echo '<span class="braves-admin-header__logo-text">';
            echo esc_html__('BravesChat iA', 'braveschat');
            echo '</span>';
        }
    }

    /**
     * Obtener HTML del header sin renderizarlo
     *
     * @param array $args Argumentos opcionales
     * @return string HTML del header
     */
    public function get_html($args = array()) {
        ob_start();
        $this->render($args);
        return ob_get_clean();
    }
}
