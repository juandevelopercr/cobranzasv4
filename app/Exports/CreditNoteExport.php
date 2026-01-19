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

class CreditNoteExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->customer_name,
            $record->user_name,
            Carbon::parse($record->transaction_date)->format('d/m/Y'),
            $record->fecha_solicitud_factura ? Carbon::parse($record->fecha_solicitud_factura)->format('d/m/Y') : '',
            $record->issuer_name,
            $record->codigo_contable_code,
            $record->numero_caso,
            $record->nombre_caso, // Referencia
            $record->oc,
            $record->migo,
            $record->bank_name,
            $record->currency_code,
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
            ['Notas de Crédito'],
            [
                'No. Proforma',
                'Consecutivo',
                'Cliente',
                'Usuario',
                'Fecha Emisión',
                'Fecha Solicitud',
                'Emisor',
                'Código Contable',
                'Número Caso',
                'Referencia',
                'O.C',
                'MIGO',
                'Banco',
                'Moneda',
                'Tipo Acto',
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
            'A' => 15, // No Proforma
            'B' => 15, // Consecutivo
            'C' => 30, // Cliente
            'D' => 20, // Usuario
            'E' => 15, // Fecha
            'F' => 15, // App Date
            'G' => 25, // Emisor
            'H' => 15, // Codigo
            'I' => 15, // Caso
            'J' => 20, // Ref
            'K' => 15,
            'L' => 15,
            'M' => 20, // Banco
            'N' => 10, // Moneda
            'O' => 15, // Tipo
            'P' => 15, // Email
            'Q' => 15, // Status
            'R' => 15, // Total
            'S' => 15,
            'T' => 15,
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
                $event->sheet->mergeCells('A1:T1');
            },
        ];
    }
}
