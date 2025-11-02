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
use App\Models\CasoJuzgado;
use App\Models\CasoProceso;
use Livewire\Attributes\On;
use App\Models\CasoProducto;
use App\Models\CasoServicio;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Helpers\ImportColumns;
use App\Livewire\BaseComponent;
use App\Models\CasoExpectativa;
use App\Models\DataTableConfig;
use Livewire\Attributes\Computed;
use App\Models\CasoListadoJuzgado;
use Illuminate\Support\Facades\DB;
use App\Livewire\Casos\CasoManager;
use App\Exports\CasosTemplateExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CasoEstadoNotificadores;
use App\Services\DocumentSequenceService;

class CasoCafsa extends CasoManager
{
  public $titleNotification = 'Notificación - Public Edicto';

  #[Computed]
  public function estados()
  {
    $estados = CasoEstado::join('casos_estados_bancos', 'casos_estados_bancos.estado_id', '=', 'casos_estados.id')
      ->join('casos_estados_productos', 'casos_estados_productos.estado_id', '=', 'casos_estados.id')
      ->where('casos_estados_bancos.bank_id', $this->bank_id)
      ->where('casos_estados_productos.product_id', $this->product_id)
      ->orderBy('name', 'ASC')
      ->get();
    return $estados;
  }

  public function mount()
  {
    $this->bank_id = Bank::FINANCIERACAFSA;
    $CONCURSALES   = 78;
    $LETRADECAMBIO = 31;
    $PAGARE        = 32;

    /*
    if (auth()->user()->hasAnyRole(['ASIGNACIONES'])) {
      $this->productos = CasoProducto::join('casos_productos_bancos', 'casos_productos_bancos.product_id', '=', 'casos_productos.id')
        ->where('casos_productos_bancos.bank_id', '=', $this->bank_id)
        ->whereIn('casos_productos.id', [$CONCURSALES])
        ->orderBy('nombre', 'ASC')
        ->get();
    } else {
      $this->productos = CasoProducto::join('casos_productos_bancos', 'casos_productos_bancos.product_id', '=', 'casos_productos.id')
        ->where('casos_productos_bancos.bank_id', '=', $this->bank_id)
        ->whereNotIn('casos_productos.id', [$LETRADECAMBIO, $PAGARE])
        ->orderBy('nombre', 'ASC')
        ->get();
    }
    */
    $this->productos = CasoProducto::join('casos_productos_bancos', 'casos_productos_bancos.product_id', '=', 'casos_productos.id')
        ->where('casos_productos_bancos.bank_id', '=', $this->bank_id)
        ->orderBy('nombre', 'ASC')
        ->get();

    $this->clientes = Contact::where('active', 1)->orderby('name', 'ASC')->get();

    $this->banks = Bank::where('id', '=', $this->bank_id)->get();

    $this->procesos = CasoProceso::where('bank_id', $this->bank_id)->orderBy('nombre', 'ASC')->get();

    $this->servicios = CasoServicio::where('activo', 1)->orderBy('id', 'ASC')->get();

    $this->currencies = Currency::whereIn('id', [1,16])->orderBy('code', 'ASC')->get();

    $this->abogados = User::where('active', 1)
      ->whereHas('roles', fn($q) => $q->whereIn('name', [User::ABOGADO, User::JEFE_AREA]))
      ->orderBy('name')->get();

    $this->asistentes = User::where('active', 1)
      ->whereHas('roles', fn($q) => $q->where('name', User::ASISTENTE))
      ->orderBy('name')->get();

    // Estados de casos
    $this->estados = CasoEstado::join('casos_estados_bancos', 'casos_estados_bancos.estado_id', '=', 'casos_estados.id')
      ->where('casos_estados_bancos.bank_id', $this->bank_id)
      ->orderBy('name', 'ASC')
      ->get();

    $this->estadosNotificadores = CasoEstadoNotificadores::orderBy('nombre', 'ASC')->get();

    $this->expectativas = CasoExpectativa::where('activo', 1)->orderBy('nombre', 'ASC')->get();

    $this->juzgados = CasoJuzgado::where('activo', 1)->orderBy('nombre', 'ASC')->get();

    $this->expectedColumns = ImportColumns::getColumnasPorBanco(Bank::FINANCIERACAFSA);

    $this->refresDatatable();
  }

  public function render()
  {
    $query = Caso::search($this->search, $this->filters ?? [])
      ->where('casos.bank_id', $this->bank_id);

    $query->orderBy($this->sortBy, $this->sortDir);

    $records = $query->paginate($this->perPage);

    return view('livewire.casos.cafsa-datatable', [
      'records' => $records,
    ]);
  }

  // ===================== VALIDACIÓN =====================

  // Definir reglas, mensajes y atributos
  public function rules(): array
  {
    $rules = [
      // === REQUIRED ===
      'contact_id'   => ['required', 'integer', 'exists:contacts,id'],
      'bank_id'     => ['required', 'integer', 'exists:banks,id'],
      'product_id'  => ['required', 'integer', 'exists:casos_productos,id'],
      'currency_id'    => ['required', 'integer', 'exists:currencies,id'],
      'fecha_creacion' => ['required', 'date'],

      // === INTEGER FIELDS ===
      'abogado_id'   => ['nullable', 'integer', 'exists:users,id'],
      'pexpectativa_recuperacion_id' => ['nullable', 'integer'],
      'asistente1_id' => ['nullable', 'integer', 'exists:users,id'],
      'asistente2_id' => ['nullable', 'integer', 'exists:users,id'],
      'aestado_proceso_general_id' => ['nullable', 'integer', 'exists:casos_estados,id'],
      'proceso_id'   => ['nullable', 'integer', 'exists:casos_procesos,id'],
      'testado_proceso_id' => ['nullable', 'integer', 'exists:casos_estados,id'],
      'lestado_levantamiento_id' => ['nullable', 'integer', 'exists:casos_estados,id'],
      'ddespacho_judicial_juzgado_id' => ['nullable', 'integer'],
      'bestado_levantamiento_id' => ['nullable', 'integer'],
      'ldespacho_judicial_juzgado_id' => ['nullable', 'integer'],
      'ppoderdante_id' => ['nullable', 'integer'],
      'nestado_id' => ['nullable', 'integer'],
      'estado_id'  => ['nullable', 'integer'],
      'pnumero'    => ['nullable', 'integer'],
      'caso_servicio_capturador_id' => ['nullable', 'integer'],
      'caso_servicio_notificador_id' => ['nullable', 'integer'],

      // === NUMERIC SAFE ===
      'psaldo_de_seguros' => ['nullable', 'numeric'],
      'psaldo_de_multas'  => ['nullable', 'numeric'],
      //'_colones'          => ['nullable', 'numeric'],
      'pmonto_estimacion_demanda_dolares' => ['nullable', 'numeric'],
      'pgastos_legales_caso' => ['nullable', 'numeric'],
      'pmonto_prima' => ['nullable', 'numeric'],
      'nhonorarios_notificacion' => ['nullable', 'numeric'],
      'nhonorarios_cobro_administrativo' => ['nullable', 'numeric'],
      'thonorarios_traspaso' => ['nullable', 'numeric'],
      'tgastos_traspaso' => ['nullable', 'numeric'],
      'tgastos_legales' => ['nullable', 'numeric'],
      'thonorarios_totales' => ['nullable', 'numeric'],
      'fhonorarios_levantamiento' => ['nullable', 'numeric'],
      'fcomision_ccc' => ['nullable', 'numeric'],
      'fhonorarios_totales' => ['nullable', 'numeric'],
      'rhonorario_escritura_inscripcion' => ['nullable', 'numeric'],
      'rgastos_impuestos' => ['nullable', 'numeric'],
      'dgastos_microfilm' => ['nullable', 'numeric'],
      'dhonorarios' => ['nullable', 'numeric'],
      'bhonorarios_levantamiento' => ['nullable', 'numeric'],
      'bhonorarios_comision' => ['nullable', 'numeric'],
      'bhonorarios_totales' => ['nullable', 'numeric'],
      'f1honorarios_capturador' => ['nullable', 'numeric'],
      'f1honorarios_comision' => ['nullable', 'numeric'],
      'agastos_mas_honorarios_acumulados' => ['nullable', 'numeric'],
      'ahonorarios_iniciales' => ['nullable', 'numeric'],
      'adiferencia_demanda_presentada' => ['nullable', 'numeric'],
      'adiferencia_sentencia_afavor' => ['nullable', 'numeric'],
      'adiferencia_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_segunda_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_tercera_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_cuarta_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_quinta_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_sexta_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_septima_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_octava_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_novena_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_primera_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_septima_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_octava_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'adiferencia_decima_novena_liquidacion_de_sentencia_enfirme' => ['nullable', 'numeric'],
      'agastos_legales_iniciales' => ['nullable', 'numeric'],
      'adiferencia_gastos_legales' => ['nullable', 'numeric'],
      'anumero_grupo' => ['nullable', 'numeric'],
      'acarga_gastos_legales' => ['nullable', 'numeric'],
      'pretenciones' => ['nullable', 'numeric'],
      'pmonto_arreglo_pago' => ['nullable', 'numeric'],
      'pmonto_cuota' => ['nullable', 'numeric'],
      'honorarios_legales_dolares' => ['nullable', 'numeric'],


      // === FECHAS SAFE ===
      'pfecha_pago_multas_y_seguros' => ['nullable', 'date'],
      'nfecha_ultima_liquidacion' => ['nullable', 'date'],
      'pfecha_asignacion_caso' => ['nullable', 'date'],
      'pfecha_presentacion_demanda' => ['nullable', 'date'],
      'nfecha_traslado_juzgado' => ['nullable', 'date'],
      'nfecha_notificacion_todas_partes' => ['nullable', 'date'],
      'sfecha_captura' => ['nullable', 'date'],
      'sfecha_sentencia' => ['nullable', 'date'],
      'sfecha_remate' => ['nullable', 'date'],
      'afecha_aprobacion_remate' => ['nullable', 'date'],
      'afecha_protocolizacion' => ['nullable', 'date'],
      'afecha_senalamiento_puesta_posesion' => ['nullable', 'date'],
      //Esto estaba mal es un texto
      'afecha_presentacion_protocolizacion' => ['nullable', 'date'],
      'afecha_inscripcion' => ['nullable', 'date'],
      'afecha_terminacion' => ['nullable', 'date'],
      'afecha_suspencion_arreglo' => ['nullable', 'date'],
      'pfecha_curso_demanda' => ['nullable', 'date'],
      'afecha_informe_ultima_gestion' => ['nullable', 'date'],
      'nfecha_notificacion' => ['nullable', 'date'],
      'nfecha_pago' => ['nullable', 'date'],
      'afecha_aprobacion_arreglo' => ['nullable', 'date'],
      'afecha_envio_cotizacion_gasto' => ['nullable', 'date'],
      'tfecha_traspaso' => ['nullable', 'date'],
      'tfecha_envio_borrador_escritura' => ['nullable', 'date'],
      'tfecha_firma_escritura' => ['nullable', 'date'],
      'tfecha_presentacion_escritura' => ['nullable', 'date'],
      'tfecha_comunicacion' => ['nullable', 'date'],
      'tfecha_entrega_titulo_propiedad' => ['nullable', 'date'],
      'tfecha_exclusion' => ['nullable', 'date'],
      'tfecha_terminacion' => ['nullable', 'date'],
      'pfecha_e_instruccion_levantamiento' => ['nullable', 'date'],
      'lfecha_entrega_poder' => ['nullable', 'date'],
      'lfecha_levantamiento_gravamen' => ['nullable', 'date'],
      'lfecha_comunicado_banco' => ['nullable', 'date'],
      'efecha_visita' => ['nullable', 'date'],
      'rfecha_desinscripcion' => ['nullable', 'date'],
      'dfecha_interposicion_denuncia' => ['nullable', 'date'],
      'bfecha_entrega_poder' => ['nullable', 'date'],
      'bfecha_levantamiento_gravamen' => ['nullable', 'date'],
      'f1fecha_asignacion_capturador' => ['nullable', 'date'],
      'f1fecha_asignacion_notificador'=> ['nullable', 'date'],
      'f2fecha_publicacion_edicto' => ['nullable', 'date'],
      'pfecha_ingreso_cobro_judicial' => ['nullable', 'date'],
      'pfecha_devolucion_demanda_firma' => ['nullable', 'date'],
      'pfecha_escrito_demanda' => ['nullable', 'date'],
      'sfecha_primer_remate' => ['nullable', 'date'],
      'sfecha_segundo_remate' => ['nullable', 'date'],
      'sfecha_tercer_remate' => ['nullable', 'date'],
      'afecha_firmeza_aprobacion_remate' => ['nullable', 'date'],
      'fecha_activacion' => ['nullable', 'date'],
      'afecha_levantamiento' => ['nullable', 'date'],
      'fecha_importacion' => ['nullable', 'date'],
      'pfecha_informe' => ['nullable', 'date'],
      'pfecha_ultimo_giro' => ['nullable', 'date'],
      'nfecha_entrega_requerimiento_pago' => ['nullable', 'date'],
      'nfecha_entrega_orden_captura' => ['nullable', 'date'],
      'afecha_avaluo' => ['nullable', 'date'],
      'afecha_ultimo_giro' => ['nullable', 'date'],
      'pfecha_primer_giro' => ['nullable', 'date'],
      'fecha_inicio_retenciones' => ['nullable', 'date'],
      'fecha_prescripcion' => ['nullable', 'date'],
      'fecha_pruebas' => ['nullable', 'date'],
      'pultima_gestion_cobro_administrativo' => ['nullable', 'date'],
      'afecha_presentacion_embargo' => ['nullable', 'date'],
      'afecha_arreglo_pago' => ['nullable', 'date'],
      'afecha_pago' => ['nullable', 'date'],
      'nfecha_audiencia' => ['nullable', 'date'],
      'afecha_registro' => ['nullable', 'date'],

      // === STRINGS ===
      'pdetalle_garantia' => ['nullable', 'string'],
      'pubicacion_garantia' => ['nullable', 'string'],
      'npartes_notificadas' => ['nullable', 'string'],
      'acolisiones_embargos_anotaciones' => ['nullable', 'string'],
      'ajustificacion_casos_protocolizados_embargo' => ['nullable', 'string'],
      'tiempo_dias' => ['nullable', 'string'],
      'tiempo_annos' => ['nullable', 'string'],
      'pcomentarios_bullet_point' => ['nullable', 'string'],
      'pavance_cronologico' => ['nullable', 'string'],
      'nanotaciones' => ['nullable', 'string'],
      'nubicacion_garantia' => ['nullable', 'string'],
      'ntalleres_situaciones' => ['nullable', 'string'],
      'ncomentarios' => ['nullable', 'string'],
      'acomentarios' => ['nullable', 'string'],
      'aregistro_pago' => ['nullable', 'string'],
      'atraspaso_tercero' => ['nullable', 'string'],
      'ttraspaso_favor_tercero' => ['nullable', 'string'],
      'tborrador_escritura'  => ['nullable', 'string'],
      'tautorizacion_tercero' => ['nullable', 'string'],
      'rcausa' => ['nullable', 'string'],
      'dresultado_sentencia' => ['nullable', 'string'],
      'apuesta_posesion' => ['nullable', 'string'],
      'pmonto_retencion_colones' => ['nullable', 'string'],
      'pmonto_retencion_dolares' => ['nullable', 'string'],
      'codigo_alerta' => ['nullable', 'string'],
      'ames_avance_judicial' => ['nullable', 'string'],
      'lavance_cronologico' => ['nullable', 'string'],
      'savance_cronologico' => ['nullable', 'string'],
      'aavance_cronologico' => ['nullable', 'string'],
      'f1avance_cronologico' => ['nullable', 'string'],
      'f2avance_cronologico' => ['nullable', 'string'],
      'navance_cronologico' => ['nullable', 'string'],

      'nombre_cliente' => ['nullable', 'string', 'max:150'],
      'empresa' => ['nullable', 'string', 'max:150'],
      'email_cliente' => ['nullable', 'string', 'max:160'],
      'user_update' => ['nullable', 'string', 'max:50'],
      'acontacto_telefonico' => ['nullable', 'string', 'max:50'],
      'acorreo' => ['nullable', 'string', 'max:50'],
      'aembargo_cuentas' => ['nullable', 'string', 'max:2'],
      'aembargo_salarios' => ['nullable', 'string', 'max:2'],
      'aembargo_muebles' => ['nullable', 'string', 'max:2'],
      'aembargo_inmuebles' => ['nullable', 'string', 'max:2'],
      'ranotacion' => ['nullable', 'string', 'max:2'],
      'rmarchamo_al_dia' => ['nullable', 'string', 'max:2'],
      'rpendiente' => ['nullable', 'string', 'max:2'],
      'nexonerado_cobro' => ['nullable', 'string', 'max:2'],
      'noposicion_demanda' => ['nullable', 'string', 'max:2'],
      'nembargos_cuentas' => ['nullable', 'string', 'max:2'],
      'nembargos_salarios' => ['nullable', 'string', 'max:2'],
      'nembargos_muebles' => ['nullable', 'string', 'max:2'],
      'nembargos_inmuebles' => ['nullable', 'string', 'max:2'],
      'abienes_adjudicados' => ['nullable', 'string', 'max:2'],

      'nmarchamo' => ['nullable', 'string', 'max:10'],
      'pestado_arreglo' => ['nullable', 'string', 'max:10'],
      'codigo_activacion' => ['nullable', 'string', 'max:10'],

      'dcorreo_electronico' => ['nullable', 'email'],
      'pcorreo_demandado_deudor_o_arrendatario' => ['nullable', 'email'],
      'pcorreo_coarrendatario' => ['nullable', 'email'],

      'pnumero_operacion2' => ['nullable', 'string', 'max:50'],
      'pnumero_contrato' => ['nullable', 'string', 'max:50'],
      'anumero_placa1' => ['nullable', 'string', 'max:50'],
      'anumero_placa2' => ['nullable', 'string', 'max:50'],
      'anumero_marchamo' => ['nullable', 'string', 'max:50'],
      'atipo_expediente' => ['nullable', 'string', 'max:50'],
      'dnumero_carnet' => ['nullable', 'string', 'max:50'],
      'dnumero_telefonico' => ['nullable', 'string', 'max:50'],
      'pcedula_arrendatario' => ['nullable', 'string', 'max:50'],
      'dnumero_expediente' => ['nullable', 'string', 'max:50'],
      'pcedula_deudor' => ['nullable', 'string', 'max:50'],
      'ptelefono_demandado_deudor_o_arrendatario' => ['nullable', 'string', 'max:30'],
      'pplaca1' => ['nullable', 'string', 'max:30'],
      'pplaca2' => ['nullable', 'string', 'max:30'],
      'pnumero_cedula_juridica' => ['nullable', 'string', 'max:30'],

      'pnombre_contacto_o_arrendatario' => ['nullable', 'string', 'max:100'],
      'pnombre_coarrendatario' => ['nullable', 'string', 'max:100'],
      'pcedula_coarrendatario' => ['nullable', 'string', 'max:100'],
      'pcorreo_coarrendatario' => ['nullable', 'string', 'max:100'],
      'ptelefono_coarrendatario' => ['nullable', 'string', 'max:100'],
      'afirma_legal' => ['nullable', 'string', 'max:100'],
      'areasignaciones' => ['nullable', 'string', 'max:100'],
      'pdepartamento_solicitante' => ['nullable', 'string', 'max:100'],
      'lasesoramiento_formal' => ['nullable', 'string', 'max:100'],
      'lsumaria' => ['nullable', 'string', 'max:100'],
      'lcausa' => ['nullable', 'string', 'max:100'],
      'lproveedores_servicio' => ['nullable', 'string', 'max:100'],
      'pcontrato_leasing' => ['nullable', 'string', 'max:100'],
      'ptitular_contrato' => ['nullable', 'string', 'max:100'],
      'pcedula_titular' => ['nullable', 'string', 'max:100'],
      'egestion_a_realizar' => ['nullable', 'string', 'max:100'],
      'eestado_cliente_gran_tamano' => ['nullable', 'string', 'max:100'],
      'dnombre_notario' => ['nullable', 'string', 'max:100'],
      'destado_casos_con_anotaciones' => ['nullable', 'string', 'max:100'],
      'bapersonamiento_formal' => ['nullable', 'string', 'max:100'],
      'bsumaria' => ['nullable', 'string', 'max:100'],
      'bcausa' => ['nullable', 'string', 'max:100'],
      'bproveedores_servicios' => ['nullable', 'string', 'max:100'],
      'f1proveedor_servicio' => ['nullable', 'string', 'max:100'],
      'f1estado_captura' => ['nullable', 'string', 'max:100'],
      'f2causa_remate' => ['nullable', 'string', 'max:100'],
      'f2publicacion_edicto' => ['nullable', 'string', 'max:100'],
      'f2tiempo_concedido_edicto' => ['nullable', 'string', 'max:100'],
      'f2preclusion_tiempo' => ['nullable', 'string', 'max:100'],
      'f2estado_remanente' => ['nullable', 'string', 'max:100'],
      'pnombre_arrendatario' => ['nullable', 'string', 'max:100'],
      'pnombre_apellidos_deudor' => ['nullable', 'string', 'max:100'],
      'pestatus_operacion' => ['nullable', 'string', 'max:100'],
      'nestado_actual_primera_notificacion' => ['nullable', 'string', 'max:100'],
      'ntipo_garantia' => ['nullable', 'string', 'max:100'],
      'abufete' => ['nullable', 'string', 'max:100'],
      'ajuzgado' => ['nullable', 'string', 'max:100'],
      'aestado_operacion' => ['nullable', 'string', 'max:100'],
      'pnumero_tarjeta' => ['nullable', 'string', 'max:100'],
      'pnombre_persona_juridica' => ['nullable', 'string', 'max:100'],
      'pcomprador' => ['nullable', 'string', 'max:100'],
      'aretenciones_con_giro' => ['nullable', 'string', 'max:100'],
      'pente' => ['nullable', 'string', 'max:100'],
      'pplazo_arreglo_pago' => ['nullable', 'string', 'max:100'],
      'pno_cuota' => ['nullable', 'string', 'max:100'],
      'psubsidiaria' => ['nullable', 'string', 'max:100'],
      'pestadoid' => ['nullable', 'string', 'max:100'],
      'motivo_terminacion' => ['nullable', 'string', 'max:100'],


      'pdatos_codeudor1' => ['nullable', 'string', 'max:190'],
      'pdatos_anotantes' => ['nullable', 'string', 'max:190'],
      'pnumero_cedula' => ['nullable', 'string', 'max:190'],
      'pinmueble' => ['nullable', 'string', 'max:190'],
      'pmueble' => ['nullable', 'string', 'max:190'],
      'pvehiculo' => ['nullable', 'string', 'max:190'],
      'pdatos_fiadores' => ['nullable', 'string', 'max:190'],
      'pnumero_expediente_judicial' => ['nullable', 'string', 'max:190'],
      'pnumero_operacion1' => ['nullable', 'string', 'max:190'],
      'pmonto_estimacion_demanda' => ['nullable', 'string', 'max:190'],
      'pmonto_estimacion_demanda_colones' => ['nullable', 'string', 'max:190'],
      'pmonto_estimacion_demanda_dolares' => ['nullable', 'string', 'max:190'],
      'asaldo_capital_operacion' => ['nullable', 'string', 'max:190'],
      'asaldo_capital_operacion_usd' => ['nullable', 'string', 'max:190'],
      'aestimacion_demanda_en_presentacion' => ['nullable', 'string', 'max:190'],
      'aestimacion_demanda_en_presentacion_usd' => ['nullable', 'string', 'max:190'],
      'liquidacion_intereses_aprobada_crc' => ['nullable', 'string', 'max:190'],
      'liquidacion_intereses_aprobada_usd' => ['nullable', 'string', 'max:190'],
      'agastos_legales' => ['nullable', 'string', 'max:190'],
      'ahonorarios_totales' => ['nullable', 'string', 'max:190'],
      'ahonorarios_totales_usd' => ['nullable', 'string', 'max:190'],
      'amonto_cancelar' => ['nullable', 'string', 'max:190'],
      'amonto_incobrable' => ['nullable', 'string', 'max:190'],
      'amonto_avaluo' => ['nullable', 'string', 'max:190'],
      'psaldo_dolarizado' => ['nullable', 'string', 'max:190'],
      'pnombre_demandado' => ['nullable', 'string', 'max:190'],
      'bgastos_proceso' => ['nullable', 'string', 'max:190'],
      'pdespacho_judicial_juzgado' => ['nullable', 'string', 'max:190'],
      'pdatos_codeudor2' => ['nullable', 'string', 'max:190'],
      'nombre_capturador' => ['nullable', 'string', 'max:100'],
      'nombre_notificador' => ['nullable', 'string', 'max:100'],

      'fechasRemate' => 'nullable|array|min:0',
      'fechasRemate.*.fecha' => 'nullable|date|after_or_equal:today',
      'fechasRemate.*.titulo' => 'nullable|string|max:100',
      /*
      'fechasRemate' => 'required|array|min:1',
      'fechasRemate.*.fecha' => 'required|date|after_or_equal:today',
      'fechasRemate.*.titulo' => 'required|string|max:100',
      */
    ];

    return $rules;
  }

  // Mensajes de error personalizados
  public function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'required_if' => 'El campo :attribute es obligatorio cuando el tipo es :value.',
      'required_with' => 'El campo :attribute es obligatorio.',
      'numeric' => 'El campo :attribute debe ser un número válido.',
      'min' => 'El campo :attribute debe tener al menos :min caracteres.',
      'max' => 'El campo :attribute no puede exceder :max caracteres.',
      'in' => 'El campo :attribute no es válido.',
      'exists' => 'El campo :attribute no existe en el sistema.',
      'string' => 'El campo :attribute debe ser texto.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
      'integer' => 'El campo :attribute debe ser un número entero.',
      'fechasRemate.required' => 'Debe agregar al menos una fecha de remate.',
      'fechasRemate.*.fecha.required' => 'La fecha es obligatoria.',
      'fechasRemate.*.fecha.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
      'fechasRemate.*.titulo.required' => 'El título es obligatorio.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    $attributes = [
      'contact_id'     => "cliente",
      'bank_id'        => "banco",
      'product_id'     => "producto",
      'currency_id'    => "moneda",
      'fecha_creacion' => "fecha de creación",

      // === INTEGER FIELDS ===
      'abogado_id'   => "abogado",
      'pexpectativa_recuperacion_id' => "expectativa de recuperación",
      'asistente1_id' => "asistente 1",
      'asistente2_id' => "asistente 2",
      'aestado_proceso_general_id' => "estado del proceso",
      'proceso_id'   => "proceso",
      'testado_proceso_id' => "estado del proceso",
      'lestado_levantamiento_id' => "estado levantamiento",
      'ddespacho_judicial_juzgado_id' => "despacho judicial juzgado",
      'bestado_levantamiento_id' => "estado de levantamiento",
      'ldespacho_judicial_juzgado_id' => "despacho judicial juzgado",
      'ppoderdante_id' => "poderdante",
      'nestado_id' => "estado",
      'estado_id'  => "estado",
      'pnumero'    => "número",

      // === NUMERIC SAFE ===
      /*
      'psaldo_de_seguros' => ,
      'psaldo_de_multas'  => ['nullable', 'numeric'],
      '_colones'          => ['nullable', 'numeric'],
      'pmonto_estimacion_demanda_dolares' => ['nullable', 'numeric'],
      'pgastos_legales_caso' => ['nullable', 'numeric'],
      'pmonto_prima' => ['nullable', 'numeric'],

      // === FECHAS SAFE ===
      'pfecha_pago_multas_y_seguros' => ['nullable', 'date'],
      'nfecha_ultima_liquidacion' => ['nullable', 'date'],
      'pfecha_asignacion_caso' => ['nullable', 'date'],
      'pfecha_presentacion_demanda' => ['nullable', 'date'],
      'nfecha_traslado_juzgado' => ['nullable', 'date'],
      'nfecha_notificacion_todas_partes' => ['nullable', 'date'],
      'sfecha_captura' => ['nullable', 'date'],
      'sfecha_sentencia' => ['nullable', 'date'],
      'sfecha_remate' => ['nullable', 'date'],
      'afecha_aprobacion_remate' => ['nullable', 'date'],
      'afecha_protocolizacion' => ['nullable', 'date'],
      'afecha_senalamiento_puesta_posesion' => ['nullable', 'date'],
      'afecha_registro' => ['nullable', 'date'],
      'afecha_presentacion_protocolizacion' => ['nullable', 'date'],
      'afecha_inscripcion' => ['nullable', 'date'],
      'afecha_terminacion' => ['nullable', 'date'],
      'afecha_suspencion_arreglo' => ['nullable', 'date'],
      'pfecha_curso_demanda' => ['nullable', 'date'],
      'afecha_informe_ultima_gestion' => ['nullable', 'date'],
      'nfecha_notificacion' => ['nullable', 'date'],
      'nfecha_pago' => ['nullable', 'date'],
      'afecha_aprobacion_arreglo' => ['nullable', 'date'],
      'afecha_envio_cotizacion_gasto' => ['nullable', 'date'],
      'tfecha_traspaso' => ['nullable', 'date'],
      'tfecha_envio_borrador_escritura' => ['nullable', 'date'],
      'tfecha_firma_escritura' => ['nullable', 'date'],
      'tfecha_presentacion_escritura' => ['nullable', 'date'],
      'tfecha_comunicacion' => ['nullable', 'date'],
      'tfecha_entrega_titulo_propiedad' => ['nullable', 'date'],
      'tfecha_exclusion' => ['nullable', 'date'],
      'tfecha_terminacion' => ['nullable', 'date'],
      'pfecha_e_instruccion_levantamiento' => ['nullable', 'date'],
      'lfecha_entrega_poder' => ['nullable', 'date'],
      'lfecha_levantamiento_gravamen' => ['nullable', 'date'],
      'lfecha_comunicado_banco' => ['nullable', 'date'],
      'efecha_visita' => ['nullable', 'date'],
      'rfecha_desinscripcion' => ['nullable', 'date'],
      'dfecha_interposicion_denuncia' => ['nullable', 'date'],
      'bfecha_entrega_poder' => ['nullable', 'date'],
      'bfecha_levantamiento_gravamen' => ['nullable', 'date'],
      'f1fecha_asignacion_capturador' => ['nullable', 'date'],
      'f1fecha_asignacion_notificador'=> ['nullable', 'date'],
      'f2fecha_publicacion_edicto' => ['nullable', 'date'],
      'pfecha_ingreso_cobro_judicial' => ['nullable', 'date'],
      'pfecha_devolucion_demanda_firma' => ['nullable', 'date'],
      'pfecha_escrito_demanda' => ['nullable', 'date'],
      'sfecha_primer_remate' => ['nullable', 'date'],
      'sfecha_segundo_remate' => ['nullable', 'date'],
      'sfecha_tercer_remate' => ['nullable', 'date'],
      'afecha_firmeza_aprobacion_remate' => ['nullable', 'date'],
      'fecha_activacion' => ['nullable', 'date'],
      'afecha_levantamiento' => ['nullable', 'date'],
      'fecha_importacion' => ['nullable', 'date'],
      'pfecha_informe' => ['nullable', 'date'],
      'pfecha_ultimo_giro' => ['nullable', 'date'],
      'nfecha_entrega_requerimiento_pago' => ['nullable', 'date'],
      'nfecha_entrega_orden_captura' => ['nullable', 'date'],
      'afecha_avaluo' => ['nullable', 'date'],
      'afecha_ultimo_giro' => ['nullable', 'date'],
      'pfecha_primer_giro' => ['nullable', 'date'],
      'fecha_inicio_retenciones' => ['nullable', 'date'],
      'fecha_prescripcion' => ['nullable', 'date'],
      'fecha_pruebas' => ['nullable', 'date'],

      // === STRINGS ===
      'pdetalle_garantia' => ['nullable', 'string'],
      'pubicacion_garantia' => ['nullable', 'string'],
      'npartes_notificadas' => ['nullable', 'string'],
      'acolisiones_embargos_anotaciones' => ['nullable', 'string'],
      'ajustificacion_casos_protocolizados_embargo' => ['nullable', 'string'],
      'tiempo_dias' => ['nullable', 'string'],
      'tiempo_annos' => ['nullable', 'string'],

      'nombre_cliente' => ['nullable', 'string', 'max:150'],
      'empresa' => ['nullable', 'string', 'max:150'],
      'email_cliente' => ['nullable', 'string', 'max:160'],
      'user_update' => ['nullable', 'string', 'max:50'],
      'acontacto_telefonico' => ['nullable', 'string', 'max:50'],
      'acorreo' => ['nullable', 'string', 'max:50'],
      'aembargo_cuentas' => ['nullable', 'string', 'max:2'],
      'aembargo_salarios' => ['nullable', 'string', 'max:2'],
      'aembargo_muebles' => ['nullable', 'string', 'max:2'],
      'aembargo_inmuebles' => ['nullable', 'string', 'max:2'],
      'ranotacion' => ['nullable', 'string', 'max:2'],
      'rmarchamo_al_dia' => ['nullable', 'string', 'max:2'],
      'rpendiente' => ['nullable', 'string', 'max:2'],
      'nexonerado_cobro' => ['nullable', 'string', 'max:2'],
      'noposicion_demanda' => ['nullable', 'string', 'max:2'],
      'nembargos_cuentas' => ['nullable', 'string', 'max:2'],
      'nembargos_salarios' => ['nullable', 'string', 'max:2'],
      'nembargos_muebles' => ['nullable', 'string', 'max:2'],
      'nembargos_inmuebles' => ['nullable', 'string', 'max:2'],
      'abienes_adjudicados' => ['nullable', 'string', 'max:2'],

      'nmarchamo' => ['nullable', 'string', 'max:10'],
      'pestado_arreglo' => ['nullable', 'string', 'max:10'],
      'codigo_activacion' => ['nullable', 'string', 'max:10'],

      'dcorreo_electronico' => ['nullable', 'email'],
      'pcorreo_demandado_deudor_o_arrendatario' => ['nullable', 'email'],
      'pcorreo_coarrendatario' => ['nullable', 'email'],

      'pnumero_operacion2' => ['nullable', 'string', 'max:50'],
      'pnumero_contrato' => ['nullable', 'string', 'max:50'],
      'anumero_placa1' => ['nullable', 'string', 'max:50'],
      'anumero_placa2' => ['nullable', 'string', 'max:50'],
      'anumero_marchamo' => ['nullable', 'string', 'max:50'],
      'atipo_expediente' => ['nullable', 'string', 'max:50'],
      'dnumero_carnet' => ['nullable', 'string', 'max:50'],
      'dnumero_telefonico' => ['nullable', 'string', 'max:50'],
      'pcedula_arrendatario' => ['nullable', 'string', 'max:50'],
      'dnumero_expediente' => ['nullable', 'string', 'max:50'],
      'pcedula_deudor' => ['nullable', 'string', 'max:50'],
      'ptelefono_demandado_deudor_o_arrendatario' => ['nullable', 'string', 'max:30'],
      'pplaca1' => ['nullable', 'string', 'max:30'],
      'pplaca2' => ['nullable', 'string', 'max:30'],
      'pnumero_cedula_juridica' => ['nullable', 'string', 'max:30'],

      'pnombre_contacto_o_arrendatario' => ['nullable', 'string', 'max:100'],
      'pnombre_coarrendatario' => ['nullable', 'string', 'max:100'],
      'pcedula_coarrendatario' => ['nullable', 'string', 'max:100'],
      'pcorreo_coarrendatario' => ['nullable', 'string', 'max:100'],
      'ptelefono_coarrendatario' => ['nullable', 'string', 'max:100'],
      'afirma_legal' => ['nullable', 'string', 'max:100'],
      'areasignaciones' => ['nullable', 'string', 'max:100'],
      'pdepartamento_solicitante' => ['nullable', 'string', 'max:100'],
      'lasesoramiento_formal' => ['nullable', 'string', 'max:100'],
      'lsumaria' => ['nullable', 'string', 'max:100'],
      'lcausa' => ['nullable', 'string', 'max:100'],
      'lproveedores_servicio' => ['nullable', 'string', 'max:100'],
      'pcontrato_leasing' => ['nullable', 'string', 'max:100'],
      'ptitular_contrato' => ['nullable', 'string', 'max:100'],
      'pcedula_titular' => ['nullable', 'string', 'max:100'],
      'egestion_a_realizar' => ['nullable', 'string', 'max:100'],
      'eestado_cliente_gran_tamano' => ['nullable', 'string', 'max:100'],
      'egestion_a_realizar' => ['nullable', 'string', 'max:100'],
      'eestado_cliente_gran_tamano' => ['nullable', 'string', 'max:100'],
      'dnombre_notario' => ['nullable', 'string', 'max:100'],
      'destado_casos_con_anotaciones' => ['nullable', 'string', 'max:100'],
      'bapersonamiento_formal' => ['nullable', 'string', 'max:100'],
      'bsumaria' => ['nullable', 'string', 'max:100'],
      'bcausa' => ['nullable', 'string', 'max:100'],
      'bproveedores_servicios' => ['nullable', 'string', 'max:100'],
      'f1proveedor_servicio' => ['nullable', 'string', 'max:100'],
      'f1estado_captura' => ['nullable', 'string', 'max:100'],
      'f2causa_remate' => ['nullable', 'string', 'max:100'],
      'f2publicacion_edicto' => ['nullable', 'string', 'max:100'],
      'f2tiempo_concedido_edicto' => ['nullable', 'string', 'max:100'],
      'f2preclusion_tiempo' => ['nullable', 'string', 'max:100'],
      'f2estado_remanente' => ['nullable', 'string', 'max:100'],
      'pnombre_arrendatario' => ['nullable', 'string', 'max:100'],
      'pnombre_apellidos_deudor' => ['nullable', 'string', 'max:100'],
      'pestatus_operacion' => ['nullable', 'string', 'max:100'],
      'nestado_actual_primera_notificacion' => ['nullable', 'string', 'max:100'],
      'ntipo_garantia' => ['nullable', 'string', 'max:100'],
      'abufete' => ['nullable', 'string', 'max:100'],
      'ajuzgado' => ['nullable', 'string', 'max:100'],
      'aestado_operacion' => ['nullable', 'string', 'max:100'],
      'pnumero_tarjeta' => ['nullable', 'string', 'max:100'],
      'pnombre_persona_juridica' => ['nullable', 'string', 'max:100'],
      'pcomprador' => ['nullable', 'string', 'max:100'],
      'aretenciones_con_giro' => ['nullable', 'string', 'max:100'],
      'pente' => ['nullable', 'string', 'max:100'],
      'pplazo_arreglo_pago' => ['nullable', 'string', 'max:100'],
      'pno_cuota' => ['nullable', 'string', 'max:100'],
      'psubsidiaria' => ['nullable', 'string', 'max:100'],
      'pestadoid' => ['nullable', 'string', 'max:100'],
      'motivo_terminacion' => ['nullable', 'string', 'max:100'],


      'pdatos_codeudor1' => ['nullable', 'string', 'max:190'],
      'pdatos_anotantes' => ['nullable', 'string', 'max:190'],
      'pnumero_cedula' => ['nullable', 'string', 'max:190'],
      'pinmueble' => ['nullable', 'string', 'max:190'],
      'pmueble' => ['nullable', 'string', 'max:190'],
      'pvehiculo' => ['nullable', 'string', 'max:190'],
      'pdatos_fiadores' => ['nullable', 'string', 'max:190'],
      'pnumero_expediente_judicial' => ['nullable', 'string', 'max:190'],
      'pnumero_operacion1' => ['nullable', 'string', 'max:190'],
      'pmonto_estimacion_demanda' => ['nullable', 'string', 'max:190'],
      'pmonto_estimacion_demanda_colones' => ['nullable', 'string', 'max:190'],
      'pmonto_estimacion_demanda_dolares' => ['nullable', 'string', 'max:190'],
      'asaldo_capital_operacion' => ['nullable', 'string', 'max:190'],
      'asaldo_capital_operacion_usd' => ['nullable', 'string', 'max:190'],
      'aestimacion_demanda_en_presentacion' => ['nullable', 'string', 'max:190'],
      'aestimacion_demanda_en_presentacion_usd' => ['nullable', 'string', 'max:190'],
      'liquidacion_intereses_aprobada_crc' => ['nullable', 'string', 'max:190'],
      'liquidacion_intereses_aprobada_usd' => ['nullable', 'string', 'max:190'],
      'agastos_legales' => ['nullable', 'string', 'max:190'],
      'ahonorarios_totales' => ['nullable', 'string', 'max:190'],
      'ahonorarios_totales_usd' => ['nullable', 'string', 'max:190'],
      'amonto_cancelar' => ['nullable', 'string', 'max:190'],
      'amonto_incobrable' => ['nullable', 'string', 'max:190'],
      'amonto_avaluo' => ['nullable', 'string', 'max:190'],
      'psaldo_dolarizado' => ['nullable', 'string', 'max:190'],
      'pnombre_demandado' => ['nullable', 'string', 'max:190'],
      'bgastos_proceso' => ['nullable', 'string', 'max:190'],
      'pdespacho_judicial_juzgado' => ['nullable', 'string', 'max:190'],
      'pdatos_codeudor2' => ['nullable', 'string', 'max:190']
      */
    ];

    return $attributes;
  }

  // ===================== CRUD =====================
  public function create()
  {
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    // Obtener la fecha actual en formato Y-m-d
    $today = Carbon::now()->toDateString();

    // Convertir a formato d-m-Y para mostrar en el input
    $this->fecha_creacion = Carbon::parse($today)->format('d-m-Y');
    $this->user_create = auth()->user()->name;
    $this->user_update = auth()->user()->name;

    $this->action = 'create';
    $this->dispatch('scroll-to-top');
    //$this->dispatch('select2');
    $this->dispatch('reinitSelect2Controls');
  }

  public function store()
  {
    $this->validate();

    $this->formatDateForStorageDB();

    $this->user_create = auth()->user()->name;

    $validatedData = $this->validate();

    // Generar consecutivo
    $consecutive = DocumentSequenceService::generateConsecutiveCaso(
      $this->document_type
    );

    $this->pnumero = $consecutive;
    $validatedData['pnumero'] = $consecutive;

    $closeForm = $this->closeForm;

    DB::beginTransaction();
    try {
      $caso = Caso::create($validatedData);

      // Eliminar fechas antiguas y volver a insertar
      $caso->fechasRemate()->delete();

      foreach ($this->fechasRemate as $fecha) {
          $caso->fechasRemate()->create([
              'fecha' => $this->normalizeDateForDB($fecha['fecha']),
              'titulo' => $fecha['titulo'],
              'actualizado_por' => Auth::user()->name ?? 'sistema',
          ]);
      }
      DB::commit();

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($caso->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => 'Caso creado correctamente.']);
    } catch (\Throwable $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Error al crear el caso: ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $record = $this->getRecordActionReturnModel($recordId, Caso::class);

    if (!$record) {
        return;
    }

    $this->recordId = $record->id;

    // Asignación de campos → aquí puedes usar fill() para no escribir todo a mano
    $this->fill($record->toArray());

    // Cargar datos existentes si el caso ya tiene fechas
    $this->fechasRemate = $record->fechasRemate
        ->map(fn ($f) => [
            'id' => $f->id,
            'fecha' => $f->fecha->format('d-m-Y'),
            'titulo' => $f->titulo,
        ])
        ->toArray();

    //Campos de fecha
    $this->formatDateForView($record);

    $this->getPanelsProperty();

    $this->action = 'edit';
    $this->dispatch('select2');
  }

  public function update()
  {
    //Campos de fecha
    $this->validate();

    $this->formatDateForStorageDB();

    $this->user_update = auth()->user()->name;

    $validatedData = $this->validate();

    DB::beginTransaction();
    try {
      $record = Caso::findOrFail($this->recordId);
      $record->update($validatedData);
      DB::commit();

      // Eliminar fechas antiguas y volver a insertar
      $record->fechasRemate()->delete();

      foreach ($this->fechasRemate as $fecha) {
          $record->fechasRemate()->create([
              'fecha' => $this->normalizeDateForDB($fecha['fecha']),
              'titulo' => $fecha['titulo'],
              'actualizado_por' => Auth::user()->name ?? 'sistema',
          ]);
      }

      $closeForm = $this->closeForm;

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();
      $this->dispatch('scroll-to-top');

      $this->dispatch('show-notification', ['type' => 'success', 'message' => 'Caso actualizado correctamente.']);

      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Error al actualizar el caso: ' . $e->getMessage()]);
    }
  }

  // ===================== Helpers =====================

  // ====== Datatable (placeholder mínimo para respetar estructura del ejemplo) ======
  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'casos-cafsa-datatable')
      ->first();

    if ($config) {
      // Verifica si ya es un array o si necesita decodificarse
      $columns = is_array($config->columns) ? $config->columns : json_decode($config->columns, true);
      $this->columns = array_values($columns); // Asegura que los índices se mantengan correctamente
      $this->perPage = $config->perPage  ?? 10; // Valor por defecto si viene null
    } else {
      $this->columns = $this->getDefaultColumns();
      $this->perPage = 10;
    }
  }

  public function updated($propertyName)
  {
    $this->resetErrorBag($propertyName);
    $this->resetValidation();
    if ($propertyName == 'product_id') {
      $this->getPanelsProperty();
    }

    if ($propertyName == 'pnumero_expediente_judicial') {
        $codigo = trim($this->pnumero_expediente_judicial);

        // Extraemos los 4 dígitos que necesitamos del input
        $ultimosCuatro = substr($codigo, 10, 4); // substr empieza en 0

        $listadoJuzgado = CasoListadoJuzgado::whereRaw(
            "SUBSTRING(codigo, 11, 4) = ?", // posición 11 en MySQL
            [$ultimosCuatro]
        )->first();

        $this->pdespacho_judicial_juzgado = $listadoJuzgado ? strtoupper($listadoJuzgado->nombre) : '';
    }


    $this->dispatch('select2');
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }


  public function setSortBy($field)
  {
    if ($this->sortBy === $field) {
      $this->sortDir = $this->sortDir === 'ASC' ? 'DESC' : 'ASC';
    } else {
      $this->sortBy = $field;
      $this->sortDir = 'DESC';
    }
  }

  public $filters = [
    'filter_pnumero' => NULL,
    'filter_pnumero_operacion1' => NULL,
    'filter_pfecha_asignacion_caso' => NULL,
    'filter_bank_name' => NULL,
    'filter_producto' => NULL,
    'filter_proceso' => NULL,
    'filter_abogado' => NULL,
    'filter_asistente' => NULL,
    'filter_pnumero_contrato' => NULL,
    'filter_pdespacho_judicial_juzgado' => NULL,
    'filter_pnombre_demandado' => NULL,
    'filter_pnumero_cedula' => NULL,
    'filter_pnumero_expediente_judicial' => NULL,
    'filter_pfecha_presentacion_demanda' => NULL,
    'filter_nfecha_traslado_juzgado' => NULL,
    'filter_nfecha_notificacion_todas_partes' => NULL,
    'filter_aestado_proceso_general_id' => NULL,
    'filter_fecha_importacion' => NULL
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'pnumero',
        'orderName' => 'pnumero',
        'label' => __('Número'),
        'filter' => 'filter_pnumero',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pnumero_operacion1',
        'orderName' => 'pnumero_operacion1',
        'label' => __('Número Operación1'),
        'filter' => 'filter_pnumero_operacion1',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pfecha_asignacion_caso',
        'orderName' => 'pfecha_asignacion_caso',
        'label' => __('Fecha de asignación de caso'),
        'filter' => 'filter_pfecha_asignacion_caso',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'bank_name',
        'orderName' => 'banks.name',
        'label' => __('Bank'),
        'filter' => 'filter_bank_name',
        'filter_type' => 'select',
        'filter_sources' => 'banks',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'producto',
        'orderName' => 'casos_productos.nombre',
        'label' => __('Producto'),
        'filter' => 'filter_producto',
        'filter_type' => 'select',
        'filter_sources' => 'productos',
        'filter_source_field' => 'nombre',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'proceso',
        'orderName' => 'procesos.name',
        'label' => __('Proceso'),
        'filter' => 'filter_proceso',
        'filter_type' => 'select',
        'filter_sources' => 'productos',
        'filter_source_field' => 'nombre',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'abogado',
        'orderName' => 'u.name',
        'label' => __('Abogado'),
        'filter' => 'filter_abogado',
        'filter_type' => 'select',
        'filter_sources' => 'abogados',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'asistente',
        'orderName' => 'ua.name',
        'label' => __('Asistente'),
        'filter' => 'filter_asistente',
        'filter_type' => 'select',
        'filter_sources' => 'asistentes',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pnumero_contrato',
        'orderName' => 'pnumero_contrato',
        'label' => __('Número de Contrato'),
        'filter' => 'filter_pnumero_contrato',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pdespacho_judicial_juzgado',
        'orderName' => 'pdespacho_judicial_juzgado',
        'label' => __('Despacho Judicial Juzgado'),
        'filter' => 'filter_pdespacho_judicial_juzgado',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pnombre_demandado',
        'orderName' => 'pnombre_demandado',
        'label' => __('Nombre del demandado'),
        'filter' => 'filter_pnombre_demandado',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pnumero_cedula',
        'orderName' => 'pnumero_cedula',
        'label' => __('Número de Cédula del demandado'),
        'filter' => 'filter_pnumero_cedula',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pnumero_expediente_judicial',
        'orderName' => 'casos.pnumero_expediente_judicial',
        'label' => __('Número de Expediente Judicial'),
        'filter' => 'filter_pnumero_expediente_judicial',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'pfecha_presentacion_demanda',
        'orderName' => 'pfecha_presentacion_demanda',
        'label' => __('Fecha Presentación Demanda'),
        'filter' => 'filter_pfecha_presentacion_demanda',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'nfecha_traslado_juzgado',
        'orderName' => 'nfecha_traslado_juzgado',
        'label' => __('Fecha Traslado Juzgado'),
        'filter' => 'filter_nfecha_traslado_juzgado',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'nfecha_notificacion_todas_partes',
        'orderName' => 'nfecha_notificacion_todas_partes',
        'label' => __('Fecha Notificación Todas las Partes'),
        'filter' => 'filter_nfecha_notificacion_todas_partes',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'aestado_proceso_general',
        'orderName' => 'aestado.name',
        'label' => __('Estado Proceso General'),
        'filter' => 'filter_aestado_proceso_general_id',
        'filter_type' => 'select',
        'filter_sources' => 'estados',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'fecha_importacion',
        'orderName' => 'fecha_importacion',
        'label' => __('Fecha de importación'),
        'filter' => 'filter_fecha_importacion',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'action',
        'orderName' => '',
        'label' => __('Actions'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'action',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ]
    ];
    return $this->defaultColumns;
  }

  public function storeAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de almacenamiento
    $this->store();
  }

  public function updateAndClose()
  {
    // ... el resto del código
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function getPanelsProperty()
  {
    $panels = [
      'info' => true, // siempre se muestra
      'notificacion' => true,
      'notificadoresCapturadores' => true,
      'sentencia' => true,
      'arreglo' => false,
      'aprobacion' => true,
      'traspaso' => false,
      'terminacion' => false,
      'levantamiento' => false,
      'facturacion' => false,
      'segmento' => false,
      'denuncia' => false,
      'anotaciones' => false,
      'bienes' => false,
      'filtro1' => false,
      'filtro2' => false,
    ];

    $this->titleNotification = 'Notificación - Public Edicto';

    return $panels;
  }

  public function addFechaRemate()
  {
      $this->fechasRemate[] = [
          'fecha' => '',
          'titulo' => '',
      ];
  }

  public function removeFechaRemate($index)
  {
      unset($this->fechasRemate[$index]);
      $this->fechasRemate = array_values($this->fechasRemate);
  }

  /*
  public function importar()
  {
      $this->resetErrorBag();
      $this->message = null;
      $this->tipoMessage = 'info';

      if (empty($this->expectedColumns)) {
          $this->addError('archivo', 'No hay definición de columnas para el banco seleccionado.');
          return;
      }

      if (!$this->archivo) {
          $this->addError('archivo', 'Debe seleccionar un archivo Excel.');
          return;
      }

      $extension = strtolower($this->archivo->getClientOriginalExtension());
      if (!in_array($extension, ['xlsx', 'xls'])) {
          $this->addError('archivo', 'El archivo debe ser .xlsx o .xls');
          return;
      }

      // Leer el archivo como array
      $array = Excel::toArray([], $this->archivo->getRealPath());
      $sheet = $array[0] ?? null;

      if (!$sheet || count($sheet) === 0) {
          $this->addError('archivo', 'El archivo está vacío o no tiene filas.');
          return;
      }

      // Normalizar encabezados
      $headerRow = $sheet[0];
      $normalize = fn($h) => mb_strtolower(trim(str_replace("\xC2\xA0", ' ', (string)$h)));
      $headersNormalized = array_map($normalize, $headerRow);
      $expectedHeaders = array_keys($this->expectedColumns);
      $expectedNormalized = array_map($normalize, $expectedHeaders);

      // Mapear columnas
      $mapping = [];
      $missing = [];
      foreach ($expectedNormalized as $i => $expNorm) {
          $foundIndex = array_search($expNorm, $headersNormalized, true);
          if ($foundIndex === false) {
              $missing[] = $expectedHeaders[$i];
          } else {
              $mapping[$expectedHeaders[$i]] = $foundIndex;
          }
      }

      if (!empty($missing)) {
          $this->tipoMessage = 'danger';
          $this->message = "Faltan columnas obligatorias: <b>" . implode(', ', $missing) . "</b>";
          return;
      }

      $casosParaGuardar = [];
      $errores = [];

      // Procesar filas
      for ($r = 1; $r < count($sheet); $r++) {
          $row = $sheet[$r];
          if (empty(array_filter($row, fn($c) => !is_null($c) && trim((string)$c) !== ''))) {
              continue;
          }

          // Identificar caso existente o crear nuevo
          $pnumero = trim($row[$mapping['numero']] ?? null);
          $caso = null;
          if ($pnumero) {
              $caso = \App\Models\Caso::where([
                  'pnumero' => $pnumero,
                  'bank_id' => Bank::FINANCIERACAFSA
              ])->first();
          }
          if (!$caso) {
              $caso = new \App\Models\Caso();
              $caso->bank_id = Bank::FINANCIERACAFSA;
              $caso->fecha_creacion = now();
          }

          // Asignar valores de columnas
          foreach ($this->expectedColumns as $header => $config) {
              $campo = $config['campo'];
              $tipo = $config['tipo'];
              $colIndex = $mapping[$header] ?? null;
              $valor = $colIndex !== null ? $row[$colIndex] : null;

              if ($tipo === 'date' && $valor) {
                  try {
                      if ($valor instanceof \Carbon\Carbon) {
                          $valor = $valor->format('Y-m-d');
                      } else {
                          $valor = date('Y-m-d', strtotime($valor));
                      }
                  } catch (\Throwable $e) {
                      $valor = null;
                  }
              } elseif ($tipo === 'int') {
                  $valor = (is_numeric($valor) ? (int)$valor : null);
              } elseif ($tipo === 'float') {
                  $valor = (is_numeric($valor) ? (float)$valor : null);
              } elseif ($tipo === 'string') {
                  $valor = trim((string)$valor);
                  if ($valor === '') {
                      $valor = null;
                  }
              }

              $caso->$campo = $valor;
          }

          // Validación de llaves foráneas y campos obligatorios
          $this->setProducto($caso, $errores, $r);
          $this->setProceso($caso, $errores, $r);
          $this->setMoneda($caso);
          $this->setEstadoNotificacion($caso, $errores, $r);
          $this->setEstadoProcesal($caso, $errores, $r);
          $this->setExpectativaRecuperacion($caso, $errores, $r);

          if (empty($caso->product_id) || empty($caso->proceso_id)) {
              $errores[] = "Fila " . ($r + 1) . ": 'Producto' o 'Proceso' vacíos.";
              continue;
          }

          $casosParaGuardar[] = $caso;
      }

      if (!empty($errores)) {
          $this->tipoMessage = 'danger';
          $this->message = "Se encontraron errores, no se ha guardado ningún registro:<br>" . implode('<br>', $errores);
          return;
      }

      // Guardar casos en la base de datos
      $filasGuardadas = 0;
      if (!empty($casosParaGuardar)) {
          DB::beginTransaction();
          try {
              foreach ($casosParaGuardar as $caso) {
                  if (!$caso->exists) {
                      $caso->fecha_creacion = now();
                  }
                  $caso->fecha_importacion = now();
                  $caso->save();
                  $filasGuardadas++;
              }
              DB::commit();
              $this->tipoMessage = 'success';
              $this->message = "Se importaron correctamente <b>{$filasGuardadas}</b> registros.";
          } catch (\Throwable $e) {
              DB::rollBack();
              $this->tipoMessage = 'danger';
              $this->message = "Ocurrió un error al guardar los registros: " . $e->getMessage();
          }
      }
  }
  */
  public function importar()
  {
    $this->resetErrorBag();
    $this->message = null;
    $this->tipoMessage = 'info';

    if (empty($this->expectedColumns)) {
        $this->addError('archivo', 'No hay definición de columnas para el banco seleccionado.');
        return;
    }

    if (!$this->archivo) {
        $this->addError('archivo', 'Debe seleccionar un archivo Excel.');
        return;
    }

    $extension = strtolower($this->archivo->getClientOriginalExtension());
    if (!in_array($extension, ['xlsx', 'xls'])) {
        $this->addError('archivo', 'El archivo debe ser .xlsx o .xls');
        return;
    }

    // Leer Excel
    $array = Excel::toArray([], $this->archivo->getRealPath());
    $sheet = $array[0] ?? null;

    if (!$sheet || count($sheet) === 0) {
        $this->addError('archivo', 'El archivo está vacío o no tiene filas.');
        return;
    }

    // Normalizar encabezados
    $headerRow = $sheet[0];
    $headersNormalized = array_map([ImportColumns::class, 'normalizeHeader'], $headerRow);
    $expectedHeaders = array_keys($this->expectedColumns);
    $expectedNormalized = array_map([ImportColumns::class, 'normalizeHeader'], $expectedHeaders);

    // Mapeo
    $mapping = [];
    $missing = [];

    foreach ($expectedNormalized as $i => $expNorm) {
        $foundIndex = array_search($expNorm, $headersNormalized, true);
        if ($foundIndex === false) {
            $missing[] = $expectedHeaders[$i];
        } else {
            $mapping[$expectedHeaders[$i]] = $foundIndex;
        }
    }

    if (!empty($missing)) {
        $this->tipoMessage = 'danger';
        $this->message = "Faltan columnas obligatorias: <b>" . implode(', ', $missing) . "</b>";
        return;
    }

    $casosParaGuardar = [];
    $errores = [];

    // Procesar filas
    $nuevos = 0;
    $actualizados = 0;
    for ($r = 1; $r < count($sheet); $r++) {
        $row = $sheet[$r];

        // Fila vacía = skip
        if (empty(array_filter($row, fn($c) => !is_null($c) && trim((string)$c) !== ''))) {
            continue;
        }

        // Verificar columna Número
        if (!isset($mapping['Número'])) {
            $errores[] = "Fila {$r}: columna 'Número' no existe en el Excel.";
            continue;
        }

        $pnumero = trim($row[$mapping['Número']] ?? null);

        // Ver si existe el caso
        $caso = null;
        if ($pnumero) {
            $caso = \App\Models\Caso::where([
                'pnumero' => $pnumero,
            ])->first();

            if ($caso && $caso->bank_id != Bank::FINANCIERACAFSA){
              $errores[] = "Fila {$r}: columna 'Número' existe en un caso del banco: ". $caso->bank->name;
              continue;
            }
        }

        $esNuevo = false;
        // ✅ Si existe → actualizar
        if ($caso) {
            // Aquí actualizas los campos normalmente
        } else {
            // ✅ Si NO existe → crear
            $caso = new \App\Models\Caso();
            $caso->bank_id = Bank::FINANCIERACAFSA;
            $caso->fecha_creacion = now();
            $esNuevo = true;
        }

        // Asignar valores
        foreach ($this->expectedColumns as $header => $config) {
            $campo = $config['campo'];
            $tipo = $config['tipo'];
            $colIndex = $mapping[$header] ?? null;
            $valor = $colIndex !== null ? $row[$colIndex] : null;

            if ($tipo === 'date' && $valor) {
                try {
                    if ($valor instanceof \Carbon\Carbon) {
                        $valor = $valor->format('Y-m-d');
                    } else {
                        $valor = date('Y-m-d', strtotime($valor));
                    }
                } catch (\Throwable $e) {
                    $valor = null;
                }
            } elseif ($tipo === 'int') {
                $valor = is_numeric($valor) ? (int)$valor : null;
            } elseif ($tipo === 'float') {
                $valor = is_numeric($valor) ? (float)$valor : null;
            } elseif ($tipo === 'string') {
                $valor = trim((string)$valor);
                if ($valor === '') {
                    $valor = null;
                }
            }

            $caso->$campo = $valor;
        }

        // Validaciones extra
        $this->setCliente($caso, $errores, $r);
        $this->setProducto($caso, $errores, $r);
        $this->setProceso($caso, $errores, $r);
        $this->setMoneda($caso);
        $this->setEstadoProcesal($caso, $errores, $r);

        if (empty($caso->product_id) || empty($caso->proceso_id)) {
            $errores[] = "Fila " . ($r + 1) . ": 'Producto' o 'Proceso' vacíos.";
            continue;
        }

        $casosParaGuardar[] = $caso;
    }

    // Mostrar errores si hay
    if (!empty($errores)) {
        $this->tipoMessage = 'danger';
        $this->message = "Se encontraron errores, no se ha guardado ningún registro:<br>" . implode('<br>', $errores);
        return;
    }

    // Si no hay nuevos casos
    if (empty($casosParaGuardar)) {
        $this->tipoMessage = 'warning';
        $this->message = "No se insertaron registros nuevos.";
        return;
    }

    // Guardar
    $filasGuardadas = 0;
    DB::beginTransaction();
    try {
        foreach ($casosParaGuardar as $caso) {
            $esNuevo = !$caso->exists;  // <- detecta si es insert o update

            $caso->fecha_importacion = now();
            $caso->save();

            if ($esNuevo) {
                $nuevos++;
            } else {
                $actualizados++;
            }
        }

        DB::commit();

        $this->tipoMessage = 'success';
        $this->message =
            "Importación exitosa:<br>
            ✅ Nuevos: <b>{$nuevos}</b><br>
            🔁 Actualizados: <b>{$actualizados}</b><br>
            📄 Total procesados: <b>" . ($nuevos + $actualizados) . "</b>";
    } catch (\Throwable $e) {
        DB::rollBack();
        $this->tipoMessage = 'danger';
        $this->message = "Ocurrió un error al guardar los registros: " . $e->getMessage();
    }
  }

  public function descargarPlantilla()
  {
      $fileName = "plantilla_casos_financiera_cafsa.xlsx";
      return Excel::download(new CasosTemplateExport(Bank::FINANCIERACAFSA), $fileName);
  }
}
