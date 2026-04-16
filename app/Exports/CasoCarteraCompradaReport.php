<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use Illuminate\Support\Facades\DB;

class CasoCarteraCompradaReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Número de caso', 'field' => 'pnumero', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Origen Cartera', 'field' => 'origen_cartera', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Producto', 'field' => 'producto', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Proceso', 'field' => 'proceso', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Número de Operación', 'field' => 'pnumero_operacion1', 'type' => 'string', 'align' => 'left', 'width' => 25],
      ['label' => 'Cliente', 'field' => 'cliente', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Cédula', 'field' => 'pnumero_cedula', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Saldo adeudado', 'field' => 'psaldo_dolarizado', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Moneda', 'field' => 'moneda', 'type' => 'string', 'align' => 'center', 'width' => 10],
      ['label' => 'Expediente', 'field' => 'pnumero_expediente_judicial', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Estado Procesal', 'field' => 'estado_del_proceso', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'F. Asignación', 'field' => 'fecha_asignacion_caso', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'F. Demanda', 'field' => 'pfecha_presentacion_demanda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'F. Curso demanda', 'field' => 'pfecha_curso_demanda', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'F. Notificación', 'field' => 'nfecha_notificacion_todas_partes', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'F. Arreglo P', 'field' => 'afecha_suspencion_arreglo', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Monto Retenc ¢', 'field' => 'pmonto_retencion_colones', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Monto Retenc $', 'field' => 'pmonto_retencion_dolares', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'F. Terminado', 'field' => 'afecha_terminacion', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Comentarios', 'field' => 'ncomentarios', 'type' => 'string', 'align' => 'left', 'width' => 100],
      ['label' => 'Fecha de asignación al notificador', 'field' => 'f1fecha_asignacion_notificador', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Nombre del notificador', 'field' => 'notificador', 'type' => 'string', 'align' => 'center', 'width' => 15],
      ['label' => 'Monto AP', 'field' => 'monto_ap', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Cuota AP', 'field' => 'cuota_ap', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => '% Descuento aplicado', 'field' => 'descuento_aplicado', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Expectativa de recuperación', 'field' => 'expectativa', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
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
        'casos.pdespacho_judicial_juzgado',
        'notificador.nombre as notificador',
        'casos.origen_cartera',
        'casos.psaldo_dolarizado',
        'casos.monto_ap',
        'casos.cuota_ap',
        'casos.descuento_aplicado',
        'casos.expectativa',
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
        'pmonto_retencion_colones',
        'pmonto_retencion_dolares',
        'pinmueble',
        'pvehiculo',
        'ncomentarios',
        DB::raw("'' AS ncomentariosEmpty"),
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
    ->leftJoin('contacts as c', 'casos.contact_id', '=', 'c.id')
    ->leftJoin('users as ua', 'casos.abogado_id', '=', 'ua.id')
    ->leftJoin('users as ua1', 'casos.asistente1_id', '=', 'ua1.id')
    ->leftJoin('users as ua2', 'casos.asistente2_id', '=', 'ua2.id')
    ->leftJoin('casos_productos as product', 'casos.product_id', '=', 'product.id')
    ->leftJoin('casos_procesos as proceso', 'casos.proceso_id', '=', 'proceso.id')
    ->leftJoin('casos_estados as estado', 'casos.aestado_proceso_general_id', '=', 'estado.id')
    ->leftJoin('casos_notificadores as notificador', 'casos.notificador_id', '=', 'notificador.id')
    ->join('currencies', 'casos.currency_id', '=', 'currencies.id')
    ->join('banks', 'casos.bank_id', '=', 'banks.id')
    ->where('casos.bank_id', Bank::CARTERA);

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
