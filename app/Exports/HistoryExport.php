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

class HistoryExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            $record->is_retencion ? 'Con Retención' : 'Sin Retención',
            $record->fecha_deposito_pago ? Carbon::parse($record->fecha_deposito_pago)->format('d/m/Y') : '',
            $record->numero_deposito_pago,
            $record->proforma_no,
            $record->user_name,
            $record->fecha_solicitud_factura ? Carbon::parse($record->fecha_solicitud_factura)->format('d/m/Y') : '',
            $record->issuer_name,
            $record->nombre_caso . ' ' . $record->referencia, // Combining specific fields if needed
            $record->oc,
            $record->migo,
            $record->bank_name,
            $record->fecha_envio_email ? Carbon::parse($record->fecha_envio_email)->format('d/m/Y') : '',
            $record->proforma_status,
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
            ['Historial de Transacciones'],
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
                'Fecha Depósito',
                'Número Depósito',
                'No. Proforma',
                'Usuario',
                'Fecha Solicitud',
                'Emisor',
                'Caso/Referencia',
                'O.C',
                'MIGO',
                'Banco',
                'Fecha Envío Email',
                'Estado',
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
            'E' => 15,
            'F' => 10,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 20, // Usuario
            'N' => 15,
            'O' => 25, // Emisor
            'P' => 20,
            'Q' => 15,
            'R' => 15,
            'S' => 20, // Banco
            'T' => 15,
            'U' => 15,
            'V' => 15,
            'W' => 15,
            'X' => 15,
            'Y' => 15,
            'Z' => 15,
            'AA' => 15,
            'AB' => 15,
            'AC' => 15,
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
                $event->sheet->mergeCells('A1:AC1');
            },
        ];
    }
}
