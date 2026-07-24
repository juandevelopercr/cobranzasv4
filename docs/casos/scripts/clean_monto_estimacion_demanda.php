<?php
// Limpieza de "Monto Estimación Demanda" (pmonto_estimacion_demanda).
// Este campo se quedó fuera de la limpieza original de las 7 columnas de
// dinero (04-plan-migracion-campos-numericos.md) — solo se le corrigió el
// formato del input y se endureció su validación a 'numeric' (porque está
// sincronizado con "Saldo Inicial"), pero nunca se limpiaron los datos que
// ya tenía. Eso bloqueaba el guardado de cualquier caso con un valor viejo
// mal formado en este campo, aunque el usuario no tocara ese campo.
//
// Mismo método que docs/casos/scripts/clean_all_final.php: clasifica cada
// valor sucio, resuelve automáticamente los que tienen un patrón claro
// (símbolos de moneda, separadores de miles/decimales, sufijo/prefijo
// USD/CRC), y aplica solo las excepciones ya revisadas y aprobadas
// explícitamente por el usuario. Si aparece algo nuevo no previsto, aborta
// sin tocar nada (igual que los scripts anteriores).
use Illuminate\Support\Facades\DB;

function isGenuinelyClean(string $v): bool
{
    return (bool) preg_match('/^-?\d+(\.\d+)?$/', $v);
}

function parseMontoEstimacion(string $raw): ?float
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

// Casos sin ningún número real adentro (USD/CRC solos, "No aplica",
// "pendiente", texto suelto) — aprobados para quedar en NULL.
$forceNull = [
    6766, 10432, 10440, 10441, 10442, 10444, 10446, 10449, 10452, 10454, 10456,
    15035, 15037, 15040, 15183, 15186, 15187, 15196, 15198, 15508, 15526,
    16547, 16550, 16551, 16552, 16553, 16554, 16565, 16566, 16567, 16639,
    16643, 16646, 16648, 16649, 16650, 16653, 16654, 16655, 16661, 16663,
    16666, 16667, 16668, 16669,
];

// Casos con formato raro (guion o separadores mezclados), aprobados con la
// interpretación "el último separador es el decimal".
$approvedSpecial = [
    10701 => 5377753.46,  // '5.377,753,46'
    11709 => 4337829.63,  // '4337829-63'
    24307 => 1726738.07,  // '1,726.738,07'
];

$column = 'pmonto_estimacion_demanda';
$rows = DB::table('casos')->whereNotNull($column)->where($column, '!=', '')->pluck($column, 'id');

$updates = [];
$unresolved = [];

foreach ($rows as $id => $v) {
    if (isGenuinelyClean(trim((string) $v))) continue;

    if (in_array($id, $forceNull, true)) {
        $updates[$id] = null;
        continue;
    }
    if (array_key_exists($id, $approvedSpecial)) {
        $updates[$id] = $approvedSpecial[$id];
        continue;
    }

    $parsed = parseMontoEstimacion((string) $v);
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
