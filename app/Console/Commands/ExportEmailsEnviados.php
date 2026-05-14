<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportEmailsEnviados extends Command
{
    protected $signature = 'reporte:emails-enviados-14mayo';
    protected $description = 'Exporta XLSX con facturas enviadas erróneamente el 14/05/2026';

    private array $ids = [
        16312,16311,16310,16201,16832,16828,16753,16669,16670,16671,16974,16830,16973,16975,
        17197,17198,17199,17200,17397,17399,17400,17398,17186,17448,17483,17462,17480,17482,
        17470,17466,17479,17391,17689,18037,17723,17729,17013,17587,17603,17923,18249,18247,
        18265,17697,17699,17711,17701,17709,17471,18577,18509,17967,17791,18275,18203,18205,
        18209,18881,19005,18995,19011,18543,19055,18123,18895,19073,
    ];

    public function handle(): void
    {
        $transactions = Transaction::with(['contact', 'location'])
            ->whereIn('id', $this->ids)
            ->orderBy('invoice_date')
            ->get();

        $export = new class($transactions) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
            public function __construct(private $transactions) {}

            public function collection()
            {
                return $this->transactions->map(fn($t) => [
                    $t->id,
                    $t->consecutivo ?? '',
                    $t->document_type ?? '',
                    $t->invoice_date ? \Carbon\Carbon::parse($t->invoice_date)->format('d/m/Y H:i') : '',
                    $t->customer_name ?? ($t->contact->name ?? ''),
                    $t->contact->email ?? '',
                    $t->email_cc ?? '',
                    $t->location->name ?? '',
                    $t->status ?? '',
                    $t->totalComprobante ?? 0,
                ]);
            }

            public function headings(): array
            {
                return [
                    'ID', 'Consecutivo', 'Tipo', 'Fecha Factura', 'Cliente',
                    'Correo Destinatario', 'CC', 'Empresa Emisora', 'Estado', 'Monto Total',
                ];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFD9E1F2']]],
                ];
            }
        };

        $filename = 'emails_enviados_14mayo2026.xlsx';
        $path = 'app/' . $filename;

        Excel::store($export, $path);

        $this->info('XLSX generado: ' . storage_path($path));
        $this->info('Total registros: ' . $transactions->count());
    }
}
