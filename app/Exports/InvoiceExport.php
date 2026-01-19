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

class InvoiceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->proforma_no,
            $record->consecutivo,
            $record->document_type,
            $record->customer_name,
            $record->user_name,
            Carbon::parse($record->transaction_date)->format('d/m/Y'),
            $record->issuer_name,
            $record->numero_caso, // Case Number
            $record->nombre_caso . ' ' . $record->referencia, // Case/Ref
            $record->oc,
            $record->migo,
            $record->bank_name,
            $record->currency_code,
            $record->fecha_envio_email ? Carbon::parse($record->fecha_envio_email)->format('d/m/Y') : '',
            $record->status, // Use status for invoices
            $record->totalComprobante,
            $record->getTotalHonorarioIva('USD'),
            $record->getTotalHonorarioIva('CRC'),
            $record->getTotalHonorario('USD'),
            $record->getTotalHonorario('CRC'),
            $record->getTotalIva('USD'),
            $record->getTotalIva('CRC'),
            $record->getTotalComprobante('USD'),
            $record->getTotalComprobante('CRC'),
        ];
    }

    public function headings(): array
    {
        return [
            ['Facturas y Tiquetes'],
            [
                'No. Proforma',
                'Consecutivo',
                'Tipo',
                'Cliente',
                'Usuario',
                'Fecha Emisión',
                'Emisor',
                'Número Caso',
                'Caso/Referencia',
                'O.C',
                'MIGO',
                'Banco',
                'Moneda',
                'Fecha Envío Email',
                'Estado',
                'Total',
                'Total Honorarios Con IVA USD',
                'Total Honorarios Con IVA CRC',
                'Total Honorarios USD',
                'Total Honorarios CRC',
                'Total IVA USD',
                'Total IVA CRC',
                'Total USD',
                'Total CRC',
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
            'E' => 20, // Usuario
            'F' => 15,
            'G' => 25, // Emisor
            'H' => 15,
            'I' => 20,
            'J' => 15,
            'K' => 15,
            'L' => 20, // Banco
            'M' => 10,
            'N' => 15,
            'O' => 15, // Status
            'P' => 15, // Total
            'Q' => 15,
            'R' => 15,
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
