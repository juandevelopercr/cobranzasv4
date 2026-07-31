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
    ->whereIn('bank_id', [1, 13])
    ->whereNull('asaldo_capital_operacion')
    ->whereNull('pmonto_estimacion_demanda')
    ->whereNotNull('psaldo_dolarizado')
    ->orderBy('bank_id')
    ->orderBy('pnumero')
    ->get(['pnumero', 'pnombre_demandado', 'pnumero_expediente_judicial', 'bank_id', 'currency_id', 'psaldo_dolarizado', 'tipo_de_cambio']);

$path = storage_path('app/casos_saldo_inicial_huerfano.csv');
$fh = fopen($path, 'w');
fputcsv($fh, ['Banco', 'Numero de caso', 'Nombre del Cliente', 'Numero expediente judicial', 'Saldo Dolarizado (sistema, no verificado)', 'Tipo de cambio usado', 'Saldo Inicial real (a llenar por el banco)']);

foreach ($rows as $row) {
    $banco = $row->bank_id == 1 ? 'DAVIBANK' : 'DAVIBANK-BCH';
    fputcsv($fh, [
        $banco,
        $row->pnumero,
        $row->pnombre_demandado,
        $row->pnumero_expediente_judicial,
        $row->psaldo_dolarizado,
        $row->tipo_de_cambio,
        '',
    ]);
}
fclose($fh);

echo "Exportados " . count($rows) . " casos a: $path\n";
echo "Descargarlo con: scp o el gestor de archivos del hosting.\n";
