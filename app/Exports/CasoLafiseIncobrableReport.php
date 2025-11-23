<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;

class CasoLafiseIncobrableReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número de caso', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Número de Operación', 'field' => 'pnumero_operacion1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Número de tarjeta', 'field' => 'pnumero_tarjeta', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Apellidos y nombre del deudor', 'field' => 'pnombre_demandado', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Documento de Identificación', 'field' => 'pnumero_cedula', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Nombre de la persona Jurídica', 'field' => 'pnombre_persona_juridica', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Número de cédula jurídica', 'field' => 'pnumero_cedula_juridica', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Datos de los Codeudores', 'field' => 'pdatos_codeudor1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Datos de los Fiadores', 'field' => 'pdatos_codeudor2', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de crédito', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de Proceso', 'field' => 'proceso', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Expectativa de Recuperación', 'field' => 'expectaviva_recuperacion', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Estatus de Operación', 'field' => 'aestado_operacion', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Número de expediente', 'field' => 'pnumero_expediente_judicial', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Despacho judicial', 'field' => 'pdespacho_judicial_juzgado', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Comprador', 'field' => 'pcomprador', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Poderdante', 'field' => 'poderdante', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de ingreso a cobro judicial', 'field' => 'pfecha_ingreso_cobro_judicial', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de escrito de demanda', 'field' => 'pfecha_escrito_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de presentación de la demanda', 'field' => 'pfecha_presentacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de traslado de la demanda', 'field' => 'nfecha_traslado_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de finalización del proceso', 'field' => 'afecha_terminacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de notificación de las partes', 'field' => 'nfecha_notificacion_todas_partes', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estado actual de la primera notificación de los demandados', 'field' => 'estado_notificacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estado_ID', 'field' => 'proceso_general', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Oposición de Demanda', 'field' => 'noposicion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Audiencia', 'field' => 'nfecha_audiencia', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de última acción judicial', 'field' => 'afecha_informe_ultima_gestion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Avances en orden cronológico del proceso', 'field' => 'pavance_cronologico', 'type' => 'string', 'align' => 'left', 'width' => 100],
      ['label' => 'Tipo de Garantía', 'field' => 'ntipo_garantia', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Detalle de la Garantía', 'field' => 'pdetalle_garantia', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Monto Avaluo', 'field' => 'amonto_avaluo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha Avaluo', 'field' => 'afecha_avaluo', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos cuentas', 'field' => 'aembargo_cuentas', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos Salarios', 'field' => 'aembargo_salarios', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos Muebles', 'field' => 'aembargo_muebles', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos Inmuebles', 'field' => 'aembargo_inmuebles', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Retenciones con giro', 'field' => 'aretenciones_con_giro', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de último giro', 'field' => 'afecha_ultimo_giro', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Captura', 'field' => 'sfecha_captura', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => '1° Remate', 'field' => 'remate_1', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => '2° Remate', 'field' => 'remate_2', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => '3° Remate', 'field' => 'remate_3', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de firmeza de aprobación de remate', 'field' => 'afecha_aprobacion_remate', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Bienes Adjudicados', 'field' => 'abienes_adjudicados', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha señalamiento de puesta en posesión', 'field' => 'afecha_senalamiento_puesta_posesion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Puesta en posesión', 'field' => 'apuesta_posesion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Saldo Capital de la Operación', 'field' => 'asaldo_capital_operacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estimación en colones de la Demanda', 'field' => 'pmonto_estimacion_demanda_colones', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estimación en dólares de la Demanda', 'field' => 'pmonto_estimacion_demanda_dolares', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estimación dolarizada de la Demanda', 'field' => 'psaldo_dolarizado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Gastos legales', 'field' => 'agastos_legales', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Honorarios totales', 'field' => 'ahonorarios_totales', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Bufete', 'field' => 'abufete', 'type' => 'string', 'align' => 'center', 'width' => 25],
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
        'pnumero_cedula_juridica',
        'pdatos_codeudor1',
        'pdatos_codeudor2',
        'casos.pdespacho_judicial_juzgado',
        'casos_poderdantes.nombre as poderdante',
        'casos_estados_notificaciones.nombre as estado_notificacion',
        'noposicion_demanda',
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
    ->where('casos.bank_id', Bank::LAFISE)
    ->where('casos.aestado_operacion', '=', 'INCOBRABLE')
    ->with('fechasRemate');

    // --- FILTROS SEGURAMENTE --
    $filters = $this->filters ?? [];

    /*
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
    */

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
