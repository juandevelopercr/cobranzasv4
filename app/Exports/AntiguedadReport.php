<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\TransactionCommission;

class AntiguedadReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Saldo Pendiente', 'field' => 'saldoPendiente', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Sin Vencer', 'field' => 'sin_vencer', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '1-30 Días', 'field' => 'vencido_1_30', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '31-45 Días', 'field' => 'vencido_31_45', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '46-60 Días', 'field' => 'vencido_46_60', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '61-90 Días', 'field' => 'vencido_61_90', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '91-120 Días', 'field' => 'vencido_91_120', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '121 o Más', 'field' => 'vencido_121_mas', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $currencyFilter = $this->filters['filter_currency'] ?? null;
    $centrosExcluyentes = $this->filters['filter_centroCosto'] ?? [];

    // Conversión de moneda según currency_id de la factura y filtro
    $conversionCase = "
        CASE
            WHEN transactions.currency_id = 1 AND $currencyFilter = 16
                THEN COALESCE(transactions.totalComprobante,0) * COALESCE(transactions.proforma_change_type,1)
            WHEN transactions.currency_id = 16 AND $currencyFilter = 1
                THEN COALESCE(transactions.totalComprobante,0) / COALESCE(transactions.proforma_change_type,1)
            ELSE COALESCE(transactions.totalComprobante,0)
        END
    ";

    // Abonos/pagos
    $pagosCase = "
        COALESCE((
            SELECT SUM(total_medio_pago)
            FROM transactions_payments
            WHERE transactions_payments.transaction_id = transactions.id
        ),0)
    ";

    $query = Transaction::select([
        'contacts.id as cliente_id',
        'contacts.name as customer_name',

        // Total ajustado
        DB::raw("SUM(($conversionCase) * COALESCE(cc_sum.percent,100)/100) AS totalComprobanteAjustado"),

        // Saldo pendiente (total - pagos)
        DB::raw("SUM((($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)) AS saldoPendiente"),

        // Rangos de vencimiento
        DB::raw("
            SUM(
                CASE WHEN DATEDIFF(NOW(), transactions.transaction_date) - transactions.pay_term_number <= 0
                    THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
                    ELSE 0
                END
            ) AS sin_vencer
        "),
        DB::raw("
            SUM(
                CASE WHEN DATEDIFF(NOW(), transactions.transaction_date) - transactions.pay_term_number BETWEEN 1 AND 30
                    THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
                    ELSE 0
                END
            ) AS vencido_1_30
        "),
        DB::raw("
            SUM(
                CASE WHEN DATEDIFF(NOW(), transactions.transaction_date) - transactions.pay_term_number BETWEEN 31 AND 45
                    THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
                    ELSE 0
                END
            ) AS vencido_31_45
        "),
        DB::raw("
            SUM(
                CASE WHEN DATEDIFF(NOW(), transactions.transaction_date) - transactions.pay_term_number BETWEEN 46 AND 60
                    THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
                    ELSE 0
                END
            ) AS vencido_46_60
        "),
        DB::raw("
            SUM(
                CASE WHEN DATEDIFF(NOW(), transactions.transaction_date) - transactions.pay_term_number BETWEEN 61 AND 90
                    THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
                    ELSE 0
                END
            ) AS vencido_61_90
        "),
        DB::raw("
            SUM(
                CASE WHEN DATEDIFF(NOW(), transactions.transaction_date) - transactions.pay_term_number BETWEEN 91 AND 120
                    THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
                    ELSE 0
                END
            ) AS vencido_91_120
        "),
        DB::raw("
            SUM(
                CASE WHEN DATEDIFF(NOW(), transactions.transaction_date) - transactions.pay_term_number > 120
                    THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
                    ELSE 0
                END
            ) AS vencido_121_mas
        "),
    ])
    ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
    // LEFT JOIN para calcular porcentaje total
    ->leftJoin(
        DB::raw("(SELECT transaction_id, SUM(percent) as percent
                  FROM transactions_commissions
                  GROUP BY transaction_id) AS cc_sum"),
        'cc_sum.transaction_id', '=', 'transactions.id'
    )
    ->whereIn('transactions.proforma_status', ['FACTURADA'])
    ->whereIn('transactions.document_type', ['PR','FE','TE'])
    ->whereNull('transactions.numero_deposito_pago')
    ->groupBy('contacts.id', 'contacts.name')
    ->orderBy('contacts.name');

    // Filtro para excluir transacciones con centros de costo no permitidos
    if (!empty($centrosExcluyentes)) {
        $query->whereNotExists(function ($q) use ($centrosExcluyentes) {
            $q->select(DB::raw(1))
              ->from('transactions_commissions')
              ->whereRaw('transactions_commissions.transaction_id = transactions.id')
              ->whereIn('transactions_commissions.centro_costo_id', $centrosExcluyentes);
        });
    }

    // Otros filtros opcionales
    if (!empty($this->filters['filter_contact'])) {
        $query->where('transactions.contact_id', '=', $this->filters['filter_contact']);
    }

    if (!empty($this->filters['filter_department'])) {
        $query->where('transactions.department_id', '=', $this->filters['filter_department']);
    }

    if (!empty($this->filters['filter_currency'])) {
        $query->where('transactions.currency_id', '=', $this->filters['filter_currency']);
    }

    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);

        if (count($range) === 2) {
            try {
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();
                $query->whereBetween('transactions.transaction_date', [$start, $end]);
            } catch (\Exception $e) {
                // manejar error
            }
        } else {
            try {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));
                $query->whereDate('transactions.transaction_date', $singleDate->format('Y-m-d'));
            } catch (\Exception $e) {
                // manejar error
            }
        }
    }

    return $query;
  }
}
