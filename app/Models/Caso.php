<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\CasoEstado;
use App\Models\CasoJuzgado;
use App\Models\CasoProceso;
use App\Models\CasoProducto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Caso extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'casos';

  protected $fillable = [
    'contact_id',
    'bank_id',
    'product_id',
    'currency_id',
    'fecha_creacion',

    // === INTEGER FIELDS ===
    'abogado_id',
    'pexpectativa_recuperacion_id',
    'asistente1_id',
    'asistente2_id',
    'aestado_proceso_general_id',
    'proceso_id',
    'testado_proceso_id',
    'lestado_levantamiento_id',
    'ddespacho_judicial_juzgado_id',
    'bestado_levantamiento_id',
    'ldespacho_judicial_juzgado_id',
    'ppoderdante_id',
    'nestado_id',
    'estado_id',
    'pnumero',

    // === NUMERIC SAFE ===
    'psaldo_de_seguros',
    'psaldo_de_multas',

    'pgastos_legales_caso',
    'pmonto_prima',
    'nhonorarios_notificacion',
    'nhonorarios_cobro_administrativo',
    'thonorarios_traspaso',
    'tgastos_traspaso',
    'tgastos_legales',
    'thonorarios_totales',
    'fhonorarios_levantamiento',
    'fcomision_ccc',
    'fhonorarios_totales',
    'rhonorario_escritura_inscripcion',
    'rgastos_impuestos',
    'dgastos_microfilm',
    'dhonorarios',
    'bhonorarios_levantamiento',
    'bhonorarios_comision',
    'bhonorarios_totales',
    'f1honorarios_capturador',
    'f1honorarios_comision',
    'agastos_mas_honorarios_acumulados',
    'ahonorarios_iniciales',
    'adiferencia_demanda_presentada',
    'adiferencia_sentencia_afavor',
    'adiferencia_sentencia_enfirme',
    'adiferencia_liquidacion_de_sentencia_enfirme',
    'adiferencia_segunda_liquidacion_de_sentencia_enfirme',
    'adiferencia_tercera_liquidacion_de_sentencia_enfirme',
    'adiferencia_cuarta_liquidacion_de_sentencia_enfirme',
    'adiferencia_quinta_liquidacion_de_sentencia_enfirme',
    'adiferencia_sexta_liquidacion_de_sentencia_enfirme',
    'adiferencia_septima_liquidacion_de_sentencia_enfirme',
    'adiferencia_octava_liquidacion_de_sentencia_enfirme',
    'adiferencia_novena_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_primera_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_septima_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_octava_liquidacion_de_sentencia_enfirme',
    'adiferencia_decima_novena_liquidacion_de_sentencia_enfirme',
    'agastos_legales_iniciales',
    'adiferencia_gastos_legales',
    'anumero_grupo',
    'acarga_gastos_legales',
    'pretenciones',
    'pmonto_arreglo_pago',
    'pmonto_cuota',
    'honorarios_legales_dolares',


    // === FECHAS SAFE ===
    'pfecha_pago_multas_y_seguros',
    'nfecha_ultima_liquidacion',
    'pfecha_asignacion_caso',
    'pfecha_presentacion_demanda',
    'nfecha_traslado_juzgado',
    'nfecha_notificacion_todas_partes',
    'sfecha_captura',
    'sfecha_sentencia',
    'sfecha_remate',
    'afecha_aprobacion_remate',
    'afecha_protocolizacion',
    'afecha_senalamiento_puesta_posesion',
    'afecha_registro',
    'afecha_presentacion_protocolizacion',
    'afecha_inscripcion',
    'afecha_terminacion',
    'afecha_suspencion_arreglo',
    'pfecha_curso_demanda',
    'afecha_informe_ultima_gestion',
    'nfecha_notificacion',
    'nfecha_pago',
    'afecha_aprobacion_arreglo',
    'afecha_envio_cotizacion_gasto',
    'tfecha_traspaso',
    'tfecha_envio_borrador_escritura',
    'tfecha_firma_escritura',
    'tfecha_presentacion_escritura',
    'tfecha_comunicacion',
    'tfecha_entrega_titulo_propiedad',
    'tfecha_exclusion',
    'tfecha_terminacion',
    'pfecha_e_instruccion_levantamiento',
    'lfecha_entrega_poder',
    'lfecha_levantamiento_gravamen',
    'lfecha_comunicado_banco',
    'efecha_visita',
    'rfecha_desinscripcion',
    'dfecha_interposicion_denuncia',
    'bfecha_entrega_poder',
    'bfecha_levantamiento_gravamen',
    'f1fecha_asignacion_capturador',
    'f2fecha_publicacion_edicto',
    'pfecha_ingreso_cobro_judicial',
    'pfecha_devolucion_demanda_firma',
    'pfecha_escrito_demanda',
    'sfecha_primer_remate',
    'sfecha_segundo_remate',
    'sfecha_tercer_remate',
    'afecha_firmeza_aprobacion_remate',
    'fecha_activacion',
    'afecha_levantamiento',
    'fecha_importacion',
    'pfecha_informe',
    'pfecha_ultimo_giro',
    'nfecha_entrega_requerimiento_pago',
    'nfecha_entrega_orden_captura',
    'afecha_avaluo',
    'afecha_ultimo_giro',
    'pfecha_primer_giro',
    'fecha_inicio_retenciones',
    'fecha_prescripcion',
    'fecha_pruebas',
    'pultima_gestion_cobro_administrativo',
    'afecha_presentacion_embargo',
    'afecha_arreglo_pago',
    'afecha_pago',
    'nfecha_audiencia',

    // === STRINGS ===
    'pdetalle_garantia',
    'pubicacion_garantia',
    'npartes_notificadas',
    'acolisiones_embargos_anotaciones',
    'ajustificacion_casos_protocolizados_embargo',
    'tiempo_dias',
    'tiempo_annos',
    'pcomentarios_bullet_point',
    'pavance_cronologico',
    'nanotaciones',
    'nubicacion_garantia',
    'ntalleres_situaciones',
    'ncomentarios',
    'acomentarios',
    'aregistro_pago',
    'atraspaso_tercero',
    'ttraspaso_favor_tercero',
    'tborrador_escritura',
    'tautorizacion_tercero',
    'rcausa',
    'dresultado_sentencia',
    'apuesta_posesion',
    'pmonto_retencion_colones',
    'pmonto_retencion_dolares',
    'codigo_alerta',
    'ames_avance_judicial',
    'lavance_cronologico',
    'savance_cronologico',
    'aavance_cronologico',
    'f1avance_cronologico',
    'f2avance_cronologico',
    'navance_cronologico',

    'nombre_cliente',
    'empresa',
    'email_cliente',
    'user_update',
    'acontacto_telefonico',
    'acorreo',
    'aembargo_cuentas',
    'aembargo_salarios',
    'aembargo_muebles',
    'aembargo_inmuebles',
    'ranotacion',
    'rmarchamo_al_dia',
    'rpendiente',
    'nexonerado_cobro',
    'noposicion_demanda',
    'nembargos_cuentas',
    'nembargos_salarios',
    'nembargos_muebles',
    'nembargos_inmuebles',
    'abienes_adjudicados',

    'nmarchamo',
    'pestado_arreglo',
    'codigo_activacion',

    'dcorreo_electronico',
    'pcorreo_demandado_deudor_o_arrendatario',

    'pnumero_operacion2',
    'pnumero_contrato',
    'anumero_placa1',
    'anumero_placa2',
    'anumero_marchamo',
    'atipo_expediente',
    'dnumero_carnet',
    'dnumero_telefonico',
    'pcedula_arrendatario',
    'dnumero_expediente',
    'pcedula_deudor',
    'ptelefono_demandado_deudor_o_arrendatario',
    'pplaca1',
    'pplaca2',
    'pnumero_cedula_juridica',

    'pnombre_contacto_o_arrendatario',
    'pnombre_coarrendatario',
    'pcedula_coarrendatario',
    'pcorreo_coarrendatario',
    'ptelefono_coarrendatario',
    'afirma_legal',
    'areasignaciones',
    'pdepartamento_solicitante',
    'lasesoramiento_formal',
    'lsumaria',
    'lcausa',
    'lproveedores_servicio',
    'pcontrato_leasing',
    'ptitular_contrato',
    'pcedula_titular',
    'egestion_a_realizar',
    'eestado_cliente_gran_tamano',
    'dnombre_notario',
    'destado_casos_con_anotaciones',
    'bapersonamiento_formal',
    'bsumaria',
    'bcausa',
    'bproveedores_servicios',
    'f1proveedor_servicio',
    'f1estado_captura',
    'f2causa_remate',
    'f2publicacion_edicto',
    'f2tiempo_concedido_edicto',
    'f2preclusion_tiempo',
    'f2estado_remanente',
    'pnombre_arrendatario',
    'pnombre_apellidos_deudor',
    'pestatus_operacion',
    'nestado_actual_primera_notificacion',
    'ntipo_garantia',
    'abufete',
    'ajuzgado',
    'aestado_operacion',
    'pnumero_tarjeta',
    'pnombre_persona_juridica',
    'pcomprador',
    'aretenciones_con_giro',
    'pente',
    'pplazo_arreglo_pago',
    'pno_cuota',
    'psubsidiaria',
    'pestadoid',
    'motivo_terminacion',

    'pdatos_codeudor1',
    'pdatos_anotantes',
    'pnumero_cedula',
    'pinmueble',
    'pmueble',
    'pvehiculo',
    'pdatos_fiadores',
    'pnumero_expediente_judicial',
    'pnumero_operacion1',
    'pmonto_estimacion_demanda',
    'pmonto_estimacion_demanda_colones',
    'pmonto_estimacion_demanda_dolares',
    'asaldo_capital_operacion',
    'asaldo_capital_operacion_usd',
    'aestimacion_demanda_en_presentacion',
    'aestimacion_demanda_en_presentacion_usd',
    'liquidacion_intereses_aprobada_crc',
    'liquidacion_intereses_aprobada_usd',
    'agastos_legales',
    'ahonorarios_totales',
    'ahonorarios_totales_usd',
    'amonto_cancelar',
    'amonto_incobrable',
    'amonto_avaluo',
    'psaldo_dolarizado',
    'pnombre_demandado',
    'bgastos_proceso',
    'pdespacho_judicial_juzgado',
    'pdatos_codeudor2'
  ];

  public function bank()
  {
    return $this->belongsTo(Bank::class, 'bank_id');
  }

  public function contacto()
  {
    return $this->belongsTo(Contact::class, 'contact_id');
  }

  public function producto()
  {
    return $this->belongsTo(CasoProducto::class, 'product_id');
  }

  public function proceso()
  {
    return $this->belongsTo(CasoProceso::class, 'proceso_id');
  }

  public function currency()
  {
    return $this->belongsTo(Currency::class, 'currency_id');
  }

  public function abogado()
  {
    return $this->belongsTo(User::class, 'abogado_id');
  }

  public function asistente1()
  {
    return $this->belongsTo(User::class, 'asistente1_id');
  }

  public function asistente2()
  {
    return $this->belongsTo(User::class, 'asistente2_id');
  }

  public function estado()
  {
    return $this->belongsTo(CasoEstado::class, 'estado_id');
  }

  public function estadoProceso()
  {
    return $this->belongsTo(CasoEstado::class, 'testado_proceso_id');
  }

  public function estadoLevantamientoL()
  {
    return $this->belongsTo(CasoEstado::class, 'lestado_levantamiento_id');
  }

  public function estadoLevantamientoB()
  {
    return $this->belongsTo(CasoEstado::class, 'bestado_levantamiento_id');
  }

  public function juzgadoDenuncia()
  {
    return $this->belongsTo(CasoJuzgado::class, 'ddespacho_judicial_juzgado_id');
  }

  public function juzgadoLevantamiento()
  {
    return $this->belongsTo(CasoJuzgado::class, 'ldespacho_judicial_juzgado_id');
  }

  public function expectativaRecuperacion()
  {
    return $this->belongsTo(CasoExpectativa::class, 'pexpectativa_recuperacion_id');
  }

  public function contact()
  {
    return $this->belongsTo(Contact::class);
  }

  // app/Models/Caso.php
  public function fechasRemate()
  {
      return $this->hasMany(CasoFechaRemate::class);
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    $columns = [
      'casos.id',
      'casos.pnumero',
      'casos.pnumero_operacion1',
      'casos.pfecha_asignacion_caso',
      'banks.name as bank_name',
      'casos.pnumero_contrato',
      'casos.pdespacho_judicial_juzgado',
      'casos.pnombre_demandado',
      'casos.pnumero_cedula',
      'casos.pfecha_presentacion_demanda',
      'casos.nfecha_traslado_juzgado',
      'casos.nfecha_notificacion_todas_partes',
      'aestado.name as aestado_proceso_general',
      'casos.fecha_importacion',
      'casos_productos.nombre as producto',
      'casos_procesos.nombre as proceso',
      'u.name as abogado',
      'ua.name as asistente',
      'banks.name as bank_name',
      'contacts.name as contacto',
      'currencies.code as moneda',
      'casos.created_at',
      'casos.updated_at',
    ];

    $query->select($columns)
      ->leftJoin('banks', 'casos.bank_id', '=', 'banks.id')
      ->leftJoin('contacts', 'casos.contact_id', '=', 'contacts.id')
      ->leftJoin('casos_productos', 'casos.product_id', '=', 'casos_productos.id')
      ->leftJoin('casos_procesos', 'casos.proceso_id', '=', 'casos_procesos.id')
      ->leftJoin('currencies', 'casos.currency_id', '=', 'currencies.id')
      ->leftJoin('users as u', 'casos.abogado_id', '=', 'u.id')
      ->leftJoin('users as ua', 'casos.asistente1_id', '=', 'ua.id')
      ->leftJoin('casos_estados as aestado', 'casos.aestado_proceso_general_id', '=', 'aestado.id');

    // 🔹 Filtros adicionales
    if (!empty($filters['filter_pnumero'])) {
      $query->where('casos.pnumero', $filters['filter_pnumero']);
    }

    if (!empty($filters['filter_pnumero_operacion1'])) {
      $query->where('casos.pnumero_operacion1', $filters['filter_pnumero_operacion1']);
    }

    if (!empty($filters['filter_pfecha_asignacion_caso'])) {
      $range = explode(' to ', $filters['filter_pfecha_asignacion_caso']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('casos.pfecha_asignacion_caso', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_pfecha_asignacion_caso'])
                            ->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->where('casos.pfecha_asignacion_caso', '=', (string) $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_banco'])) {
      $query->where('banks.id', $filters['filter_banco']);
    }

    if (!empty($filters['filter_producto'])) {
      $query->where('casos.product_id', $filters['filter_producto']);
    }

    if (!empty($filters['filter_proceso'])) {
      $query->where('casos.proceso_id', $filters['filter_proceso']);
    }

    if (!empty($filters['filter_abogado'])) {
      $query->where('users.id', $filters['filter_abogado']);
    }

    if (!empty($filters['filter_asistente'])) {
      $query->where('users.id', $filters['filter_asistente']);
    }

    if (!empty($filters['filter_pnumero_contrato'])) {
      $query->where('casos.pnumero_contrato', $filters['filter_pnumero_contrato']);
    }

    if (!empty($filters['filter_pdespacho_judicial_juzgado'])) {
      $query->where('casos.pdespacho_judicial_juzgado', 'like', '%' . $filters['filter_pdespacho_judicial_juzgado'] . '%');
    }

    if (!empty($filters['filter_pnombre_demandado'])) {
      $query->where('casos.pnombre_demandado', 'like', '%' . $filters['filter_pnombre_demandado'] . '%');
    }

    if (!empty($filters['filter_pnumero_cedula'])) {
      $query->where('casos.pnumero_cedula', '=', $filters['filter_pnumero_cedula']);
    }

    if (!empty($filters['filter_pfecha_presentacion_demanda'])) {
      $range = explode(' to ', $filters['filter_pfecha_presentacion_demanda']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('casos.pfecha_presentacion_demanda', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_pfecha_presentacion_demanda'])
                            ->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->where('casos.pfecha_presentacion_demanda', '=', (string) $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_nfecha_traslado_juzgado'])) {
      $range = explode(' to ', $filters['filter_nfecha_traslado_juzgado']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('casos.nfecha_traslado_juzgado', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_nfecha_traslado_juzgado'])
                            ->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->where('casos.nfecha_traslado_juzgado', '=', (string) $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_nfecha_notificacion_todas_partes'])) {
      $range = explode(' to ', $filters['filter_nfecha_notificacion_todas_partes']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('casos.nfecha_notificacion_todas_partes', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_nfecha_notificacion_todas_partes'])
                            ->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->where('casos.nfecha_notificacion_todas_partes', '=', (string) $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_aestado_proceso_general_id'])) {
      $query->where('casos.aestado_proceso_general_id', $filters['filter_aestado_proceso_general_id']);
    }


    if (!empty($filters['filter_fecha_importacion'])) {
      $range = explode(' to ', $filters['filter_fecha_importacion']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('casos.fecha_importacion', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_importacion'])
                            ->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->where('casos.fecha_importacion', '=', (string) $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }


    return $query;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';


    $html .= '</div>';
    return $html;
  }
}
