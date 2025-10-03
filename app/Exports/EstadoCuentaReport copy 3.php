<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EstadoCuentaReport implements FromArray, WithEvents
{
    protected $filters;
    protected $title;

    public function __construct(array $filters, $title)
    {
        $this->filters = $filters;
        $this->title = $title;
    }

    public function array(): array
    {
        $data = [];

        // Obtener clientes que tienen facturas pendientes
        $clientes = Contact::whereNull('deleted_at')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('transactions as t')
                    ->whereColumn('t.contact_id', 'contacts.id')
                    ->whereIn('t.document_type', ['PR','FE','TE'])
                    ->whereIn('t.proforma_status', ['FACTURADA'])
                    ->whereNull('t.deleted_at')
                    ->whereNull('t.numero_deposito_pago')
                    ->whereExists(function ($commissionSubquery) {
                        $commissionSubquery->selectRaw('1')
                            ->from('transactions_commissions as tc')
                            ->whereColumn('tc.transaction_id', 't.id')
                            ->whereNotIn('tc.centro_costo_id', [1, 12, 14, 15, 16, 17, 28, 31]);
                    });
            })
            ->orderBy('name')
            ->get();

        foreach ($clientes as $cliente) {
            // Agregar cliente (fila vacía para el merge que se hará en registerEvents)
            $data[] = []; // Fila para el cliente (se llenará en registerEvents)

            // Agregar encabezados
            $data[] = [
                '#',
                'No Factura',
                'Emisor',
                'Centro de costo',
                'Fecha Factura',
                'Fecha Vencimiento',
                'Moneda',
                'Tipo de Cambio',
                'Total Venta',
                'Total Descuento',
                'Total Venta Neta',
                'IVA',
                'Otros Cargos',
                'Total',
                'Total Equivalente USD',
                'Número de Proforma',
                'Deudor',
                'O.C',
                'MIGO'
            ];

            // Obtener facturas del cliente
            $facturasQuery = Transaction::withoutGlobalScopes()
                ->from('transactions as t')
                ->selectRaw("
                    t.id,
                    t.consecutivo,
                    (SELECT em.name FROM business_locations em WHERE em.id = t.location_id) AS nombreEmisor,
                    CASE
                        WHEN (SELECT cc.codigo
                              FROM transactions_commissions tc
                              JOIN centro_costos cc ON tc.centro_costo_id = cc.id
                              WHERE tc.transaction_id = t.id
                              LIMIT 1) IS NULL
                        OR (SELECT codcontable.codigo
                            FROM codigo_contables codcontable
                            WHERE codcontable.id = t.codigo_contable_id) IS NULL
                        THEN '-'
                        ELSE REPLACE(
                                REPLACE(
                                    (SELECT codcontable.codigo
                                    FROM codigo_contables codcontable
                                    WHERE codcontable.id = t.codigo_contable_id),
                                    'XX',
                                    (SELECT cc.codigo
                                    FROM transactions_commissions tc
                                    JOIN centro_costos cc ON tc.centro_costo_id = cc.id
                                    WHERE tc.transaction_id = t.id
                                    LIMIT 1)
                                ),
                                'YYY',
                                (SELECT em.code
                                FROM business_locations em
                                WHERE em.id = t.location_id)
                            )
                    END AS centro_costo,
                    CASE
                        WHEN t.transaction_date IS NULL THEN ''
                        ELSE DATE_FORMAT(t.transaction_date, '%d-%m-%Y')
                    END AS transaction_date,
                    CASE
                        WHEN t.transaction_date IS NULL THEN ''
                        WHEN c.pay_term_number > 0 THEN
                            DATE_FORMAT(DATE_ADD(t.transaction_date, INTERVAL c.pay_term_number DAY), '%d-%m-%Y')
                        ELSE
                            DATE_FORMAT(t.transaction_date, '%d-%m-%Y')
                    END AS fecha_vencimiento,
                    cu.code as moneda,
                    t.proforma_change_type,
                    COALESCE(t.totalHonorarios, 0) as totalVenta,
                    COALESCE(t.totalDiscount, 0) as totalDescuento,
                    COALESCE(t.totalHonorarios, 0) - COALESCE(t.totalDiscount, 0) as totalVentaNeta,
                    COALESCE(t.totalTax, 0) as iva,
                    COALESCE(t.totalOtrosCargos, 0) as otrosCargos,
                    COALESCE(t.totalComprobante, 0) as total,
                    CASE
                        WHEN t.currency_id = 1 THEN COALESCE(t.totalComprobante, 0)
                        ELSE COALESCE(t.totalComprobante, 0) / NULLIF(COALESCE(t.proforma_change_type, 1), 0)
                    END as totalUSD,
                    t.proforma_no,
                    ca.deudor,
                    t.oc AS ordenCompra,
                    t.migo AS migo
                ")
                ->leftJoin('currencies as cu', 't.currency_id', '=', 'cu.id')
                ->leftJoin('casos as ca', 't.caso_id', '=', 'ca.id')
                ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->where('t.contact_id', $cliente->id)
                ->whereIn('t.document_type', ['PR','FE','TE'])
                ->whereIn('t.proforma_status', ['FACTURADA'])
                ->whereNull('t.deleted_at')
                ->whereNull('t.numero_deposito_pago')
                ->whereExists(function ($commissionSubquery) {
                    $commissionSubquery->selectRaw('1')
                        ->from('transactions_commissions as tc')
                        ->whereColumn('tc.transaction_id', 't.id')
                        ->whereNotIn('tc.centro_costo_id', [1, 12, 14, 15, 16, 17, 28, 31]);
                });

            // Aplicar filtros
            if (!empty($this->filters['filter_date'])) {
                $range = explode(' to ', $this->filters['filter_date']);
                try {
                    if (count($range) === 2) {
                        $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                        $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();
                        $facturasQuery->whereBetween('t.transaction_date', [$start, $end]);
                    } else {
                        $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));
                        $facturasQuery->whereDate('t.transaction_date', $singleDate->format('Y-m-d'));
                    }
                } catch (\Exception $e) {
                    // Manejar error silenciosamente
                }
            }

            if (!empty($this->filters['filter_department'])) {
                $facturasQuery->where('t.department_id', '=', $this->filters['filter_department']);
            }

            if (!empty($this->filters['filter_currency'])) {
                $facturasQuery->where('t.currency_id', '=', $this->filters['filter_currency']);
            }

            if (!empty($this->filters['filter_contact'])) {
                $facturasQuery->where('t.contact_id', '=', $this->filters['filter_contact']);
            }

            $facturas = $facturasQuery->orderBy('t.transaction_date', 'DESC')
                                    ->orderBy('t.consecutivo', 'DESC')
                                    ->get();

            foreach ($facturas as $factura) {
                $data[] = [
                    $factura->id,
                    $factura->consecutivo,
                    $factura->nombreEmisor,
                    $factura->centro_costo,
                    $factura->transaction_date,
                    $factura->fecha_vencimiento,
                    $factura->moneda,
                    $factura->proforma_change_type,
                    $factura->totalVenta,
                    $factura->totalDescuento,
                    $factura->totalVentaNeta,
                    $factura->iva,
                    $factura->otrosCargos,
                    $factura->total,
                    $factura->totalUSD,
                    $factura->proforma_no,
                    $factura->deudor,
                    $factura->ordenCompra,
                    $factura->migo
                ];
            }
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- LOGO ---
                $logoPath = public_path('storage/assets/default-image.png');
                if (method_exists($this, 'getLogoPath')) {
                    $customLogo = $this->getLogoPath();
                    if ($customLogo && file_exists($customLogo)) {
                        $logoPath = $customLogo;
                    }
                }

                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo de la empresa');
                $drawing->setPath($logoPath);
                $drawing->setHeight(50);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);

                // --- TÍTULO PRINCIPAL ---
                $sheet->mergeCells("B1:S1");
                $sheet->setCellValue('B1', $this->title);
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(60);

                // --- ANCHO DE COLUMNAS ---
                $columnWidths = [
                    'A' => 5, 'B' => 25, 'C' => 40, 'D' => 25, 'E' => 15,
                    'F' => 15, 'G' => 10, 'H' => 15, 'I' => 15, 'J' => 15,
                    'K' => 15, 'L' => 15, 'M' => 15, 'N' => 15, 'O' => 15,
                    'P' => 25, 'Q' => 35, 'R' => 25, 'S' => 25
                ];

                foreach ($columnWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // Obtener clientes para procesar las filas
                $clientes = Contact::whereNull('deleted_at')
                    ->whereExists(function ($query) {
                        $query->selectRaw('1')
                            ->from('transactions as t')
                            ->whereColumn('t.contact_id', 'contacts.id')
                            ->whereIn('t.document_type', ['PR','FE','TE'])
                            ->whereIn('t.proforma_status', ['FACTURADA'])
                            ->whereNull('t.deleted_at')
                            ->whereNull('t.numero_deposito_pago')
                            ->whereExists(function ($commissionSubquery) {
                                $commissionSubquery->selectRaw('1')
                                    ->from('transactions_commissions as tc')
                                    ->whereColumn('tc.transaction_id', 't.id')
                                    ->whereNotIn('tc.centro_costo_id', [1, 12, 14, 15, 16, 17, 28, 31]);
                            });
                    })
                    ->orderBy('name')
                    ->get();

                $currentRow = 2; // Empezar en fila 2 (después del título)

                foreach ($clientes as $cliente) {
                    // Fila del CLIENTE (merge completo)
                    $sheet->mergeCells("A{$currentRow}:S{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", $cliente->name . ' - ' . $cliente->identification);
                    $sheet->getStyle("A{$currentRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    $currentRow++;

                    // Fila de ENCABEZADOS (gris)
                    $sheet->getStyle("A{$currentRow}:S{$currentRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFE6E6E6']
                        ]
                    ]);
                    $currentRow++;

                    // Saltar las filas de facturas (ya están en el array)
                    $facturasCount = Transaction::where('contact_id', $cliente->id)
                        ->whereIn('document_type', ['PR','FE','TE'])
                        ->whereIn('proforma_status', ['FACTURADA'])
                        ->whereNull('deleted_at')
                        ->whereNull('numero_deposito_pago')
                        ->whereExists(function ($commissionSubquery) {
                            $commissionSubquery->selectRaw('1')
                                ->from('transactions_commissions as tc')
                                ->whereColumn('tc.transaction_id', 'transactions.id')
                                ->whereNotIn('tc.centro_costo_id', [1, 12, 14, 15, 16, 17, 28, 31]);
                        })
                        ->count();

                    // Aplicar estilo verde a las facturas
                    for ($i = 0; $i < $facturasCount; $i++) {
                        $sheet->getStyle("A{$currentRow}:S{$currentRow}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFCCFFCC']
                            ]
                        ]);
                        $currentRow++;
                    }
                }

                // --- FORMATO DE NÚMEROS ---
                $lastRow = $sheet->getHighestRow();
                $decimalColumns = ['H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];

                foreach ($decimalColumns as $col) {
                    if ($lastRow >= 2) {
                        $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                              ->getNumberFormat()->setFormatCode('#,##0.00');
                    }
                }

                // Columna ID como entero
                if ($lastRow >= 2) {
                    $sheet->getStyle("A2:A{$lastRow}")
                          ->getNumberFormat()->setFormatCode('0');
                }

                // --- AJUSTE DE TEXTO ---
                if ($lastRow >= 2) {
                    $sheet->getStyle("A2:S{$lastRow}")
                          ->getAlignment()->setWrapText(true);
                }

                // --- ALTURA AUTOMÁTICA FILAS ---
                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }

    protected function getLogoPath()
    {
        $business = \App\Models\Business::find(1);
        $logoFileName = $business?->logo;
        $logoPath = public_path("storage/assets/img/logos/{$logoFileName}");
        if (!file_exists($logoPath)) {
            return public_path("storage/assets/default-image.png");
        }
        return $logoPath;
    }
}
