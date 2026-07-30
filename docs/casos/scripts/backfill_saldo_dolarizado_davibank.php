<?php
// Backfill de Davibank (bank_id 1 = "DAVIBANK", bank_id 13 = "DAVIBANK-BCH").
// El cliente reportó casos donde Saldo Inicial (asaldo_capital_operacion) y
// Monto Estimación Demanda (pmonto_estimacion_demanda) no coincidían -uno
// quedaba en blanco- y casos con Saldo Dolarizado (psaldo_dolarizado) sin
// calcular. El formulario ya sincroniza estos campos hacia adelante; este
// script corrige, una sola vez, los casos que ya existían con datos sucios.
//
// Fase 1: si de los dos campos (saldo inicial / estimación) solo uno tiene
// valor, se copia al que está en blanco. Antes de tocar nada, revisa que no
// existan filas con AMBOS campos poblados pero con valores distintos (un
// conflicto real) — si aparece alguna, aborta sin cambiar nada, igual que
// los demás scripts de limpieza de este directorio.
//
// Fase 2: recalcula Saldo Dolarizado para los casos que tienen Saldo Inicial
// pero no tienen Saldo Dolarizado. Reutiliza CalcularSaldoDolarizadoJob (el
// mismo que usa el botón "recalcular masivo") para los que no tienen tipo de
// cambio guardado, y para los pocos que sí tienen tipo de cambio pero nunca
// se recalcularon, aplica la misma fórmula sin volver a llamar al BCCR.

use App\Jobs\CalcularSaldoDolarizadoJob;
use App\Models\Currency;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

$bankIds = [1, 13];

function normalizarMonto(string $raw): ?float
{
    $val = trim(str_replace([' ', "\xc2\xa0"], '', $raw));
    if (strpos($val, '.') !== false && strpos($val, ',') !== false) {
        if (strrpos($val, ',') > strrpos($val, '.')) {
            $val = str_replace(',', '.', str_replace('.', '', $val));
        } else {
            $val = str_replace(',', '', $val);
        }
    } elseif (strpos($val, ',') !== false) {
        $afterComma = substr($val, strrpos($val, ',') + 1);
        $val = strlen($afterComma) <= 2 ? str_replace(',', '.', $val) : str_replace(',', '', $val);
    }
    return is_numeric($val) ? (float) $val : null;
}

echo "=== Fase 1: sincronizar Saldo Inicial <-> Monto Estimación Demanda ===\n";

$ambosPoblados = DB::table('casos')
    ->whereIn('bank_id', $bankIds)
    ->whereNotNull('asaldo_capital_operacion')
    ->whereNotNull('pmonto_estimacion_demanda')
    ->where('pmonto_estimacion_demanda', '!=', '')
    ->get(['id', 'asaldo_capital_operacion', 'pmonto_estimacion_demanda']);

$conflictos = [];
foreach ($ambosPoblados as $row) {
    $estimacion = normalizarMonto((string) $row->pmonto_estimacion_demanda);
    if ($estimacion === null || round($estimacion, 2) !== round((float) $row->asaldo_capital_operacion, 2)) {
        $conflictos[] = "id={$row->id} saldo_inicial={$row->asaldo_capital_operacion} estimacion={$row->pmonto_estimacion_demanda}";
    }
}

if (count($conflictos) > 0) {
    echo "ABORTADO: hay " . count($conflictos) . " caso(s) con Saldo Inicial y Estimación distintos. Requieren revisión manual, no se aplicó ningún cambio:\n";
    foreach ($conflictos as $c) {
        echo "   CONFLICTO $c\n";
    }
    exit(1);
}
echo "Sin conflictos: los " . count($ambosPoblados) . " casos con ambos campos poblados coinciden.\n";

$soloUno = DB::table('casos')
    ->whereIn('bank_id', $bankIds)
    ->where(function ($q) {
        $q->where(function ($q1) {
            $q1->whereNotNull('asaldo_capital_operacion')
               ->where(function ($q2) {
                   $q2->whereNull('pmonto_estimacion_demanda')->orWhere('pmonto_estimacion_demanda', '');
               });
        })->orWhere(function ($q1) {
            $q1->whereNull('asaldo_capital_operacion')
               ->whereNotNull('pmonto_estimacion_demanda')
               ->where('pmonto_estimacion_demanda', '!=', '');
        });
    })
    ->get(['id', 'asaldo_capital_operacion', 'pmonto_estimacion_demanda']);

echo "Casos con un solo campo poblado a corregir: " . count($soloUno) . "\n";

DB::beginTransaction();
try {
    foreach ($soloUno as $row) {
        if ($row->asaldo_capital_operacion !== null) {
            DB::table('casos')->where('id', $row->id)->update([
                'pmonto_estimacion_demanda' => number_format((float) $row->asaldo_capital_operacion, 2, '.', ''),
            ]);
        } else {
            $valor = normalizarMonto((string) $row->pmonto_estimacion_demanda);
            if ($valor !== null) {
                DB::table('casos')->where('id', $row->id)->update([
                    'asaldo_capital_operacion' => number_format($valor, 2, '.', ''),
                ]);
            } else {
                echo "   AVISO: id={$row->id} tiene Estimación '{$row->pmonto_estimacion_demanda}' que no se pudo interpretar como número, se dejó igual.\n";
            }
        }
    }
    DB::commit();
    echo "COMMIT Fase 1 realizado.\n\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR en Fase 1, se revirtió todo: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== Fase 2: recalcular Saldo Dolarizado faltante ===\n";

foreach ($bankIds as $bankId) {
    echo "Recalculando (tipo de cambio faltante) bank_id=$bankId...\n";
    Bus::dispatchSync(new CalcularSaldoDolarizadoJob($bankId, false));
}

$conTipoCambioSinDolarizar = DB::table('casos')
    ->whereIn('bank_id', $bankIds)
    ->whereNotNull('asaldo_capital_operacion')
    ->whereNotNull('tipo_de_cambio')
    ->whereNull('psaldo_dolarizado')
    ->get(['id', 'asaldo_capital_operacion', 'tipo_de_cambio', 'currency_id']);

echo "Casos con tipo de cambio pero sin Saldo Dolarizado calculado: " . count($conTipoCambioSinDolarizar) . "\n";

foreach ($conTipoCambioSinDolarizar as $row) {
    $saldo = (float) $row->asaldo_capital_operacion;
    $tasa = (float) $row->tipo_de_cambio;
    if ($saldo <= 0 || $tasa <= 0) {
        continue;
    }
    $currency = Currency::find($row->currency_id);
    $dolarizado = ($currency && strtoupper($currency->code) === 'USD')
        ? $saldo
        : round($saldo / $tasa, 2);

    DB::table('casos')->where('id', $row->id)->update([
        'psaldo_dolarizado' => number_format($dolarizado, 2, '.', ''),
    ]);
}

echo "\nListo. Vuelva a correr las consultas de diagnóstico para confirmar que los conteos quedaron en cero.\n";
