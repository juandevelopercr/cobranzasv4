<?php

// Exporta a CSV los casos de Davibank/Davibank-BCH (bank_id 1 y 13) que
// tienen "Saldo Dolarizado" calculado pero NO tienen ni "Saldo Inicial" ni
// "Monto Estimación Demanda" — el valor dolarizado quedó huérfano (se
// calculó a partir de un Saldo Inicial que ya no está en el sistema, y no
// hay forma de recuperarlo: el log de actividad de Caso solo registra
// cambios en estado_id). Se le entrega esta lista al cliente para que
// confirme el Saldo Inicial real de cada caso contra su sistema CCC.
//
// Uso: php artisan tinker docs/casos/scripts/exportar_casos_saldo_inicial_huerfano.php
// Genera: storage/app/casos_saldo_inicial_huerfano.csv

use Illuminate\Support\Facades\DB;

$rows = DB::table('casos')
    ->leftJoin('casos_productos', 'casos.product_id', '=', 'casos_productos.id')
    ->whereIn('casos.bank_id', [1, 13])
    ->whereNull('casos.asaldo_capital_operacion')
    ->whereNull('casos.pmonto_estimacion_demanda')
    ->whereNotNull('casos.psaldo_dolarizado')
    ->orderBy('casos.bank_id')
    ->orderBy('casos.pnumero')
    ->get([
        'casos.pnumero',
        'casos.pnombre_demandado',
        'casos_productos.nombre as producto',
        'casos.pnumero_operacion1',
        'casos.pnumero_operacion2',
        'casos.pnumero_expediente_judicial',
        'casos.bank_id',
        'casos.currency_id',
        'casos.psaldo_dolarizado',
        'casos.tipo_de_cambio',
    ]);

$path = storage_path('app/casos_saldo_inicial_huerfano.csv');
$fh = fopen($path, 'w');
fputcsv($fh, ['Banco', 'Numero de caso', 'Nombre del Cliente', 'Tipo de producto', 'Numero de Operacion #1', 'Numero de Operacion #2', 'Numero expediente judicial', 'Saldo Dolarizado (sistema, no verificado)', 'Tipo de cambio usado', 'Saldo Inicial real (a llenar por el banco)']);

foreach ($rows as $row) {
    $banco = $row->bank_id == 1 ? 'DAVIBANK' : 'DAVIBANK-BCH';
    fputcsv($fh, [
        $banco,
        $row->pnumero,
        $row->pnombre_demandado,
        $row->producto,
        $row->pnumero_operacion1,
        $row->pnumero_operacion2,
        $row->pnumero_expediente_judicial,
        $row->psaldo_dolarizado,
        $row->tipo_de_cambio,
        '',
    ]);
}
fclose($fh);

echo "Exportados " . count($rows) . " casos a: $path\n";
echo "Descargarlo con: scp o el gestor de archivos del hosting.\n";
