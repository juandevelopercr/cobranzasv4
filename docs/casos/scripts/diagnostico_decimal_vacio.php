<?php
// Diagnostico de emergencia: busca filas donde alguna de las 7 columnas ya
// migradas a decimal(18,2) tenga un valor '' (string vacio) en vez de NULL.
//
// OJO: comparar `columna = ''` directo en SQL sobre una columna numerica es
// una trampa — MySQL convierte '' a 0 para la comparacion, así que
// `WHERE psaldo_dolarizado = ''` en realidad encuentra las filas en 0.00,
// no las que tienen '' de verdad. Por eso aquí se trae el valor crudo (como
// texto, con CAST a CHAR) y se compara en PHP, evitando la coerción.
use Illuminate\Support\Facades\DB;

$columns = [
    'psaldo_dolarizado', 'asaldo_capital_operacion', 'asaldo_capital_operacion_usd',
    'agastos_legales', 'amonto_avaluo', 'pmonto_retencion_colones', 'pmonto_retencion_dolares',
];

$total = 0;
foreach ($columns as $col) {
    $rows = DB::select("SELECT id, CAST(`$col` AS CHAR) as v FROM casos WHERE `$col` IS NOT NULL");
    $bad = [];
    foreach ($rows as $r) {
        if ($r->v === '') {
            $bad[] = $r->id;
        }
    }
    echo "$col: " . count($bad) . " filas con '' real" . (count($bad) ? (" -> ids: " . implode(',', array_slice($bad, 0, 30)) . (count($bad) > 30 ? '...' : '')) : '') . "\n";
    $total += count($bad);
}
echo "TOTAL: $total\n";
