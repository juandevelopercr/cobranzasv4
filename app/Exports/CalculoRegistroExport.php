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

class CalculoRegistroExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->numero_caso . ' ' . $record->nombre_caso, // Caso
            $record->proforma_change_type, // Assuming this is the change type
            $record->issuer_name,
            $record->bank_name,
            $record->currency_code,
            $record->proforma_type,
            $record->fecha_envio_email ? Carbon::parse($record->fecha_envio_email)->format('d/m/Y') : '',
            $record->totalComprobante,
            $record->proforma_status,
        ];
    }

    public function headings(): array
    {
        return [
            ['Cálculos de Registro'],
            [
                'Consecutivo',
                'No. Proforma',
                'Cliente',
                'Usuario',
                'Fecha Emisión',
                'Nombre de Caso o Referencia',
                'Tipo de Cambio',
                'Emisor',
                'Banco',
                'Moneda',
                'Tipo Acto',
                'Fecha Envío Email',
                'Total',
                'Estado',
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
            'F' => 30, // Caso
            'G' => 15, // Cambio
            'H' => 25, // Emisor
            'I' => 20, // Banco
            'J' => 10, // Moneda
            'K' => 15,
            'L' => 15,
            'M' => 15, // Total
            'N' => 15, // Status
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
                $event->sheet->mergeCells('A1:N1');
            },
        ];
    }
}
