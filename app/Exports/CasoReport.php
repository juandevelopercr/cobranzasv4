<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Caso;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\TransactionCommission;

class CasoReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número de caso', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Banco', 'field' => 'banco', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Abogado', 'field' => 'abogado', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Asistente1', 'field' => 'asistente1', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Asistente2', 'field' => 'asistente2', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Nombre demandado', 'field' => 'pnombre_demandado', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Producto', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Proceso', 'field' => 'proceso', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Número de Operación', 'field' => 'pnumero_operacion1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Fecha de asignación del caso', 'field' => 'fecha_asignacion_caso', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 10],
      ['label' => 'Honorarios USD', 'field' => 'total_honorarios_usd', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Honorarios CRC', 'field' => 'total_honorarios_crc', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
      $query = Caso::query()
        ->select([
            'casos.id',
            'pnumero',
            'banks.name as banco',
            'ua.name as abogado',
            'ua1.name as asistente1',
            'ua2.name as asistente2',
            'pnombre_demandado',
            'pnumero_operacion1',
            'currencies.code as moneda',
            'product.nombre as producto',
            'proceso.nombre as proceso',
            // Fechas formateadas
            DB::raw("DATE_FORMAT(pfecha_asignacion_caso, '%d-%m-%Y') AS fecha_asignacion_caso"),
        ])
        ->addSelect([
          // --- Honorarios CRC ---
          DB::raw("
              COALESCE((
                  SELECT SUM(
                             COALESCE(t.totalHonorarios, 0)
                             + COALESCE(t.totalTax, 0)
                             - COALESCE(t.totalDiscount, 0)
                  )
                  FROM transactions t
                  WHERE t.caso_id = casos.id
                    AND t.proforma_type = 'HONORARIO'
                    AND t.currency_id = 16
                    AND t.document_type in ('PR', 'TE', 'FE')
                    AND t.proforma_status = 'FACTURADA'
              ), 0) AS total_honorarios_crc
          "),

          // --- Honorarios USD ---
          DB::raw("
              COALESCE((
                  SELECT SUM(
                             COALESCE(t.totalHonorarios, 0)
                             + COALESCE(t.totalTax, 0)
                             - COALESCE(t.totalDiscount, 0)
                  )
                  FROM transactions t
                  WHERE t.caso_id = casos.id
                    AND t.proforma_type = 'HONORARIO'
                    AND t.currency_id = 1
                    AND t.document_type in ('PR', 'TE', 'FE')
                    AND t.proforma_status = 'FACTURADA'
              ), 0) AS total_honorarios_usd
          ")
        ])
        ->leftJoin('users as ua', 'abogado_id', '=', 'ua.id')
        ->leftJoin('users as ua1', 'asistente1_id', '=', 'ua1.id')
        ->leftJoin('users as ua2', 'asistente2_id', '=', 'ua2.id')
        ->leftJoin('casos_productos as product', 'casos.product_id', '=', 'product.id')
        ->leftJoin('casos_procesos as proceso', 'casos.proceso_id', '=', 'proceso.id')
        ->join('currencies', 'currency_id', '=', 'currencies.id')
        ->join('banks', 'casos.bank_id', '=', 'banks.id');

        // Filtro especial para rol Asignaciones
    if (auth()->user() && auth()->user()->hasAnyRole(['ASIGNACIONES'])) {
        $query->join(
            'casos_productos_bancos',
            'casos_productos_bancos.bank_id',
            '=',
            'casos.bank_id'
        )
        ->whereColumn(
            'casos_productos_bancos.product_id',
            'casos.product_id'
        )
        ->where('casos.product_id', 78);
    }

    // --- FILTROS SEGURAMENTE ---
    $filters = $this->filters ?? [];

    /*
    if (!empty($filters['filter_numero'])) {
        $query->where('numero', $filters['filter_numero']);
    }
    if (!empty($filters['filter_deudor'])) {
        $deudor = $filters['filter_deudor'];
        if (is_array($deudor)) {
            $query->whereIn('deudor', $deudor);
        } elseif (is_string($deudor)) {
            $query->where('deudor', 'like', "%$deudor%");
        }
    }
     */

    // Filtros de fechas (se asegura formato y rango)
    $dateFields = [
        'filter_date' => 'pfecha_asignacion_caso',
    ];

    foreach ($dateFields as $filterKey => $column) {
        if (!empty($filters[$filterKey])) {
            $range = explode(' to ', $filters[$filterKey]);
            try {
                if (count($range) === 2) {
                    $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                    $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();
                    $query->whereBetween("$column", [$start, $end]);
                } else {
                    $singleDate = Carbon::createFromFormat('d-m-Y', trim($filters[$filterKey]));
                    $query->whereDate("$column", $singleDate->format('Y-m-d'));
                }
            } catch (\Exception $e) {
                // ignorar error si fecha inválida
            }
        }
    }

    // Otros filtros simples
    $simpleFilters = [
        'filter_numero_caso' => 'abogado_cargo_id',
        'filter_abogado' => 'abogado_revisor_id',
        'filter_asistente' => 'casos.bank_id',
        'filter_banco' => 'sucursal',
        'filter_currency' => 'currency_id'
    ];

    foreach ($simpleFilters as $key => $column) {
        if (!empty($filters[$key])) {
            $query->where("$column", $filters[$key]);
        }
    }

    //dd($query->toSql(), $query->getBindings());

    return $query;
  }

}
