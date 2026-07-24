<?php

use Illuminate\Support\Facades\DB;

// Una columna esta "genuinamente limpia" solo si YA es un decimal simple:
// digitos, opcional un signo -, opcional UN punto decimal. Nada de comas,
// espacios (ni siquiera al inicio/final) ni simbolos. Sin trim(): el guard
// de la migracion usa REGEXP en SQL, que tampoco perdona espacios sobrantes
// (ver caso id=13879 con espacio final que este check con trim() dejaba
// pasar por error).
function isGenuinelyClean(string $v): bool
{
    return (bool) preg_match('/^-?\d+(\.\d+)?$/', $v);
}

$columns = [
    'psaldo_dolarizado',
    'asaldo_capital_operacion',
    'asaldo_capital_operacion_usd',
    'agastos_legales',
    'amonto_avaluo',
    'pmonto_retencion_colones',
    'pmonto_retencion_dolares',
];

foreach ($columns as $col) {
    $rows = DB::table('casos')->whereNotNull($col)->where($col, '!=', '')->pluck($col, 'id');
    $total = $rows->count();
    $dirty = 0;
    $examples = [];
    foreach ($rows as $id => $v) {
        if (!isGenuinelyClean((string) $v)) {
            $dirty++;
            if (count($examples) < 5) {
                $examples[] = "id={$id} '{$v}'";
            }
        }
    }
    printf("%-32s total=%-6d dirty=%d\n", $col, $total, $dirty);
    foreach ($examples as $e) {
        echo "   $e\n";
    }
}
