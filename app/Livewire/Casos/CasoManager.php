<?php

namespace App\Livewire\Casos;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use App\Models\User;
use App\Models\Contact;
use App\Helpers\Helpers;
use App\Models\Currency;
use App\Models\CasoEstado;
use App\Models\CasoProceso;
use Livewire\Attributes\On;
use App\Models\CasoProducto;
use App\Models\CasoServicio;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\CasoPoderdante;
use App\Livewire\BaseComponent;
use App\Models\CasoExpectativa;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CasoEstadoNotificadores;
use Illuminate\Database\QueryException;

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

  public $fechasRemate = [];

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
  public $juzgados    = [];
  public $estadosNotificadores = [];
  public $servicios   = [];

  // =========  TODAS LAS COLUMNAS DE la tabla `casos`  =========
  public $contact_id = NULL;
  public $bank_id = NULL;
  public $product_id = NULL;
  public $caso_servicio_capturador_id = NULL;
  public $caso_servicio_notificador_id = NULL;
  public $currency_id = NULL;
  public $fecha_creacion = NULL;

  // === INTEGER FIELDS ===
  public $abogado_id = NULL;
  public $pexpectativa_recuperacion_id = NULL;
  public $asistente1_id = NULL;
  public $asistente2_id = NULL;
  public $aestado_proceso_general_id = NULL;
  public $proceso_id = NULL;
  public $testado_proceso_id = NULL;
  public $lestado_levantamiento_id = NULL;
  public $ddespacho_judicial_juzgado_id = NULL;
  public $bestado_levantamiento_id = NULL;
  public $ldespacho_judicial_juzgado_id = NULL;
  public $ppoderdante_id = NULL;
  public $nestado_id = NULL;
  public $estado_id = NULL;
  public $pnumero = NULL;

  // === NUMERIC SAFE ===
  public $psaldo_de_seguros = NULL;
  public $psaldo_de_multas = NULL;

  public $pgastos_legales_caso = NULL;
  public $pmonto_prima = NULL;
  public $nhonorarios_notificacion = NULL;
  public $nhonorarios_cobro_administrativo = NULL;
  public $thonorarios_traspaso = NULL;
  public $tgastos_traspaso= NULL;
  public $tgastos_legales= NULL;
  public $thonorarios_totales= NULL;
  public $fhonorarios_levantamiento= NULL;
  public $fcomision_ccc= NULL;
  public $fhonorarios_totales= NULL;
  public $rhonorario_escritura_inscripcion= NULL;
  public $rgastos_impuestos= NULL;
  public $dgastos_microfilm= NULL;
  public $dhonorarios= NULL;
  public $bhonorarios_levantamiento= NULL;
  public $bhonorarios_comision= NULL;
  public $bhonorarios_totales= NULL;
  public $f1honorarios_capturador= NULL;
  public $f1honorarios_comision= NULL;
  public $agastos_mas_honorarios_acumulados= NULL;
  public $ahonorarios_iniciales= NULL;
  public $adiferencia_demanda_presentada= NULL;
  public $adiferencia_sentencia_afavor= NULL;
  public $adiferencia_sentencia_enfirme= NULL;
  public $adiferencia_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_segunda_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_tercera_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_cuarta_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_quinta_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_sexta_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_septima_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_octava_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_novena_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_primera_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_septima_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_octava_liquidacion_de_sentencia_enfirme= NULL;
  public $adiferencia_decima_novena_liquidacion_de_sentencia_enfirme= NULL;
  public $agastos_legales_iniciales= NULL;
  public $adiferencia_gastos_legales= NULL;
  public $anumero_grupo= NULL;
  public $acarga_gastos_legales= NULL;
  public $pretenciones= NULL;
  public $pmonto_arreglo_pago= NULL;
  public $pmonto_cuota= NULL;
  public $honorarios_legales_dolares= NULL;


  // === FECHAS SAFE ===
  public $pfecha_pago_multas_y_seguros= NULL;
  public $nfecha_ultima_liquidacion= NULL;
  public $pfecha_asignacion_caso= NULL;
  public $pfecha_presentacion_demanda= NULL;
  public $nfecha_traslado_juzgado= NULL;
  public $nfecha_notificacion_todas_partes= NULL;
  public $sfecha_captura= NULL;
  public $sfecha_sentencia= NULL;
  public $sfecha_remate= NULL;
  public $afecha_aprobacion_remate= NULL;
  public $afecha_protocolizacion= NULL;
  public $afecha_senalamiento_puesta_posesion= NULL;
  public $afecha_registro= NULL;
  public $afecha_presentacion_protocolizacion= NULL;
  public $afecha_inscripcion= NULL;
  public $afecha_terminacion= NULL;
  public $afecha_suspencion_arreglo= NULL;
  public $pfecha_curso_demanda= NULL;
  public $afecha_informe_ultima_gestion= NULL;
  public $nfecha_notificacion= NULL;
  public $nfecha_pago= NULL;
  public $afecha_aprobacion_arreglo= NULL;
  public $afecha_envio_cotizacion_gasto= NULL;
  public $tfecha_traspaso= NULL;
  public $tfecha_envio_borrador_escritura= NULL;
  public $tfecha_firma_escritura= NULL;
  public $tfecha_presentacion_escritura= NULL;
  public $tfecha_comunicacion= NULL;
  public $tfecha_entrega_titulo_propiedad= NULL;
  public $tfecha_exclusion= NULL;
  public $tfecha_terminacion= NULL;
  public $pfecha_e_instruccion_levantamiento= NULL;
  public $lfecha_entrega_poder= NULL;
  public $lfecha_levantamiento_gravamen= NULL;
  public $lfecha_comunicado_banco= NULL;
  public $efecha_visita= NULL;
  public $rfecha_desinscripcion= NULL;
  public $dfecha_interposicion_denuncia= NULL;
  public $bfecha_entrega_poder= NULL;
  public $bfecha_levantamiento_gravamen= NULL;
  public $f1fecha_asignacion_capturador= NULL;
  public $f1fecha_asignacion_notificador= NULL;
  public $f2fecha_publicacion_edicto= NULL;
  public $pfecha_ingreso_cobro_judicial= NULL;
  public $pfecha_devolucion_demanda_firma= NULL;
  public $pfecha_escrito_demanda= NULL;
  public $sfecha_primer_remate= NULL;
  public $sfecha_segundo_remate= NULL;
  public $sfecha_tercer_remate= NULL;
  public $afecha_firmeza_aprobacion_remate= NULL;
  public $fecha_activacion= NULL;
  public $afecha_levantamiento= NULL;
  public $fecha_importacion= NULL;
  public $pfecha_informe= NULL;
  public $pfecha_ultimo_giro= NULL;
  public $nfecha_entrega_requerimiento_pago= NULL;
  public $nfecha_entrega_orden_captura= NULL;
  public $afecha_avaluo= NULL;
  public $afecha_ultimo_giro= NULL;
  public $pfecha_primer_giro= NULL;
  public $fecha_inicio_retenciones= NULL;
  public $fecha_prescripcion= NULL;
  public $fecha_pruebas= NULL;
  public $pultima_gestion_cobro_administrativo= NULL;
  public $afecha_presentacion_embargo= NULL;
  public $afecha_arreglo_pago= NULL;
  public $afecha_pago= NULL;
  public $nfecha_audiencia= NULL;

  // === STRINGS ===
  public $pdetalle_garantia= NULL;
  public $pubicacion_garantia= NULL;
  public $npartes_notificadas= NULL;
  public $acolisiones_embargos_anotaciones= NULL;
  public $ajustificacion_casos_protocolizados_embargo= NULL;
  public $tiempo_dias= NULL;
  public $tiempo_annos= NULL;
  public $pcomentarios_bullet_point= NULL;
  public $pavance_cronologico= NULL;
  public $nanotaciones= NULL;
  public $nubicacion_garantia= NULL;
  public $ntalleres_situaciones= NULL;
  public $ncomentarios= NULL;
  public $acomentarios= NULL;
  public $aregistro_pago= NULL;
  public $atraspaso_tercero= NULL;
  public $ttraspaso_favor_tercero= NULL;
  public $tborrador_escritura= NULL;
  public $tautorizacion_tercero= NULL;
  public $rcausa= NULL;
  public $dresultado_sentencia= NULL;
  public $apuesta_posesion= NULL;
  public $pmonto_retencion_colones= NULL;
  public $pmonto_retencion_dolares= NULL;
  public $codigo_alerta= NULL;
  public $ames_avance_judicial= NULL;
  public $lavance_cronologico= NULL;
  public $savance_cronologico= NULL;
  public $aavance_cronologico= NULL;
  public $f1avance_cronologico= NULL;
  public $f2avance_cronologico= NULL;
  public $navance_cronologico= NULL;

  public $nombre_cliente= NULL;
  public $empresa= NULL;
  public $email_cliente= NULL;
  public $user_create= NULL;
  public $user_update= NULL;
  public $acontacto_telefonico= NULL;
  public $acorreo= NULL;
  public $aembargo_cuentas= NULL;
  public $aembargo_salarios= NULL;
  public $aembargo_muebles= NULL;
  public $aembargo_inmuebles= NULL;
  public $ranotacion= NULL;
  public $rmarchamo_al_dia= NULL;
  public $rpendiente= NULL;
  public $nexonerado_cobro= NULL;
  public $noposicion_demanda= NULL;
  public $nembargos_cuentas= NULL;
  public $nembargos_salarios= NULL;
  public $nembargos_muebles= NULL;
  public $nembargos_inmuebles= NULL;
  public $abienes_adjudicados= NULL;

  public $nmarchamo= NULL;
  public $pestado_arreglo= NULL;
  public $codigo_activacion= NULL;

  public $dcorreo_electronico= NULL;
  public $pcorreo_demandado_deudor_o_arrendatario= NULL;

  public $pnumero_operacion2= NULL;
  public $pnumero_contrato= NULL;
  public $anumero_placa1= NULL;
  public $anumero_placa2= NULL;
  public $anumero_marchamo= NULL;
  public $atipo_expediente= NULL;
  public $dnumero_carnet= NULL;
  public $dnumero_telefonico= NULL;
  public $pcedula_arrendatario= NULL;
  public $dnumero_expediente= NULL;
  public $pcedula_deudor= NULL;
  public $ptelefono_demandado_deudor_o_arrendatario= NULL;
  public $pplaca1= NULL;
  public $pplaca2= NULL;
  public $pnumero_cedula_juridica= NULL;

  public $pnombre_contacto_o_arrendatario= NULL;
  public $pnombre_coarrendatario= NULL;
  public $pcedula_coarrendatario= NULL;
  public $pcorreo_coarrendatario= NULL;
  public $ptelefono_coarrendatario= NULL;
  public $afirma_legal= NULL;
  public $areasignaciones= NULL;
  public $pdepartamento_solicitante= NULL;
  public $lasesoramiento_formal= NULL;
  public $lsumaria= NULL;
  public $lcausa= NULL;
  public $lproveedores_servicio= NULL;
  public $pcontrato_leasing= NULL;
  public $ptitular_contrato= NULL;
  public $pcedula_titular= NULL;
  public $egestion_a_realizar= NULL;
  public $eestado_cliente_gran_tamano= NULL;
  public $dnombre_notario= NULL;
  public $destado_casos_con_anotaciones= NULL;
  public $bapersonamiento_formal= NULL;
  public $bsumaria= NULL;
  public $bcausa= NULL;
  public $bproveedores_servicios= NULL;
  public $f1proveedor_servicio= NULL;
  public $f1estado_captura= NULL;
  public $f2causa_remate= NULL;
  public $f2publicacion_edicto= NULL;
  public $f2tiempo_concedido_edicto= NULL;
  public $f2preclusion_tiempo= NULL;
  public $f2estado_remanente= NULL;
  public $pnombre_arrendatario= NULL;
  public $pnombre_apellidos_deudor= NULL;
  public $pestatus_operacion= NULL;
  public $nestado_actual_primera_notificacion= NULL;
  public $ntipo_garantia= NULL;
  public $abufete= NULL;
  public $ajuzgado= NULL;
  public $aestado_operacion= NULL;
  public $pnumero_tarjeta= NULL;
  public $pnombre_persona_juridica= NULL;
  public $pcomprador= NULL;
  public $aretenciones_con_giro= NULL;
  public $pente= NULL;
  public $pplazo_arreglo_pago= NULL;
  public $pno_cuota= NULL;
  public $psubsidiaria= NULL;
  public $pestadoid= NULL;
  public $motivo_terminacion= NULL;

  public $pdatos_codeudor1= NULL;
  public $pdatos_codeudor2= NULL;
  public $pdatos_anotantes= NULL;
  public $pnumero_cedula= NULL;
  public $pinmueble= NULL;
  public $pmueble= NULL;
  public $pvehiculo= NULL;
  public $pdatos_fiadores= NULL;
  public $pnumero_expediente_judicial= NULL;
  public $pnumero_operacion1= NULL;
  public $pmonto_estimacion_demanda= NULL;
  public $pmonto_estimacion_demanda_colones= NULL;
  public $pmonto_estimacion_demanda_dolares= NULL;
  public $asaldo_capital_operacion= NULL;
  public $asaldo_capital_operacion_usd= NULL;
  public $aestimacion_demanda_en_presentacion= NULL;
  public $aestimacion_demanda_en_presentacion_usd= NULL;
  public $liquidacion_intereses_aprobada_crc= NULL;
  public $liquidacion_intereses_aprobada_usd= NULL;
  public $agastos_legales= NULL;
  public $ahonorarios_totales= NULL;
  public $ahonorarios_totales_usd= NULL;
  public $amonto_cancelar= NULL;
  public $amonto_incobrable= NULL;
  public $amonto_avaluo= NULL;
  public $psaldo_dolarizado= NULL;
  public $pnombre_demandado= NULL;
  public $bgastos_proceso= NULL;
  public $pdespacho_judicial_juzgado= NULL;
  public $pdatos_codeudor= NULL;
  public $nombre_capturador= NULL;
  public $nombre_notificador= NULL;
  public $created_at= NULL;
  public $updated_at= NULL;
  public $deleted_at= NULL;

  public $message = NULL;
  public $tipoMessage = NULL;
  public $archivo = NULL;
  public $errores = [];
  public $importedCount = 0;
  public $expectedColumns = [];

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
      'fechasRemate',

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
      'f1fecha_asignacion_notificador',
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
      'nombre_capturador',
      'nombre_notificador',
      'caso_servicio_capturador_id',
      'caso_servicio_notificador_id',
      'closeForm',
      'message',
      'tipoMessage',
      'archivo'
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
    $this->f1fecha_asignacion_notificador = $this->normalizeDateForDB($this->f1fecha_asignacion_notificador);
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
    $this->afecha_registro = $this->normalizeDateForDB($this->afecha_registro);
    $this->afecha_presentacion_protocolizacion = $this->normalizeDateForDB($this->afecha_presentacion_protocolizacion);
    $this->afecha_inscripcion = $this->normalizeDateForDB($this->afecha_inscripcion);
    $this->afecha_suspencion_arreglo = $this->normalizeDateForDB($this->afecha_suspencion_arreglo);
    $this->afecha_terminacion = $this->normalizeDateForDB($this->afecha_terminacion);
    $this->pultima_gestion_cobro_administrativo = $this->normalizeDateForDB($this->pultima_gestion_cobro_administrativo);
  }

  public function formatDateForView($record)
  {
    $this->pfecha_pago_multas_y_seguros = $this->normalizeDateForView($record->pfecha_pago_multas_y_seguros);
    $this->pfecha_asignacion_caso = $this->normalizeDateForView($record->pfecha_asignacion_caso);
    $this->pfecha_presentacion_demanda = $this->normalizeDateForView($record->pfecha_presentacion_demanda);
    $this->pfecha_e_instruccion_levantamiento = $this->normalizeDateForView($record->pfecha_e_instruccion_levantamiento);
    $this->pfecha_ingreso_cobro_judicial = $this->normalizeDateForView($record->pfecha_ingreso_cobro_judicial);
    $this->pfecha_escrito_demanda = $this->normalizeDateForView($record->pfecha_escrito_demanda);
    $this->nfecha_traslado_juzgado = $this->normalizeDateForView($record->nfecha_traslado_juzgado);
    $this->nfecha_notificacion_todas_partes = $this->normalizeDateForView($record->nfecha_notificacion_todas_partes);
    $this->sfecha_captura = $this->normalizeDateForView($record->sfecha_captura);
    $this->sfecha_sentencia = $this->normalizeDateForView($record->sfecha_sentencia);
    $this->sfecha_remate = $this->normalizeDateForView($record->sfecha_remate);
    $this->sfecha_primer_remate = $this->normalizeDateForView($record->sfecha_primer_remate);
    $this->sfecha_segundo_remate = $this->normalizeDateForView($record->sfecha_segundo_remate);
    $this->sfecha_tercer_remate = $this->normalizeDateForView($record->sfecha_tercer_remate);
    $this->afecha_aprobacion_remate = $this->normalizeDateForView($record->afecha_aprobacion_remate);
    $this->afecha_protocolizacion = $this->normalizeDateForView($record->afecha_protocolizacion);
    $this->afecha_senalamiento_puesta_posesion = $this->normalizeDateForView($record->afecha_senalamiento_puesta_posesion);
    $this->afecha_informe_ultima_gestion = $this->normalizeDateForView($record->afecha_informe_ultima_gestion);
    $this->nfecha_notificacion = $this->normalizeDateForView($record->nfecha_notificacion);
    $this->nfecha_pago = $this->normalizeDateForView($record->nfecha_pago);
    $this->nfecha_audiencia = $this->normalizeDateForView($record->nfecha_audiencia);
    $this->afecha_aprobacion_arreglo = $this->normalizeDateForView($record->afecha_aprobacion_arreglo);
    $this->afecha_envio_cotizacion_gasto = $this->normalizeDateForView($record->afecha_envio_cotizacion_gasto);
    $this->tfecha_traspaso = $this->normalizeDateForView($record->tfecha_traspaso);
    $this->tfecha_envio_borrador_escritura = $this->normalizeDateForView($record->tfecha_envio_borrador_escritura);
    $this->tfecha_firma_escritura = $this->normalizeDateForView($record->tfecha_firma_escritura);
    $this->tfecha_presentacion_escritura = $this->normalizeDateForView($record->tfecha_presentacion_escritura);
    $this->tfecha_comunicacion = $this->normalizeDateForView($record->tfecha_comunicacion);
    $this->tfecha_entrega_titulo_propiedad = $this->normalizeDateForView($record->tfecha_entrega_titulo_propiedad);
    $this->tfecha_exclusion = $this->normalizeDateForView($record->tfecha_exclusion);
    $this->tfecha_terminacion = $this->normalizeDateForView($record->tfecha_terminacion);
    $this->lfecha_entrega_poder = $this->normalizeDateForView($record->lfecha_entrega_poder);
    $this->lfecha_levantamiento_gravamen = $this->normalizeDateForView($record->lfecha_levantamiento_gravamen);
    $this->lfecha_comunicado_banco = $this->normalizeDateForView($record->lfecha_comunicado_banco);
    $this->efecha_visita = $this->normalizeDateForView($record->efecha_visita);
    $this->rfecha_desinscripcion = $this->normalizeDateForView($record->rfecha_desinscripcion);
    $this->dfecha_interposicion_denuncia = $this->normalizeDateForView($record->dfecha_interposicion_denuncia);
    $this->bfecha_entrega_poder = $this->normalizeDateForView($record->bfecha_entrega_poder);
    $this->bfecha_levantamiento_gravamen = $this->normalizeDateForView($record->bfecha_levantamiento_gravamen);
    $this->f1fecha_asignacion_capturador = $this->normalizeDateForView($record->f1fecha_asignacion_capturador);
    $this->f1fecha_asignacion_notificador = $this->normalizeDateForView($record->f1fecha_asignacion_notificador);
    $this->f2fecha_publicacion_edicto = $this->normalizeDateForView($record->f2fecha_publicacion_edicto);
    $this->afecha_firmeza_aprobacion_remate = $this->normalizeDateForView($record->afecha_firmeza_aprobacion_remate);
    $this->pfecha_ultimo_giro = $this->normalizeDateForView($record->pfecha_ultimo_giro);
    $this->nfecha_entrega_requerimiento_pago = $this->normalizeDateForView($record->nfecha_entrega_requerimiento_pago);
    $this->nfecha_entrega_orden_captura = $this->normalizeDateForView($record->nfecha_entrega_orden_captura);
    $this->afecha_levantamiento = $this->normalizeDateForView($record->afecha_levantamiento);
    $this->pfecha_informe = $this->normalizeDateForView($record->pfecha_informe);
    $this->afecha_avaluo = $this->normalizeDateForView($record->afecha_avaluo);
    $this->afecha_ultimo_giro = $this->normalizeDateForView($record->afecha_ultimo_giro);
    $this->pfecha_curso_demanda = $this->normalizeDateForView($record->pfecha_curso_demanda);
    $this->pfecha_primer_giro = $this->normalizeDateForView($record->pfecha_primer_giro);
    $this->fecha_creacion = $this->normalizeDateForView($record->fecha_creacion);
    $this->afecha_presentacion_embargo = $this->normalizeDateForView($record->afecha_presentacion_embargo);
    $this->afecha_arreglo_pago = $this->normalizeDateForView($record->afecha_arreglo_pago);
    $this->afecha_pago = $this->normalizeDateForView($record->afecha_pago);
    $this->fecha_importacion = $this->normalizeDateForView($record->fecha_importacion);
    $this->nfecha_ultima_liquidacion = $this->normalizeDateForView($record->nfecha_ultima_liquidacion);
    $this->fecha_activacion = $this->normalizeDateForView($record->fecha_activacion);
    $this->pfecha_devolucion_demanda_firma = $this->normalizeDateForView($record->pfecha_devolucion_demanda_firma);
    $this->fecha_inicio_retenciones = $this->normalizeDateForView($record->fecha_inicio_retenciones);
    $this->fecha_prescripcion = $this->normalizeDateForView($record->fecha_prescripcion);
    $this->fecha_pruebas = $this->normalizeDateForView($record->fecha_pruebas);
    $this->afecha_registro = $this->normalizeDateForView($record->afecha_registro);
    $this->afecha_presentacion_protocolizacion = $this->normalizeDateForView($record->afecha_presentacion_protocolizacion);
    $this->afecha_inscripcion = $this->normalizeDateForView($record->afecha_inscripcion);
    $this->afecha_suspencion_arreglo = $this->normalizeDateForView($record->afecha_suspencion_arreglo);
    $this->afecha_terminacion = $this->normalizeDateForView($record->afecha_terminacion);
    $this->pultima_gestion_cobro_administrativo = $this->normalizeDateForView($record->pultima_gestion_cobro_administrativo);
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

  public function confirmarAccionNotificaion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $recordId = $this->getRecordAction($recordId);

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

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
  }

  public function resetFilters()
  {
    $this->reset('filters');
    $this->selectedIds = [];
  }

  protected function cleanEmptyForeignKeys()
  {
    // Lista de campos que pueden ser claves foráneas
    $foreignKeys = [
      'contact_id',
      'bank_id',
      'product_id',
      'currency_id',
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
    ];

    foreach ($foreignKeys as $key) {
      if (isset($this->$key) && $this->$key === '') {
        $this->$key = null;
      }
    }
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }

  public function showImport(){
    $this->action = 'importar';
  }

  // Mostrar formulario de importación
  public function mostrarImportacion()
  {
      $this->resetValidation();
      $this->reset(['archivo', 'mensaje', 'tipo']);
      $this->action = 'importar';
  }

  // Cancelar importación y volver al listado
  public function cancelarImportacion()
  {
     $this->action = 'list';
  }

  protected function setCliente(&$caso, &$errores, $fila)
  {
      if (!empty($caso->contact_id)) {

          // Buscar cliente por nombre
          $cliente = Contact::where('name', $caso->contact_id)->first();

          if ($cliente) {
              $caso->contact_id = $cliente->id;
          } else {
              $errores[] = "Fila {$fila}: No se ha encontrado el cliente '{$caso->contact_id}'.";
          }
      }
  }

  protected function setProducto(&$caso, &$errores, $fila)
  {
      if (!empty($caso->product_id)) {

          $producto = CasoProducto::where('nombre', trim($caso->product_id))
              ->whereHas('banks', function ($q) use ($caso) {
                  $q->where('bank_id', $caso->bank_id);
              })
              ->first();

          if ($producto) {
              $caso->product_id = $producto->id;
          } else {
              $errores[] = "Fila {$fila}: No se ha encontrado el producto '{$caso->product_id}'.";
          }
      }
  }

  public function setProceso(&$caso, &$errores, $fila)
  {
      if (!empty($caso->proceso_id)) {
          $proceso = CasoProceso::where('bank_id', $caso->bank_id)
              ->where('nombre', trim($caso->proceso_id))
              ->first();

          if ($proceso) {
              $caso->proceso_id = $proceso->id;
          } else {
              $errores[] = "Fila {$fila}: No se ha encontrado el proceso '{$caso->proceso_id}'.";
          }
      }
  }

  public function setServicioCapturador(&$caso, &$errores, $fila)
  {
      if (!empty($caso->caso_servicio_capturador_id)) {
          $data = CasoServicio::where('nombre', $caso->caso_servicio_capturador_id)->first();

          if ($data) {
              $caso->caso_servicio_capturador_id = $data->id;
          } else {
              $errores[] = "Fila {$fila}: No se ha encontrado el servicio del capturador '{$caso->caso_servicio_capturador_id}'.";
          }
      }
  }

  public function setServicioNotificador(&$caso, &$errores, $fila)
  {
      if (!empty($caso->caso_servicio_notificador_id)) {
          $data = CasoServicio::where('nombre', $caso->caso_servicio_notificador_id)->first();

          if ($data) {
              $caso->caso_servicio_notificador_id = $data->id;
          } else {
              $errores[] = "Fila {$fila}: No se ha encontrado el servicio del notificador '{$caso->caso_servicio_notificador_id}'.";
          }
      }
  }

  public function setMoneda(&$caso)
  {
      if (!empty($caso->currency_id)) {
          $moneda = strtoupper(trim($caso->currency_id));

          if ($moneda === 'CRC' || $moneda === 'COLONES') {
              $caso->currency_id = 16;
          } elseif ($moneda === 'MIXTO' || $moneda === 'MIXTA') {
              $caso->currency_id = 158;
          } else {
              $caso->currency_id = 1;
          }
      }
  }

  public function setEstadoProcesal(&$caso, &$errores, $fila)
  {
      if (!empty($caso->producto_id) && $caso->producto_id > 0 && !empty($caso->aestado_proceso_general_id)) {

          $estado = CasoEstado::join('casos_estados_bancos', function ($join) use ($caso) {
                  $join->on('casos_estados_bancos.estado_id', '=', 'casos_estados.id')
                      ->where('casos_estados_bancos.bank_id', $caso->banco_id);
              })
              ->join('casos_estados_productos', function ($join) use ($caso) {
                  $join->on('casos_estados_productos.estado_id', '=', 'casos_estados.id')
                      ->where('casos_estados_productos.product_id', $caso->producto_id);
              })
              ->where('casos_estados.name', trim($caso->aestado_proceso_general_id))
              ->first();

          if ($estado) {
              $caso->aestado_proceso_general_id = $estado->id;
          } else {
              $productoNombre = $caso->producto ? $caso->producto->nombre : 'No encontrado';
              $errores[] = "Fila {$fila}: No se ha encontrado el Estado Procesal '{$caso->aestado_proceso_general_id}' para el producto '{$productoNombre}'.";
          }
      }
  }

	public function setEstadoNotificacion(&$caso, &$errores, $fila)
	{
		if (!empty($caso->nestado_id)) {
			// Estados notificaciones
			$estado = CasoEstadoNotificadores::where(['nombre' => $caso->nestado_id])->first();
			if (!empty($estado))
				$caso->nestado_id = $estado['id'];
			else
        $errores[] = "Fila {$fila}: No se ha encontrado el Estado  '{$caso->nestado_id}'";
		}
	}

	public function setExpectativaRecuperacion(&$caso, &$errores, $fila)
	{
		if (!empty($caso->pexpectativa_recuperacion_id)) {
			// Expectativa de recuperación
			$expectativa = CasoExpectativa::where(['nombre' => $caso->pexpectativa_recuperacion_id])->first();
			if (!empty($expectativa))
				$caso->pexpectativa_recuperacion_id = $expectativa['id'];
			else
        $errores[] = "Fila {$fila}: No se ha encontrado la expectativa de recuperación  '{$caso->pexpectativa_recuperacion_id}'";
		}
	}

	public function setPoderdante(&$caso, &$mensaje, $fila)
	{
		if (!empty($caso->ppoderdante_id)) {
			// Poderdante
      $poderdante = \App\Models\CasoPoderdante::join(
          'casos_poderdantes_bancos',
          function($join) use ($caso) {
              $join->on('casos_poderdantes_bancos.poderdante_id', '=', 'casos_poderdantes.id')
                  ->where('casos_poderdantes_bancos.bank_id', $caso->banco_id);
          }
      )
      ->where('casos_poderdantes.nombre', trim($caso->ppoderdante_id))
      ->first(); // devuelve un objeto o null

			if (!empty($poderdante))
				$caso->ppoderdante_id = $poderdante['id'];
			else
				$mensaje .= 'No se ha encontrado el Poderdante: ' . $caso->ppoderdante_id . ' en la fila: ' . $fila . "<br />";
		}
	}

  // En tu componente Livewire
  public function excelColumnLetter($index)
  {
      $letter = '';
      while ($index >= 0) {
          $letter = chr($index % 26 + 65) . $letter;
          $index = intval($index / 26) - 1;
      }
      return $letter;
  }
}
