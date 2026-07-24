<?php
// Fix de emergencia: pone en NULL cualquier '' REAL que haya quedado en las
// 7 columnas decimal.
//
// OJO: un UPDATE directo tipo `SET col = NULL WHERE col = ''` es peligroso
// en una columna numerica — MySQL convierte '' a 0 para comparar, así que
// ese WHERE agarraría también las filas legítimas en 0.00 y las pondría en
// NULL por error. Por eso aquí se identifican los ids con '' real en PHP
// (con CAST a CHAR, sin coerción) y se actualiza solo esos, uno por uno.
use Illuminate\Support\Facades\DB;

$columns = [
    'psaldo_dolarizado', 'asaldo_capital_operacion', 'asaldo_capital_operacion_usd',
    'agastos_legales', 'amonto_avaluo', 'pmonto_retencion_colones', 'pmonto_retencion_dolares',
];

$total = 0;
foreach ($columns as $col) {
    $rows = DB::select("SELECT id, CAST(`$col` AS CHAR) as v FROM casos WHERE `$col` IS NOT NULL");
    $ids = [];
    foreach ($rows as $r) {
        if ($r->v === '') {
            $ids[] = $r->id;
        }
    }
    if (count($ids) > 0) {
        DB::table('casos')->whereIn('id', $ids)->update([$col => null]);
    }
    echo "$col: " . count($ids) . " filas corregidas\n";
    $total += count($ids);
}
echo "TOTAL: $total\n";
