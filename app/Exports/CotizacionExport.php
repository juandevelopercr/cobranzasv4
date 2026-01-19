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
use App\Helpers\Helpers;

class CotizacionExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
        $userName = '';
        if ($record->user) {
            $userName = $record->user->name;
        }

        $bankName = '';
        if ($record->bank) {
            $bankName = $record->bank->name;
        }

        $currencyCode = '';
        if ($record->currency) {
            $currencyCode = $record->currency->code;
        }

        return [
            $record->proforma_no,
            $record->customer_name,
            $userName,
            Carbon::parse($record->transaction_date)->format('d/m/Y'),
            $bankName,
            $currencyCode,
            $record->proforma_type,
            $record->fecha_envio_email ? Carbon::parse($record->fecha_envio_email)->format('d/m/Y') : '',
            $record->proforma_status,
            $record->totalComprobante,
            $record->getTotalComprobante('USD'),
            $record->getTotalComprobante('CRC'),
        ];
    }

    public function headings(): array
    {
        return [
            ['Cotizaciones'],
            [
                'No. Proforma',
                'Cliente',
                'Usuario',
                'Fecha Emisión',
                'Banco',
                'Moneda',
                'Tipo',
                'Fecha Envío Email',
                'Estado',
                'Total',
                'Total USD',
                'Total CRC',
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30, // Cliente
            'C' => 20, // Usuario
            'D' => 15, // Fecha
            'E' => 20, // Banco
            'F' => 10, // Moneda
            'G' => 15, // Tipo
            'H' => 15, // Fecha Email
            'I' => 15, // Estado
            'J' => 15, // Total
            'K' => 15, // Total USD
            'L' => 15, // Total CRC
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
                // Merge cells for title -> A to L (12 columns)
                $event->sheet->mergeCells('A1:L1');
            },
        ];
    }
}
