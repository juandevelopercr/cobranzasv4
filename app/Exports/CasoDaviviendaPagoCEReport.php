<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;

class CasoDaviviendaPagoCEReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Fecha de asignación de Caso', 'field' => 'fecha_asignacion_caso', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Mes', 'field' => 'mes', 'type' => 'string', 'align' => 'center', 'width' => 13],
      ['label' => 'Identificación', 'field' => 'pcedula_deudor', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'No. de Operación corta', 'field' => 'pnumero_operacion1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'No. de Operación larga', 'field' => 'pnumero_operacion2', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Ente', 'field' => 'pente', 'type' => 'string', 'align' => 'left', 'width' => 10],
      ['label' => 'Deudor', 'field' => 'pnombre_apellidos_deudor', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Producto', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Estimación de la demanda Colones', 'field' => 'aestimacion_demanda_en_presentacion', 'type' => 'string', 'align' => 'right', 'width' => 20],
      ['label' => 'Estimación de la demanda Dólares', 'field' => 'aestimacion_demanda_en_presentacion_usd', 'type' => 'string', 'align' => 'right', 'width' => 20],
      ['label' => 'Estado Procesal', 'field' => 'proceso_general', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Expediente Judicial', 'field' => 'pnumero_expediente_judicial', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Estado OP (Activo-Incobrable)', 'field' => 'pestatus_operacion', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Fecha de inicio', 'field' => 'pfecha_ingreso_cobro_judicial', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Monto Prima', 'field' => 'pmonto_prima', 'type' => 'string', 'align' => 'center', 'width' => 20],
      ['label' => 'Plazo del Arreglo de Pago', 'field' => 'pplazo_arreglo_pago', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Monto del Arreglo de Pago', 'field' => 'pmonto_arreglo_pago', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto Cuota', 'field' => 'pmonto_cuota', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'No. Cuota', 'field' => 'pno_cuota', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Bufete', 'field' => 'abufete', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Estado de Arreglo', 'field' => 'pestado_arreglo', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Usuario que creó el caso', 'field' => 'user_create', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Usuario de última actualización', 'field' => 'user_update', 'type' => 'string', 'align' => 'center', 'width' => 25]
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $CREDITO_COMERCIAL = 23;
    $CREDITO_CREDIFACIL = 49;
    $CREDITO_CONSUMO = 18;
    $CREDITO_LIBRANZA = 26;
    $CREDITO_PERSONAL = 22;

    $listProductos = [$CREDITO_COMERCIAL, $CREDITO_CREDIFACIL, $CREDITO_CONSUMO, $CREDITO_LIBRANZA, $CREDITO_PERSONAL];

    $query = Caso::query()
    ->select([
        'casos.id',
        'casos.pnumero',
        'c.name as cliente',
        'casos.empresa',
        'pnumero_cedula',
        'pmonto_estimacion_demanda',
        'banks.name as banco',
        'ua.name as abogado',
        'ua1.name as asistente1',
        'ua2.name as asistente2',
        'casos.pnombre_demandado',
        'casos.pnumero_operacion1',
        'casos.pnumero_operacion2',
        'pente',
        'currencies.code as moneda',
        'product.nombre as producto',
        'proceso.nombre as proceso',
        'casos.pplazo_arreglo_pago',
        'casos.aestado_operacion',
        'pcomprador',
        'pcedula_deudor',
        'pnombre_apellidos_deudor',
        'pmonto_prima',
        'pmonto_arreglo_pago',
        'aestimacion_demanda_en_presentacion',
        'aestimacion_demanda_en_presentacion_usd',
        'casos_poderdantes.nombre as poderdante',
        'casos_estados_notificaciones.nombre as estado_notificacion',
        'noposicion_demanda',
        'casos.pestatus_operacion',
        'abufete',
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
        DB::raw("DATE_FORMAT(casos.pfecha_curso_demanda, '%d-%m-%Y') AS pfecha_curso_demanda"),
        DB::raw("DATE_FORMAT(casos.pfecha_primer_giro, '%d-%m-%Y') AS pfecha_primer_giro"),
        DB::raw("DATE_FORMAT(casos.afecha_senalamiento_puesta_posesion, '%d-%m-%Y') AS afecha_senalamiento_puesta_posesion"),
        DB::raw("DATE_FORMAT(casos.afecha_suspencion_arreglo, '%d-%m-%Y') AS afecha_suspencion_arreglo"),
        DB::raw("DATE_FORMAT(casos.pfecha_ingreso_cobro_judicial, '%d-%m-%Y') AS pfecha_ingreso_cobro_judicial"),
        DB::raw("DATE_FORMAT(casos.pfecha_escrito_demanda, '%d-%m-%Y') AS pfecha_escrito_demanda"),
        DB::raw("DATE_FORMAT(casos.nfecha_audiencia, '%d-%m-%Y') AS nfecha_audiencia"),
        DB::raw("DATE_FORMAT(casos.afecha_informe_ultima_gestion, '%d-%m-%Y') AS afecha_informe_ultima_gestion"),
        DB::raw("DATE_FORMAT(casos.afecha_ultimo_giro, '%d-%m-%Y') AS afecha_ultimo_giro"),
        DB::raw("(SELECT DATE_FORMAT(r.fecha, '%d-%m-%Y')
                  FROM casos_fecha_remate r
                  WHERE r.caso_id = casos.id
                  ORDER BY r.fecha ASC
                  LIMIT 1 OFFSET 0) AS remate_1"),

        DB::raw("(SELECT DATE_FORMAT(r.fecha, '%d-%m-%Y')
                  FROM casos_fecha_remate r
                  WHERE r.caso_id = casos.id
                  ORDER BY r.fecha ASC
                  LIMIT 1 OFFSET 1) AS remate_2"),

        DB::raw("(SELECT DATE_FORMAT(r.fecha, '%d-%m-%Y')
                  FROM casos_fecha_remate r
                  WHERE r.caso_id = casos.id
                  ORDER BY r.fecha ASC
                  LIMIT 1 OFFSET 2) AS remate_3"),
        'pmonto_retencion_colones',
        'pmonto_retencion_dolares',
        'pinmueble',
        'pvehiculo',
        'ncomentarios',
        'ajustificacion_casos_protocolizados_embargo',
        'estado.name as proceso_general',
        'pnumero_expediente_judicial',
        'pavance_cronologico',
        'atipo_expediente',
        'areasignaciones',
        'pnumero_tarjeta',
        'pnombre_persona_juridica',
        'casos_expectativas_recuperacion.nombre as expectaviva_recuperacion',
        'ntipo_garantia',
        'pdetalle_garantia',
        'amonto_avaluo',
        'afecha_avaluo',
        'aembargo_cuentas',
        'aembargo_salarios',
        'aembargo_muebles',
        'aembargo_inmuebles',
        'aretenciones_con_giro',
        'abienes_adjudicados',
        'apuesta_posesion',
        'asaldo_capital_operacion',
        'pmonto_estimacion_demanda_colones',
        'pmonto_estimacion_demanda_dolares',
        'psaldo_dolarizado',
        'agastos_legales',
        DB::raw("DATE_FORMAT(casos.fecha_activacion, '%d-%m-%Y') AS fecha_activacion"),
        'codigo_activacion',
        'motivo_terminacion',
        'honorarios_legales_dolares',
        'ahonorarios_totales',
        'user_create',
        'user_update',
        DB::raw("DATE_FORMAT(casos.pfecha_asignacion_caso, '%d-%m-%Y') AS fecha_asignacion_caso"),
        DB::raw("MONTHNAME(casos.pfecha_asignacion_caso) AS mes"),
        DB::raw("DATE_FORMAT(casos.pfecha_ingreso_cobro_judicial, '%d-%m-%Y') AS pfecha_ingreso_cobro_judicial")
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
    ->leftJoin('casos_expectativas_recuperacion', 'casos.pexpectativa_recuperacion_id', '=', 'casos_expectativas_recuperacion.id')
    ->leftJoin('casos_poderdantes', 'casos.ppoderdante_id', '=', 'casos_poderdantes.id')
    ->leftJoin('casos_estados_notificaciones', 'casos.nestado_id', '=', 'casos_estados_notificaciones.id')
    ->join('currencies', 'casos.currency_id', '=', 'currencies.id')
    ->join('banks', 'casos.bank_id', '=', 'banks.id')
    ->where('casos.bank_id', Bank::DAVIVIENDA)
    ->whereIn('casos.product_id', $listProductos)
    ->with('fechasRemate');

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
        'filter_date' => 'pfecha_ingreso_cobro_judicial',
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
