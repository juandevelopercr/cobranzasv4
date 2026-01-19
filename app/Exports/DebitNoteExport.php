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

class DebitNoteExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->id,
            $record->consecutivo,
            $record->customer_name,
            $record->user_name,
            Carbon::parse($record->transaction_date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            $record->issuer_name,
            $record->numero_caso,
            $record->referencia,
            $record->oc,
            $record->migo,
            $record->bank_name,
            $record->currency_name,
            $record->status,
            $record->totalComprobante,
            $record->getTotalComprobante('USD'),
            $record->getTotalComprobante('CRC'),
        ];
    }

    public function headings(): array
    {
        return [
            ['Notas de Débito'],
            [
                'ID',
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
            'A' => 10,
            'B' => 20,
            'C' => 30, // Cliente
            'D' => 20, // Usuario
            'E' => 30, // Fecha
            'F' => 25, // Emisor
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 20, // Banco
            'L' => 10, // Moneda
            'M' => 15, // Estado
            'N' => 15,
            'O' => 15,
            'P' => 15,
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
                $event->sheet->mergeCells('A1:P1');
            },
        ];
    }
}
