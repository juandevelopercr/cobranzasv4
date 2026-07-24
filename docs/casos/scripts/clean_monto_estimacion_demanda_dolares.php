<?php
// Limpieza de "Monto Estimación Demanda Dólares" (pmonto_estimacion_demanda_dolares).
// Mismo caso que pmonto_estimacion_demanda: quedó fuera de la limpieza
// original, validado como 'numeric' pero con datos viejos sin formato.
use Illuminate\Support\Facades\DB;

function isGenuinelyClean(string $v): bool
{
    return (bool) preg_match('/^-?\d+(\.\d+)?$/', $v);
}

function parseMontoEstimacionDolares(string $raw): ?float
{
    $val = trim(str_replace(['$', '₡', '¢', ' ', "\xc2\xa0"], '', $raw));
    $val = preg_replace('/^(USD|CRC)\s*/i', '', $val);
    $val = preg_replace('/\s*(USD|CRC)\.?$/i', '', $val);
    $val = trim($val, ' .');
    if ($val === '') return null;

    $commaCount = substr_count($val, ',');
    $dotCount = substr_count($val, '.');

    if ($commaCount >= 1 && $dotCount >= 1) {
        if (strrpos($val, ',') > strrpos($val, '.')) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } else {
            $val = str_replace(',', '', $val);
        }
    } elseif ($commaCount >= 2) {
        $lastComma = strrpos($val, ',');
        $decimals = substr($val, $lastComma + 1);
        $intPart = str_replace(',', '', substr($val, 0, $lastComma));
        $val = $intPart . '.' . $decimals;
    } elseif ($commaCount === 1) {
        $afterComma = substr($val, strrpos($val, ',') + 1);
        $val = strlen($afterComma) <= 2 ? str_replace(',', '.', $val) : str_replace(',', '', $val);
    } elseif ($dotCount >= 2) {
        $lastDot = strrpos($val, '.');
        $decimals = substr($val, $lastDot + 1);
        $intPart = str_replace('.', '', substr($val, 0, $lastDot));
        $val = $intPart . '.' . $decimals;
    } elseif (preg_match('/^\d+\.$/', $val)) {
        $val = rtrim($val, '.');
    }

    if (!preg_match('/^-?\d+(\.\d+)?$/', $val)) return null;
    return is_numeric($val) ? (float) $val : null;
}

// '-' (sin dato) — mismo patrón ya aprobado varias veces antes.
$forceNull = [10961, 10962];

$column = 'pmonto_estimacion_demanda_dolares';
$rows = DB::table('casos')->whereNotNull($column)->where($column, '!=', '')->pluck($column, 'id');

$updates = [];
$unresolved = [];

foreach ($rows as $id => $v) {
    if (isGenuinelyClean(trim((string) $v))) continue;

    if (in_array($id, $forceNull, true)) {
        $updates[$id] = null;
        continue;
    }

    $parsed = parseMontoEstimacionDolares((string) $v);
    if ($parsed === null) {
        $unresolved[] = "id=$id '$v'";
        continue;
    }
    $updates[$id] = $parsed;
}

echo "Filas a actualizar: " . count($updates) . "\n";
echo "Filas sin resolver (no se tocan): " . count($unresolved) . "\n";
foreach ($unresolved as $u) {
    echo "   SIN RESOLVER $u\n";
}

if (count($unresolved) > 0) {
    echo "\nABORTADO: hay filas nuevas sin resolver que no estaban previstas. No se aplicó ningún cambio.\n";
    exit(1);
}

DB::beginTransaction();
try {
    foreach ($updates as $id => $val) {
        DB::table('casos')->where('id', $id)->update([
            $column => $val === null ? null : number_format($val, 2, '.', ''),
        ]);
    }
    DB::commit();
    echo "\nCOMMIT realizado. " . count($updates) . " filas actualizadas en $column.\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR, se revirtió todo: " . $e->getMessage() . "\n";
}
