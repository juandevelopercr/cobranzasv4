<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProformaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->proforma_status,
            Carbon::parse($record->transaction_date)->format('d/m/Y'),
            $record->fecha_solicitud_factura ? Carbon::parse($record->fecha_solicitud_factura)->format('d/m/Y') : '',
            $record->proforma_no,
            $record->consecutivo,
            $record->customer_name,
            $record->numero_caso . ' ' . $record->nombre_caso, // Caso Info
            $record->user_name,
            $record->issuer_name,
            $record->codigo_contable_code, // Or name if available
            $record->oc,
            $record->migo,
            $record->bank_name,
            $record->currency_code,
            $record->proforma_type,
            $record->fecha_envio_email ? Carbon::parse($record->fecha_envio_email)->format('d/m/Y') : '',
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
            ['Proformas'],
            [
                'Estado',
                'Fecha Emisión',
                'Fecha Solicitud',
                'No. Proforma',
                'Consecutivo',
                'Cliente',
                'Caso',
                'Usuario',
                'Emisor',
                'Código Contable',
                'O.C',
                'MIGO',
                'Banco',
                'Moneda',
                'Tipo Acto',
                'Fecha Envío Email',
                'Total',
                'Total Hon IVA USD',
                'Total Hon IVA CRC',
                'Total Hon USD',
                'Total Hon CRC',
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
            'A' => 15, // Estado
            'B' => 15, // Fecha
            'C' => 15, // Fecha Solicitud
            'D' => 15, // No Proforma
            'E' => 15, // Consecutivo
            'F' => 30, // Cliente
            'G' => 20, // Caso
            'H' => 20, // Usuario
            'I' => 25, // Emisor
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 20, // Banco
            'N' => 10, // Moneda
            'O' => 15, // Tipo Acto
            'P' => 15, // Email Date
            'Q' => 15, // Total
            'R' => 15,
            'S' => 15,
            'T' => 15,
            'U' => 15,
            'V' => 15,
            'W' => 15,
            'X' => 15,
            'Y' => 15,
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
                // Merge cells for title -> A to Y (25 columns)
                $event->sheet->mergeCells('A1:Y1');
            },
        ];
    }
}
