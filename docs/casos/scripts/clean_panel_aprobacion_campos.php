<?php
// Limpieza de los campos de dinero del panel "Aprobación" que se quedaron
// como varchar/string sin la limpieza de las 9 columnas anteriores:
// ahonorarios_totales, ahonorarios_totales_usd,
// aestimacion_demanda_en_presentacion(_usd), amonto_incobrable,
// bgastos_proceso, pmonto_estimacion_demanda_colones.
// (amonto_cancelar, liquidacion_intereses_aprobada_crc/usd y
// pmonto_estimacion_demanda_dolares ya estaban limpios, se incluyen en la
// lista por si en producción sí tienen algo sucio que en la copia local no
// apareció.)
use App\Helpers\ImportColumns;
use Illuminate\Support\Facades\DB;

function isGenuinelyClean(string $v): bool
{
    return (bool) preg_match('/^-?\d+(\.\d+)?$/', $v);
}

$columns = [
    'ahonorarios_totales', 'ahonorarios_totales_usd',
    'aestimacion_demanda_en_presentacion', 'aestimacion_demanda_en_presentacion_usd',
    'amonto_cancelar', 'amonto_incobrable', 'bgastos_proceso',
    'liquidacion_intereses_aprobada_crc', 'liquidacion_intereses_aprobada_usd',
    'pmonto_estimacion_demanda_colones', 'pmonto_estimacion_demanda_dolares',
];

// Aprobado explícitamente por el usuario: placeholders de "sin dato" y
// fórmulas de Excel sin calcular, ninguno es un monto real recuperable.
$forceNull = [
    'ahonorarios_totales' => [23451, 23453, 23455, 23457, 23861, 23863, 23865, 23867, 23869, 23871, 23923, 23925],
    'ahonorarios_totales_usd' => [23863, 23865, 23867],
    'amonto_incobrable' => [10610,10611,10612,10613,10614,10619,10626,10630,10631,10632,10633,10634,10635,10636,10637,10638,10639,10640,10641,10642,10643,10647,10652,10653,10654,10655,10656,10657,10658,10659,10660,10661,10662,10663,10664,10665,10666,10667,10668,10669,10670,10671,10672,10673,10674,10675,10676,10677,10678,10679,10680],
    'bgastos_proceso' => [9909, 9910, 9920, 9921, 9926, 9927, 9928],
    'pmonto_estimacion_demanda_colones' => [10959,10960,10963,10964,10965,10966,10967,10968,10969,10970,10971,10973,10974,10975,10976,10977,10978,10979,10980,10981,10982,10983,10984,10985,10986,10987,10988,10989,10991,10992,10993],
];

// --- Fase 1: clasificar TODO primero, sin tocar la base de datos ---
$plan = []; // ['col' => [id => valor_o_null, ...], ...]
$unresolved = [];

foreach ($columns as $col) {
    $rows = DB::table('casos')->whereNotNull($col)->where($col, '!=', '')->pluck($col, 'id');
    foreach ($rows as $id => $v) {
        if (isGenuinelyClean(trim((string) $v))) continue;

        if (in_array($id, $forceNull[$col] ?? [], true)) {
            $plan[$col][$id] = null;
            continue;
        }

        $parsed = ImportColumns::parseMoney((string) $v);
        if ($parsed === null) {
            $unresolved[] = "$col id=$id '$v'";
            continue;
        }
        $plan[$col][$id] = $parsed;
    }
}

$totalPlanned = array_sum(array_map('count', $plan));
echo "Filas a actualizar: $totalPlanned\n";
echo "Filas sin resolver (no se tocan): " . count($unresolved) . "\n";
foreach ($unresolved as $u) {
    echo "   SIN RESOLVER $u\n";
}

if (count($unresolved) > 0) {
    echo "\nABORTADO: hay filas nuevas sin resolver que no estaban previstas. No se aplicó ningún cambio en ninguna columna.\n";
    exit(1);
}

// --- Fase 2: todo resuelto, aplicar en una sola transacción ---
DB::beginTransaction();
try {
    foreach ($plan as $col => $updates) {
        foreach ($updates as $id => $val) {
            DB::table('casos')->where('id', $id)->update([
                $col => $val === null ? null : number_format($val, 2, '.', ''),
            ]);
        }
        echo "$col: " . count($updates) . " filas actualizadas\n";
    }
    DB::commit();
    echo "\nCOMMIT realizado. Total: $totalPlanned filas.\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR, se revirtió todo: " . $e->getMessage() . "\n";
}
