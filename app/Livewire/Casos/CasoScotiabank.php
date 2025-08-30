<?php

namespace App\Livewire\Casos;

use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Livewire\Casos\CasoManager;
use App\Models\Bank;
use App\Models\Caso;
use App\Models\CasoEstado;
use App\Models\CasoExpectativa;
use App\Models\CasoProceso;
use App\Models\CasoProducto;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\User;
use App\Services\DocumentSequenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CasoScotiabank extends CasoManager
{

  #[Computed]
  public function estadosLevantamientos()
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
    $this->bank_id = Bank::SCOTIABANKCR;
    $CONCURSALES   = 78;
    $LETRADECAMBIO = 31;
    $PAGARE        = 32;

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

    $this->clientes = Contact::where('active', 1)->orderby('name', 'ASC')->get();

    $this->banks = Bank::where('id', '=', $this->bank_id)->get();

    $this->procesos = CasoProceso::where('bank_id', $this->bank_id)->orderBy('nombre', 'ASC')->get();

    $this->currencies = Currency::orderBy('code', 'ASC')->get();

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

    $this->expectativas = CasoExpectativa::where('activo', 1)->orderBy('nombre', 'ASC')->get();


    $this->refresDatatable();
  }

  public function render()
  {
    $query = Caso::search($this->search, $this->filters ?? [])
      ->where('casos.bank_id', $this->bank_id);

    $query->orderBy($this->sortBy, $this->sortDir);

    $records = $query->paginate($this->perPage);

    return view('livewire.casos.scotiabanks-datatable', [
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

      // === NUMERIC SAFE ===
      'psaldo_de_seguros' => ['nullable', 'numeric'],
      'psaldo_de_multas'  => ['nullable', 'numeric'],
      //'_colones'          => ['nullable', 'numeric'],
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
    $this->formatDateForStorageDB();

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
      DB::commit();

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($caso->id);
      }

      $this->action = 'list';
      $this->dispatch('show-notification', ['type' => 'success', 'message' => 'Caso creado correctamente.']);
    } catch (\Throwable $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Error al crear el caso: ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Caso::findOrFail($recordId);
    $this->recordId = $record->id;

    $this->pnumero = $record->pnumero;
    $this->contact_id = $record->contact_id;
    $this->bank_id = $record->bank_id;
    $this->product_id = $record->product_id;
    $this->proceso_id = $record->proceso_id;
    $this->currency_id = $record->currency_id;
    $this->pnombre_apellidos_deudor = $record->pnombre_apellidos_deudor;
    $this->pcedula_deudor = $record->pcedula_deudor;
    $this->psaldo_dolarizado = $record->psaldo_dolarizado;
    $this->psaldo_de_seguros = $record->psaldo_de_seguros;
    $this->psaldo_de_multas = $record->psaldo_de_multas;

    //Campos de fecha
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

    $this->abogado_id = $record->abogado_id;
    $this->asistente1_id = $record->asistente1_id;
    $this->asistente2_id = $record->asistente2_id;
    $this->pnumero_operacion1 = $record->pnumero_operacion1;
    $this->pnumero_operacion2 = $record->pnumero_operacion2;
    $this->pnumero_contrato = $record->pnumero_contrato;
    $this->pnombre_demandado = $record->pnombre_demandado;
    $this->pnumero_cedula = $record->pnumero_cedula;
    $this->pnombre_arrendatario = $record->pnombre_arrendatario;
    $this->pcedula_arrendatario = $record->pcedula_arrendatario;
    $this->pcorreo_demandado_deudor_o_arrendatario = $record->pcorreo_demandado_deudor_o_arrendatario;
    $this->ptelefono_demandado_deudor_o_arrendatario = $record->ptelefono_demandado_deudor_o_arrendatario;
    $this->pnombre_contacto_o_arrendatario = $record->pnombre_contacto_o_arrendatario;
    $this->pnombre_coarrendatario = $record->pnombre_coarrendatario;
    $this->pcedula_coarrendatario = $record->pcedula_coarrendatario;
    $this->pcorreo_coarrendatario = $record->pcorreo_coarrendatario;
    $this->ptelefono_coarrendatario = $record->ptelefono_coarrendatario;
    $this->pdatos_codeudor1 = $record->pdatos_codeudor1;
    $this->pdatos_codeudor2 = $record->pdatos_codeudor2;
    $this->pdatos_anotantes = $record->pdatos_anotantes;
    $this->pdetalle_garantia = $record->pdetalle_garantia;
    $this->pubicacion_garantia = $record->pubicacion_garantia;
    $this->pdespacho_judicial_juzgado = $record->pdespacho_judicial_juzgado;
    $this->pnumero_expediente_judicial = $record->pnumero_expediente_judicial;
    $this->pmonto_estimacion_demanda = $record->pmonto_estimacion_demanda;
    $this->pexpectativa_recuperacion_id = $record->pexpectativa_recuperacion_id;
    $this->pgastos_legales_caso = $record->pgastos_legales_caso;
    $this->pcomentarios_bullet_point = $record->pcomentarios_bullet_point;
    $this->pplaca1 = $record->pplaca1;
    $this->pplaca2 = $record->pplaca2;
    $this->pdepartamento_solicitante = $record->pdepartamento_solicitante;
    $this->pcontrato_leasing = $record->pcontrato_leasing;
    $this->ptitular_contrato = $record->ptitular_contrato;
    $this->pcedula_titular = $record->pcedula_titular;
    $this->pestatus_operacion = $record->pestatus_operacion;
    $this->ppoderdante_id = $record->ppoderdante_id;
    $this->npartes_notificadas = $record->npartes_notificadas;
    $this->apuesta_posesion = $record->apuesta_posesion;
    $this->agastos_legales = $record->agastos_legales;
    $this->ahonorarios_totales = $record->ahonorarios_totales;
    $this->anumero_placa1 = $record->anumero_placa1;
    $this->anumero_placa2 = $record->anumero_placa2;
    $this->acolisiones_embargos_anotaciones = $record->acolisiones_embargos_anotaciones;
    $this->anumero_marchamo = $record->anumero_marchamo;
    $this->afirma_legal = $record->afirma_legal;
    $this->afecha_registro = $record->afecha_registro;
    $this->afecha_presentacion_protocolizacion = $record->afecha_presentacion_protocolizacion;
    $this->afecha_inscripcion = $record->afecha_inscripcion;
    $this->afecha_terminacion = $record->afecha_terminacion;
    $this->afecha_suspencion_arreglo = $record->afecha_suspencion_arreglo;
    $this->ajustificacion_casos_protocolizados_embargo = $record->ajustificacion_casos_protocolizados_embargo;
    $this->aestado_proceso_general_id = $record->aestado_proceso_general_id;
    $this->atipo_expediente = $record->atipo_expediente;
    $this->areasignaciones = $record->areasignaciones;
    $this->nmarchamo = $record->nmarchamo;
    $this->nanotaciones = $record->nanotaciones;
    $this->nubicacion_garantia = $record->nubicacion_garantia;
    $this->ntalleres_situaciones = $record->ntalleres_situaciones;
    $this->ncomentarios = $record->ncomentarios;
    $this->nhonorarios_notificacion = $record->nhonorarios_notificacion;
    $this->nhonorarios_cobro_administrativo = $record->nhonorarios_cobro_administrativo;
    $this->nexonerado_cobro = $record->nexonerado_cobro;
    $this->nestado_actual_primera_notificacion = $record->nestado_actual_primera_notificacion;
    $this->noposicion_demanda = $record->noposicion_demanda;
    $this->ntipo_garantia = $record->ntipo_garantia;
    $this->nembargos_cuentas = $record->nembargos_cuentas;
    $this->nembargos_salarios = $record->nembargos_salarios;
    $this->nembargos_muebles = $record->nembargos_muebles;
    $this->nembargos_inmuebles = $record->nembargos_inmuebles;
    $this->nestado_id = $record->nestado_id;
    $this->acomentarios = $record->acomentarios;
    $this->aregistro_pago = $record->aregistro_pago;
    $this->atraspaso_tercero = $record->atraspaso_tercero;
    $this->thonorarios_traspaso = $record->thonorarios_traspaso;
    $this->tgastos_traspaso = $record->tgastos_traspaso;
    $this->ttraspaso_favor_tercero = $record->ttraspaso_favor_tercero;
    $this->tborrador_escritura = $record->tborrador_escritura;
    $this->tautorizacion_tercero = $record->tautorizacion_tercero;
    $this->tgastos_legales = $record->tgastos_legales;
    $this->thonorarios_totales = $record->thonorarios_totales;
    $this->lasesoramiento_formal = $record->lasesoramiento_formal;
    $this->lsumaria = $record->lsumaria;
    $this->lcausa = $record->lcausa;
    $this->lproveedores_servicio = $record->lproveedores_servicio;
    $this->fhonorarios_levantamiento = $record->fhonorarios_levantamiento;
    $this->fcomision_ccc = $record->fcomision_ccc;
    $this->fhonorarios_totales = $record->fhonorarios_totales;
    $this->egestion_a_realizar = $record->egestion_a_realizar;
    $this->eestado_cliente_gran_tamano = $record->eestado_cliente_gran_tamano;
    $this->ranotacion = $record->ranotacion;
    $this->rmarchamo_al_dia = $record->rmarchamo_al_dia;
    $this->rpendiente = $record->rpendiente;
    $this->rcausa = $record->rcausa;
    $this->rhonorario_escritura_inscripcion = $record->rhonorario_escritura_inscripcion;
    $this->rgastos_impuestos = $record->rgastos_impuestos;
    $this->dnombre_notario = $record->dnombre_notario;
    $this->dnumero_carnet = $record->dnumero_carnet;
    $this->dcorreo_electronico = $record->dcorreo_electronico;
    $this->dnumero_telefonico = $record->dnumero_telefonico;
    $this->destado_casos_con_anotaciones = $record->destado_casos_con_anotaciones;
    $this->dnumero_expediente = $record->dnumero_expediente;
    $this->dresultado_sentencia = $record->dresultado_sentencia;
    $this->dgastos_microfilm = $record->dgastos_microfilm;
    $this->dhonorarios = $record->dhonorarios;
    $this->bapersonamiento_formal = $record->bapersonamiento_formal;
    $this->bsumaria = $record->bsumaria;
    $this->bcausa = $record->bcausa;
    $this->bproveedores_servicios = $record->bproveedores_servicios;
    $this->bgastos_proceso = $record->bgastos_proceso;
    $this->bhonorarios_levantamiento = $record->bhonorarios_levantamiento;
    $this->bhonorarios_comision = $record->bhonorarios_comision;
    $this->bhonorarios_totales = $record->bhonorarios_totales;
    $this->f1proveedor_servicio = $record->f1proveedor_servicio;
    $this->f1estado_captura = $record->f1estado_captura;
    $this->f1honorarios_capturador = $record->f1honorarios_capturador;
    $this->f1honorarios_comision = $record->f1honorarios_comision;
    $this->f2causa_remate = $record->f2causa_remate;
    $this->f2publicacion_edicto = $record->f2publicacion_edicto;
    $this->f2tiempo_concedido_edicto = $record->f2tiempo_concedido_edicto;
    $this->f2preclusion_tiempo = $record->f2preclusion_tiempo;
    $this->f2estado_remanente = $record->f2estado_remanente;
    $this->abienes_adjudicados = $record->abienes_adjudicados;
    $this->asaldo_capital_operacion = $record->asaldo_capital_operacion;
    $this->aestimacion_demanda_en_presentacion = $record->aestimacion_demanda_en_presentacion;
    $this->abufete = $record->abufete;
    $this->acarga_gastos_legales = $record->acarga_gastos_legales;
    $this->agastos_mas_honorarios_acumulados = $record->agastos_mas_honorarios_acumulados;
    $this->ahonorarios_iniciales = $record->ahonorarios_iniciales;
    $this->adiferencia_demanda_presentada = $record->adiferencia_demanda_presentada;
    $this->adiferencia_sentencia_afavor = $record->adiferencia_sentencia_afavor;
    $this->adiferencia_sentencia_enfirme = $record->adiferencia_sentencia_enfirme;
    $this->adiferencia_liquidacion_de_sentencia_enfirme = $record->adiferencia_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_segunda_liquidacion_de_sentencia_enfirme = $record->adiferencia_segunda_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_tercera_liquidacion_de_sentencia_enfirme = $record->adiferencia_tercera_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_cuarta_liquidacion_de_sentencia_enfirme = $record->adiferencia_cuarta_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_quinta_liquidacion_de_sentencia_enfirme = $record->adiferencia_quinta_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_sexta_liquidacion_de_sentencia_enfirme = $record->adiferencia_sexta_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_septima_liquidacion_de_sentencia_enfirme = $record->adiferencia_septima_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_octava_liquidacion_de_sentencia_enfirme = $record->adiferencia_octava_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_novena_liquidacion_de_sentencia_enfirme = $record->adiferencia_novena_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_primera_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_primera_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_septima_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_septima_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_octava_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_octava_liquidacion_de_sentencia_enfirme;
    $this->adiferencia_decima_novena_liquidacion_de_sentencia_enfirme = $record->adiferencia_decima_novena_liquidacion_de_sentencia_enfirme;
    $this->agastos_legales_iniciales = $record->agastos_legales_iniciales;
    $this->adiferencia_gastos_legales = $record->adiferencia_gastos_legales;
    $this->anumero_grupo = $record->anumero_grupo;
    $this->ajuzgado = $record->ajuzgado;
    $this->aestado_operacion = $record->aestado_operacion;
    $this->pretenciones = $record->pretenciones;
    $this->testado_proceso_id = $record->testado_proceso_id;
    $this->lestado_levantamiento_id = $record->lestado_levantamiento_id;
    $this->bestado_levantamiento_id = $record->bestado_levantamiento_id;
    $this->ddespacho_judicial_juzgado_id = $record->ddespacho_judicial_juzgado_id;
    $this->ldespacho_judicial_juzgado_id = $record->ldespacho_judicial_juzgado_id;
    $this->pnumero_tarjeta = $record->pnumero_tarjeta;
    $this->pnombre_persona_juridica = $record->pnombre_persona_juridica;
    $this->pnumero_cedula_juridica = $record->pnumero_cedula_juridica;
    $this->pcomprador = $record->pcomprador;
    $this->amonto_avaluo = $record->amonto_avaluo;
    $this->aembargo_cuentas = $record->aembargo_cuentas;
    $this->aembargo_salarios = $record->aembargo_salarios;
    $this->aembargo_muebles = $record->aembargo_muebles;
    $this->aembargo_inmuebles = $record->aembargo_inmuebles;
    $this->aretenciones_con_giro = $record->aretenciones_con_giro;
    $this->pmonto_estimacion_demanda_colones = $record->pmonto_estimacion_demanda_colones;
    $this->pmonto_estimacion_demanda_dolares = $record->pmonto_estimacion_demanda_dolares;
    $this->pmonto_retencion_colones = $record->pmonto_retencion_colones;
    $this->pmonto_retencion_dolares = $record->pmonto_retencion_dolares;
    $this->pinmueble = $record->pinmueble;
    $this->pvehiculo = $record->pvehiculo;
    $this->pente = $record->pente;
    $this->pmonto_prima = $record->pmonto_prima;
    $this->pplazo_arreglo_pago = $record->pplazo_arreglo_pago;
    $this->pmonto_arreglo_pago = $record->pmonto_arreglo_pago;
    $this->pmonto_cuota = $record->pmonto_cuota;
    $this->pestado_arreglo = $record->pestado_arreglo;
    $this->pno_cuota = $record->pno_cuota;
    $this->pdatos_fiadores = $record->pdatos_fiadores;
    $this->psubsidiaria = $record->psubsidiaria;
    $this->amonto_cancelar = $record->amonto_cancelar;
    $this->amonto_incobrable = $record->amonto_incobrable;
    $this->acontacto_telefonico = $record->acontacto_telefonico;
    $this->acorreo = $record->acorreo;
    $this->pmueble = $record->pmueble;
    $this->pestadoid = $record->pestadoid;
    $this->ames_avance_judicial = $record->ames_avance_judicial;
    $this->pavance_cronologico = $record->pavance_cronologico;
    $this->lavance_cronologico = $record->lavance_cronologico;
    $this->savance_cronologico = $record->savance_cronologico;
    $this->aavance_cronologico = $record->aavance_cronologico;
    $this->f1avance_cronologico = $record->f1avance_cronologico;
    $this->f2avance_cronologico = $record->f2avance_cronologico;
    $this->navance_cronologico = $record->navance_cronologico;
    $this->nombre_cliente = $record->nombre_cliente;
    $this->email_cliente = $record->email_cliente;
    $this->codigo_activacion = $record->codigo_activacion;
    $this->user_create = $record->user_create;
    $this->user_update = $record->user_update;
    $this->pultima_gestion_cobro_administrativo = $record->pultima_gestion_cobro_administrativo;
    $this->estado_id = $record->estado_id;
    $this->asaldo_capital_operacion_usd = $record->asaldo_capital_operacion_usd;
    $this->aestimacion_demanda_en_presentacion_usd = $record->aestimacion_demanda_en_presentacion_usd;
    $this->liquidacion_intereses_aprobada_crc = $record->liquidacion_intereses_aprobada_crc;
    $this->liquidacion_intereses_aprobada_usd = $record->liquidacion_intereses_aprobada_usd;
    $this->ahonorarios_totales_usd = $record->ahonorarios_totales_usd;
    $this->tiempo_dias = $record->tiempo_dias;
    $this->tiempo_annos = $record->tiempo_annos;
    $this->empresa = $record->empresa;
    $this->motivo_terminacion = $record->motivo_terminacion;
    $this->honorarios_legales_dolares = $record->honorarios_legales_dolares;

    $this->action = 'edit';
    $this->dispatch('select2');
  }


  public function update()
  {
    $this->formatDateForStorageDB();

    //Campos de fecha
    $this->validate();

    $validatedData = $this->validate();

    DB::beginTransaction();
    try {
      $record = Caso::findOrFail($this->recordId);
      $record->update($validatedData);
      DB::commit();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => 'Caso actualizado correctamente.']);
      $this->action = 'edit';
      $this->edit($record->id);
    } catch (\Throwable $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Error al actualizar el caso: ' . $e->getMessage()]);
    }
  }

  // ===================== Helpers =====================

  /** Devuelve el listado COMPLETO de campos persistibles (todas las columnas) */
  private function fieldList(): array
  {
    // Todas las propiedades excepto auxiliares de componente
    return [
      // -- copiar literalmente el bloque de propiedades sin id timestamps si quieres excluirlos --
      // Para no omitir NADA, incluimos todo excepto id y timestamps (autogestionados).
      // Si quieres también persistir created_at/updated_at/deleted_at, añade esos campos aquí.
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
      'honorarios_legales_dolares'
    ];
  }

  // ====== Datatable (placeholder mínimo para respetar estructura del ejemplo) ======
  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'classifier-casos-scotiabanks-datatable')
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
}
