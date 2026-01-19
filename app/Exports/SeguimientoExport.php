<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SeguimientoExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function map($record): array
    {
        return [
            $record->consecutivo,
            Carbon::parse($record->transaction_date)->format('d/m/Y'),
            $record->document_type,
            $record->customer_name,
            $record->numero_caso,
            $record->currency_code,
            $record->totalComprobante,
            $record->proforma_type,
            $record->is_retencion ? 'SI' : 'NO',
            $record->fecha_deposito_pago ? Carbon::parse($record->fecha_deposito_pago)->format('d/m/Y') : '',
            $record->numero_deposito_pago,
            $record->proforma_no,
            $record->user_name,
            $record->issuer_name,
            $record->nombre_caso . ' ' . $record->referencia,
            $record->oc,
            $record->migo,
            $record->bank_name,
            $record->fecha_envio_email ? Carbon::parse($record->fecha_envio_email)->format('d/m/Y') : '',
            $record->proforma_status,
            $record->fecha_traslado_honorario ? Carbon::parse($record->fecha_traslado_honorario)->format('d/m/Y') : '',
            $record->numero_traslado_honorario,
            $record->fecha_traslado_gasto ? Carbon::parse($record->fecha_traslado_gasto)->format('d/m/Y') : '',
            $record->numero_traslado_gasto,
        ];
    }

    public function headings(): array
    {
        return [
            ['Seguimiento de Facturas'],
            [
                'Consecutivo',
                'Fecha Emisión',
                'Tipo',
                'Cliente',
                'Número Caso',
                'Moneda',
                'Total',
                'Tipo Acto',
                '2%',
                'Fecha Depósito Pago',
                'Número Depósito Pago',
                'No. Proforma',
                'Usuario',
                'Emisor',
                'Caso/Referencia',
                'O.C',
                'MIGO',
                'Banco',
                'Fecha Envío Email',
                'Estado',
                'Fecha Traslado Honorario',
                'Número Traslado Honorario',
                'Fecha Traslado Gasto',
                'Número Traslado Gasto',
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 10,
            'D' => 30, // Cliente
            'E' => 15,
            'F' => 10,
            'G' => 15,
            'H' => 15,
            'I' => 10,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 20, // Usuario
            'N' => 25, // Emisor
            'O' => 20,
            'P' => 15,
            'Q' => 15,
            'R' => 20, // Banco
            'S' => 15,
            'T' => 15,
            'U' => 15,
            'V' => 15,
            'W' => 15,
            'X' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => 'center'],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->mergeCells('A1:X1');
            },
        ];
    }
}
