<?php

// Exporta a Excel (.xlsx) los casos de Davibank/Davibank-BCH (bank_id 1 y
// 13) que tienen "Saldo Dolarizado" calculado pero NO tienen ni "Saldo
// Inicial" ni "Monto Estimación Demanda" — el valor dolarizado quedó
// huérfano (se calculó a partir de un Saldo Inicial que ya no está en el
// sistema, y no hay forma de recuperarlo: el log de actividad de Caso solo
// registra cambios en estado_id). Se le entrega esta lista al cliente para
// que confirme el Saldo Inicial real de cada caso contra su sistema CCC.
//
// Se genera .xlsx (no .csv) y las columnas de números de caso/operación se
// fuerzan a formato texto — si se dejan como número, Excel/Sheets las
// muestra en notación científica (4.9E+11) por tener 12+ dígitos, y además
// se arriesga a perder ceros a la izquierda.
//
// Uso: php artisan tinker docs/casos/scripts/exportar_casos_saldo_inicial_huerfano.php
// Genera: storage/app/entregas/casos_saldo_inicial_huerfano.xlsx

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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

$headers = ['Banco', 'Numero de caso', 'Nombre del Cliente', 'Tipo de producto', 'Numero de Operacion #1', 'Numero de Operacion #2', 'Numero expediente judicial', 'Saldo Dolarizado (sistema, no verificado)', 'Tipo de cambio usado', 'Saldo Inicial real (a llenar por el banco)'];
$textColumns = ['B', 'E', 'F', 'G']; // Numero de caso, Operacion #1, Operacion #2, Expediente

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Saldo Inicial huerfano');
$sheet->fromArray($headers, null, 'A1');
$sheet->getStyle('A1:J1')->getFont()->setBold(true);

foreach ($textColumns as $col) {
    $sheet->getStyle($col . '2:' . $col . '1000')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
}

$r = 2;
foreach ($rows as $row) {
    $banco = $row->bank_id == 1 ? 'DAVIBANK' : 'DAVIBANK-BCH';
    $sheet->setCellValueExplicit('A' . $r, $banco, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B' . $r, (string) $row->pnumero, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('C' . $r, (string) $row->pnombre_demandado, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('D' . $r, (string) $row->producto, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E' . $r, (string) $row->pnumero_operacion1, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('F' . $r, (string) $row->pnumero_operacion2, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('G' . $r, (string) $row->pnumero_expediente_judicial, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('H' . $r, (float) $row->psaldo_dolarizado);
    $sheet->setCellValue('I' . $r, $row->tipo_de_cambio !== null ? (float) $row->tipo_de_cambio : null);
    $sheet->setCellValue('J' . $r, null);
    $r++;
}

foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$dir = storage_path('app/entregas');
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$path = $dir . '/casos_saldo_inicial_huerfano.xlsx';

$writer = new Xlsx($spreadsheet);
$writer->save($path);

echo "Exportados " . count($rows) . " casos a: $path\n";
echo "Descargarlo con: scp o el gestor de archivos del hosting.\n";
