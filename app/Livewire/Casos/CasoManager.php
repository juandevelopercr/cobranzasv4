<?php

namespace App\Livewire\Casos;

use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\Caso;
use App\Models\CasoEstado;
use App\Models\Currency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CasoManager extends BaseComponent
{
  use WithFileUploads, WithPagination;

  // ====== Parámetros de tabla/listado ======
  #[Url(as: 'c1_search', history: true)]
  public $search = '';

  #[Url(as: 'c1_active', history: true)]
  public $active = '';

  #[Url(as: 'c1_sortBy', history: true)]
  public $sortBy = 'casos.id';

  #[Url(as: 'c1_sortDir', history: true)]
  public $sortDir = 'DESC';

  #[Url(as: 'c1_perPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';
  public $closeForm = false;
  public $columns;
  public $defaultColumns;

  public $document_type = 'CASO';

  // ====== Catálogos/auxiliares ======
  public $banks      = [];
  public $currencies = [];
  public $abogados   = [];
  public $asistentes = [];
  public $estados    = [];
  public $productos  = [];
  public $procesos   = [];
  public $clientes   = [];
  public $expectativas   = [];
  public $juzgados = [];

  // =========  TODAS LAS COLUMNAS DE la tabla `casos`  =========
  public $contact_id;
  public $bank_id;
  public $product_id;
  public $currency_id;
  public $fecha_creacion;

  // === INTEGER FIELDS ===
  public $abogado_id;
  public $pexpectativa_recuperacion_id;
  public $asistente1_id;
  public $asistente2_id;
  public $aestado_proceso_general_id;
  public $proceso_id;
  public $testado_proceso_id;
  public $lestado_levantamiento_id;
  public $ddespacho_judicial_juzgado_id;
  public $bestado_levantamiento_id;
  public $ldespacho_judicial_juzgado_id;
  public $ppoderdante_id;
  public $nestado_id;
  public $estado_id;
  public $pnumero;

  // === NUMERIC SAFE ===
  public $psaldo_de_seguros;
  public $psaldo_de_multas;

  public $pgastos_legales_caso;
  public $pmonto_prima;
  public $nhonorarios_notificacion;
  public $nhonorarios_cobro_administrativo;
  public $thonorarios_traspaso;
  public $tgastos_traspaso;
  public $tgastos_legales;
  public $thonorarios_totales;
  public $fhonorarios_levantamiento;
  public $fcomision_ccc;
  public $fhonorarios_totales;
  public $rhonorario_escritura_inscripcion;
  public $rgastos_impuestos;
  public $dgastos_microfilm;
  public $dhonorarios;
  public $bhonorarios_levantamiento;
  public $bhonorarios_comision;
  public $bhonorarios_totales;
  public $f1honorarios_capturador;
  public $f1honorarios_comision;
  public $agastos_mas_honorarios_acumulados;
  public $ahonorarios_iniciales;
  public $adiferencia_demanda_presentada;
  public $adiferencia_sentencia_afavor;
  public $adiferencia_sentencia_enfirme;
  public $adiferencia_liquidacion_de_sentencia_enfirme;
  public $adiferencia_segunda_liquidacion_de_sentencia_enfirme;
  public $adiferencia_tercera_liquidacion_de_sentencia_enfirme;
  public $adiferencia_cuarta_liquidacion_de_sentencia_enfirme;
  public $adiferencia_quinta_liquidacion_de_sentencia_enfirme;
  public $adiferencia_sexta_liquidacion_de_sentencia_enfirme;
  public $adiferencia_septima_liquidacion_de_sentencia_enfirme;
  public $adiferencia_octava_liquidacion_de_sentencia_enfirme;
  public $adiferencia_novena_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_primera_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_septima_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_octava_liquidacion_de_sentencia_enfirme;
  public $adiferencia_decima_novena_liquidacion_de_sentencia_enfirme;
  public $agastos_legales_iniciales;
  public $adiferencia_gastos_legales;
  public $anumero_grupo;
  public $acarga_gastos_legales;
  public $pretenciones;
  public $pmonto_arreglo_pago;
  public $pmonto_cuota;
  public $honorarios_legales_dolares;


  // === FECHAS SAFE ===
  public $pfecha_pago_multas_y_seguros;
  public $nfecha_ultima_liquidacion;
  public $pfecha_asignacion_caso;
  public $pfecha_presentacion_demanda;
  public $nfecha_traslado_juzgado;
  public $nfecha_notificacion_todas_partes;
  public $sfecha_captura;
  public $sfecha_sentencia;
  public $sfecha_remate;
  public $afecha_aprobacion_remate;
  public $afecha_protocolizacion;
  public $afecha_senalamiento_puesta_posesion;
  public $afecha_registro;
  public $afecha_presentacion_protocolizacion;
  public $afecha_inscripcion;
  public $afecha_terminacion;
  public $afecha_suspencion_arreglo;
  public $pfecha_curso_demanda;
  public $afecha_informe_ultima_gestion;
  public $nfecha_notificacion;
  public $nfecha_pago;
  public $afecha_aprobacion_arreglo;
  public $afecha_envio_cotizacion_gasto;
  public $tfecha_traspaso;
  public $tfecha_envio_borrador_escritura;
  public $tfecha_firma_escritura;
  public $tfecha_presentacion_escritura;
  public $tfecha_comunicacion;
  public $tfecha_entrega_titulo_propiedad;
  public $tfecha_exclusion;
  public $tfecha_terminacion;
  public $pfecha_e_instruccion_levantamiento;
  public $lfecha_entrega_poder;
  public $lfecha_levantamiento_gravamen;
  public $lfecha_comunicado_banco;
  public $efecha_visita;
  public $rfecha_desinscripcion;
  public $dfecha_interposicion_denuncia;
  public $bfecha_entrega_poder;
  public $bfecha_levantamiento_gravamen;
  public $f1fecha_asignacion_capturador;
  public $f2fecha_publicacion_edicto;
  public $pfecha_ingreso_cobro_judicial;
  public $pfecha_devolucion_demanda_firma;
  public $pfecha_escrito_demanda;
  public $sfecha_primer_remate;
  public $sfecha_segundo_remate;
  public $sfecha_tercer_remate;
  public $afecha_firmeza_aprobacion_remate;
  public $fecha_activacion;
  public $afecha_levantamiento;
  public $fecha_importacion;
  public $pfecha_informe;
  public $pfecha_ultimo_giro;
  public $nfecha_entrega_requerimiento_pago;
  public $nfecha_entrega_orden_captura;
  public $afecha_avaluo;
  public $afecha_ultimo_giro;
  public $pfecha_primer_giro;
  public $fecha_inicio_retenciones;
  public $fecha_prescripcion;
  public $fecha_pruebas;
  public $pultima_gestion_cobro_administrativo;
  public $afecha_presentacion_embargo;
  public $afecha_arreglo_pago;
  public $afecha_pago;
  public $nfecha_audiencia;

  // === STRINGS ===
  public $pdetalle_garantia;
  public $pubicacion_garantia;
  public $npartes_notificadas;
  public $acolisiones_embargos_anotaciones;
  public $ajustificacion_casos_protocolizados_embargo;
  public $tiempo_dias;
  public $tiempo_annos;
  public $pcomentarios_bullet_point;
  public $pavance_cronologico;
  public $nanotaciones;
  public $nubicacion_garantia;
  public $ntalleres_situaciones;
  public $ncomentarios;
  public $acomentarios;
  public $aregistro_pago;
  public $atraspaso_tercero;
  public $ttraspaso_favor_tercero;
  public $tborrador_escritura;
  public $tautorizacion_tercero;
  public $rcausa;
  public $dresultado_sentencia;
  public $apuesta_posesion;
  public $pmonto_retencion_colones;
  public $pmonto_retencion_dolares;
  public $codigo_alerta;
  public $ames_avance_judicial;
  public $lavance_cronologico;
  public $savance_cronologico;
  public $aavance_cronologico;
  public $f1avance_cronologico;
  public $f2avance_cronologico;
  public $navance_cronologico;

  public $nombre_cliente;
  public $empresa;
  public $email_cliente;
  public $user_update;
  public $acontacto_telefonico;
  public $acorreo;
  public $aembargo_cuentas;
  public $aembargo_salarios;
  public $aembargo_muebles;
  public $aembargo_inmuebles;
  public $ranotacion;
  public $rmarchamo_al_dia;
  public $rpendiente;
  public $nexonerado_cobro;
  public $noposicion_demanda;
  public $nembargos_cuentas;
  public $nembargos_salarios;
  public $nembargos_muebles;
  public $nembargos_inmuebles;
  public $abienes_adjudicados;

  public $nmarchamo;
  public $pestado_arreglo;
  public $codigo_activacion;

  public $dcorreo_electronico;
  public $pcorreo_demandado_deudor_o_arrendatario;

  public $pnumero_operacion2;
  public $pnumero_contrato;
  public $anumero_placa1;
  public $anumero_placa2;
  public $anumero_marchamo;
  public $atipo_expediente;
  public $dnumero_carnet;
  public $dnumero_telefonico;
  public $pcedula_arrendatario;
  public $dnumero_expediente;
  public $pcedula_deudor;
  public $ptelefono_demandado_deudor_o_arrendatario;
  public $pplaca1;
  public $pplaca2;
  public $pnumero_cedula_juridica;

  public $pnombre_contacto_o_arrendatario;
  public $pnombre_coarrendatario;
  public $pcedula_coarrendatario;
  public $pcorreo_coarrendatario;
  public $ptelefono_coarrendatario;
  public $afirma_legal;
  public $areasignaciones;
  public $pdepartamento_solicitante;
  public $lasesoramiento_formal;
  public $lsumaria;
  public $lcausa;
  public $lproveedores_servicio;
  public $pcontrato_leasing;
  public $ptitular_contrato;
  public $pcedula_titular;
  public $egestion_a_realizar;
  public $eestado_cliente_gran_tamano;
  public $dnombre_notario;
  public $destado_casos_con_anotaciones;
  public $bapersonamiento_formal;
  public $bsumaria;
  public $bcausa;
  public $bproveedores_servicios;
  public $f1proveedor_servicio;
  public $f1estado_captura;
  public $f2causa_remate;
  public $f2publicacion_edicto;
  public $f2tiempo_concedido_edicto;
  public $f2preclusion_tiempo;
  public $f2estado_remanente;
  public $pnombre_arrendatario;
  public $pnombre_apellidos_deudor;
  public $pestatus_operacion;
  public $nestado_actual_primera_notificacion;
  public $ntipo_garantia;
  public $abufete;
  public $ajuzgado;
  public $aestado_operacion;
  public $pnumero_tarjeta;
  public $pnombre_persona_juridica;
  public $pcomprador;
  public $aretenciones_con_giro;
  public $pente;
  public $pplazo_arreglo_pago;
  public $pno_cuota;
  public $psubsidiaria;
  public $pestadoid;
  public $motivo_terminacion;

  public $pdatos_codeudor1;
  public $pdatos_codeudor2;
  public $pdatos_anotantes;
  public $pnumero_cedula;
  public $pinmueble;
  public $pmueble;
  public $pvehiculo;
  public $pdatos_fiadores;
  public $pnumero_expediente_judicial;
  public $pnumero_operacion1;
  public $pmonto_estimacion_demanda;
  public $pmonto_estimacion_demanda_colones;
  public $pmonto_estimacion_demanda_dolares;
  public $asaldo_capital_operacion;
  public $asaldo_capital_operacion_usd;
  public $aestimacion_demanda_en_presentacion;
  public $aestimacion_demanda_en_presentacion_usd;
  public $liquidacion_intereses_aprobada_crc;
  public $liquidacion_intereses_aprobada_usd;
  public $agastos_legales;
  public $ahonorarios_totales;
  public $ahonorarios_totales_usd;
  public $amonto_cancelar;
  public $amonto_incobrable;
  public $amonto_avaluo;
  public $psaldo_dolarizado;
  public $pnombre_demandado;
  public $bgastos_proceso;
  public $pdespacho_judicial_juzgado;
  public $pdatos_codeudor;
  public $created_at;
  public $updated_at;
  public $deleted_at;

  // ====== Config de validación por grupos (para crear reglas sin omitir nada) ======
  /*
  private $textFields = [
    'pdetalle_garantia',
    'pubicacion_garantia',
    'pcomentarios_bullet_point',
    'npartes_notificadas',
    'acolisiones_embargos_anotaciones',
    'ajustificacion_casos_protocolizados_embargo',
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
    'ames_avance_judicial',
    'pavance_cronologico',
    'lavance_cronologico',
    'savance_cronologico',
    'aavance_cronologico',
    'f1avance_cronologico',
    'f2avance_cronologico',
    'navance_cronologico'
  ];
  */

  /*
  private $decimalFields = [
    'psaldo_de_seguros',
    'psaldo_de_multas',
    'pgastos_legales_caso',
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
    'pretenciones',
    'pmonto_prima',
    'pmonto_arreglo_pago',
    'pmonto_cuota',
    'honorarios_legales_dolares'
  ];
  */

  /*
  private $intFields = ['pnumero', 'pexpectativa_recuperacion_id'];
  */

  /*
  private $bigintFKFields = [
    'contact_id' => 'contacts,id',
    'bank_id' => 'banks,id',
    'product_id' => 'casos_productos,id',
    'proceso_id' => 'casos_procesos,id',
    'currency_id' => 'currencies,id',
    'abogado_id' => 'users,id',
    'asistente1_id' => 'users,id',
    'asistente2_id' => 'users,id',
    'aestado_proceso_general_id' => 'casos_estados,id',
    'testado_proceso_id' => 'casos_estados,id',
    'lestado_levantamiento_id' => 'casos_estados,id',
    'bestado_levantamiento_id' => 'casos_estados,id',
    'ddespacho_judicial_juzgado_id' => 'casos_juzgados,id',
    'ldespacho_judicial_juzgado_id' => 'casos_juzgados,id',
    'ppoderdante_id' => 'casos_poderdantes,id',
    'nestado_id' => 'casos_estados,id',
    'estado_id' => 'casos_estados,id',
  ];
  */

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected',
    'dateSelected' => 'handleDateSelected',
  ];

  protected function getModelClass(): string
  {
    return Caso::class;
  }

  public function resetControls()
  {
    $this->reset(
      'contact_id',
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
      'pdatos_codeudor2',
      'closeForm'
    );
    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function formatDateForStorageDB()
  {
    $this->pfecha_pago_multas_y_seguros = $this->normalizeDateForDB($this->pfecha_pago_multas_y_seguros);
    $this->pfecha_asignacion_caso = $this->normalizeDateForDB($this->pfecha_asignacion_caso);
    $this->pfecha_presentacion_demanda = $this->normalizeDateForDB($this->pfecha_presentacion_demanda);
    $this->pfecha_e_instruccion_levantamiento = $this->normalizeDateForDB($this->pfecha_e_instruccion_levantamiento);
    $this->pfecha_ingreso_cobro_judicial = $this->normalizeDateForDB($this->pfecha_ingreso_cobro_judicial);
    $this->pfecha_escrito_demanda = $this->normalizeDateForDB($this->pfecha_escrito_demanda);
    $this->nfecha_traslado_juzgado = $this->normalizeDateForDB($this->nfecha_traslado_juzgado);
    $this->nfecha_notificacion_todas_partes = $this->normalizeDateForDB($this->nfecha_notificacion_todas_partes);
    $this->sfecha_captura = $this->normalizeDateForDB($this->sfecha_captura);
    $this->sfecha_sentencia = $this->normalizeDateForDB($this->sfecha_sentencia);
    $this->sfecha_remate = $this->normalizeDateForDB($this->sfecha_remate);
    $this->sfecha_primer_remate = $this->normalizeDateForDB($this->sfecha_primer_remate);
    $this->sfecha_segundo_remate = $this->normalizeDateForDB($this->sfecha_segundo_remate);
    $this->sfecha_tercer_remate = $this->normalizeDateForDB($this->sfecha_tercer_remate);
    $this->afecha_aprobacion_remate = $this->normalizeDateForDB($this->afecha_aprobacion_remate);
    $this->afecha_protocolizacion = $this->normalizeDateForDB($this->afecha_protocolizacion);
    $this->afecha_senalamiento_puesta_posesion = $this->normalizeDateForDB($this->afecha_senalamiento_puesta_posesion);
    $this->afecha_informe_ultima_gestion = $this->normalizeDateForDB($this->afecha_informe_ultima_gestion);
    $this->nfecha_notificacion = $this->normalizeDateForDB($this->nfecha_notificacion);
    $this->nfecha_pago = $this->normalizeDateForDB($this->nfecha_pago);
    $this->nfecha_audiencia = $this->normalizeDateForDB($this->nfecha_audiencia);
    $this->afecha_aprobacion_arreglo = $this->normalizeDateForDB($this->afecha_aprobacion_arreglo);
    $this->afecha_envio_cotizacion_gasto = $this->normalizeDateForDB($this->afecha_envio_cotizacion_gasto);
    $this->tfecha_traspaso = $this->normalizeDateForDB($this->tfecha_traspaso);
    $this->tfecha_envio_borrador_escritura = $this->normalizeDateForDB($this->tfecha_envio_borrador_escritura);
    $this->tfecha_firma_escritura = $this->normalizeDateForDB($this->tfecha_firma_escritura);
    $this->tfecha_presentacion_escritura = $this->normalizeDateForDB($this->tfecha_presentacion_escritura);
    $this->tfecha_comunicacion = $this->normalizeDateForDB($this->tfecha_comunicacion);
    $this->tfecha_entrega_titulo_propiedad = $this->normalizeDateForDB($this->tfecha_entrega_titulo_propiedad);
    $this->tfecha_exclusion = $this->normalizeDateForDB($this->tfecha_exclusion);
    $this->tfecha_terminacion = $this->normalizeDateForDB($this->tfecha_terminacion);
    $this->lfecha_entrega_poder = $this->normalizeDateForDB($this->lfecha_entrega_poder);
    $this->lfecha_levantamiento_gravamen = $this->normalizeDateForDB($this->lfecha_levantamiento_gravamen);
    $this->lfecha_comunicado_banco = $this->normalizeDateForDB($this->lfecha_comunicado_banco);
    $this->efecha_visita = $this->normalizeDateForDB($this->efecha_visita);
    $this->rfecha_desinscripcion = $this->normalizeDateForDB($this->rfecha_desinscripcion);
    $this->dfecha_interposicion_denuncia = $this->normalizeDateForDB($this->dfecha_interposicion_denuncia);
    $this->bfecha_entrega_poder = $this->normalizeDateForDB($this->bfecha_entrega_poder);
    $this->bfecha_levantamiento_gravamen = $this->normalizeDateForDB($this->bfecha_levantamiento_gravamen);
    $this->f1fecha_asignacion_capturador = $this->normalizeDateForDB($this->f1fecha_asignacion_capturador);
    $this->f2fecha_publicacion_edicto = $this->normalizeDateForDB($this->f2fecha_publicacion_edicto);
    $this->afecha_firmeza_aprobacion_remate = $this->normalizeDateForDB($this->afecha_firmeza_aprobacion_remate);
    $this->pfecha_ultimo_giro = $this->normalizeDateForDB($this->pfecha_ultimo_giro);
    $this->nfecha_entrega_requerimiento_pago = $this->normalizeDateForDB($this->nfecha_entrega_requerimiento_pago);
    $this->nfecha_entrega_orden_captura = $this->normalizeDateForDB($this->nfecha_entrega_orden_captura);
    $this->afecha_levantamiento = $this->normalizeDateForDB($this->afecha_levantamiento);
    $this->pfecha_informe = $this->normalizeDateForDB($this->pfecha_informe);
    $this->afecha_avaluo = $this->normalizeDateForDB($this->afecha_avaluo);
    $this->afecha_ultimo_giro = $this->normalizeDateForDB($this->afecha_ultimo_giro);
    $this->pfecha_curso_demanda = $this->normalizeDateForDB($this->pfecha_curso_demanda);
    $this->pfecha_primer_giro = $this->normalizeDateForDB($this->pfecha_primer_giro);
    $this->fecha_creacion = $this->normalizeDateForDB($this->fecha_creacion);
    $this->afecha_presentacion_embargo = $this->normalizeDateForDB($this->afecha_presentacion_embargo);
    $this->afecha_arreglo_pago = $this->normalizeDateForDB($this->afecha_arreglo_pago);
    $this->afecha_pago = $this->normalizeDateForDB($this->afecha_pago);
    $this->fecha_importacion = $this->normalizeDateForDB($this->fecha_importacion);
    $this->nfecha_ultima_liquidacion = $this->normalizeDateForDB($this->nfecha_ultima_liquidacion);
    $this->fecha_activacion = $this->normalizeDateForDB($this->fecha_activacion);
    $this->pfecha_devolucion_demanda_firma = $this->normalizeDateForDB($this->pfecha_devolucion_demanda_firma);
    $this->fecha_inicio_retenciones = $this->normalizeDateForDB($this->fecha_inicio_retenciones);
    $this->fecha_prescripcion = $this->normalizeDateForDB($this->fecha_prescripcion);
    $this->fecha_pruebas = $this->normalizeDateForDB($this->fecha_pruebas);
  }

  public function normalizeDateForDB($value)
  {
    if (empty($value)) return null;

    try {
      // El formato de entrada es d-m-Y (12-08-2025 → 12 agosto 2025)
      return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
    } catch (\Throwable $e) {
      return null;
    }
  }

  public function normalizeDateForView($value)
  {
    if (empty($value)) return null;
    try {
      return Carbon::createFromFormat('Y-m-d', $value)->format('d-m-Y');
    } catch (\Throwable $e) {
      return null;
    }
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton, $clonar = false)
  {
    $recordId = $this->getRecordAction($recordId, $clonar);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    // static::getName() devuelve automáticamente el nombre del componente Livewire actual, útil para dispatchTo.
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforedelete()
  {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $record = Caso::findOrFail($recordId);

      if ($record->delete()) {

        $this->selectedIds = array_filter(
          $this->selectedIds,
          fn($selectedId) => $selectedId != $recordId
        );

        // Opcional: limpiar "seleccionar todo" si ya no aplica
        if (empty($this->selectedIds)) {
          $this->selectAll = false;
        }

        // Emitir actualización
        $this->dispatch('updateSelectedIds', $this->selectedIds);

        // Emitir un evento de éxito si la eliminación es exitosa
        $this->dispatch('show-notification', [
          'type' => 'success',
          'message' => __('The record has been deleted')
        ]);
      }
    } catch (QueryException $e) {
      // Capturar errores de integridad referencial (clave foránea)
      if ($e->getCode() == '23000') { // Código de error SQL para restricciones de integridad
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('The record cannot be deleted because it is related to other data.')
        ]);
      } else {
        // Otro tipo de error SQL
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage()
        ]);
      }
    } catch (\Exception $e) {
      // Capturar cualquier otro error general
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while deleting the record') . ' ' . $e->getMessage()
      ]);
    }
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
  }
}
