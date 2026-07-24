# Correcciones aplicadas — incidente DAVIBANK 2026-07-22

Origen: correo del cliente reportando que el expediente 12-015907-1170-CJ no
guardaba, un campo con etiqueta incorrecta, y el "Saldo Dolarizado" mostrando
demasiados decimales / no coincidiendo con el sistema CCC del banco.

**Nota de nomenclatura:** el banco del cliente se llama "DAVIBANK" en la
tabla `banks` (`bank_id = 1`), pero internamente usa el código que
originalmente se escribió para "Scotiabank" (`Bank::SCOTIABANKCR = 1`, clase
`App\Livewire\Casos\CasoScotiabank`, vistas en
`resources/views/livewire/casos/partials/scotiabank/`, reporte
`App\Exports\CasoScotiabankReport`, ruta `/casos-scotiabank/download/{key}`).
Existe además un banco distinto llamado "Banco Davivienda" (`bank_id = 5`,
clase `CasoDavivienda`) que **no** es el mismo cliente. Cualquiera que toque
este módulo debe confirmar primero el `bank_id` real del caso antes de editar
archivos, para no corregir el banco equivocado.

## 1. Guardado fallaba con campos de dinero vacíos (causa raíz del ticket)

**Síntoma:** `SQLSTATE[22007]: Invalid datetime format: 1366 Incorrect decimal
value: ''` al guardar el caso 482 (expediente 12-015907-1170-CJ).

**Causa:** los ~60 campos de dinero de cada formulario de banco se validan
como `['nullable', 'numeric']`. Cuando el usuario deja el campo vacío,
Livewire envía `''`. La regla `nullable` no convierte `''` en `null` dentro
de los datos validados, así que `$record->update($validatedData)` intenta
guardar `''` en una columna `decimal` de MySQL. Ya existía una solución
equivalente para llaves foráneas (`cleanEmptyForeignKeys()` en
`CasoManager.php`), pero nunca se hizo el equivalente para dinero.

**Fix:** `app/Livewire/Casos/CasoManager.php` — nuevo método
`cleanEmptyNumericFields()` que recorre `$this->rules()`, detecta los campos
con la regla `numeric`, y convierte `''` en `null` antes de validar/guardar.
Se llama junto a `cleanEmptyForeignKeys()` en `store()` y `update()` de los
11 formularios de banco (`CasoBac`, `CasoBancoGeneral`, `CasoCafsa`,
`CasoCartera`, `CasoCoocique`, `CasoCoocique2`, `CasoDavivienda`,
`CasoLafise`, `CasoScotiabank`, `CasoScotiabankBch`, `CasoTerceros`).

Se construye a partir de `rules()` (no con una lista fija) para que quede
sincronizado automáticamente si mañana se agrega o quita un campo numérico
en cualquier banco.

**Verificación:** ver `docs/casos/scripts/regression_all_banks.php` — corre
contra los 11 bancos dentro de una transacción que siempre revierte (no
modifica datos reales). Resultado al aplicar el fix: 10/11 OK (el banco
"Cartera Comprada" no tiene casos de prueba en esta base de datos). También
se verificó puntualmente el caso 482 con `thonorarios_totales` guardando
correctamente y `tgastos_legales` quedando en `null` en vez de tronar.

## 2. Etiqueta "Exonerado de cobro" debía decir "Código de Activación"

**Causa:** copy-paste. El campo `codigo_activacion` (panel "Terminación del
proceso") tenía la etiqueta de otro campo distinto (`nexonerado_cobro`, que
sí existe y vive en el panel de Notificación con esa etiqueta, correcta ahí).

**Fix:** corregido en los 7 bancos que tienen panel de terminación con este
copy-paste: `cafsa`, `coocique`, `coocique2`, `davivienda`, `lafise`,
`scotiabank`, `scotiabank-bch`
(`resources/views/livewire/casos/partials/<banco>/panels/terminacion-caso.blade.php`).

## 3. "Saldo Dolarizado" se mostraba con todos los decimales

**Causa:** en `resources/views/livewire/casos/partials/scotiabank/panels/info-caso.blade.php`
existía un input con formato de dinero (`cleaveLivewire`, 2 decimales) para
`psaldo_dolarizado`, pero estaba comentado (`@php /* ... */ @endphp`) y
reemplazado por un `<input type="text">` plano, sin formato. Por historial
de commits (`d0bfd66`, `84e3024`) este campo se marcó primero como
`disabled` ("el sistema lo genera") y una limpieza posterior de
`placeholder`/`disabled` en todos los formularios lo dejó editable pero sin
restaurar el formato.

**Fix:** se restauró el input con `cleaveLivewire` (2 decimales), quedando
editable (no se reintrodujo `disabled` — ver pendiente abajo).

**Pendiente a decidir con el cliente/negocio:** ¿debe volver a ser de solo
lectura ("el sistema lo genera") ahora que existe `CalcularSaldoDolarizadoJob`
+ cálculo automático por tipo de cambio BCCR? Si sí, hay que decidir también
qué pasa con los casos importados donde el valor ya está mal (ver punto 4).

## 4. Descuadre entre el "Saldo Dolarizado" del reporte y el sistema CCC del banco

**No es un bug de UI, es un problema de datos.** `psaldo_dolarizado` se
guarda como `varchar`, sin ninguna normalización. La importación masiva
(`app/Helpers/ImportColumns.php:150,289,375`) copia el valor de la hoja de
origen tal cual, con la precisión que traiga. El job
`App\Jobs\CalcularSaldoDolarizadoJob` sí calcula y redondea correctamente con
tipo de cambio BCCR, **pero solo corre sobre casos con `tipo_de_cambio`
nulo** — si el caso ya trae un valor (aunque venga mal de la importación),
el job nunca lo toca ni lo corrige.

Esto no se puede "arreglar" solo con código: hay que decidir, caso por caso o
en bloque, si se fuerza un recálculo (`sobreescribir = true` en el job) para
los casos de DAVIBANK, y compararlo contra la fuente real (CCC) antes de
pisar datos.

**Actualización:** `psaldo_dolarizado` (y otras 6 columnas de dinero) ya se
migraron de `varchar` a `decimal(18,2)`, normalizando 6,534 filas con
formato sucio (símbolos de moneda, coma decimal costarricense, separadores
inconsistentes) — ver `docs/casos/04-plan-migracion-campos-numericos.md`.
Esto arregla la *precisión/formato* del dato. La pregunta de fondo de este
punto — si el **valor en sí** coincide con el sistema CCC del banco — sigue
sin resolverse porque requiere comparar contra una fuente externa a la que
no tenemos acceso desde el código.

## Qué no se tocó (fuera de alcance de este incidente)

- El reporte en sí (`App\Exports\CasoScotiabankReport`, usado por
  `downloadDavibank()`) no se modificó — el formato de 2 decimales en Excel
  ya funciona correctamente para columnas `type => 'decimal'`
  (`app/Exports/BaseReport.php::columnFormats()`). El número "con todos los
  decimales" que vio el cliente corresponde al **valor crudo almacenado**,
  no a un problema del exportador.
- No se resolvió la causa de fondo del punto 4 (por qué el valor no coincide
  con CCC) porque requiere comparar contra el sistema del banco, información
  que no está disponible desde el código.
