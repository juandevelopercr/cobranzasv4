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

class BuscadorExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->proforma_no,
            $record->customer_name,
            $record->user_name,
            Carbon::parse($record->transaction_date)->format('d/m/Y'),
            $record->fecha_solicitud_factura ? Carbon::parse($record->fecha_solicitud_factura)->format('d/m/Y') : '',
            $record->issuer_name,
            $record->numero_caso . ' ' . $record->nombre_caso,
            $record->oc,
            $record->migo,
            $record->is_retencion ? 'Con Retención' : 'Sin Retención',
            $record->bank_name,
            $record->currency_code,
            $record->proforma_type,
            $record->fecha_envio_email ? Carbon::parse($record->fecha_envio_email)->format('d/m/Y') : '',
            $record->proforma_status,
            $record->totalComprobante,
            $record->getTotalComprobante('USD'),
            $record->getTotalComprobante('CRC'),
            $record->fecha_deposito_pago ? Carbon::parse($record->fecha_deposito_pago)->format('d/m/Y') : '',
            $record->numero_deposito_pago,
        ];
    }

    public function headings(): array
    {
        return [
            ['Buscador de Proformas'],
            [
                'Consecutivo',
                'No. Proforma',
                'Cliente',
                'Usuario',
                'Fecha Emisión',
                'Fecha Solicitud',
                'Emisor',
                'Caso',
                'O.C',
                'MIGO',
                '2%',
                'Banco',
                'Moneda',
                'Tipo Acto',
                'Fecha Envío Email',
                'Estado',
                'Total',
                'Total USD',
                'Total CRC',
                'Fecha depósito de pago',
                'Número depósito de pago',
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 30, // Cliente
            'D' => 20, // Usuario
            'E' => 15, // Fecha
            'F' => 15,
            'G' => 25, // Emisor
            'H' => 20, // Caso
            'I' => 15,
            'J' => 15,
            'K' => 15, // 2%
            'L' => 20, // Banco
            'M' => 10, // Moneda
            'N' => 15,
            'O' => 15,
            'P' => 15, // Estado
            'Q' => 15,
            'R' => 15,
            'S' => 15,
            'T' => 15,
            'U' => 15,
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
                // Merge cells for title -> A to U (21 columns)
                $event->sheet->mergeCells('A1:U1');
            },
        ];
    }
}
