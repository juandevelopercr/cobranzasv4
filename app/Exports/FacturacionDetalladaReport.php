<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionCommission;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FacturacionDetalladaReport extends BaseReport implements WithEvents
{
  protected array $processedTransactions = [];

  // En tu clase del export agrega propiedades:
  protected $phase = 'facturas'; // primero facturas, luego detalles
  protected $facturas = [];
  protected $detalles = [];
  protected $loaded = false;

  public function __construct(array $filters, $title)
  {
      parent::__construct($filters, $title);
      $this->processedTransactions = []; // reset en cada exportación
  }

  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Consecutivo', 'field' => 'consecutivo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Código Contable', 'field' => 'codcont', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de emisión', 'field' => 'transaction_date', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Cédula del cliente', 'field' => 'identification', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Emisor', 'field' => 'nombreEmisor', 'type' => 'string', 'align' => 'left', 'width' => 40],

      ['label' => 'Linea de detalle', 'field' => 'lineaDetalle', 'type' => 'string', 'align' => 'left', 'width' => 65],

      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'T.C', 'field' => 'proforma_change_type', 'type' => 'decimal', 'align' => 'right', 'width' => 15],
      ['label' => 'Monto de Gastos', 'field' => 'gastos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto Honorarios Menos Descuento', 'field' => 'honorariosConDescuento', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto I.V.A', 'field' => 'totalTax', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total Honorario Mas IVA', 'field' => 'honorariosConIva', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Otros Gastos', 'field' => 'totalOtrosCargos', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total', 'field' => 'totalComprobante', 'type' => 'decimal', 'align' => 'right', 'width' => 20],

      ['label' => 'Monto de Gastos(USD)', 'field' => 'gastosUSD', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto Honorarios Menos Descuento(USD)', 'field' => 'honorariosConDescuentoUSD', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto I.V.A(USD)', 'field' => 'totalTaxUSD', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total Honorario Mas IVA(USD)', 'field' => 'honorariosConIvaUSD', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Otros Gastos(USD)', 'field' => 'totalOtrosCargosUSD', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total(USD)', 'field' => 'totalComprobanteUSD', 'type' => 'decimal', 'align' => 'right', 'width' => 20],

      ['label' => 'Monto de Gastos(CRC)', 'field' => 'gastosCRC', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto Honorarios Menos Descuento(CRC)', 'field' => 'honorariosConDescuentoCRC', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto I.V.A(CRC)', 'field' => 'totalTaxCRC', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total Honorario Mas IVA(CRC)', 'field' => 'honorariosConIvaCRC', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Otros Gastos(CRC)', 'field' => 'totalOtrosCargosCRC', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total(CRC)', 'field' => 'totalComprobanteCRC', 'type' => 'decimal', 'align' => 'right', 'width' => 20],

      ['label' => 'Estado', 'field' => 'proforma_status', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Número de Nota', 'field' => 'numeroNotaCredito', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Orden de Compra', 'field' => 'ordenCompra', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Migo', 'field' => 'migo', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Orden de Requisición', 'field' => 'ordenRequisicion', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Prebill', 'field' => 'prebill', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Fecha de Depósito', 'field' => 'fecha_deposito_pago', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Número de Depósito', 'field' => 'numero_deposito_pago', 'type' => 'string', 'align' => 'left', 'width' => 35],
      ['label' => 'Mensaje', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
      ['label' => 'Número de Proforma', 'field' => 'proforma_no', 'type' => 'string', 'align' => 'left', 'width' => 30]
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Transaction::withoutGlobalScopes()
        ->from('transactions as t')
        ->selectRaw("
            t.id,

            -- Código contable con centro de costo y emisor
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
            END AS codcont,

            t.consecutivo,

            -- Fecha transacción
            CASE
                WHEN t.transaction_date IS NULL THEN ''
                ELSE DATE_FORMAT(t.transaction_date, '%d-%m-%Y')
            END AS transaction_date,

            -- Nombre cliente + caso
            CASE
                WHEN t.nombre_caso IS NULL OR t.nombre_caso = ''
                THEN t.customer_name
                ELSE CONCAT(t.customer_name, ' - ', t.nombre_caso)
            END AS customer_name,

            CAST(c.identification AS CHAR) AS identification,

            -- Nombre emisor
            (SELECT em.name FROM business_locations em WHERE em.id = t.location_id) AS nombreEmisor,
            b.name as banco,
            cu.code as moneda,
            cu.symbol as monedasymbolo,
            t.proforma_change_type,

            -- Lineas detalle concatenadas
            (
                SELECT GROUP_CONCAT(tl.detail SEPARATOR ' - ')
                FROM transactions_lines tl
                WHERE tl.transaction_id = t.id
            ) AS lineaDetalle,

            -- Totales
            CASE WHEN t.proforma_status = 'ANULADA' THEN 0 ELSE COALESCE(t.totalTimbres,0) END AS gastos,
            CASE WHEN t.proforma_status = 'ANULADA' THEN 0 ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)) END AS honorariosConDescuento,
            CASE WHEN t.proforma_status = 'ANULADA' THEN 0 ELSE COALESCE(t.totalTax,0) END AS totalTax,
            CASE WHEN t.proforma_status = 'ANULADA' THEN 0 ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)) + COALESCE(t.totalTax,0) END AS honorariosConIva,
            CASE WHEN t.proforma_status = 'ANULADA' THEN 0 ELSE COALESCE(t.totalOtrosCargos,0) END AS totalOtrosCargos,
            CASE WHEN t.proforma_status = 'ANULADA' THEN 0 ELSE COALESCE(t.totalComprobante,0) END AS totalComprobante,

            -- Totales USD
            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalTimbres,0)
                        ELSE COALESCE(t.totalTimbres,0) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END, 0) AS gastosUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)
                        ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END, 0) AS honorariosConDescuentoUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalTax,0)
                        ELSE COALESCE(t.totalTax,0) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END, 0) AS totalTaxUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0)
                        ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0)) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END, 0) AS honorariosConIvaUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalOtrosCargos,0)
                        ELSE COALESCE(t.totalOtrosCargos,0) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END, 0) AS totalOtrosCargosUSD,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 1 THEN COALESCE(t.totalComprobante,0)
                        ELSE COALESCE(t.totalComprobante,0) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                    END
                END, 0) AS totalComprobanteUSD,

            -- Totales CRC
            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalTimbres,0)
                        ELSE COALESCE(t.totalTimbres,0) * COALESCE(t.proforma_change_type,1)
                    END
                END, 0) AS gastosCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)
                        ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)) * COALESCE(t.proforma_change_type,1)
                    END
                END, 0) AS honorariosConDescuentoCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalTax,0)
                        ELSE COALESCE(t.totalTax,0) * COALESCE(t.proforma_change_type,1)
                    END
                END, 0) AS totalTaxCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0)
                        ELSE (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0) + COALESCE(t.totalTax,0)) * COALESCE(t.proforma_change_type,1)
                    END
                END, 0) AS honorariosConIvaCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalOtrosCargos,0)
                        ELSE COALESCE(t.totalOtrosCargos,0) * COALESCE(t.proforma_change_type,1)
                    END
                END, 0) AS totalOtrosCargosCRC,

            COALESCE(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    ELSE CASE t.currency_id
                        WHEN 16 THEN COALESCE(t.totalComprobante,0)
                        ELSE COALESCE(t.totalComprobante,0) * COALESCE(t.proforma_change_type,1)
                    END
                END, 0) AS totalComprobanteCRC,

            t.proforma_status,

            CASE WHEN t.proforma_status = 'ANULADA' THEN (
                SELECT t1.consecutivo
                FROM transactions t1
                WHERE t1.RefCodigo = t.key
                LIMIT 1
            ) ELSE '' END AS numeroNotaCredito,

            t.oc AS ordenCompra,
            t.migo AS migo,
            t.or AS ordenRequisicion,
            t.prebill AS prebill,

            CASE
                WHEN t.fecha_deposito_pago IS NULL THEN ''
                ELSE DATE_FORMAT(t.fecha_deposito_pago, '%d-%m-%Y')
            END AS fecha_deposito_pago,

            t.numero_deposito_pago,
            t.message AS message,
            t.proforma_no
        ")
        ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
        ->leftJoin('banks as b', 't.bank_id', '=', 'b.id')
        ->join('currencies as cu', 't.currency_id', '=', 'cu.id')
        ->whereNull('t.deleted_at')
        ->whereIn('t.document_type', ['PR','FE','TE'])
        ->whereIn('t.proforma_status', ['FACTURADA','ANULADA'])
        ->orderBy('t.transaction_date', 'DESC')
        ->orderBy('c.name', 'ASC');

    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);

        try {
            if (count($range) === 2) {
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();

                $query->whereBetween('t.transaction_date', [$start, $end]);
            } else {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));
                $query->whereDate('t.transaction_date', $singleDate->format('Y-m-d'));
            }
        } catch (\Exception $e) {
            // Opcional: podrías registrar el error para depurar
            //\Log::error("Error en filtro de fechas: ".$e->getMessage());
        }
    }

    if (!empty($this->filters['filter_contact'])) {
      $query->where('t.contact_id', '=', $this->filters['filter_contact']);
    }

    if (!empty($this->filters['filter_status'])) {
      $query->where('t.proforma_status', '=', $this->filters['filter_status']);
    }

    return $query;
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

            // --- TÍTULO ---
            $lastColumnLetter = $this->columnLetter(count($this->columns()) - 1);
            $sheet->mergeCells("B1:{$lastColumnLetter}1");
            $sheet->setCellValue('B1', $this->title);
            $sheet->getStyle('B1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(60);

            // --- ENCABEZADOS ---
            $headings = $this->headings();
            foreach ($headings as $index => $heading) {
                $colLetter = $this->columnLetter($index);
                $sheet->setCellValue("{$colLetter}3", $heading);
                $sheet->getStyle("{$colLetter}3")->getFont()->setBold(true);
                $sheet->getStyle("{$colLetter}3")->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setWrapText(true);
            }
            $sheet->getRowDimension(3)->setRowHeight(-1);


            // --- AJUSTE DE TEXTO ---
            $lastRow = $sheet->getHighestRow();
            $totalsRow = $lastRow + 1;
            foreach ($this->columns() as $index => $col) {
                if (($col['type'] ?? 'string') === 'string') {
                    $colLetter = $this->columnLetter($index);
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                          ->getAlignment()->setWrapText(true);
                }
            }

            // --- ALTURA AUTOMÁTICA FILAS DATOS ---
            for ($row = 4; $row <= $lastRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(-1);
            }


            // --- COLUMNAS ---
            foreach ($this->columnWidths() as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            $lastRow = $sheet->getHighestRow();

            // --- RESALTAR FILAS PRINCIPALES ---
            // Calculamos la letra de la columna is_main
            $lastColumnIndex = count($this->columns()) + 1; // +1 porque PhpSpreadsheet usa 1-based
            $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);

            // --- RESALTAR FILAS PRINCIPALES ---
            $lastRow = $sheet->getHighestRow();
            for ($row = 4; $row <= $lastRow; $row++) {
                $isMain = $sheet->getCell("{$lastColumnLetter}{$row}")->getValue();

                // Solo si es '1' (fila principal)
                if ((string)$isMain === '1') {
                    $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                          ->getFill()
                          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                          ->getStartColor()->setARGB('FFCCFFCC'); // verde
                }
                else
                {
                    $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                          ->getFill()
                          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                          ->getStartColor()->setARGB('FFFFCCCC'); // verde
                }
            }

            // --- IDENTIFICATION como TEXTO ---
            foreach ($this->columns() as $index => $col) {
                if (($col['field'] ?? '') === 'identification') {
                    $colLetter = $this->columnLetter($index);
                    for ($row = 4; $row <= $lastRow; $row++) {
                        $sheet->setCellValueExplicit(
                            "{$colLetter}{$row}",
                            (string)$sheet->getCell("{$colLetter}{$row}")->getValue(),
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    }
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                          ->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_TEXT);
                    break;
                }
            }

            // --- TOTALES SOLO DETALLE ---
            // Buscar índice de la columna 'is_main'
            $isMainColumnIndex = null;
            foreach ($this->columns() as $idx => $col) {
                if (($col['field'] ?? '') === 'is_main') {
                    $isMainColumnIndex = $idx;
                    break;
                }
            }

            // --- TOTALES SOLO DETALLE ---
            // Buscar índice de la columna 'is_main'
            $isMainColumnIndex = null;
            foreach ($this->columns() as $idx => $col) {
                if (($col['field'] ?? '') === 'is_main') {
                    $isMainColumnIndex = $idx;
                    break;
                }
            }

            if ($isMainColumnIndex === null) {
                // Si no existe, ponerla al final
                $isMainColumnIndex = count($this->columns());
            }

            $isMainColumnLetter = $this->columnLetter($isMainColumnIndex);

            // --- Ocultar la columna 'is_main' ---
            $sheet->getColumnDimension($isMainColumnLetter)->setVisible(false);

            // --- TOTALES SOLO DETALLE ---
            $totalsRow = $lastRow + 1;

            foreach ($this->columns() as $index => $col) {
                $letter = $this->columnLetter($index);

                if (in_array($col['type'] ?? 'string', ['decimal','currency','integer'])) {
                    // SUM solo de las filas donde is_main = 'N'
                    $sumFormula = "SUMIF({$isMainColumnLetter}4:{$isMainColumnLetter}{$lastRow},\"N\",{$letter}4:{$letter}{$lastRow})";
                    $sheet->setCellValue("{$letter}{$totalsRow}", "={$sumFormula}");
                    $sheet->getStyle("{$letter}{$totalsRow}")
                          ->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("{$letter}{$totalsRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("{$letter}{$totalsRow}")->getFont()->setBold(true);
                }
            }

            $sheet->setCellValue("A{$totalsRow}", 'TOTALES');
            $sheet->getStyle("A{$totalsRow}")->getFont()->setBold(true);


            // Ajuste de altura
            for ($row = 4; $row <= $lastRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(-1);
            }

            // --- FORZAR ID COMO ENTERO ---
            foreach ($this->columns() as $index => $col) {
                if (($col['field'] ?? '') === 'id') {
                    $colLetter = $this->columnLetter($index);

                    // Asegurar que cada celda se trate como número entero
                    for ($row = 4; $row <= $lastRow; $row++) {
                        $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();
                        if (is_numeric($cellValue)) {
                            $sheet->setCellValueExplicit(
                                "{$colLetter}{$row}",
                                (int)$cellValue,
                                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                            );
                        }
                    }

                    // Formato de celda entero (sin decimales)
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                          ->getNumberFormat()->setFormatCode('0');
                    break;
                }
            }
        },
    ];
  }

  public function map($row): array
  {
    $mapped = [];

    $totalColumns = count($this->columns()); // número real de columnas mapeadas
    $lastColumnIndex = $totalColumns; // índice de la nueva columna 'is_main'

    // --- Fila principal: cada fila ya es un centro de costo ---
    $mainRow = collect($this->columns())->map(function ($col) use ($row) {
        $field = $col['field'];
        $value = $row->{$field} ?? null;
        $type = $col['type'] ?? 'string';

        switch ($type) {
            case 'date':
                if ($value instanceof \DateTimeInterface) {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($value->format('Y-m-d'));
                }
                if (is_string($value) && !empty($value)) {
                    try {
                        $dt = \Carbon\Carbon::createFromFormat('d-m-Y', $value);
                    } catch (\Exception $e) {
                        try {
                            $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $value);
                        } catch (\Exception $e2) {
                            return null;
                        }
                    }
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($dt->format('Y-m-d'));
                }
                return null;

            case 'currency':
            case 'decimal':
                return is_numeric($value) ? (float)$value : null;

            case 'integer':
                return is_numeric($value) ? (int)$value : null;

            case 'string':
                if ($field === 'identification') {
                    $val = (string)trim(strip_tags((string)$value));
                    if (is_numeric($val) && strlen($val) > 12) $val = "'".$val;
                    return $val;
                }
                return trim(strip_tags((string)$value));

            default:
                return $value;
        }
    })->toArray();

    // Agregamos la columna is_main al final
    $mainRow[$lastColumnIndex] = 1;

    $mapped[] = $mainRow;

    // --- Filas de detalle por línea y otros cargos ---
    $transaction = Transaction::with('commisions', 'contact', 'location', 'currency', 'otherCharges')->find($row->id);

    if ($transaction) {
        // Líneas de detalle
        if ($transaction->commisions->count() > 1)
        {
          foreach ($transaction->commisions ?? [] as $commision) {
              $percentFactor = $commision->percent / 100;

              // --- Valores base ---
              $timbresBase        = $transaction->totalTimbres * $percentFactor;
              $honorariosBase     = ($transaction->totalHonorarios - $transaction->totalDiscount) * $percentFactor;
              $taxBase            = $transaction->totalTax * $percentFactor;
              $honorariosSumBase  = ($transaction->totalHonorarios - $transaction->totalDiscount + $transaction->totalTax) * $percentFactor;
              $totalComprobanteBase = $transaction->totalComprobante * $percentFactor;

              // --- Generar codcont ---
              $codcont = '-';
              if (!empty($commision->centroCosto->codigo) && !empty($transaction->codigoContable->codigo)) {
                  $codcont = str_replace('XX', $commision->centroCosto->codigo, $transaction->codigoContable->codigo);
                  $codcont = str_replace('YYY', $transaction->location->code ?? '', $codcont);
              }

              // --- USD ---
              $usdTimbres       = $transaction->currency_id == 1
                                  ? $timbresBase
                                  : $timbresBase / $transaction->proforma_change_type;
              $usdHonorarios    = $transaction->currency_id == 1
                                  ? $honorariosBase
                                  : $honorariosBase / $transaction->proforma_change_type;
              $usdTax           = $transaction->currency_id == 1
                                  ? $taxBase
                                  : $taxBase / $transaction->proforma_change_type;
              $usdHonorariosSum = $transaction->currency_id == 1
                                  ? $honorariosSumBase
                                  : $honorariosSumBase / $transaction->proforma_change_type;
              $usdTotal         = $transaction->currency_id == 1
                                  ? $totalComprobanteBase
                                  : $totalComprobanteBase / $transaction->proforma_change_type;

              // --- CRC ---
              $crcTimbres       = $transaction->currency_id == 16
                                  ? $timbresBase
                                  : $timbresBase * $transaction->proforma_change_type;
              $crcHonorarios    = $transaction->currency_id == 16
                                  ? $honorariosBase
                                  : $honorariosBase * $transaction->proforma_change_type;
              $crcTax           = $transaction->currency_id == 16
                                  ? $taxBase
                                  : $taxBase * $transaction->proforma_change_type;
              $crcHonorariosSum = $transaction->currency_id == 16
                                  ? $honorariosSumBase
                                  : $honorariosSumBase * $transaction->proforma_change_type;
              $crcTotal         = $transaction->currency_id == 16
                                  ? $totalComprobanteBase
                                  : $totalComprobanteBase * $transaction->proforma_change_type;

              // --- Fila detallada ---
              $rowDetail = [
                  $transaction->id,
                  $transaction->consecutivo,
                  $codcont,
                  $transaction->transaction_date ? Carbon::parse($transaction->transaction_date)->format('d-m-Y') : '',
                  $transaction->customer_name,
                  $transaction->contact->identification,
                  $transaction->location->name,
                  '',
                  $transaction->currency->code,
                  $transaction->proforma_change_type,
                  $timbresBase,
                  $honorariosBase,
                  $taxBase,
                  $honorariosSumBase,
                  '',
                  $totalComprobanteBase,

                  $usdTimbres,
                  $usdHonorarios,
                  $usdTax,
                  $usdHonorariosSum,
                  '',
                  $usdTotal,
                  $crcTimbres,
                  $crcHonorarios,
                  $crcTax,
                  $crcHonorariosSum,
                  '',
                  $crcTotal,
                  '',
                  '',
                  '',
                  '',
                  '',
                  '',
                  '',
                  '',
                  '',
                  '',
                  '',
                  'N' // columna is_main
              ];
              $mapped[] = $rowDetail;
          }
        }
    }

    return $mapped;
  }

}
