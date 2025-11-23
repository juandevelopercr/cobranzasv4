<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;

class CasoScotiabankReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número de caso', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Número de Operación', 'field' => 'pnumero_operacion1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de Producto', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Nombre del Cliente', 'field' => 'pnombre_demandado', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Número de Identificación', 'field' => 'pnumero_cedula', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Monto de la Demanda', 'field' => 'pmonto_estimacion_demanda', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Buffete', 'field' => 'buffete', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha de asignación del caso', 'field' => 'fecha_asignacion_caso', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación demanda', 'field' => 'pfecha_presentacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de traslado', 'field' => 'nfecha_traslado_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de captura', 'field' => 'sfecha_captura', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de notificación todas las partes', 'field' => 'nfecha_notificacion_todas_partes', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'F. Última liquidación', 'field' => 'nfecha_ultima_liquidacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de inicio de retenciones', 'field' => 'fecha_inicio_retenciones', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Prescripción', 'field' => 'fecha_prescripcion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Pruebas', 'field' => 'fecha_pruebas', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Remate', 'field' => 'sfecha_remate', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de aprobación de remate', 'field' => 'afecha_aprobacion_remate', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de registro', 'field' => 'afecha_registro', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación protocolización', 'field' => 'afecha_protocolizacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de inscripción', 'field' => 'afecha_inscripcion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de levantamientos', 'field' => 'afecha_levantamiento', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de terminación', 'field' => 'afecha_terminacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de suspensión por arreglo', 'field' => 'afecha_aprobacion_arreglo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Justificación casos protocolizado por embargo', 'field' => 'ajustificacion_casos_protocolizados_embargo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estado proceso general', 'field' => 'proceso_general', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Número expediente judicial', 'field' => 'pnumero_expediente_judicial', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Juzgado', 'field' => 'pdespacho_judicial_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de informe', 'field' => 'pfecha_informe', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Comentarios', 'field' => 'pavance_cronologico', 'type' => 'string', 'align' => 'left', 'width' => 200],
      ['label' => 'Tipo exp', 'field' => 'atipo_expediente', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Reasignaciones', 'field' => 'areasignaciones', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de activación', 'field' => 'fecha_activacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Código de activación', 'field' => 'codigo_activacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Motivo de Terminación', 'field' => 'motivo_terminacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Honorarios Legales Dólares', 'field' => 'honorarios_legales_dolares', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Honorarios Legales Colones', 'field' => 'ahonorarios_totales', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Usuario que creó el caso', 'field' => 'user_create', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Usuario de última actualización', 'field' => 'user_update', 'type' => 'string', 'align' => 'center', 'width' => 25]
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Caso::query()
    ->select([
        'casos.id',
        'casos.pnumero',
        'c.name as cliente',
        'pnumero_cedula',
        'pmonto_estimacion_demanda',
        'banks.name as banco',
        'ua.name as abogado',
        'ua1.name as asistente1',
        'ua2.name as asistente2',
        'casos.pnombre_demandado',
        'casos.pnumero_operacion1',
        'casos.pdespacho_judicial_juzgado',
        DB::raw("
            CASE
                WHEN currencies.code = 'USD' THEN 2
                ELSE 1
            END AS moneda
        "),
        'product.nombre as producto',
        'proceso.nombre as proceso',
        DB::raw("'BUFETE LACLE' AS buffete"),
        DB::raw("DATE_FORMAT(casos.pfecha_presentacion_demanda, '%d-%m-%Y') AS pfecha_presentacion_demanda"),
        DB::raw("DATE_FORMAT(casos.nfecha_traslado_juzgado, '%d-%m-%Y') AS nfecha_traslado_juzgado"),
        DB::raw("DATE_FORMAT(casos.sfecha_captura, '%d-%m-%Y') AS sfecha_captura"),
        DB::raw("DATE_FORMAT(casos.nfecha_notificacion_todas_partes, '%d-%m-%Y') AS nfecha_notificacion_todas_partes"),
        DB::raw("DATE_FORMAT(casos.nfecha_ultima_liquidacion, '%d-%m-%Y') AS nfecha_ultima_liquidacion"),
        DB::raw("DATE_FORMAT(casos.fecha_inicio_retenciones, '%d-%m-%Y') AS fecha_inicio_retenciones"),
        DB::raw("DATE_FORMAT(casos.fecha_prescripcion, '%d-%m-%Y') AS fecha_prescripcion"),
        DB::raw("DATE_FORMAT(casos.fecha_pruebas, '%d-%m-%Y') AS fecha_pruebas"),
        DB::raw("DATE_FORMAT(casos.sfecha_remate, '%d-%m-%Y') AS sfecha_remate"),
        DB::raw("DATE_FORMAT(casos.afecha_aprobacion_remate, '%d-%m-%Y') AS afecha_aprobacion_remate"),
        DB::raw("DATE_FORMAT(casos.afecha_registro, '%d-%m-%Y') AS afecha_registro"),
        DB::raw("DATE_FORMAT(casos.afecha_protocolizacion, '%d-%m-%Y') AS afecha_protocolizacion"),
        DB::raw("DATE_FORMAT(casos.afecha_inscripcion, '%d-%m-%Y') AS afecha_inscripcion"),
        DB::raw("DATE_FORMAT(casos.afecha_levantamiento, '%d-%m-%Y') AS afecha_levantamiento"),
        DB::raw("DATE_FORMAT(casos.afecha_terminacion, '%d-%m-%Y') AS afecha_terminacion"),
        DB::raw("DATE_FORMAT(casos.afecha_aprobacion_arreglo, '%d-%m-%Y') AS afecha_aprobacion_arreglo"),
        DB::raw("DATE_FORMAT(casos.pfecha_informe, '%d-%m-%Y') AS pfecha_informe"),
        'ajustificacion_casos_protocolizados_embargo',
        'estado.name as proceso_general',
        'pnumero_expediente_judicial',
        'pavance_cronologico',
        'atipo_expediente',
        'areasignaciones',
        DB::raw("DATE_FORMAT(casos.fecha_activacion, '%d-%m-%Y') AS fecha_activacion"),
        'codigo_activacion',
        'motivo_terminacion',
        'honorarios_legales_dolares',
        'ahonorarios_totales',
        'user_create',
        'user_update',
        DB::raw("DATE_FORMAT(casos.pfecha_asignacion_caso, '%d-%m-%Y') AS fecha_asignacion_caso"),
    ])
    // --- Honorarios CRC ---
    ->addSelect(DB::raw("
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
              AND t.document_type IN ('PR', 'TE', 'FE')
              AND t.proforma_status = 'FACTURADA'
        ), 0) AS total_honorarios_crc
    "))

    // --- Honorarios USD ---
    ->addSelect(DB::raw("
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
              AND t.document_type IN ('PR', 'TE', 'FE')
              AND t.proforma_status = 'FACTURADA'
        ), 0) AS total_honorarios_usd
    "))
    ->leftJoin('contacts as c', 'casos.contact_id', '=', 'c.id')
    ->leftJoin('users as ua', 'casos.abogado_id', '=', 'ua.id')
    ->leftJoin('users as ua1', 'casos.asistente1_id', '=', 'ua1.id')
    ->leftJoin('users as ua2', 'casos.asistente2_id', '=', 'ua2.id')
    ->leftJoin('casos_productos as product', 'casos.product_id', '=', 'product.id')
    ->leftJoin('casos_procesos as proceso', 'casos.proceso_id', '=', 'proceso.id')
    ->leftJoin('casos_estados as estado', 'casos.aestado_proceso_general_id', '=', 'estado.id')
    ->join('currencies', 'casos.currency_id', '=', 'currencies.id')
    ->join('banks', 'casos.bank_id', '=', 'banks.id')
    ->where('casos.bank_id', Bank::SCOTIABANKCR);


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
        'filter_numero_caso' => 'pnumero',
        'filter_abogado' => 'abogado_id',
        'filter_asistente' => 'asistente1_id',
        'filter_banco' => 'bank_id',
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
