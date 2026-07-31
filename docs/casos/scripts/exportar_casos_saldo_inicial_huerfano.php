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
// fuerzan a formula de texto ="..." — el formato de columna a Texto (@) no
// basta, Google Sheets lo ignora al importar un .xlsx en columnas que
// parecen puramente numéricas (12+ dígitos) y las muestra en notación
// científica. La fórmula ="valor" sí es respetada tanto por Excel como por
// Google Sheets.
//
// Se consulta con DB::table() (no Eloquent), lo que incluye casos con
// borrado suave (soft delete) — a propósito, para no ocultarle al cliente
// que existen, pero se marcan con la columna "Estado" porque esos casos NO
// aparecen en la búsqueda normal del sistema (Eloquent los excluye por
// defecto). Sin esta columna el cliente busca un caso eliminado, no lo
// encuentra, y parece que todo el listado está mal.
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
    ->orderByRaw('casos.deleted_at IS NOT NULL') // activos primero, eliminados al final
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
        'casos.deleted_at',
    ]);

$headers = ['Banco', 'Numero de caso', 'Estado', 'Nombre del Cliente', 'Tipo de producto', 'Numero de Operacion #1', 'Numero de Operacion #2', 'Numero expediente judicial', 'Saldo Dolarizado (sistema, no verificado)', 'Tipo de cambio usado', 'Saldo Inicial real (a llenar por el banco)'];
$textColumns = ['B', 'F', 'G', 'H']; // Numero de caso, Operacion #1, Operacion #2, Expediente

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Saldo Inicial huerfano');
$sheet->fromArray($headers, null, 'A1');
$sheet->getStyle('A1:K1')->getFont()->setBold(true);

foreach ($textColumns as $col) {
    $sheet->getStyle($col . '2:' . $col . '1000')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
}

// Forzar como formula de texto ="..." ademas del formato @: ver nota arriba.
$asText = function (string $col, int $row, $value) use ($sheet) {
    $value = (string) $value;
    if ($value === '') {
        return;
    }
    $sheet->setCellValue($col . $row, '="' . str_replace('"', '""', $value) . '"');
};

$r = 2;
$eliminadosCount = 0;
foreach ($rows as $row) {
    $banco = $row->bank_id == 1 ? 'DAVIBANK' : 'DAVIBANK-BCH';
    $eliminado = $row->deleted_at !== null;
    if ($eliminado) {
        $eliminadosCount++;
    }

    $sheet->setCellValueExplicit('A' . $r, $banco, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $asText('B', $r, $row->pnumero);
    $sheet->setCellValueExplicit('C' . $r, $eliminado ? 'ELIMINADO' : 'ACTIVO', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    if ($eliminado) {
        $sheet->getStyle('A' . $r . ':K' . $r)->getFont()->getColor()->setRGB('FF0000');
    }
    $sheet->setCellValueExplicit('D' . $r, (string) $row->pnombre_demandado, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E' . $r, (string) $row->producto, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $asText('F', $r, $row->pnumero_operacion1);
    $asText('G', $r, $row->pnumero_operacion2);
    $asText('H', $r, $row->pnumero_expediente_judicial);
    $sheet->setCellValue('I' . $r, (float) $row->psaldo_dolarizado);
    $sheet->setCellValue('J' . $r, $row->tipo_de_cambio !== null ? (float) $row->tipo_de_cambio : null);
    $sheet->setCellValue('K' . $r, null);
    $r++;
}

foreach (range('A', 'K') as $col) {
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
echo "De esos, $eliminadosCount estan eliminados (soft delete) - no apareceran en la busqueda normal del sistema, marcados en rojo y columna Estado.\n";
echo "Descargarlo con: scp o el gestor de archivos del hosting.\n";
