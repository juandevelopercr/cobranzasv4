<?php

namespace App\Models;

use App\Models\CasoEstado;
use App\Models\CasoJuzgado;
use App\Models\CasoProceso;
use App\Models\CasoProducto;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caso extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'casos';

  protected $fillable = [
    'pnumero',
    'contact_id',
    'bank_id',
    'product_id',
    'proceso_id',
    'currency_id',
    'pnombre_apellidos_deudor',
    'pcedula_deudor',
    'psaldo_dolarizado',
    'psaldo_de_seguros',
    'psaldo_de_multas',
    'pfecha_pago_multas_y_seguros',
    'pfecha_asignacion_caso',
    'abogado_id',
    'asistente1_id',
    'asistente2_id',
    'pnumero_operacion1',
    'pnumero_operacion2',
    'pnumero_contrato',
    'pnombre_demandado',
    'pnumero_cedula',
    'pnombre_arrendatario',
    'pcedula_arrendatario',
    'pcorreo_demandado_deudor_o_arrendatario',
    'ptelefono_demandado_deudor_o_arrendatario',
    'pnombre_contacto_o_arrendatario',
    'pnombre_coarrendatario',
    'pcedula_coarrendatario',
    'pcorreo_coarrendatario',
    'ptelefono_coarrendatario',
    'pdatos_codeudor1',
    'pdatos_codeudor2',
    'pdatos_anotantes',
    'pdetalle_garantia',
    'pubicacion_garantia',
    'pfecha_presentacion_demanda',
    'pdespacho_judicial_juzgado',
    'pnumero_expediente_judicial',
    'pmonto_estimacion_demanda',
    'pexpectativa_recuperacion_id',
    'pgastos_legales_caso',
    'pcomentarios_bullet_point',
    'pplaca1',
    'pplaca2',
    'pdepartamento_solicitante',
    'pfecha_e_instruccion_levantamiento',
    'pcontrato_leasing',
    'ptitular_contrato',
    'pcedula_titular',
    'pestatus_operacion',
    'ppoderdante_id',
    'pfecha_ingreso_cobro_judicial',
    'pfecha_escrito_demanda',
    'nfecha_traslado_juzgado',
    'nfecha_notificacion_todas_partes',
    'npartes_notificadas',
    'sfecha_captura',
    'sfecha_sentencia',
    'sfecha_remate',
    'sfecha_primer_remate',
    'sfecha_segundo_remate',
    'sfecha_tercer_remate',
    'afecha_aprobacion_remate',
    'afecha_protocolizacion',
    'afecha_senalamiento_puesta_posesion',
    'apuesta_posesion',
    'agastos_legales',
    'ahonorarios_totales',
    'anumero_placa1',
    'anumero_placa2',
    'acolisiones_embargos_anotaciones',
    'anumero_marchamo',
    'afirma_legal',
    'afecha_registro',
    'afecha_presentacion_protocolizacion',
    'afecha_inscripcion',
    'afecha_terminacion',
    'afecha_suspencion_arreglo',
    'ajustificacion_casos_protocolizados_embargo',
    'aestado_proceso_general_id',
    'afecha_informe_ultima_gestion',
    'atipo_expediente',
    'areasignaciones',
    'nmarchamo',
    'nanotaciones',
    'nubicacion_garantia',
    'ntalleres_situaciones',
    'nfecha_notificacion',
    'ncomentarios',
    'nhonorarios_notificacion',
    'nhonorarios_cobro_administrativo',
    'nexonerado_cobro',
    'nfecha_pago',
    'nestado_actual_primera_notificacion',
    'noposicion_demanda',
    'nfecha_audiencia',
    'ntipo_garantia',
    'nembargos_cuentas',
    'nembargos_salarios',
    'nembargos_muebles',
    'nembargos_inmuebles',
    'nestado_id',
    'afecha_aprobacion_arreglo',
    'acomentarios',
    'aregistro_pago',
    'afecha_envio_cotizacion_gasto',
    'atraspaso_tercero',
    'tfecha_traspaso',
    'thonorarios_traspaso',
    'tgastos_traspaso',
    'ttraspaso_favor_tercero',
    'tfecha_envio_borrador_escritura',
    'tborrador_escritura',
    'tfecha_firma_escritura',
    'tfecha_presentacion_escritura',
    'tfecha_comunicacion',
    'tautorizacion_tercero',
    'tfecha_entrega_titulo_propiedad',
    'tfecha_exclusion',
    'tfecha_terminacion',
    'tgastos_legales',
    'thonorarios_totales',
    'lasesoramiento_formal',
    'lfecha_entrega_poder',
    'lsumaria',
    'lcausa',
    'lfecha_levantamiento_gravamen',
    'lfecha_comunicado_banco',
    'lproveedores_servicio',
    'fhonorarios_levantamiento',
    'fcomision_ccc',
    'fhonorarios_totales',
    'efecha_visita',
    'egestion_a_realizar',
    'eestado_cliente_gran_tamano',
    'ranotacion',
    'rmarchamo_al_dia',
    'rpendiente',
    'rcausa',
    'rfecha_desinscripcion',
    'rhonorario_escritura_inscripcion',
    'rgastos_impuestos',
    'dnombre_notario',
    'dnumero_carnet',
    'dcorreo_electronico',
    'dnumero_telefonico',
    'destado_casos_con_anotaciones',
    'dfecha_interposicion_denuncia',
    'dnumero_expediente',
    'dresultado_sentencia',
    'dgastos_microfilm',
    'dhonorarios',
    'bapersonamiento_formal',
    'bfecha_entrega_poder',
    'bsumaria',
    'bcausa',
    'bfecha_levantamiento_gravamen',
    'bproveedores_servicios',
    'bgastos_proceso',
    'bhonorarios_levantamiento',
    'bhonorarios_comision',
    'bhonorarios_totales',
    'f1fecha_asignacion_capturador',
    'f1proveedor_servicio',
    'f1estado_captura',
    'f1honorarios_capturador',
    'f1honorarios_comision',
    'f2causa_remate',
    'f2publicacion_edicto',
    'f2fecha_publicacion_edicto',
    'f2tiempo_concedido_edicto',
    'f2preclusion_tiempo',
    'f2estado_remanente',
    'afecha_firmeza_aprobacion_remate',
    'abienes_adjudicados',
    'asaldo_capital_operacion',
    'aestimacion_demanda_en_presentacion',
    'abufete',
    'acarga_gastos_legales',
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
    'ajuzgado',
    'aestado_operacion',
    'pretenciones',
    'pfecha_ultimo_giro',
    'nfecha_entrega_requerimiento_pago',
    'nfecha_entrega_orden_captura',
    'testado_proceso_id',
    'lestado_levantamiento_id',
    'bestado_levantamiento_id',
    'ddespacho_judicial_juzgado_id',
    'ldespacho_judicial_juzgado_id',
    'afecha_levantamiento',
    'pfecha_informe',
    'pnumero_tarjeta',
    'pnombre_persona_juridica',
    'pnumero_cedula_juridica',
    'pcomprador',
    'amonto_avaluo',
    'afecha_avaluo',
    'aembargo_cuentas',
    'aembargo_salarios',
    'aembargo_muebles',
    'aembargo_inmuebles',
    'aretenciones_con_giro',
    'afecha_ultimo_giro',
    'pmonto_estimacion_demanda_colones',
    'pmonto_estimacion_demanda_dolares',
    'pfecha_curso_demanda',
    'pfecha_primer_giro',
    'pmonto_retencion_colones',
    'pmonto_retencion_dolares',
    'pinmueble',
    'pvehiculo',
    'pente',
    'pmonto_prima',
    'pplazo_arreglo_pago',
    'pmonto_arreglo_pago',
    'pmonto_cuota',
    'pestado_arreglo',
    'pno_cuota',
    'pdatos_fiadores',
    'fecha_creacion',
    'psubsidiaria',
    'afecha_presentacion_embargo',
    'afecha_arreglo_pago',
    'afecha_pago',
    'amonto_cancelar',
    'amonto_incobrable',
    'acontacto_telefonico',
    'acorreo',
    'pmueble',
    'pestadoid',
    'ames_avance_judicial',
    'pavance_cronologico',
    'lavance_cronologico',
    'savance_cronologico',
    'aavance_cronologico',
    'f1avance_cronologico',
    'f2avance_cronologico',
    'navance_cronologico',
    'fecha_importacion',
    'nombre_cliente',
    'email_cliente',
    'nfecha_ultima_liquidacion',
    'fecha_activacion',
    'codigo_activacion',
    'user_create',
    'user_update',
    'pultima_gestion_cobro_administrativo',
    'pfecha_devolucion_demanda_firma',
    'estado_id',
    'asaldo_capital_operacion_usd',
    'aestimacion_demanda_en_presentacion_usd',
    'liquidacion_intereses_aprobada_crc',
    'liquidacion_intereses_aprobada_usd',
    'ahonorarios_totales_usd',
    'tiempo_dias',
    'tiempo_annos',
    'empresa',
    'fecha_inicio_retenciones',
    'fecha_prescripcion',
    'fecha_pruebas',
    'motivo_terminacion',
    'honorarios_legales_dolares',
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
    if (!empty($filters['filter_banco'])) {
      $query->where('banks.id', $filters['filter_banco']);
    }

    if (!empty($filters['filter_contacto'])) {
      $query->where('contacts.id', $filters['filter_contacto']);
    }

    if (!empty($filters['filter_abogado'])) {
      $query->where('users.id', $filters['filter_abogado']);
    }

    if (!empty($filters['filter_estado'])) {
      $query->where('estados.id', $filters['filter_estado']);
    }

    if (!empty($filters['filter_estado_proceso'])) {
      $query->where('estado_procesos.id', $filters['filter_estado_proceso']);
    }

    if (!empty($filters['filter_fecha_inicio'])) {
      $query->whereDate('casos.created_at', '>=', $filters['filter_fecha_inicio']);
    }

    if (!empty($filters['filter_fecha_fin'])) {
      $query->whereDate('casos.created_at', '<=', $filters['filter_fecha_fin']);
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
