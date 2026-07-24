# Migración de campos de dinero — de texto a `decimal`

## Estado: 7 columnas ya migradas (2026-07-23)

Completado y verificado contra una copia real de producción
(`C:\4BARCODE\cobranza_sistema.sql`, restaurada el mismo día):

| Columna | Filas con dato sucio encontradas (tabla completa, no muestra) | Resultado |
|---|---|---|
| `psaldo_dolarizado` | 1,564 | ✅ Normalizadas y migradas a `decimal(18,2)` |
| `asaldo_capital_operacion` | 4,918 | ✅ Normalizadas y migradas a `decimal(18,2)` |
| `asaldo_capital_operacion_usd` | 31 | ✅ Normalizadas y migradas a `decimal(18,2)` |
| `agastos_legales` | 21 | ✅ Normalizadas y migradas a `decimal(18,2)` |
| `amonto_avaluo` | 0 | ✅ Migrada directo (ya estaba limpia) |
| `pmonto_retencion_colones` | 0 | ✅ Migrada directo (ya estaba limpia) |
| `pmonto_retencion_dolares` | 0 | ✅ Migrada directo (ya estaba limpia) |

**Total: 6,534 filas normalizadas**, 0 filas sin resolver. Migración de
esquema (`database/migrations/2026_07_23_003055_change_money_columns_to_decimal_in_casos_table.php`)
corrida y confirmada (`php artisan migrate:status` → Ran). Conteos y sumas
verificados iguales antes/después en cada columna (ver
`docs/casos/scripts/regression_all_banks.php` y prueba manual en
`docs/casos/evidencia/`).

### Por qué la primera auditoría (500 filas de muestra) se quedó corta

La primera pasada (`docs/casos/scripts/auditar_full_correcto.php` primera
versión) solo miraba una muestra de 500 filas por columna y tenía además un
bug: consideraba "ya limpio" cualquier valor donde quitar comas y espacios
diera un número válido — por ejemplo `21555,63` (coma decimal
costarricense) se leía como `2155563` (mal, pero pasaba el check). Al
corregir el criterio y auditar la **tabla completa** (9,000+ filas por
columna, no 500), aparecieron 6,494 filas sucias reales, no 47 como se
pensó al principio. El criterio correcto quedó en
`docs/casos/scripts/auditar_full_correcto.php`.

### Los 19 casos que no se pudieron normalizar automáticamente

Revisados y aprobados uno por uno con el usuario antes de tocar nada
(no se adivinó ningún monto):

- **3 sin ningún patrón numérico** → puestos en `null`: id=7440 (texto libre
  "Caso devuelto a BAC..."), id=10961 (`'-'`), id=16197 (una fecha
  `23/11/2023` puesta por error en el campo de dinero).
- **16 con doble separador ambiguo** (ej. `12,100,00`, `13.251.03`,
  `1521206.`) → interpretados con la regla "el último separador es el
  decimal, los anteriores eran de miles mal tipeados", consistente con el
  resto de los datos de este banco. Detalle línea por línea en
  `docs/casos/scripts/clean_all_final.php`.

### El bug de MySQL que casi detiene todo esto

La tabla `casos` tiene **307 columnas**. Un `ALTER TABLE ... MODIFY` que
cambia el *tipo* de una columna (varchar → decimal, no un simple ajuste de
longitud) dispara en MySQL 8.3 un error interno al reconstruir la tabla:

```
SQLSTATE[HY000]: 1366 Incorrect DECIMAL value: '0' for column '' at row -1
```

Confirmado que:
- `MODIFY` sin cambiar el tipo (varchar→varchar) funciona bien en esta tabla.
- `ADD COLUMN` / `DROP COLUMN` / `RENAME COLUMN` funcionan bien.
- El error aparece specifically al cambiar el *tipo* de una columna
  existente, probablemente por el límite interno de MySQL al recalcular el
  tamaño de fila en una tabla tan ancha.

**Solución usada** (ver la migración): en vez de `Schema::table()->decimal()->change()`,
por cada columna: `ADD COLUMN {col}_migrating decimal(18,2)` → backfill con
`UPDATE ... SET {col}_migrating = CAST({col} AS DECIMAL(18,2))` → verificar
que el conteo de filas con valor coincide antes/después → `DROP COLUMN {col}`
→ `RENAME COLUMN {col}_migrating TO {col}`. Cada paso individual ya estaba
confirmado que funciona en esta tabla.

También apareció, aparte, que `fecha_creación` tiene un default
`'0000-00-00'` (preexistente, no relacionado) que el `sql_mode
NO_ZERO_DATE` de este servidor rechaza en cualquier `ALTER TABLE` sobre
`casos` — se resuelve relajando el `sql_mode` solo para la sesión de la
migración (no se tocó esa columna).

## Columnas de dinero que quedan pendientes (fuera de esta tanda)

Estas también son `varchar` con nombre de campo monetario, pero con más
datos genuinamente sucios (no solo formato, sino texto residual real) —
requieren una revisión de Fase 1 (limpieza manual) más profunda antes de
migrar, así que se dejaron fuera a propósito de esta tanda:

| Columna | No-numéricos (muestra de 500) |
|---|---|
| `amonto_incobrable` | 62 de 162 |
| `bgastos_proceso` | 17 de 83 |
| `pmonto_estimacion_demanda_colones` | 32 de 500 |
| `ahonorarios_totales` | 2 de 500 |
| `ahonorarios_totales_usd` | 3 de 173 |
| `aestimacion_demanda_en_presentacion` | 1 de 500 |
| `pmonto_estimacion_demanda_dolares` | 2 de 500 (ya validada como `numeric` en los formularios, aunque la columna sigue en `varchar`) |

**Nota:** `aretenciones_con_giro` parecía monetario por el nombre pero NO lo
es — son valores `'Sí'`/`'No'`. Ya descartado del plan.

Para migrar estas: repetir el mismo proceso (auditar tabla completa con
`auditar_full_correcto.php` agregando la columna a la lista, clasificar con
la misma heurística de `clean_all_final.php`, aprobar los casos ambiguos
uno por uno con el usuario, limpiar, y usar el mismo patrón
ADD+backfill+DROP+RENAME de la migración ya escrita — no `->change()`
directo, por el bug de la tabla ancha).

## Qué hay que ejecutar en el servidor de producción real

Ver `docs/casos/05-instrucciones-produccion.md`.
