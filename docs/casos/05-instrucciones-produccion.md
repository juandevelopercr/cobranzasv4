# Instrucciones para aplicar todo esto en el servidor de producción

Checklist en orden. No saltarse pasos — en particular, no correr la
migración de esquema (paso 4) sin haber confirmado el paso 3 (limpieza de
datos) primero, porque la migración exige que las 7 columnas ya estén
limpias y aborta si no lo están.

## 0. Respaldo

```bash
mysqldump -u<usuario> -p<password> <base_de_datos> > backup-antes-de-migracion-$(date +%Y%m%d).sql
```

No continuar sin esto. Es una migración de esquema + una limpieza de miles
de filas de dinero; si algo sale mal, este respaldo es la forma de volver
atrás sin depender de `down()` de la migración.

## 1. Desplegar el código

```bash
git pull
composer install --no-dev --optimize-autoloader   # si aplica en su flujo normal
```

## 2. Auditar el estado actual de las 7 columnas

```bash
php artisan tinker docs/casos/scripts/auditar_full_correcto.php
```

Esto va a mostrar cuántas filas "sucias" hay en cada una de las 7 columnas
**en este momento, en producción** — casi seguro va a ser un número
distinto al que vimos en la copia de prueba (0 columnas migradas + más
casos nuevos desde que se tomó la copia). Eso es normal y esperado.

## 3. Limpiar los datos sucios

```bash
php artisan tinker docs/casos/scripts/clean_all_final.php
```

Dos resultados posibles:

- **`Filas sin resolver (no se tocan): 0`** seguido de `COMMIT realizado` →
  perfecto, continuar al paso 4.
- **`Filas sin resolver: N` con una lista de `SIN RESOLVER col id=X 'valor'`** →
  el script **no aplicó ningún cambio** (aborta todo si encuentra algo que
  no reconoce, como pasó con el caso id=540 durante las pruebas). En ese
  caso: copiar esa lista y decidir cómo interpretar cada valor (igual que
  se hizo con los 19 casos ya documentados en
  `04-plan-migracion-campos-numericos.md`) antes de reintentar. No adivinar
  un monto sin revisarlo.

Volver a correr `auditar_full_correcto.php` después: las 7 columnas deben
quedar en `dirty=0`.

## 4. Correr la migración de esquema

```bash
php artisan migrate --force
```

Debe decir `DONE` para
`2026_07_23_003055_change_money_columns_to_decimal_in_casos_table`. Si
aborta con "Migración abortada: la columna casos.X tiene un valor no
numérico" → significa que el paso 3 no dejó esa columna limpia (revisar el
id que indica el error, no reintentar la migración hasta resolverlo).

Si aparece el error de MySQL `1366 Incorrect DECIMAL value: '0' for column
'' at row -1` — no debería pasar porque la migración ya usa el método
seguro (ver `04-plan-migracion-campos-numericos.md`), pero si pasa es la
señal de que el servidor de producción tiene alguna otra particularidad de
esquema no vista en la copia de prueba; no forzar, avisar antes de
reintentar.

## 5. Verificar

```bash
php artisan migrate:status | grep change_money
# debe decir "Ran"

php artisan tinker --execute="
foreach (['agastos_legales','amonto_avaluo','asaldo_capital_operacion','asaldo_capital_operacion_usd','pmonto_retencion_colones','pmonto_retencion_dolares','psaldo_dolarizado'] as \$c) {
  echo \$c.': '.\Illuminate\Support\Facades\Schema::getColumnType('casos', \$c).PHP_EOL;
}
"
# las 7 deben decir 'decimal'
```

## 6. Limpiar caché de la aplicación

```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

## 7. Prueba manual rápida (2 minutos)

1. Entrar como usuario real a Casos → Davibank.
2. Abrir el expediente 12-015907-1170-CJ (el del correo original).
3. Confirmar que el panel "Terminación del proceso" dice **"Código de
   Activación"** (no "Exonerado de Cobro").
4. Confirmar que "Saldo Dolarizado" se ve con 2 decimales, no con una fila
   larga de decimales.
5. Borrar el campo "Honorarios Totales", dar Guardar, confirmar que **no**
   aparece el cuadro rojo de error SQL — debe aparecer la notificación azul
   "Caso actualizado correctamente". Volver a poner el valor original y
   guardar de nuevo.

Si los 5 puntos anteriores pasan, el despliegue está completo.
