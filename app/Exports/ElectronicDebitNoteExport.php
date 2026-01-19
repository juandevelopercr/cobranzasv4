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

class ElectronicDebitNoteExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->customer_name,
            $record->user_name,
            Carbon::parse($record->transaction_date)->format('d/m/Y'),
            $record->issuer_name,
            $record->numero_caso,
            $record->referencia,
            $record->oc,
            $record->migo,
            $record->bank_name,
            $record->currency_code,
            $record->status,
            $record->totalComprobante,
            $record->getTotalComprobante('USD'),
            $record->getTotalComprobante('CRC'),
        ];
    }

    public function headings(): array
    {
        return [
            ['Notas de Débito Electrónicas'],
            [
                'Consecutivo',
                'Cliente',
                'Usuario',
                'Fecha Emisión',
                'Emisor',
                'Número Caso',
                'Referencia',
                'O.C',
                'MIGO',
                'Banco',
                'Moneda',
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
            'A' => 20,
            'B' => 30, // Cliente
            'C' => 20, // Usuario
            'D' => 15,
            'E' => 25, // Emisor
            'F' => 15, // Caso
            'G' => 20, // Ref
            'H' => 15,
            'I' => 15,
            'J' => 20, // Banco
            'K' => 10,
            'L' => 15,
            'M' => 15,
            'N' => 15,
            'O' => 15,
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
                $event->sheet->mergeCells('A1:O1');
            },
        ];
    }
}
