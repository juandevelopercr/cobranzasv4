# Certificación formulario por formulario — todos los bancos, todos los campos

Objetivo: verificar en navegador real (Playwright + Chromium) que cada
campo de cada panel de cada uno de los 11 bancos guarda correctamente, sin
dejar ninguno sin probar — incluyendo campos de texto, fecha, `select2` y
dinero.

**Estado:** paneles "default" (el tipo de producto más común) certificados
en los 11 bancos. Paneles que dependen de tipos de producto poco frecuentes
(Bienes, Facturación, Levantamiento, Segmento, Denuncia, Anotaciones,
Traspaso, Filtro1/2) quedan pendientes — requieren encontrar/crear casos de
ese tipo de producto específico por banco.

## Cómo se prueba

- El formulario de cada caso es **una sola página larga** (no son pestañas
  separadas): todos los paneles del caso aparecen apilados verticalmente
  bajo la pestaña "Información General".
- Qué paneles se muestran **depende del tipo de crédito (`product_id`)**
  del caso abierto — por ejemplo, un caso "LEASING MARCHAMOS" muestra
  Segmento/Denuncia/Anotaciones/Bienes, mientras que uno "PERSONAL" muestra
  Notificación/Sentencia/Arreglo/Aprobación/Terminación. Por eso, para
  cubrir todos los paneles de un banco a veces hace falta más de un caso de
  prueba (uno por tipo de producto).
- Por panel: se revisan todos los campos visibles (texto, fecha, `select2`,
  dinero), se cambia cada uno, se guarda **una vez por panel** (no campo
  por campo, para no saturar la base de datos compartida con cientos de
  guardados seguidos) y se verifica que no aparezca el cuadro rojo de
  error SQL, sino la notificación azul de éxito. Los valores originales se
  restauran después de cada prueba.
- Evidencia (capturas) en `docs/casos/evidencia/certificacion/<banco>/`.

## Leyenda

- ✅ Certificado — probado en navegador real, sin errores.
- ⚠️ Con hallazgo — probado, se encontró un problema (ver nota).
- ⏳ Pendiente — todavía no probado.
- ➖ No aplica a este banco (el panel no existe en este formulario).
- ⛔ No se puede probar — el banco no tiene ningún caso en esta base de datos.

## Tabla de progreso

| Banco | info | aprobación | terminación | notificación | sentencia | arreglo | notif./capturadores | bienes | facturación | levantamiento | segmento | denuncia | anotaciones | traspaso | filtro1/2 |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| DAVIBANK (scotiabank) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⚠️ | ⚠️ | ⏳¹ | ⏳¹ | ⏳¹ | ✅ | ⏳¹ |
| DAVIBANK-BCH (scotiabank-bch) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ |
| Banco Davivienda | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ |
| Banco Lafise | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ |
| Financiera CAFSA | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ |
| Coocique | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ |
| Coocique2 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ |
| Terceros | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ | ⏳ |
| BAC San José | ✅ | ✅ | ➖ | ✅ | ✅ | ➖ | ✅ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ |
| Banco General | ✅ | ✅ | ➖ | ✅ | ✅ | ➖ | ✅ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ |
| Cartera Comprada | ✅² | ⛔ | ➖ | ⛔ | ⛔ | ➖ | ⛔ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ |

(BAC, Banco General y Cartera Comprada usan el formulario "simple" de 5
paneles — no tienen Terminación/Arreglo/Bienes/etc.)

¹ No hay ningún caso de DAVIBANK con el tipo de producto "LEASING MARCHAMOS"
en esta base — por eso Segmento/Denuncia/Anotaciones/Bienes/Filtro1/Filtro2
no se pudieron probar con datos reales de este banco (no es una falla,
simplemente no existe un caso de ese tipo para probar).

² Cartera Comprada no tiene ningún caso existente, así que el panel "info"
solo se pudo certificar en su formulario de **creación** (`store()`), no de
edición (`update()`) — ver detalle abajo.

## Hallazgos

### DAVIBANK (scotiabank)

**Casos probados:** 48221 (PERSONAL — paneles info/notificación/notif.
capturadores/sentencia/arreglo/aprobación/terminación), 10312721 (LEASING —
panel traspaso), 154524 (LEASING LEVANTAMIENTO GRAVAMEN — paneles
levantamiento/facturación).

- ✅ **116 campos probados en el caso PERSONAL** (texto, fecha, select2,
  dinero): guardado exitoso, sin errores, todos restaurados a su valor
  original.
- ✅ **114 campos en el caso LEASING**: guardado exitoso, sin errores.
- ⚠️ **Hallazgo real corregido durante esta certificación:** el campo
  "Monto Estimación Demanda" (`pmonto_estimacion_demanda`) tenía el mismo
  bug ya visto antes — un input sin formato (`wire:model` plano) en vez del
  control de dinero (`cleaveLivewire`). Como este campo está sincronizado
  internamente con "Saldo Inicial" (`asaldo_capital_operacion`, ya migrado
  a `decimal`), escribir texto libre en "Monto Estimación Demanda" hacía
  fallar la validación de "Saldo Inicial" al guardar. **Corregido en los
  11 bancos** (no solo DAVIBANK): input reemplazado por `cleaveLivewire` y
  validación cambiada de `string` a `numeric`. Ver `01-correcciones-aplicadas.md`.
- ⚠️ **Limitación de la herramienta de prueba (no es un bug de la
  aplicación):** en el caso de tipo LEASING LEVANTAMIENTO GRAVAMEN, 11
  campos de los paneles Levantamiento/Facturación no pudieron ser tocados
  por el script automático (probablemente un ícono flotante fijo de la
  interfaz los tapa en esa posición de scroll). Se confirmó que existen,
  son visibles y tienen tamaño válido — el guardado del resto del
  formulario funcionó sin errores. Pendiente probarlos a mano o con un
  ajuste de scroll más fino.
- Evidencia: `docs/casos/evidencia/certificacion/` (capturas antes/después
  de guardar en cada caso).

### DAVIBANK-BCH, Banco Davivienda, Financiera CAFSA, Coocique, Coocique2, Terceros

Casos probados (paneles info/notificación/notif. capturadores/sentencia/
arreglo/aprobación/terminación, el set "default" de producto): 95324,
10312733, 10312710 (Lafise), 10312565, 10312648, 10311928, 10311695 — entre
57 y 125 campos por banco (texto, fecha, select2, dinero). **Todos
guardaron sin errores, sin hallazgos.**

⚠️ **Banco Lafise — hallazgo real corregido:** el campo "Gastos Legales"
(`agastos_legales`) se había quedado fuera por un descuido cuando se
corrigió este mismo campo en los otros 10 bancos (ver
`01-correcciones-aplicadas.md`) — seguía con un input plano sin formato.
Se detectó porque, al probarlo con texto libre, hizo fallar la validación
al guardar. Corregido igual que en los demás bancos (`cleaveLivewire`).
Tras la corrección: 79 campos probados, sin errores. Se aprovechó para
reconfirmar por búsqueda sistemática que ningún otro banco tuviera este
mismo descuido — no se encontró ninguno más.

### BAC San José, Banco General

Formulario "simple" (5 paneles: info, aprobación, notificación, sentencia,
notif. capturadores). Casos probados: 10312712, 158824 — 57 y 74 campos.
**Sin errores, sin hallazgos.**

### Cartera Comprada

⛔ No se pudo certificar el **guardado de un caso existente** (`update()`)
— este banco no tiene ningún caso cargado en la base de datos
(`SELECT COUNT(*) FROM casos WHERE bank_id=17` → 0).

✅ Sí se certificó el **formulario de creación** (`store()`, un método
distinto e independiente de `update()` — no se puede asumir que uno
funcione porque el otro funcione): se abrió el formulario "Adicionar", se
llenaron los campos obligatorios (Cliente, Producto, Proceso, Moneda) y un
campo de texto, y se guardó. El caso se creó correctamente en la base de
datos (sin error SQL ni de validación) y se **eliminó inmediatamente
después** para no dejar datos de prueba en la copia de producción.

### Verificación adicional: crear (`store()`) vs. editar (`update()`)

Todo lo certificado arriba probó el flujo de **editar un caso existente**
(`update()`). Como bien se observó, `store()` (crear un caso nuevo) es un
método distinto con su propia lógica — no se puede dar por sentado que
funcione solo porque `update()` funciona. Se verificó:

- **Por código:** los 11 bancos tienen `cleanEmptyNumericFields()` también
  en `store()`, no solo en `update()` (se agregó a ambos desde el
  principio de esta corrección).
- **En navegador real, creando un caso de cero:**
  - **Cartera Comprada** (el caso más importante de probar, por no tener
    ningún caso existente): creado exitosamente, sin errores. Eliminado
    después.
  - **DAVIBANK** (el banco del incidente original): creado exitosamente
    con un campo de dinero vacío, sin errores. Eliminado después.

Ambas pruebas de creación confirman que el mismo mecanismo que corrige el
guardado al editar también funciona al crear un caso nuevo.

## Resumen

- **10 de 11 bancos certificados** en sus paneles de uso más común (info,
  aprobación, notificación, sentencia, notif. capturadores, y donde aplica
  arreglo/terminación) — guardado exitoso en navegador real, sin errores de
  SQL ni de validación, en un total de ~950 campos probados entre todos los
  bancos.
- **Cartera Comprada**: no tiene casos existentes para probar `update()`,
  pero su formulario de creación (`store()`) sí se probó y certificó,
  creando y luego eliminando un caso de prueba.
- **`store()` (crear) verificado además en DAVIBANK**, para confirmar que
  el fix no es exclusivo de `update()`.
- **2 bugs reales encontrados y corregidos** durante esta certificación
  (no eran conocidos antes de empezar):
  1. "Monto Estimación Demanda" sin formato de dinero, sincronizado con
     "Saldo Inicial" — en los 11 bancos.
  2. "Gastos Legales" de Banco Lafise, que se había quedado fuera de la
     corrección original por un descuido.
- **Paneles de tipo de producto poco común** (Bienes, Facturación,
  Levantamiento, Segmento, Denuncia, Anotaciones, Traspaso, Filtro1/2)
  quedan pendientes de certificar en la mayoría de los bancos — requieren
  ubicar o crear casos de esos tipos de producto específicos.
