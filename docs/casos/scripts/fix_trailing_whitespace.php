<?php
// Corrige valores con espacios sobrantes (inicio/final) en las 7 columnas de
// dinero que la migracion va a convertir a decimal. El check de "ya esta
// limpio" en clean_all_final.php usa trim() y por eso no los toca, pero el
// guard de la migracion usa REGEXP en SQL, que si los rechaza.
// Ver docs/casos/04-plan-migracion-campos-numericos.md (caso id=13879).
use Illuminate\Support\Facades\DB;

$columns = [
    'psaldo_dolarizado', 'asaldo_capital_operacion', 'asaldo_capital_operacion_usd',
    'agastos_legales', 'amonto_avaluo', 'pmonto_retencion_colones', 'pmonto_retencion_dolares',
];

$totalFixed = 0;
foreach ($columns as $col) {
    $rows = DB::table('casos')->whereNotNull($col)->where($col, '!=', '')->pluck($col, 'id');
    foreach ($rows as $id => $v) {
        $trimmed = trim((string) $v);
        if ($trimmed !== (string) $v && preg_match('/^-?\d+(\.\d+)?$/', $trimmed)) {
            DB::table('casos')->where('id', $id)->update([$col => $trimmed]);
            printf("%-32s id=%-6d '%s' -> '%s'\n", $col, $id, $v, $trimmed);
            $totalFixed++;
        }
    }
}
echo "TOTAL corregidas: $totalFixed\n";
