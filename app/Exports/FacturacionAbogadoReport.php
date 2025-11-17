<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FacturacionAbogadoReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'abogado', 'field' => 'abogado', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Total Dólares', 'field' => 'totalComprobanteUSD', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Total Colones', 'field' => 'totalComprobanteCRC', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '', 'field' => '', 'type' => 'string', 'align' => 'right', 'width' => 100],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Transaction::withoutGlobalScopes()
        ->from('transactions as t')
        ->selectRaw("
            u.name as abogado,
            -- USD
            SUM(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    WHEN t.currency_id = 1 THEN COALESCE(t.totalComprobante,0)
                    ELSE 0
                END
            ) AS totalComprobanteUSD,
            -- CRC
            SUM(
                CASE
                    WHEN t.proforma_status = 'ANULADA' THEN 0
                    WHEN t.currency_id = 16 THEN COALESCE(t.totalComprobante,0)
                    ELSE 0
                END
            ) AS totalComprobanteCRC
        ")
        ->join('casos as c', 't.caso_id', '=', 'c.id')
        ->join('users as u', 'c.abogado_id', '=', 'u.id')
        ->whereNull('t.deleted_at')
        ->whereIn('t.document_type', ['PR','FE','TE'])
        ->whereIn('t.proforma_status', ['FACTURADA','ANULADA'])
        ->groupBy('u.id', 'u.name')
        ->orderBy('u.name', 'ASC');


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

    if (!empty($this->filters['filter_abogado'])) {
      $query->where('u.id', '=', $this->filters['filter_abogado']);
    }

    return $query;
  }

}
