<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;

class CasoTerceroPrescritoReport extends BaseReport
{
  protected function columns(): array
  {
    $filters = $this->filters ?? [];
    if ($filters['filter_type'] == 'CEFA')
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número de caso', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Cliente', 'field' => 'cliente', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Subsidiaria', 'field' => 'psubsidiaria', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de producto', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Proceso', 'field' => 'proceso', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Nombre del demandado', 'field' => 'pnombre_demandado', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Número de Cédula del demandado', 'field' => 'pnumero_cedula', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Detalle de la Garantía o documento', 'field' => 'pdetalle_garantia', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Despacho judicial juzgado', 'field' => 'pdespacho_judicial_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Número de expediente judicial', 'field' => 'pnumero_expediente_judicial', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Monto de la Demanda', 'field' => 'pmonto_estimacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Inmueble', 'field' => 'pinmueble', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Muebles', 'field' => 'pmueble', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de la demanda', 'field' => 'pfecha_presentacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de asignación', 'field' => 'pfecha_asignacion_caso', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de traslado de juzgado', 'field' => 'nfecha_traslado_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de notificación todas las partes', 'field' => 'nfecha_notificacion_todas_partes', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Última liquidación', 'field' => 'nfecha_ultima_liquidacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de embargo', 'field' => 'afecha_presentacion_embargo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de arreglo de pago', 'field' => 'afecha_arreglo_pago', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Expectativa Recuperación', 'field' => 'expectaviva_recuperacion', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Avance cronológico', 'field' => 'pavance_cronologico', 'type' => 'string', 'align' => 'left', 'width' => 100],
      ['label' => 'Comentarios', 'field' => 'acomentarios', 'type' => 'string', 'align' => 'left', 'width' => 100],
      ['label' => 'Estado Proceso General', 'field' => 'proceso_general', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Tipo expediente', 'field' => 'atipo_expediente', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de pago', 'field' => 'afecha_pago', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Monto que debe cancelar', 'field' => 'amonto_cancelar', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Monto Incobrable', 'field' => 'amonto_incobrable', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Contacto telefónico', 'field' => 'acontacto_telefonico', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Correo electrónico', 'field' => 'acorreo', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Fecha de activación', 'field' => 'fecha_activacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Código de activación', 'field' => 'codigo_activacion', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Usuario que creó el caso', 'field' => 'user_create', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Usuario de última actualización', 'field' => 'user_update', 'type' => 'string', 'align' => 'center', 'width' => 25]
    ];
    else
      return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número de caso', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Cliente', 'field' => 'cliente', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Número de Producto', 'field' => 'pnumero_operacion1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de Producto', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Nombre del Cliente', 'field' => 'pnombre_demandado', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Número de Identificacióm', 'field' => 'pnumero_cedula', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Monto de la demanda', 'field' => 'pmonto_estimacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Buffete', 'field' => 'buffete', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Fecha de asignación', 'field' => 'pfecha_asignacion_caso', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de la demanda', 'field' => 'pfecha_presentacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de traslado', 'field' => 'nfecha_traslado_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Captura', 'field' => 'sfecha_captura', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de notificación todas las partes', 'field' => 'nfecha_notificacion_todas_partes', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Última liquidación', 'field' => 'nfecha_ultima_liquidacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de remate', 'field' => 'sfecha_remate', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de aprobación de remate', 'field' => 'afecha_aprobacion_remate', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de registro', 'field' => 'afecha_registro', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación protocolización', 'field' => 'afecha_protocolizacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de de inscripción', 'field' => 'afecha_inscripcion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de levantamientos', 'field' => 'afecha_levantamiento', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de terminación', 'field' => 'afecha_terminacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de suspensión por arreglo', 'field' => 'afecha_suspencion_arreglo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Justificación casos protoclizado por embargo', 'field' => 'ajustificacion_casos_protocolizados_embargo', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Estado Proceso General', 'field' => 'proceso_general', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Número de expediente judicial', 'field' => 'pnumero_expediente_judicial', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Juzgado', 'field' => 'pdespacho_judicial_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de informe', 'field' => 'pfecha_informe', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Avance cronológico', 'field' => 'pavance_cronologico', 'type' => 'string', 'align' => 'left', 'width' => 100],
      ['label' => 'Comentarios', 'field' => 'acomentarios', 'type' => 'string', 'align' => 'left', 'width' => 100],
      ['label' => 'Tipo expediente', 'field' => 'atipo_expediente', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Reasignaciones', 'field' => 'areasignaciones', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de activación', 'field' => 'fecha_activacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Código de activación', 'field' => 'codigo_activacion', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Usuario que creó el caso', 'field' => 'user_create', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Usuario de última actualización', 'field' => 'user_update', 'type' => 'string', 'align' => 'center', 'width' => 25]
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $PROBABLE = 1;
    $POSIBLE = 2;
    $REMOTA = 3;
    $INCOBRABLE = 4;
    $PRESCRITA = 5;
    $TERMINADA = 6;
    $ARREGLOPAGO = 7;
    $ALTA = 8;
    $BAJA = 9;
    $MEDIA = 10;
    $POCA = 11;

    $expectativas = [
      $PRESCRITA,
    ];

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
        'currencies.code as moneda',
        'product.nombre as producto',
        'proceso.nombre as proceso',
        'casos.pplazo_arreglo_pago',
        'casos.aestado_operacion',
        'pcomprador',
        'psubsidiaria',
        'acomentarios',
        'amonto_cancelar',
        'ames_avance_judicial',
        'acontacto_telefonico',
        'casos_poderdantes.nombre as poderdante',
        'casos_estados_notificaciones.nombre as estado_notificacion',
        'noposicion_demanda',
        'casos.pdespacho_judicial_juzgado',
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
        DB::raw("DATE_FORMAT(casos.afecha_presentacion_embargo, '%d-%m-%Y') AS afecha_presentacion_embargo"),
        DB::raw("DATE_FORMAT(casos.afecha_arreglo_pago, '%d-%m-%Y') AS afecha_arreglo_pago"),
        DB::raw("DATE_FORMAT(casos.afecha_pago, '%d-%m-%Y') AS afecha_pago"),
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
        'pmueble',
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
        DB::raw("DATE_FORMAT(casos.pfecha_asignacion_caso, '%d-%m-%Y') AS pfecha_asignacion_caso"),
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
    ->where('casos.bank_id', Bank::TERCEROS)
    ->whereIn('casos.pexpectativa_recuperacion_id', $expectativas)
    ->with('fechasRemate');

    // --- FILTROS SEGURAMENTE ---
    $filters = $this->filters ?? [];

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

    // Otros filtros simples
    $simpleFilters = [
        'filter_numero_caso' => 'pnumero',
        'filter_abogado' => 'abogado_id',
        'filter_asistente' => 'asistente1_id',
        'filter_banco' => 'bank_id',
        'filter_currency' => 'currency_id',
        'filter_contact' => 'contact_id',
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
