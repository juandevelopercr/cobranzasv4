<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionCommission;
use Illuminate\Support\Facades\DB;

class IvaReport extends BaseReport
{
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

      ['label' => 'Banco', 'field' => 'banco', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Número de caso', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],

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
      ['label' => 'Días transacurridos', 'field' => 'dias_trascurridos', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Mensaje', 'field' => 'message', 'type' => 'string', 'align' => 'left', 'width' => 90],
      ['label' => 'Número de Proforma', 'field' => 'proforma_no', 'type' => 'string', 'align' => 'left', 'width' => 30]
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = TransactionCommission::query()
    ->from('transactions_commissions as tc')
    ->selectRaw("
        t.id,
        CASE
            WHEN cc.codigo IS NULL OR codcontable.codigo IS NULL THEN '-'
            ELSE REPLACE(REPLACE(codcontable.codigo, 'XX', cc.codigo), 'YYY', emisor.code)
        END AS codcont,
        t.consecutivo,
        CASE
            WHEN t.transaction_date IS NULL THEN ''
            ELSE DATE_FORMAT(t.transaction_date, '%d-%m-%Y')
        END AS transaction_date,
        CASE
            WHEN t.nombre_caso IS NULL OR t.nombre_caso = ''
            THEN t.customer_name
            ELSE CONCAT(t.customer_name, ' - ', t.nombre_caso)
        END AS customer_name,
        CAST(c.identification AS CHAR) AS identification,
        emisor.name as nombreEmisor,
        b.name as banco,
        ca.pnumero,
        cu.code as moneda,
        cu.symbol as monedasymbolo,
        t.proforma_change_type,

        -- Días transcurridos desde la emisión
        DATEDIFF(NOW(), t.transaction_date) AS dias_trascurridos,

        -- Días vencidos (transcurridos - plazo)
        DATEDIFF(NOW(), t.transaction_date) - t.pay_term_number AS dias_vencidos,

        -- Base con percent
        CASE
            WHEN t.proforma_status = 'ANULADA' THEN 0
            ELSE COALESCE(t.totalTimbres, 0) * (tc.percent / 100)
        END AS gastos,

        CASE
            WHEN t.proforma_status = 'ANULADA' THEN 0
            ELSE ((COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)) * COALESCE(tc.percent,0)/100)
        END AS honorariosConDescuento,

        CASE
            WHEN t.proforma_status = 'ANULADA' THEN 0
            ELSE (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100)
        END AS totalTax,

        CASE
            WHEN t.proforma_status = 'ANULADA' THEN 0
            ELSE (
                -- Calculamos honorarios netos con porcentaje
                (COALESCE(t.totalHonorarios,0) - COALESCE(t.totalDiscount,0)) * COALESCE(tc.percent,0)/100
                -- Sumamos el IVA calculado sobre el total del impuesto
                + (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100)
            )
        END AS honorariosConIva,


        CASE
            WHEN t.proforma_status = 'ANULADA' THEN 0
            ELSE (COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100)
        END AS totalOtrosCargos,

        CASE
            WHEN t.proforma_status = 'ANULADA' THEN 0
            ELSE (COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100)
        END AS totalComprobante,

        -- USD
        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE
                    CASE t.currency_id
                        WHEN 1 THEN
                            -- Moneda local: primero tomamos totalTimbres y aplicamos porcentaje
                            (COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100)
                        ELSE
                            -- Otra moneda: primero calculamos el valor en moneda local, luego convertimos a USD
                            ((COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100)
                            / NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
            END,
        0) AS gastosUSD,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE
                    CASE t.currency_id
                        WHEN 1 THEN
                            -- Moneda local: primero calculamos honorarios - descuento
                            ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
                        ELSE
                            -- Otra moneda: primero calculamos honorarios - descuento, luego convertimos a USD
                            (((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
                            / NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
            END,
        0) AS honorariosConDescuentoUSD,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE CASE t.currency_id
                    WHEN 1 THEN COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100
                    ELSE (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100) / NULLIF(COALESCE(t.proforma_change_type,0),0)
                END
            END,
        0) AS totalTaxUSD,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE
                    CASE t.currency_id
                        WHEN 1 THEN
                            -- Moneda local: (honorarios * % - descuento) + impuestos * %
                            ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                            + (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100))
                        ELSE
                            -- Otra moneda: aplicamos la misma lógica y luego convertimos a USD
                            (((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                              + (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100))
                            / NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
            END,
        0) AS honorariosConIvaUSD,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE CASE t.currency_id
                    WHEN 1 THEN COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100
                    ELSE (COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                END
            END,
        0) AS totalOtrosCargosUSD,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE CASE t.currency_id
                    WHEN 1 THEN COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100
                    ELSE (COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100) / NULLIF(COALESCE(t.proforma_change_type,1),0)
                END
            END,
        0) AS totalComprobanteUSD,


        -- CRC
        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE
                    CASE t.currency_id
                        WHEN 16 THEN
                            -- Moneda local: primero tomamos totalTimbres y aplicamos porcentaje
                            (COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100)
                        ELSE
                            -- Otra moneda: primero calculamos el valor en moneda local, luego convertimos a USD
                            ((COALESCE(t.totalTimbres,0) * COALESCE(tc.percent,0)/100)
                            * NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
            END,
        0) AS gastosCRC,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE
                    CASE t.currency_id
                        WHEN 16 THEN
                            -- Moneda local: primero calculamos honorarios - descuento
                            ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
                        ELSE
                            -- Otra moneda: primero calculamos honorarios - descuento, luego convertimos a USD
                            (((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100) - COALESCE(t.totalDiscount,0))
                            * NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
            END,
        0) AS honorariosConDescuentoCRC,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE CASE t.currency_id
                    WHEN 16 THEN COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100
                    ELSE (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100) * NULLIF(COALESCE(t.proforma_change_type,0),0)
                END
            END,
        0) AS totalTaxCRC,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE
                    CASE t.currency_id
                        WHEN 16 THEN
                            -- Moneda local: (honorarios * % - descuento) + impuestos * %
                            ((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                            + (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100))
                        ELSE
                            -- Otra moneda: aplicamos la misma lógica y luego convertimos a USD
                            (((COALESCE(t.totalHonorarios,0) * COALESCE(tc.percent,0)/100 - COALESCE(t.totalDiscount,0))
                              + (COALESCE(t.totalTax,0) * COALESCE(tc.percent,0)/100))
                            * NULLIF(COALESCE(t.proforma_change_type,0),0))
                    END
            END,
        0) AS honorariosConIvaCRC,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE CASE t.currency_id
                    WHEN 16 THEN COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100
                    ELSE (COALESCE(t.totalOtrosCargos,0) * COALESCE(tc.percent,0)/100) * NULLIF(COALESCE(t.proforma_change_type,1),0)
                END
            END,
        0) AS totalOtrosCargosCRC,

        COALESCE(
            CASE
                WHEN t.proforma_status = 'ANULADA' THEN 0
                ELSE CASE t.currency_id
                    WHEN 16 THEN COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100
                    ELSE (COALESCE(t.totalComprobante,0) * COALESCE(tc.percent,0)/100) * NULLIF(COALESCE(t.proforma_change_type,1),0)
                END
            END,
        0) AS totalComprobanteCRC,

        t.proforma_status,

        CASE
            WHEN t.proforma_status = 'ANULADA'
            THEN COALESCE((SELECT t1.consecutivo FROM transactions t1 WHERE t1.RefCodigo = t.key LIMIT 1), '')
            ELSE ''
        END AS numeroNotaCredito,

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
    ->join('transactions as t', 'tc.transaction_id', '=', 't.id')
    ->leftJoin('centro_costos as cc', 'tc.centro_costo_id', '=', 'cc.id')
    ->leftJoin('codigo_contables as codcontable', 't.codigo_contable_id', '=', 'codcontable.id')
    ->leftJoin('business_locations as emisor', 't.location_id', '=', 'emisor.id')
    ->join('contacts as c', 't.contact_id', '=', 'c.id')
    ->join('banks as b', 't.bank_id', '=', 'b.id')
    ->leftJoin('casos as ca', 't.caso_id', '=', 'ca.id')
    ->join('currencies as cu', 't.currency_id', '=', 'cu.id')
    ->whereIn('t.document_type', ['PR','FE','TE'])
    ->whereIn('t.proforma_status', ['FACTURADA','ANULADA'])
    ->where('t.proforma_type', 'HONORARIO')
    ->whereNotNull('t.fecha_deposito_pago')
    //->where('t.fecha_deposito_pago', '<>', '')
    ->whereNotNull('t.numero_deposito_pago')
    //->where('t.numero_deposito_pago', '<>', '')
    ->where(function ($q) {
        $q->where('t.payment_status', Transaction::DEBIDA)
          ->orWhereNull('t.payment_status');
    })
    ->whereExists(function ($subquery) {
        $subquery->select(DB::raw(1))
                 ->from('transactions_payments as tp')
                 ->whereColumn('tp.transaction_id', 't.id');
    })
    ->orderBy('t.transaction_date', 'DESC');


    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);

        if (count($range) === 2) {
            try {
                // Convertir fechas a Carbon
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();

                // Filtro de rango (incluye todas las horas del día)
                $query->whereBetween('t.transaction_date', [$start, $end]);
            } catch (\Exception $e) {
                // manejar error
            }
        } else {
            try {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));

                // Comparar solo por la fecha, ignorando horas
                $query->whereDate('t.transaction_date', $singleDate->format('Y-m-d'));
            } catch (\Exception $e) {
                // manejar error
            }
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
}
