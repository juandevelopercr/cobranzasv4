<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;

class CasoDaviviendaFileMasterReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Fecha de asignación de Caso', 'field' => 'fecha_asignacion_caso', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Número de Operación Corta', 'field' => 'pnumero_operacion1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Número de Operación Larga', 'field' => 'pnumero_operacion2', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Apellidos y Nombre del Deudor', 'field' => 'pnombre_apellidos_deudor', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Documento de Identificación', 'field' => 'pcedula_deudor', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Ente', 'field' => 'pente', 'type' => 'string', 'align' => 'left', 'width' => 10],
      ['label' => 'Nombre de la persona Jurídica', 'field' => 'pnombre_persona_juridica', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Número de cédula jurídica', 'field' => 'pnumero_cedula_juridica', 'type' => 'string', 'align' => 'left', 'widt' => 20],
      ['label' => 'Datos de los Codeudores', 'field' => 'pdatos_codeudor1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Datos de los Fiadores', 'field' => 'pdatos_fiadores', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de Crédito', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de Proceso', 'field' => 'proceso', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tipo de Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Expectativa Recuperación', 'field' => 'expectaviva_recuperacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estatus de Operación', 'field' => 'pestatus_operacion', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Número de expediente', 'field' => 'pnumero_expediente_judicial', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Despacho Judicial', 'field' => 'pdespacho_judicial_juzgado', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Comprador', 'field' => 'pcomprador', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Poderdante', 'field' => 'poderdante', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de ultima gestion de Cobro administrativo', 'field' => 'pultima_gestion_cobro_administrativo', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha de ingreso a cobro judicial', 'field' => 'pfecha_ingreso_cobro_judicial', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha devolucion de demanda para firma', 'field' => 'pfecha_devolucion_demanda_firma', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha de presentación de la demanda', 'field' => 'pfecha_presentacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha de curso de la demanda', 'field' => 'pfecha_curso_demanda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Estado actual de la primera notificación de los demandados', 'field' => 'nestado_actual_primera_notificacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estado ID', 'field' => 'proceso_general', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Oposición de Demanda', 'field' => 'noposicion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de Audiencia', 'field' => 'nfecha_audiencia', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Avances en orden cronológico del proceso', 'field' => 'navance_cronologico', 'type' => 'string', 'align' => 'center', 'width' => 60],
      ['label' => 'Fecha de Terminado', 'field' => 'afecha_terminacion', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Tipo de Garantía', 'field' => 'ntipo_garantia', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Detalle de la Garantía', 'field' => 'pdetalle_garantia', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos cuentas', 'field' => 'nembargos_cuentas', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos Salarios', 'field' => 'nembargos_salarios', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos Muebles', 'field' => 'nembargos_muebles', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Embargos Inmuebles', 'field' => 'nembargos_inmuebles', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Capturas', 'field' => 'sfecha_captura', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha de celebración de Primer remate', 'field' => 'sfecha_primer_remate', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha de celebración de Segundo remate', 'field' => 'sfecha_segundo_remate', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha de celebración de Tercer remate', 'field' => 'sfecha_tercer_remate', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Fecha de firmeza de aprobación de remate', 'field' => 'afecha_firmeza_aprobacion_remate', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Bienes Adjudicados', 'field' => 'abienes_adjudicados', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Fecha señalamiento de puesta en posesión', 'field' => 'afecha_senalamiento_puesta_posesion', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Puesta en posesión', 'field' => 'apuesta_posesion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Saldo Capital de la Operación Colones', 'field' => 'asaldo_capital_operacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Saldo Capital de la Operación Dólares', 'field' => 'asaldo_capital_operacion_usd', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estimación de la demanda Colones', 'field' => 'aestimacion_demanda_en_presentacion', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Estimación de la demanda Dólares', 'field' => 'aestimacion_demanda_en_presentacion_usd', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Liquidacion de intereses aprobada Colones', 'field' => 'liquidacion_intereses_aprobada_crc', 'type' => 'decimal', 'align' => 'right', 'width' => 25],
      ['label' => 'Liquidacion de intereses aprobada Dólares', 'field' => 'liquidacion_intereses_aprobada_usd', 'type' => 'decimal', 'align' => 'right', 'width' => 25],
      ['label' => 'Gastos legales', 'field' => 'agastos_legales', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Honorarios totales Dólares', 'field' => 'ahonorarios_totales_usd', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Honorarios totales Colones', 'field' => 'ahonorarios_totales', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Bufete', 'field' => 'abufete', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tiempo en días', 'field' => 'tiempo_dias', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Tiempo en Años', 'field' => 'tiempo_annos', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Retenciones', 'field' => 'pretenciones', 'type' => 'decimal', 'align' => 'left', 'width' => 25],
      ['label' => 'F. Última liquidación', 'field' => 'nfecha_ultima_liquidacion', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Monto Retención Colones', 'field' => 'pmonto_retencion_colones', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto Retención Dólares', 'field' => 'pmonto_retencion_dolares', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Fecha de activación', 'field' => 'fecha_activacion', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Código de activación', 'field' => 'codigo_activacion', 'type' => 'string', 'align' => 'center', 'width' => 25],
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
        'casos.pnumero_operacion2',
        'casos.navance_cronologico',
        'casos.pente',
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
        'casos.pnumero_cedula_juridica',
        'casos.pdatos_codeudor1',
        'casos.pdatos_fiadores',
        'casos.pdespacho_judicial_juzgado',
        'casos.pultima_gestion_cobro_administrativo',
        'casos.nestado_actual_primera_notificacion',
        'casos.nfecha_audiencia',
        'casos.nfecha_audiencia',
        'casos.nembargos_salarios',
        'casos.nembargos_muebles',
        'casos.nembargos_inmuebles',
        'casos.liquidacion_intereses_aprobada_crc',
        'casos.liquidacion_intereses_aprobada_usd',
        'casos.ahonorarios_totales_usd',
        'casos.tiempo_dias',
        'casos.tiempo_annos',
        'casos.pretenciones',
        DB::raw("'BUFETE LACLE' AS buffete"),
        DB::raw("DATE_FORMAT(casos.pfecha_devolucion_demanda_firma, '%d-%m-%Y') AS pfecha_devolucion_demanda_firma"),
        DB::raw("DATE_FORMAT(casos.sfecha_primer_remate, '%d-%m-%Y') AS sfecha_primer_remate"),
        DB::raw("DATE_FORMAT(casos.sfecha_segundo_remate, '%d-%m-%Y') AS sfecha_segundo_remate"),
        DB::raw("DATE_FORMAT(casos.sfecha_tercer_remate, '%d-%m-%Y') AS sfecha_tercer_remate"),
        DB::raw("DATE_FORMAT(casos.afecha_firmeza_aprobacion_remate, '%d-%m-%Y') AS afecha_firmeza_aprobacion_remate"),
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
        'asaldo_capital_operacion_usd',
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
