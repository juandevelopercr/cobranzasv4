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
      'contact_id'   => ['required', 'integer', 'exists:clientes,id'],
      'bank_id'     => ['required', 'integer', 'exists:bancos,id'],
      'product_id'  => ['required', 'integer', 'exists:casos_productos,id'],
      'currency_id'    => ['required', 'integer', 'exists:monedas,id'],
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

    $this->action = 'edit';
    $this->dispatch('select2');
  }

  public function update()
  {
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

  public function delete($id)
  {
    try {
      $record = Caso::findOrFail($id);
      $record->delete();
      $this->dispatch('show-notification', ['type' => 'success', 'message' => 'Caso eliminado.']);
    } catch (\Throwable $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => 'Error al eliminar: ' . $e->getMessage()]);
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

  /** Construye el array para persistir incluído formateo de TODAS las fechas. */
  private function collectAllFieldsForPersistence(): array
  {
    $fields = $this->fieldList();

    $data = [];
    foreach ($fields as $f) {
      $val = $this->{$f} ?? null;
      if (in_array($f, $this->dateFields)) {
        $data[$f] = $this->normalizeDate($val);
      } else {
        $data[$f] = $val;
      }
    }

    // aseguramos bank_id si venimos preseleccionado en mount
    /*
    if (!$data['bank_id'] && $this->banks && $this->banks->first()) {
      $data['bank_id'] = $this->banks->first()->id;
    }
    */
    $data['bank_id'] = $this->bank_id;


    // No tocamos created_at/updated_at/deleted_at aquí (Eloquent los maneja)
    return $data;
  }

  private function normalizeDate($value)
  {
    if (empty($value)) return null;
    try {
      return Carbon::parse($value)->format('Y-m-d');
    } catch (\Throwable $e) {
      return null;
    }
  }

  public function resetControls()
  {
    $this->reset($this->fieldList());
    $this->recordId = '';
    $this->dispatch('updateSelectedIds', []);
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
        'label' => __('Número'),
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
}
