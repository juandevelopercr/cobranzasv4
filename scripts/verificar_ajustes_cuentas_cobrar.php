<?php

/**
 * Script de Verificación: Discrepancia CuentaPorCobrarManager vs AntiguedadReport
 *
 * Este script busca datos reales en la base de datos y verifica que los ajustes funcionen correctamente
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== VERIFICACIÓN DE AJUSTES: CuentaPorCobrarManager vs AntiguedadReport ===\n\n";

// Buscar un cliente con facturas y distribución de centro de costo
echo "Buscando datos de prueba en la base de datos...\n\n";

$datoPrueba = DB::table('transactions as t')
    ->select([
        't.contact_id',
        'c.name as cliente_nombre',
        'tc.centro_costo_id',
        'cc.descrip as centro_costo_nombre',
        DB::raw('COUNT(DISTINCT t.id) as num_facturas'),
        DB::raw('SUM(t.totalComprobante) as total_facturas')
    ])
    ->join('contacts as c', 't.contact_id', '=', 'c.id')
    ->join('transactions_commissions as tc', 't.id', '=', 'tc.transaction_id')
    ->join('centro_costos as cc', 'tc.centro_costo_id', '=', 'cc.id')
    ->where('t.proforma_status', 'FACTURADA')
    ->whereNotIn('tc.centro_costo_id', [1,12,14,15,16,17])
    ->groupBy('t.contact_id', 'c.name', 'tc.centro_costo_id', 'cc.descrip')
    ->having('num_facturas', '>', 0)
    ->orderBy('num_facturas', 'desc')
    ->first();

if (!$datoPrueba) {
    echo "❌ No se encontraron datos de prueba en la base de datos.\n";
    echo "   Asegúrese de que existan facturas con distribución de centro de costo.\n\n";
    exit(1);
}

$clienteId = $datoPrueba->contact_id;
$centroCostoId = $datoPrueba->centro_costo_id;

echo "✅ Datos de prueba encontrados:\n";
echo "   - Cliente: {$datoPrueba->cliente_nombre} (ID: $clienteId)\n";
echo "   - Centro de Costo: {$datoPrueba->centro_costo_nombre} (ID: $centroCostoId)\n";
echo "   - Número de facturas: {$datoPrueba->num_facturas}\n";
echo "   - Total de facturas: $" . number_format($datoPrueba->total_facturas, 2) . "\n\n";

// 1. Verificar facturas con distribución por centro de costo
echo "--- 1. FACTURAS CON DISTRIBUCIÓN POR CENTRO DE COSTO ---\n\n";

$facturas = DB::table('transactions as t')
    ->select([
        't.id',
        't.consecutivo',
        't.totalComprobante',
        'tc.percent',
        DB::raw('(t.totalComprobante * tc.percent / 100) as monto_centro_costo'),
        DB::raw('(SELECT COALESCE(SUM(tp.total_medio_pago), 0) FROM transactions_payments tp WHERE tp.transaction_id = t.id) as total_pagado'),
        DB::raw('(SELECT COALESCE(SUM(tp.total_medio_pago), 0) * tc.percent / 100 FROM transactions_payments tp WHERE tp.transaction_id = t.id) as pago_proporcional'),
        DB::raw('((t.totalComprobante * tc.percent / 100) - (SELECT COALESCE(SUM(tp.total_medio_pago), 0) * tc.percent / 100 FROM transactions_payments tp WHERE tp.transaction_id = t.id)) as saldo_pendiente')
    ])
    ->leftJoin('transactions_commissions as tc', 't.id', '=', 'tc.transaction_id')
    ->where('t.contact_id', $clienteId)
    ->where('t.proforma_status', 'FACTURADA')
    ->where('tc.centro_costo_id', $centroCostoId)
    ->whereNotIn('tc.centro_costo_id', [1,12,14,15,16,17])
    ->orderBy('t.transaction_date')
    ->limit(10)
    ->get();

printf("%-10s %-25s %-15s %-10s %-15s %-15s %-15s %-15s\n",
    "ID", "Consecutivo", "Total Factura", "Percent", "Monto CC", "Pagado Total", "Pago Prop.", "Saldo Pendiente");
echo str_repeat("-", 140) . "\n";

$totalFacturas = 0;
$totalMontoCc = 0;
$totalPagado = 0;
$totalPagoProp = 0;
$totalSaldo = 0;

foreach ($facturas as $factura) {
    printf("%-10s %-25s %15.2f %9.2f%% %15.2f %15.2f %15.2f %15.2f\n",
        $factura->id,
        $factura->consecutivo ?? 'N/A',
        $factura->totalComprobante,
        $factura->percent,
        $factura->monto_centro_costo,
        $factura->total_pagado,
        $factura->pago_proporcional,
        $factura->saldo_pendiente
    );

    $totalFacturas += $factura->totalComprobante;
    $totalMontoCc += $factura->monto_centro_costo;
    $totalPagado += $factura->total_pagado;
    $totalPagoProp += $factura->pago_proporcional;
    $totalSaldo += $factura->saldo_pendiente;
}

echo str_repeat("-", 140) . "\n";
printf("%-10s %-25s %15.2f %10s %15.2f %15.2f %15.2f %15.2f\n",
    "TOTALES:", "", $totalFacturas, "", $totalMontoCc, $totalPagado, $totalPagoProp, $totalSaldo);
echo "\n";

// 2. Verificar query del modelo Transaction con filtro de centro de costo
echo "--- 2. QUERY DEL MODELO TRANSACTION (con filtro de centro de costo) ---\n\n";

$transactionQuery = DB::table('transactions')
    ->select([
        'transactions.id',
        'transactions.consecutivo',
        'transactions.totalComprobante',
        DB::raw("COALESCE((
            SELECT SUM(tc.percent)
            FROM transactions_commissions tc
            WHERE tc.transaction_id = transactions.id
            AND tc.centro_costo_id = $centroCostoId
        ), 100) as centro_costo_percent"),
        DB::raw("(
            COALESCE(transactions.totalComprobante, 0) *
            COALESCE((
                SELECT SUM(tc.percent)
                FROM transactions_commissions tc
                WHERE tc.transaction_id = transactions.id
                AND tc.centro_costo_id = $centroCostoId
            ), 100) / 100
        ) as totalComprobante_ajustado"),
        DB::raw("(
            COALESCE((
                SELECT SUM(tp.total_medio_pago)
                FROM transactions_payments tp
                WHERE tp.transaction_id = transactions.id
            ), 0) *
            COALESCE((
                SELECT SUM(tc.percent)
                FROM transactions_commissions tc
                WHERE tc.transaction_id = transactions.id
                AND tc.centro_costo_id = $centroCostoId
            ), 100) / 100
        ) as payment"),
        DB::raw("ABS(
            (COALESCE(transactions.totalComprobante, 0) *
             COALESCE((
                 SELECT SUM(tc.percent)
                 FROM transactions_commissions tc
                 WHERE tc.transaction_id = transactions.id
                 AND tc.centro_costo_id = $centroCostoId
             ), 100) / 100) -
            (COALESCE((
                SELECT SUM(tp.total_medio_pago)
                FROM transactions_payments tp
                WHERE tp.transaction_id = transactions.id
            ), 0) *
             COALESCE((
                 SELECT SUM(tc.percent)
                 FROM transactions_commissions tc
                 WHERE tc.transaction_id = transactions.id
                 AND tc.centro_costo_id = $centroCostoId
             ), 100) / 100)
        ) as pending_payment")
    ])
    ->whereExists(function ($q) {
        $q->select(DB::raw(1))
          ->from('transactions_commissions')
          ->whereColumn('transactions_commissions.transaction_id', 'transactions.id')
          ->whereNotIn('transactions_commissions.centro_costo_id', [1,12,14,15,16,17]);
    })
    ->whereExists(function ($subquery) use ($centroCostoId) {
        $subquery->select(DB::raw(1))
          ->from('transactions_commissions as tc')
          ->whereRaw('tc.transaction_id = transactions.id')
          ->where('tc.centro_costo_id', '=', $centroCostoId);
    })
    ->where('transactions.contact_id', $clienteId)
    ->where('transactions.proforma_status', 'FACTURADA')
    ->limit(10)
    ->get();

printf("%-10s %-25s %-15s %-10s %-20s %-15s %-15s\n",
    "ID", "Consecutivo", "Total Original", "Percent", "Total Ajustado", "Pago", "Saldo Pendiente");
echo str_repeat("-", 120) . "\n";

$totalOriginal = 0;
$totalAjustado = 0;
$totalPago = 0;
$totalPendiente = 0;

foreach ($transactionQuery as $trans) {
    printf("%-10s %-25s %15.2f %9.2f%% %20.2f %15.2f %15.2f\n",
        $trans->id,
        $trans->consecutivo ?? 'N/A',
        $trans->totalComprobante,
        $trans->centro_costo_percent,
        $trans->totalComprobante_ajustado,
        $trans->payment,
        $trans->pending_payment
    );

    $totalOriginal += $trans->totalComprobante;
    $totalAjustado += $trans->totalComprobante_ajustado;
    $totalPago += $trans->payment;
    $totalPendiente += $trans->pending_payment;
}

echo str_repeat("-", 120) . "\n";
printf("%-10s %-25s %15.2f %10s %20.2f %15.2f %15.2f\n",
    "TOTALES:", "", $totalOriginal, "", $totalAjustado, $totalPago, $totalPendiente);
echo "\n";

// 3. Comparación de resultados
echo "--- 3. COMPARACIÓN DE RESULTADOS ---\n\n";

$diferenciaSaldo = abs($totalSaldo - $totalPendiente);
$diferenciaTotal = abs($totalMontoCc - $totalAjustado);
$diferenciaPago = abs($totalPagoProp - $totalPago);

echo "Método 1 (Facturas directas):\n";
echo "  - Total ajustado: $" . number_format($totalMontoCc, 2) . "\n";
echo "  - Total pagado: $" . number_format($totalPagoProp, 2) . "\n";
echo "  - Saldo pendiente: $" . number_format($totalSaldo, 2) . "\n\n";

echo "Método 2 (Query Transaction):\n";
echo "  - Total ajustado: $" . number_format($totalAjustado, 2) . "\n";
echo "  - Total pagado: $" . number_format($totalPago, 2) . "\n";
echo "  - Saldo pendiente: $" . number_format($totalPendiente, 2) . "\n\n";

echo "Diferencias:\n";
echo "  - Total ajustado: $" . number_format($diferenciaTotal, 2) . "\n";
echo "  - Total pagado: $" . number_format($diferenciaPago, 2) . "\n";
echo "  - Saldo pendiente: $" . number_format($diferenciaSaldo, 2) . "\n\n";

$tolerancia = 0.01;

if ($diferenciaSaldo < $tolerancia && $diferenciaTotal < $tolerancia && $diferenciaPago < $tolerancia) {
    echo "✅ ÉXITO: Los totales coinciden!\n";
    echo "   Los ajustes se aplicaron correctamente.\n";
} else {
    echo "❌ ADVERTENCIA: Se detectaron diferencias.\n";
    if ($diferenciaSaldo >= $tolerancia) {
        echo "   - Diferencia en saldo pendiente: $" . number_format($diferenciaSaldo, 2) . "\n";
    }
    if ($diferenciaTotal >= $tolerancia) {
        echo "   - Diferencia en total ajustado: $" . number_format($diferenciaTotal, 2) . "\n";
    }
    if ($diferenciaPago >= $tolerancia) {
        echo "   - Diferencia en pagos: $" . number_format($diferenciaPago, 2) . "\n";
    }
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
