<?php

use Illuminate\Support\Facades\DB;

function isGenuinelyClean(string $v): bool
{
    return (bool) preg_match('/^-?\d+(\.\d+)?$/', trim($v));
}

function parseMoney(string $raw): ?float
{
    $val = trim(str_replace(['$', '₡', '¢', ' ', "\xc2\xa0"], '', $raw));

    if (strpos($val, '.') !== false && strpos($val, ',') !== false) {
        if (strrpos($val, ',') > strrpos($val, '.')) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } else {
            $val = str_replace(',', '', $val);
        }
    } elseif (strpos($val, ',') !== false) {
        $afterComma = substr($val, strrpos($val, ',') + 1);
        if (strlen($afterComma) <= 2) {
            $val = str_replace(',', '.', $val);
        } else {
            $val = str_replace(',', '', $val);
        }
    }

    if (!preg_match('/^-?\d+(\.\d+)?$/', $val)) {
        return null;
    }

    return is_numeric($val) ? (float) $val : null;
}

function parseWeird(string $raw): ?float
{
    $val = trim(str_replace(['$', '₡', '¢', ' ', "\xc2\xa0"], '', $raw));

    if (preg_match('/^\d+\.$/', $val)) {
        return (float) rtrim($val, '.');
    }

    $commaCount = substr_count($val, ',');
    if ($commaCount >= 2) {
        $lastComma = strrpos($val, ',');
        $decimals = substr($val, $lastComma + 1);
        $intPart = str_replace([',', '.'], '', substr($val, 0, $lastComma));
        return is_numeric($intPart . '.' . $decimals) ? (float) ($intPart . '.' . $decimals) : null;
    }

    // Dos o mas puntos y ninguna coma (ej. '13.251.03', '41014.299.14'):
    // formato CR con typo, todos los separadores quedaron como punto en vez
    // de alternar punto (miles) / coma (decimal). El ultimo punto es el
    // decimal, los anteriores eran de miles.
    $dotCount = substr_count($val, '.');
    if ($commaCount === 0 && $dotCount >= 2) {
        $lastDot = strrpos($val, '.');
        $decimals = substr($val, $lastDot + 1);
        $intPart = str_replace('.', '', substr($val, 0, $lastDot));
        return is_numeric($intPart . '.' . $decimals) ? (float) ($intPart . '.' . $decimals) : null;
    }

    $val2 = str_replace(['.', ' '], '', $val);
    $lastComma = strrpos($val2, ',');
    if ($lastComma !== false) {
        $intPart = str_replace(',', '', substr($val2, 0, $lastComma));
        $decimals = substr($val2, $lastComma + 1);
        return is_numeric($intPart . '.' . $decimals) ? (float) ($intPart . '.' . $decimals) : null;
    }

    return null;
}

// Los 18 casos raros aprobados explícitamente por el usuario.
$approvedWeird = [
    'psaldo_dolarizado' => [10957, 10958, 14724],
    'asaldo_capital_operacion' => [316, 323, 540, 10405, 10826, 10946, 10952, 10953, 10957, 10958, 10998, 14125, 14778, 17842, 24307],
    'asaldo_capital_operacion_usd' => [10405],
    'agastos_legales' => [10338, 10393],
];

// Casos sin patron numerico alguno (texto libre, '-', una fecha): no se
// puede inferir un monto, se ponen en null explicitamente. Aprobado por el
// usuario para 7440, 10961 y 16197 (psaldo_dolarizado).
$forceNull = [
    'psaldo_dolarizado' => [7440, 10961, 16197],
];

$columns = ['psaldo_dolarizado', 'asaldo_capital_operacion', 'asaldo_capital_operacion_usd', 'agastos_legales'];

$updates = [];
$unresolved = [];

foreach ($columns as $col) {
    $rows = DB::table('casos')->whereNotNull($col)->where($col, '!=', '')->pluck($col, 'id');
    foreach ($rows as $id => $v) {
        if (isGenuinelyClean((string) $v)) continue;

        if (in_array($id, $forceNull[$col] ?? [], true)) {
            $updates[] = ['col' => $col, 'id' => $id, 'old' => $v, 'new' => null];
            continue;
        }

        if (in_array($id, $approvedWeird[$col] ?? [], true)) {
            $parsed = parseWeird((string) $v);
        } else {
            $parsed = parseMoney((string) $v);
        }

        if ($parsed === null) {
            $unresolved[] = "$col id=$id '$v'";
            continue;
        }

        $updates[] = ['col' => $col, 'id' => $id, 'old' => $v, 'new' => $parsed];
    }
}

echo "Filas a actualizar: " . count($updates) . "\n";
echo "Filas sin resolver (no se tocan): " . count($unresolved) . "\n";
foreach ($unresolved as $u) {
    echo "   SIN RESOLVER $u\n";
}

if (count($unresolved) > 0) {
    echo "\nABORTADO: hay filas sin resolver que no estaban previstas. No se aplico ningun cambio.\n";
    exit(1);
}

DB::beginTransaction();
try {
    $byCol = [];
    foreach ($updates as $u) {
        DB::table('casos')->where('id', $u['id'])->update([
            $u['col'] => $u['new'] === null ? null : number_format($u['new'], 2, '.', ''),
        ]);
        $byCol[$u['col']] = ($byCol[$u['col']] ?? 0) + 1;
    }
    DB::commit();
    echo "\nCOMMIT realizado.\n";
    foreach ($byCol as $col => $n) {
        echo "  $col: $n filas actualizadas\n";
    }
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR, se revirtio todo: " . $e->getMessage() . "\n";
}
