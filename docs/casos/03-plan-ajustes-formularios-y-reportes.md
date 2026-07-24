# Plan de ajustes — formularios y reportes por banco

Este documento junta lo que ya se sabe que está mal (confirmado con
evidencia), lo que se sospecha por el mismo patrón de copy-paste, y una
guía de cómo auditar el resto sin tener que releer cada archivo a mano.

## Por qué se repiten los mismos bugs en varios bancos

Los 11 formularios de banco (`app/Livewire/Casos/Caso*.php` +
`resources/views/livewire/casos/partials/<banco>/`) **no comparten una
plantilla común**: cada banco es un archivo/carpeta duplicada de los demás.
Cuando se corrige algo en un banco, casi nunca se replica a los otros 10 —
por eso el cliente ve que "cada corrección genera un problema en otro
lado": no es que una corrección rompa algo, es que el mismo bug vive
copiado 7, 8 u 11 veces y solo se arregla donde se reportó.

La única lógica realmente compartida es `App\Livewire\Casos\CasoManager`
(de la que heredan los 11) y `App\Exports\BaseReport` (de la que heredan
todos los reportes). Cualquier fix que se pueda subir a esas dos clases
base, en vez de repetirlo en cada banco, es preferible.

## 1. Confirmado y corregido en esta revisión

| Bug | Alcance | Estado |
|---|---|---|
| Guardado truena con campo de dinero vacío (`Incorrect decimal value: ''`) | 11 bancos (comparten `CasoManager`) | ✅ Corregido — `cleanEmptyNumericFields()` |
| Label "Exonerado de cobro" en vez de "Código de Activación" (panel Terminación) | cafsa, coocique, coocique2, davivienda, lafise, scotiabank, scotiabank-bch | ✅ Corregido en los 7 |
| Input de "Saldo Dolarizado" sin formato (decimales sin límite) | scotiabank (DAVIBANK) | ✅ Corregido |

## 2. Confirmado, pendiente de decisión de negocio (no es un fix de código simple)

| Tema | Detalle |
|---|---|
| `psaldo_dolarizado` mal formado en datos importados | Ver `docs/casos/01-correcciones-aplicadas.md` punto 4. Requiere decidir si se recalcula en bloque con `CalcularSaldoDolarizadoJob(sobreescribir: true)` y comparar contra el CCC del banco antes de pisar valores. |
| ¿"Saldo Dolarizado" debe ser de solo lectura? | Antes lo era (`disabled`, "el sistema lo genera"), un cleanup global de `placeholder`/`disabled` (commit `84e3024`) lo dejó editable sin querer. Decidir con el cliente. |

## 3. Inconsistencia real encontrada en reportes (mismo campo, tratado distinto)

`psaldo_dolarizado` se declara con tipo distinto según el reporte, lo que
cambia si Excel lo redondea/formatea a 2 decimales o lo deja crudo:

| Reporte | Tipo declarado | Efecto |
|---|---|---|
| `CasoScotiabankReport` (DAVIBANK) | `decimal` | ✅ Excel formatea a 2 decimales |
| `CasoScotiabankBchReport` | `decimal` | ✅ Excel formatea a 2 decimales |
| `CasoCarteraCompradaReport` | `decimal` | ✅ Excel formatea a 2 decimales |
| `CasoLafiseActivoReport` / `Incobrable` / `Terminado` | `string` | ⚠️ Sin formato, se ve crudo igual que el bug reportado por el cliente |
| Reportes de Davivienda (`CasoDavivienda*Report`) | no incluyen esta columna | — |

Recomendación: correr la misma búsqueda en los ~35 archivos de
`app/Exports/*.php` para todo campo con nombre de dinero (`saldo`, `monto`,
`honorario`, `gasto`, `comision`, `retencion`, `prima`, `avaluo`,
`liquidacion`) y confirmar que estén como `'type' => 'decimal'` (o
`'currency'`) y no `'string'`. Es una revisión mecánica, no requiere
entender cada reporte a fondo:

```bash
grep -n "'field' => '.*\(saldo\|monto\|honorario\|gasto\|comision\|retenc\|prima\|avaluo\|liquidacion\).*'.*'type' => 'string'" app/Exports/*.php
```

## 4. Cómo auditar un banco que no se ha revisado todavía

1. Abrir `terminacion-caso.blade.php` de ese banco (si existe) y comparar
   el `<label>` de `codigo_activacion` contra el de `aprobacion-caso.blade.php`
   del mismo banco — si difieren, es el mismo bug de label.
2. Correr el checklist de campos de dinero (💰) del archivo correspondiente
   en `docs/casos/bancos/<banco>.md` — vaciar y guardar cada uno.
3. Revisar los reportes de ese banco listados abajo con el grep de la
   sección 3.

## 5. Reportes por banco (para revisión)

| Banco | Reportes (`app/Exports/`) |
|---|---|
| DAVIBANK (scotiabank) | `CasoScotiabankReport` |
| DAVIBANK-BCH | `CasoScotiabankBchReport` |
| Banco Davivienda | `CasoDaviviendaFileMasterReport`, `CasoDaviviendaMatrizReport`, `CasoDaviviendaPagoCEReport`, `CasoDaviviendaPagoTCReport` |
| BAC San José | `CasoBacGestionadaReport`, `CasoBacTerminadaReport` |
| Banco General | `CasoBancoGeneralReport` |
| Financiera CAFSA | `CasoCafsaActivoReport`, `CasoCafsaIncobrableReport`, `CasoCafsaTerminadoReport` |
| Cartera Comprada | `CasoCarteraCompradaReport` |
| Coocique | `CasoCoociqueActivoReport`, `CasoCoociqueIncobrableReport`, `CasoCoociquePagoReport`, `CasoCoociquePrescritoReport`, `CasoCoociqueTerminadoReport` |
| Coocique2 | `CasoCoocique2ActivoReport`, `CasoCoocique2IncobrableReport`, `CasoCoocique2PagoReport`, `CasoCoocique2PrescritoReport`, `CasoCoocique2TerminadoReport` |
| Banco Lafise | `CasoLafiseActivoReport`, `CasoLafiseIncobrableReport`, `CasoLafiseTerminadoReport` |
| Terceros | `CasoTerceroActivoReport`, `CasoTerceroIncobrableReport`, `CasoTerceroPagoReport`, `CasoTerceroPrescritoReport`, `CasoTerceroTerminadoReport` |

## 6. Recomendación de proceso (respuesta a "¿cómo evitamos que esto siga pasando?")

El cliente preguntó explícitamente si existe algún control de calidad para
que una corrección no rompa otra cosa. Antes de este incidente no lo había
en este módulo. Propuesta mínima, barata de mantener:

1. **Regresión automática obligatoria antes de tocar `CasoManager.php` o
   `BaseReport.php`**: correr
   `docs/casos/scripts/regression_all_banks.php` — cubre los 11 bancos en
   segundos, sin navegador, sin tocar datos reales (todo corre en
   transacción que revierte).
2. **Cuando se corrija un bug en un banco, buscar el mismo patrón en los
   otros 10 antes de cerrar el ticket** — como se hizo hoy con el label de
   "Código de Activación" (se buscó y corrigió en los 7 bancos con el mismo
   copy-paste, no solo en el reportado).
3. **Mediano plazo**: extraer a `CasoManager` la lógica que hoy está
   duplicada en los 11 `store()`/`update()` (son casi idénticos) para que
   una corrección se aplique una sola vez. Es un refactor más grande, no se
   hizo en esta revisión por el tiempo que toma y el riesgo de tocar los 11
   formularios a la vez.
