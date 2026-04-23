# Verse Local List Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reemplazar la llamada a la API externa scripture.api.bible por una lista local curada de versículos en NVI, eliminando la dependencia de red y la API key hardcodeada.

**Architecture:** Se modifica únicamente `class_admin_header.php`. El método `get_daily_verse()` pasa a seleccionar directamente de la lista local (día del año % total de versículos), sin HTTP request ni transient. El método `get_fallback_verse()` se elimina porque ya no tiene sentido como fallback.

**Tech Stack:** PHP 7.4+, WordPress (sin dependencias adicionales)

---

## Mapa de archivos

| Archivo | Acción |
|---|---|
| `includes/admin/components/class_admin_header.php` | Modificar: eliminar HTTP imports, reescribir `get_daily_verse()`, eliminar `get_fallback_verse()` |

---

### Task 1: Limpiar imports y reescribir `get_daily_verse()`

**Archivos:**
- Modify: `includes/admin/components/class_admin_header.php`

- [ ] **Step 1: Eliminar los `use` statements que ya no se necesitan**

Los siguientes imports son exclusivos del HTTP call y deben eliminarse del bloque `use` al inicio del archivo:

```php
// ELIMINAR estas líneas:
use function get_transient;
use function set_transient;
use function wp_remote_get;
use function wp_remote_retrieve_response_code;
use function wp_remote_retrieve_body;
use function is_wp_error;
use function json_decode;
use function trim;
```

El bloque `use` resultante queda así:

```php
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
use function gmdate;
use function count;
```

- [ ] **Step 2: Reemplazar `get_daily_verse()` completo**

Reemplaza el método entero (líneas 139–192 aprox.) por este:

```php
/**
 * Versículo diario NVI — seleccionado de lista local por día del año.
 *
 * @return array{ text: string, ref: string }
 */
private static function get_daily_verse() {
    $verses = array(
        array( 'text' => 'Todo lo puedo en Cristo que me fortalece.',                                                   'ref' => 'Fil. 4:13' ),
        array( 'text' => 'El Señor es mi pastor; nada me faltará.',                                                     'ref' => 'Sal. 23:1' ),
        array( 'text' => 'Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito.',                    'ref' => 'Jn. 3:16' ),
        array( 'text' => 'Confía en el Señor con todo tu corazón, y no te apoyes en tu propia prudencia.',              'ref' => 'Prov. 3:5' ),
        array( 'text' => 'El Señor es mi luz y mi salvación; ¿a quién temeré?',                                         'ref' => 'Sal. 27:1' ),
        array( 'text' => 'El Señor es mi fortaleza y mi escudo; en él confía mi corazón.',                              'ref' => 'Sal. 28:7' ),
        array( 'text' => 'No se inquieten por nada; más bien, presenten sus peticiones a Dios.',                        'ref' => 'Fil. 4:6' ),
        array( 'text' => 'Busquen primeramente el reino de Dios y su justicia.',                                        'ref' => 'Mt. 6:33' ),
        array( 'text' => 'La paz de Dios, que sobrepasa todo entendimiento, cuidará sus corazones.',                    'ref' => 'Fil. 4:7' ),
        array( 'text' => 'El Señor peleará por ustedes; solo sean tranquilos.',                                         'ref' => 'Éx. 14:14' ),
        array( 'text' => 'Ya te lo he ordenado: ¡Sé fuerte y valiente! No tengas miedo.',                              'ref' => 'Jos. 1:9' ),
        array( 'text' => 'Torre inexpugnable es el nombre del Señor; a ella corren los justos.',                        'ref' => 'Prov. 18:10' ),
        array( 'text' => 'Encomienda al Señor tu camino; confía en él, y él actuará.',                                  'ref' => 'Sal. 37:5' ),
        array( 'text' => 'Deléitate en el Señor, y él te concederá los deseos de tu corazón.',                         'ref' => 'Sal. 37:4' ),
        array( 'text' => 'Pero los que confían en el Señor renovarán sus fuerzas.',                                     'ref' => 'Is. 40:31' ),
        array( 'text' => 'No temas, porque yo estoy contigo; no te angusties, porque yo soy tu Dios.',                  'ref' => 'Is. 41:10' ),
        array( 'text' => 'Yo soy el camino, la verdad y la vida.',                                                      'ref' => 'Jn. 14:6' ),
        array( 'text' => 'Tu palabra es una lámpara a mis pies; es una luz en mi sendero.',                             'ref' => 'Sal. 119:105' ),
        array( 'text' => 'Porque yo sé los planes que tengo para ustedes, planes para su bienestar.',                   'ref' => 'Jer. 29:11' ),
        array( 'text' => 'Si Dios está de nuestra parte, ¿quién puede estar en contra nuestra?',                        'ref' => 'Rom. 8:31' ),
        array( 'text' => 'Vengan a mí todos ustedes que están cansados y agobiados, y yo les daré descanso.',           'ref' => 'Mt. 11:28' ),
        array( 'text' => 'Dios es nuestro amparo y nuestra fortaleza, nuestra ayuda segura en momentos de angustia.',   'ref' => 'Sal. 46:1' ),
        array( 'text' => 'Clama a mí y te responderé, y te daré a conocer cosas grandes y ocultas.',                   'ref' => 'Jer. 33:3' ),
        array( 'text' => 'En él vivimos, nos movemos y existimos.',                                                     'ref' => 'Hch. 17:28' ),
        array( 'text' => 'El fruto del Espíritu es amor, alegría, paz, paciencia, amabilidad.',                         'ref' => 'Gál. 5:22' ),
        array( 'text' => 'Sabemos que Dios dispone todas las cosas para el bien de quienes lo aman.',                   'ref' => 'Rom. 8:28' ),
        array( 'text' => 'Y conocerán la verdad, y la verdad los hará libres.',                                         'ref' => 'Jn. 8:32' ),
        array( 'text' => 'Jesucristo es el mismo ayer y hoy y por los siglos.',                                         'ref' => 'Heb. 13:8' ),
        array( 'text' => 'El que habita al abrigo del Altísimo se acoge a la sombra del Todopoderoso.',                 'ref' => 'Sal. 91:1' ),
        array( 'text' => 'Yo y mi familia serviremos al Señor.',                                                        'ref' => 'Jos. 24:15' ),
        array( 'text' => 'El amor nunca deja de ser.',                                                                  'ref' => '1 Cor. 13:8' ),
        array( 'text' => 'Gusten y vean que el Señor es bueno.',                                                        'ref' => 'Sal. 34:8' ),
        array( 'text' => 'Echa sobre el Señor tu carga, y él te sustentará.',                                           'ref' => 'Sal. 55:22' ),
        array( 'text' => 'Porque en él fueron creadas todas las cosas.',                                                 'ref' => 'Col. 1:16' ),
        array( 'text' => 'El que comenzó tan buena obra en ustedes la irá perfeccionando.',                             'ref' => 'Fil. 1:6' ),
        array( 'text' => 'Bienaventurados los de limpio corazón, porque ellos verán a Dios.',                           'ref' => 'Mt. 5:8' ),
        array( 'text' => 'Que la gracia del Señor Jesucristo sea con todos.',                                           'ref' => 'Ap. 22:21' ),
        array( 'text' => 'Por fin, hermanos, piensen en todo lo verdadero, todo lo respetable, todo lo justo.',         'ref' => 'Fil. 4:8' ),
        array( 'text' => 'El Señor te bendiga y te guarde.',                                                            'ref' => 'Núm. 6:24' ),
        array( 'text' => 'El Señor está cerca de los quebrantados de corazón.',                                         'ref' => 'Sal. 34:18' ),
        array( 'text' => 'El que mantiene en pie la mente de ti depende, tú guardarás en completa paz.',                'ref' => 'Is. 26:3' ),
        array( 'text' => 'El Señor se manifestará a todos los que lo invocan.',                                         'ref' => 'Sal. 145:18' ),
        array( 'text' => 'Pidan, y se les dará; busquen, y encontrarán; llamen, y se les abrirá.',                      'ref' => 'Mt. 7:7' ),
        array( 'text' => 'No por el poder ni por la fuerza, sino por mi Espíritu, dice el Señor.',                      'ref' => 'Zac. 4:6' ),
        array( 'text' => 'El Señor tu Dios está en medio de ti como guerrero victorioso.',                              'ref' => 'Sof. 3:17' ),
        array( 'text' => 'Cuiden su corazón, porque de él mana la vida.',                                               'ref' => 'Prov. 4:23' ),
        array( 'text' => 'El principio de la sabiduría es el temor del Señor.',                                         'ref' => 'Sal. 111:10' ),
        array( 'text' => 'Así que no teman, porque yo estoy con ustedes.',                                              'ref' => 'Is. 41:10' ),
        array( 'text' => 'Dios bendijo el séptimo día y lo santificó.',                                                  'ref' => 'Gén. 2:3' ),
        array( 'text' => 'Nunca te dejaré; jamás te abandonaré.',                                                        'ref' => 'Heb. 13:5' ),
    );

    return $verses[ (int) gmdate( 'z' ) % count( $verses ) ];
}
```

- [ ] **Step 3: Eliminar el método `get_fallback_verse()` completo**

Eliminar desde la línea del `/** Versículo de respaldo...` hasta el cierre `}` de ese método (líneas 198–233 aprox.). Ya no tiene propósito.

- [ ] **Step 4: Verificar que el renderizado en `render()` sigue funcionando**

En el método `render()`, la línea que llama al versículo comprueba `null !== $verse`. Como ahora `get_daily_verse()` siempre retorna un array (nunca null), el `if` sigue funcionando correctamente — no hay que cambiar nada en `render()`.

- [ ] **Step 5: Commit**

```bash
git add includes/admin/components/class_admin_header.php
git commit -m "refactor: reemplazar API externa de versículos por lista local NVI"
```

---

### Task 2: Limpiar el transient huérfano (opcional pero recomendado)

**Archivos:**
- Modify: `includes/admin/components/class_admin_header.php` — añadir limpieza en activación, O bien usar WP-CLI.

- [ ] **Step 1: Limpiar transients existentes vía WP-CLI**

```bash
wp transient delete --all --path=/ruta/a/wordpress
```

O desde el dashboard de WordPress → Herramientas → Información del sitio → no hay transient manager nativo; usar el comando WP-CLI es suficiente. Este paso es solo limpieza de base de datos, no afecta funcionalidad.

- [ ] **Step 2: Commit (si se añadió limpieza en código)**

Solo aplica si se añadió código de limpieza de transient en el hook de activación. Si solo se usó WP-CLI, no hay nada que commitear.
