<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Bank;
use App\Models\BusinessLocation;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\EconomicActivity;
use App\Models\Transaction;
use App\Models\TransactionCommission;
use App\Models\TransactionLine;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\DocumentSequenceService;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

class ProformaManager extends TransactionManager {
  public $tiposFacturacion = [];
  public $caso_text = ''; // para mostrar el texto inicial
  public $customer_text = ''; // para mostrar el texto inicial
  public $activeTab = 'invoice'; // Pestaña activa por defecto

  public $filters = [
    'filter_action' => NULL,
    'filter_proforma_status' => NULL,
    'filter_transaction_date' => NULL,
    'filter_fecha_solicitud_factura' => NULL,
    'filter_proforma_no' => NULL,
    'filter_consecutivo' => NULL,
    'filter_customer_name' => NULL,
    'filter_numero_caso' => NULL,
    'filter_user_name' => NULL,
    'filter_issuer_name' => NULL,
    'filter_codigosContables' => NULL,
    'filter_nombre_caso' => NULL,
    'filter_oc' => NULL,
    'filter_migo' => NULL,
    'filter_bank_name' => NULL,
    'filter_currency_code' => NULL,
    'filter_proforma_type' => NULL,
    'filter_fecha_envio_email' => NULL,
    'filter_totalComprobante' => NULL,
    'filter_total_honorarios_con_iva_usd' => NULL,
    'filter_total_honorarios_con_iva_crc' => NULL,
    'filter_total_honorarios_usd' => NULL,
    'filter_total_honorarios_crc' => NULL,
    'filter_total_iva_usd' => NULL,
    'filter_total_iva_crc' => NULL,
    'filter_total_usd' => NULL,
    'filter_total_crc' => NULL
  ];

  public $listaUsuarios = [];

  public $document_type = ['PR', 'FE', 'TE', 'NCE', 'NDE'];

  public function mount() {
    parent::mount();
    $this->listaUsuarios = User::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->statusOptions = [];
    $this->statusOptions = Transaction::getStatusOptions(false);
    $this->tiposFacturacion = [
      ['id' => 1, 'name' => 'Individual'],
      ['id' => 2, 'name' => 'Masiva'],
      ['id' => 3, 'name' => 'Sin Caso'],
    ];
    // Aquí puedes agregar lógica específica para proformas
  }

  public function changeTab($tab) {
    $this->activeTab = $tab;
  }

  public function refresDatatable() {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'proforma-datatable')
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

    $this->syncExportFilters();
  }

  public function getDefaultColumns(): array {
    $this->defaultColumns = [
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
        'function' => 'getProformaHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'proforma_status',
        'orderName' => 'transactions.proforma_status',
        'label' => __('Status'),
        'filter' => 'filter_proforma_status',
        'filter_type' => 'select',
        'filter_sources' => 'statusOptions',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => 'wrap-col-100',
        'function' => 'getHtmlProformaStatus',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'transaction_date',
        'orderName' => 'transactions.transaction_date',
        'label' => __('Emmision Date'),
        'filter' => 'filter_transaction_date',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => 'center',
        'columnClass' => 'wrap-col-100',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'fecha_solicitud_factura',
        'orderName' => 'transactions.fecha_solicitud_factura',
        'label' => __('Application Date'),
        'filter' => 'filter_fecha_solicitud_factura',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => 'center',
        'columnClass' => 'wrap-col-100',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'proforma_no',
        'orderName' => 'proforma_no',
        'label' => __('No. Proforma'),
        'filter' => 'filter_proforma_no',
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
        'field' => 'consecutivo',
        'orderName' => 'consecutivo',
        'label' => __('Consecutive'),
        'filter' => 'filter_consecutivo',
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
        'field' => 'customer_name',
        'orderName' => 'contacts.name',
        'label' => __('Customer'),
        'filter' => 'filter_customer_name',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'caso_info',
        'orderName' => '',
        'label' => __('Case Number'),
        'filter' => 'filter_numero_caso',
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
        'field' => 'user_name',
        'orderName' => 'users.name',
        'label' => __('User'),
        'filter' => 'filter_user_name',
        'filter_type' => 'select',
        'filter_sources' => 'users',
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
        'field' => 'issuer_name',
        'orderName' => 'business_locations.name',
        'label' => __('Issuer'),
        'filter' => 'filter_issuer_name',
        'filter_type' => 'select',
        'filter_sources' => 'issuers',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'codigosContables',
        'orderName' => 'codigo_contables.codigo',
        'label' => __('Accounting Code'),
        'filter' => 'filter_codigosContables',
        'filter_type' => 'select',
        'filter_sources' => 'codigosContables',
        'filter_source_field' => 'descrip',
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
        'field' => 'nombre_caso',
        'orderName' => '',
        'label' => __('Case/Reference'),
        'filter' => 'filter_nombre_caso',
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
        'field' => 'oc',
        'orderName' => 'oc',
        'label' => __('O.C'),
        'filter' => 'filter_oc',
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
        'field' => 'migo',
        'orderName' => 'migo',
        'label' => __('MIGO'),
        'filter' => 'filter_migo',
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
        'field' => 'currency_code',
        'orderName' => 'currencies.code',
        'label' => __('Currency'),
        'filter' => 'filter_currency_code',
        'filter_type' => 'select',
        'filter_sources' => 'currencies',
        'filter_source_field' => 'code',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => 'wrap-col-100',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'proforma_type',
        'orderName' => 'transactions.proforma_type',
        'label' => __('Type of Notarial Act'),
        'filter' => 'filter_proforma_type',
        'filter_type' => 'select',
        'filter_sources' => 'proformaTypes',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => 'wrap-col-100',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'fecha_envio_email',
        'orderName' => 'transactions.fecha_envio_email',
        'label' => __('Fecha de envio de email'),
        'filter' => 'filter_fecha_envio_email',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => 'center',
        'columnClass' => 'wrap-col-100',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalComprobante',
        'orderName' => '',
        'label' => __('Total'),
        'filter' => 'filter_totalComprobante',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tComprobante',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalHonorariosIva',
        'orderName' => '',
        'label' => __('Total Honorarios Con IVA USD'),
        'filter' => 'filter_total_honorarios_con_iva_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalHonorarioIva',
        'parameters' => ['USD', true],
        'sumary' => 'tHonorarioIvaUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => '',
        'visible' => true,
      ],
      [
        'field' => 'totalHonorariosIva',
        'orderName' => '',
        'label' => __('Total Honorarios Con IVA CRC'),
        'filter' => 'filter_total_honorarios_con_iva_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalHonorarioIva',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tHonorarioIvaCrc',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalHonorarios',
        'orderName' => '',
        'label' => __('Total Honorarios USD'),
        'filter' => 'filter_total_honorarios_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalHonorario',
        'parameters' => ['USD', true],
        'sumary' => 'tHonorarioUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalHonorarios',
        'orderName' => '',
        'label' => __('Total Honorarios CRC'),
        'filter' => 'filter_total_honorarios_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalHonorario',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tHonorarioCrc',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalHonorarios',
        'orderName' => '',
        'label' => __('Total Honorarios USD'),
        'filter' => 'filter_total_honorarios_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalHonorario',
        'parameters' => ['USD', true],
        'sumary' => 'tHonorarioUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalHonorarios',
        'orderName' => '',
        'label' => __('Total Honorarios CRC'),
        'filter' => 'filter_total_honorarios_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalHonorario',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tHonorarioCrc',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'total_iva',
        'orderName' => '',
        'label' => __('Total IVA USD'),
        'filter' => 'filter_total_iva_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalIva',
        'parameters' => ['USD', true],
        'sumary' => 'tIvaUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalHonorarios',
        'orderName' => '',
        'label' => __('Total IVA CRC'),
        'filter' => 'filter_total_iva_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => 'wrap-col-100',
        'function' => 'getTotalIva',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tIvaCrc',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'total_usd',
        'orderName' => '',
        'label' => __('Total USD'),
        'filter' => 'filter_total_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => 'getTotalComprobante',
        'parameters' => ['USD', true],
        'sumary' => 'tComprobanteUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'total_crc',
        'orderName' => '',
        'label' => __('Total CRC'),
        'filter' => 'filter_total_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => 'right',
        'columnClass' => '',
        'function' => 'getTotalComprobante',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tComprobanteCrc',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ]
    ];

    return $this->defaultColumns;
  }

  protected function afterTransactionSaved() {
    // Lógica específica tras guardar una proforma
    // Ejemplo: generar PDF, enviar notificación, etc.
  }

  protected function getFilteredQuery() {
    $document_type = $this->document_type;
    if (!is_array($this->document_type)) {
      $document_type = [$this->document_type];
    }

    $query = Transaction::search($this->search, $this->filters)
      ->whereIn('document_type', $document_type);

    // Condiciones según el rol del usuario
    $allowedRoles = User::ROLES_ALL_BANKS;
    $user = auth()->user();
    if ($user->hasAnyRole($allowedRoles)) {
      $query->where(function ($q) use ($allowedRoles) {
        // Condición 1: Estado PROCESO creado por usuario con rol especial
        $q->where('proforma_status', Transaction::PROCESO)
          ->whereExists(function ($subquery) use ($allowedRoles) {
            $subquery->select(DB::raw(1))
              ->from('role_user')
              ->join('roles', 'roles.id', '=', 'role_user.role_id')
              ->whereColumn('role_user.user_id', 'transactions.created_by')
              ->whereIn('roles.name', $allowedRoles);
          });

        // Condición 2: Estado SOLICITADA (sin filtro de usuario)
        $q->orWhere('proforma_status', Transaction::SOLICITADA);
      });
    } else {
      //Obtener bancos
      $allowedBanks = $user->banks->pluck('id');
      if (!empty($allowedBanks)) {
        $query->whereIn('transactions.bank_id', $allowedBanks);
      }

      // Excluir transacciones creadas por usuarios con roles especiales
      $query->whereNotExists(function ($subquery) use ($allowedRoles) {
        $subquery->select(DB::raw(1))
          ->from('role_user')
          ->join('roles', 'roles.id', '=', 'role_user.role_id')
          ->whereColumn('role_user.user_id', 'transactions.created_by')
          ->whereIn('roles.name', $allowedRoles);
      });

      // Mostrar transacciones creadas por el usuario
      $query->where('transactions.created_by', auth()->user()->id);
    }

    return $query;
  }

  public function render() {
    $query = $this->getFilteredQuery();

    $allowedRoles = User::ROLES_ALL_BANKS;
    // Condiciones según el rol del usuario
    if (Auth::user()->hasAnyRole($allowedRoles)) {
      $query->orderBy('transactions.transaction_date', 'DESC')
        ->orderBy('id', 'DESC');
    } else
      $query->orderBy('transactions.id', 'DESC');

    // Ordenamiento y paginación final
    $records = $query->paginate($this->perPage);

    $stats = $this->getStatics();

    $this->totalProceso = 0;
    $this->totalPorAprobar = 0;
    $this->totalUsdHonorario = 0;
    $this->totalCrcHonorario = 0;
    $this->totalUsdGasto = 0;
    $this->totalCrcGasto = 0;

    if ($stats) {
      $this->totalProceso = $stats->total_facturas_proceso ?? 0;
      $this->totalPorAprobar = $stats->facturas_por_aprobar ?? 0;
      $this->totalUsdHonorario = $stats->totalUsdHonorario ?? 0;
      $this->totalCrcHonorario = $stats->totalCrcHonorario ?? 0;
      $this->totalUsdGasto = $stats->totalUsdGasto ?? 0;
      $this->totalCrcGasto = $stats->totalCrcGasto ?? 0;
    }

    return view('livewire.transactions.proforma-datatable', [
      'records' => $records,
    ]);
  }

  public function create() {
    $this->customer_text = '';
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->cleanEmptyForeignKeys();

    // Obtener la fecha actual en formato Y-m-d
    $today = Carbon::now()->toDateString();

    // Convertir a formato d-m-Y para mostrar en el input
    $this->show_transaction_date = Carbon::parse($today)->format('d-m-Y');
    $this->transaction_date = Carbon::parse($today)->format('Y-m-d H:i:s');

    $this->created_by = Auth::user()->id;
    $this->invoice_type = 'FACTURA';
    $this->document_type = Transaction::PROFORMA;

    $this->payment_status = 'due';

    $this->proforma_status = 'PROCESO';
    $this->proforma_change_type = Helpers::formatDecimal(Session::get('exchange_rate'));

    $this->payments = [[
      'tipo_medio_pago' => '04', // Transferencia
      'medio_pago_otros' => '',
      'total_medio_pago' => '0',
    ]];

    $text = '';
    $this->dispatch('setSelect2Value', id: 'contact_id', value: '', text: $text);
    $text = '';
    $this->dispatch('setSelect2Value', id: 'caso_id', value: '', text: $text);

    $this->recalcularVuelto();

    $this->action = 'create';
    $this->dispatch('scroll-to-top');
    $this->dispatch('reinitSelect2Controls');
  }

  // Definir reglas, mensajes y atributos
  protected function rules() {
    $rules = [
      // Foreign Keys
      'business_id'           => 'required|integer|exists:business,id',
      'location_id'           => 'nullable|integer|exists:business_locations,id',
      'location_economic_activity_id'  => 'nullable|integer|exists:economic_activities,id',
      'cuenta_id'             => 'nullable|integer|exists:cuentas,id',
      'contact_id'            => 'required|integer|exists:contacts,id',
      'department_id'         => 'nullable|integer|exists:departments,id',
      'contact_economic_activity_id' => 'nullable|integer|exists:economic_activities,id',
      'currency_id'           => 'required|integer|exists:currencies,id',
      'area_id'               => 'nullable|integer|exists:areas,id',
      'bank_id'               => 'nullable|integer|exists:banks,id',
      'codigo_contable_id'    => 'nullable|integer|exists:codigo_contables,id',
      'caso_id'               => 'nullable|required_if:tipo_facturacion,1|integer|exists:casos,id',
      'created_by'            => 'required|integer|exists:users,id',
      'tipo_facturacion'      => 'required|integer',

      // Enums
      'document_type'         => 'required|in:PR,FE,TE,ND,NC,FEC,FEE,REP',
      'proforma_type'         => 'required|in:HONORARIO,GASTO',
      'proforma_status'       => 'nullable|in:PROCESO,SOLICITADA,FACTURADA,RECHAZADA,ANULADA',
      'status'                => 'nullable|in:PENDIENTE,RECIBIDA,ACEPTADA,RECHAZADA,ANULADA',
      'showInstruccionesPago' => 'nullable|in:NACIONAL,INTERNACIONAL,AMBAS',
      //'payment_status'        => 'nullable|in:paid,due,partial',
      'pay_term_type'         => 'nullable|in:days,months',

      'invoice_type'          => 'required|in:FACTURA,TIQUETE',

      // Strings
      'customer_name'         => 'required|string|max:150',
      'customer_comercial_name' => 'nullable|string|max:150',
      'customer_email'        => 'nullable|email|max:150',
      'email_cc'              => 'nullable|string',
      'nombre_caso'           => 'nullable|string|max:191',

      //'proforma_no'           => 'nullable|string|max:20',
      //'consecutivo'           => 'nullable|string|max:20',
      //'key'                   => 'nullable|string|max:50',
      //'access_token'          => 'nullable|string|max:191',
      //'response_xml'          => 'nullable|string|max:191',
      //'filexml'               => 'nullable|string|max:191',
      //'filepdf'               => 'nullable|string|max:191',
      //'transaction_reference' => 'nullable|string|max:50',
      //'transaction_reference_id' => 'nullable|string|max:50',
      'condition_sale' => 'required|string|in:01,02,03,04,05,06,06,08,09,10,11,12,13,14,15,99|max:2',
      'condition_sale_other' => 'nullable|required_if:condition_sale,99|max:100|string',
      //'numero_deposito_pago'  => 'nullable|string|max:191',
      //'numero_traslado_honorario' => 'nullable|string|max:20',
      //'numero_traslado_gasto' => 'nullable|string|max:20',
      'contacto_banco'        => 'nullable|string|max:100',

      // Numerics
      //'pay_term_number'     => 'nullable|integer|min:0',
      //'pay_term_number'       => 'required_if:condition_sale,02|numeric|min:1|max:100',
      //'pay_term_number' => 'sometimes|required_if:condition_sale,02|numeric|max:100',
      //'proforma_change_type'  => 'nullable|numeric|required_if:document_type,PR|min:0.1|max:999999999999999.99999',
      //'factura_change_type'   => 'nullable|numeric|min:0|max:999999999999999.99999',
      'proforma_change_type' => 'present|required|numeric|min:0.1|max:999999999999999.99999',
      'factura_change_type'   => 'nullable|numeric|min:0|max:999999999999999.99999',
      //'num_request_hacienda_set' => 'nullable|integer|min:0',
      //'num_request_hacienda_get' => 'nullable|integer|min:0',
      //'comision_pagada'       => 'nullable|boolean',
      //'is_retencion'          => 'nullable|boolean',

      // Texts
      'message'               => 'nullable|string',
      'notes'                 => 'nullable|string',
      'detalle_adicional'     => 'nullable|string',
      'oc'                    => 'nullable|string',
      'migo'                  => 'nullable|string',
      'or'                    => 'nullable|string',
      'gln'                   => 'nullable|string',
      'prebill'               => 'nullable|string',

      // Dates
      'transaction_date'         => 'required|date',
      'fecha_pago'               => 'nullable|date',
      'fecha_deposito_pago'      => 'nullable|date',
      'fecha_traslado_honorario' => 'nullable|date',
      'fecha_traslado_gasto'     => 'nullable|date',
      'fecha_solicitud_factura'  => 'nullable|date',
      'fecha_envio_email'        => 'nullable|date',

      'totalHonorarios' => 'nullable|numeric|min:0',
      'totalTimbres' => 'nullable|numeric|min:0',
      'totalDiscount' => 'nullable|numeric|min:0',
      'totalTax' => 'nullable|numeric|min:0',
      'totalAditionalCharge' => 'nullable|numeric|min:0',

      'totalServGravados' => 'nullable|numeric|min:0',
      'totalServExentos' => 'nullable|numeric|min:0',
      'totalServExonerado' => 'nullable|numeric|min:0',
      'totalServNoSujeto' => 'nullable|numeric|min:0',

      'totalMercGravadas' => 'nullable|numeric|min:0',
      'totalMercExentas' => 'nullable|numeric|min:0',
      'totalMercExonerada' => 'nullable|numeric|min:0',
      'totalMercNoSujeta' => 'nullable|numeric|min:0',

      'totalGravado' => 'nullable|numeric|min:0',
      'totalExento' => 'nullable|numeric|min:0',
      'totalVenta' => 'nullable|numeric|min:0',
      'totalVentaNeta' => 'nullable|numeric|min:0',
      'totalExonerado' => 'nullable|numeric|min:0',
      'totalNoSujeto' => 'nullable|numeric|min:0',
      'totalImpAsumEmisorFabrica' => 'nullable|numeric|min:0',
      'totalIVADevuelto' => 'nullable|numeric|min:0',
      'totalOtrosCargos' => 'nullable|numeric|min:0',
      'totalComprobante' => 'nullable|numeric|min:0',

      'totalPagado' => 'nullable|numeric|min:0',
      'pendientePorPagar' => 'nullable|numeric|min:0',
      'vuelto' => 'nullable|numeric|min:0',

      'payments.*.tipo_medio_pago' => 'required|in:01,02,03,04,05,06,07,99',
      'payments.*.total_medio_pago' => 'required|numeric|min:0',
      'payments.*.medio_pago_otros' => 'nullable|string|max:255'
    ];

    if ($this->condition_sale == '02') {
      $rules['pay_term_number'] = 'required|integer|min:1|max:1000';
    } else {
      $rules['pay_term_number'] = 'nullable';
    }

    return $rules;
  }

  // Mensajes de error personalizados
  protected function messages() {
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
      'proforma_no.required' => 'El campo proforma es obligatorio cuando el tipo de documento es PR.',
      'consecutivo.required' => 'El campo consecutivo es obligatorio para documentos que no sean proforma.',
      'caso_id.required_if' => 'El campo caso es obligatorio cuando el tipo de facturación es Individual.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes() {
    $attributes = [
      'business_id'           => 'ID del negocio',
      'document_type'         => 'tipo de documento',
      'currency_id'           => 'moneda',
      'condition_sale'        => 'condición de venta',
      'proforma_type'         => 'tipo de acto',
      'status'                => 'estado',
      'transaction_date'      => 'fecha de transacción',
      'customer_name'         => 'nombre del cliente',
      'pay_term_number'       => 'término de pago',
      'created_by'            => 'creado por',
      'location_economic_activity_id' => 'actividad económica',
      'contact_economic_activity_id'  => 'actividad económica',
      'caso_id'               => 'caso',
    ];

    return $attributes;
  }

  public function store() {
    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    // Eliminar comas del número en el servidor
    //$this->proforma_change_type = str_replace(',', '', $this->proforma_change_type);
    $this->pay_term_number = trim($this->pay_term_number);

    if ($this->pay_term_number === '' || $this->pay_term_number === null) {
      $this->pay_term_number = 0;
    }

    $this->transaction_date = Carbon::parse($this->show_transaction_date)
      ->setTime(now()->hour, now()->minute, now()->second)
      ->format('Y-m-d H:i:s');

    // Validar
    $validatedData = $this->validate();

    $validatedData['created_by'] = Auth::user()->id;

    // Generar consecutivo
    $consecutive = DocumentSequenceService::generateConsecutive(
      $validatedData['document_type'],
      $validatedData['location_id'] ?? null
    );

    $this->proforma_no = $consecutive;
    $validatedData['proforma_no'] = $consecutive;

    $this->payments = collect($this->payments)->map(function ($pago) {
      $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']);
      return $pago;
    })->toArray();

    // Validar nuevamente para asegurar que el campo correcto esté presente
    $this->validate([
      'proforma_no' => 'required|string|max:30',
    ]);

    $this->totalPagado = collect($this->payments)->sum(function ($p) {
      $valor = str_replace(',', '', $p['total_medio_pago']); // elimina separadores de miles
      return floatval($valor);
    });

    try {
      // Iniciar la transacción
      DB::beginTransaction();

      // Determinar estado de pago
      if ($this->totalPagado <= 0) {
        $this->payment_status = 'due';
      } elseif ($this->pendientePorPagar == 0) {
        $this->payment_status = 'paid';
      } else {
        $this->payment_status = 'partial';
      }

      // Crear la transacción
      $transaction = Transaction::create($validatedData);

      foreach ($this->payments as $pago) {
        $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']); // elimina separadores de miles
        $transaction->payments()->create($pago);
      }

      $closeForm = $this->closeForm;

      if ($transaction) {
        // Commit: Confirmar todos los cambios
        DB::commit();
      }

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($transaction->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {
      // Rollback: Revertir los cambios en caso de error
      DB::rollBack();
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId) {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Transaction::find($recordId);
    $this->recordId = $recordId;

    Log::info('🔍 Edit - Record ID:', ['recordId' => $recordId]);
    Log::info('🔍 Edit - Record Data:', [
      'id' => $record->id,
      'location_id' => $record->location_id,
      'contact_id' => $record->contact_id,
      'proforma_no' => $record->proforma_no
    ]);

    $this->recordId = $recordId;
    //$this->transaction = $record;
    $this->business_id            = $record->business_id;
    $this->location_id            = $record->location_id;

    Log::info('🔍 Edit - Location ID:', ['location_id' => $this->location_id]);

    $this->setlocationEconomicActivities();
    $activities = $this->locationsEconomicActivities;

    Log::info('🔍 Edit - Location Activities:', ['count' => $activities->count()]);

    $options = $activities->map(function ($activity) {
      return [
        'id' => $activity->id,
        'text' => $activity->name,
      ];
    });

    $this->dispatch('updateSelect2Options', id: 'location_economic_activity_id', options: $options);

    $this->location_economic_activity_id = $record->location_economic_activity_id;

    if ($this->location_economic_activity_id) {
      $activity = EconomicActivity::find($this->location_economic_activity_id);
      if ($activity) {
        $this->dispatch('setSelect2Value', id: 'location_economic_activity_id', value: $activity->id, text: $activity->name);
      }
    }

    $this->contact_id             = $record->contact_id;

    Log::info('🔍 Edit - Contact ID:', ['contact_id' => $this->contact_id]);

    $this->setcontactEconomicActivities();
    $activities = $this->contactEconomicActivities;

    Log::info('🔍 Edit - Contact Activities:', ['count' => $activities->count()]);

    $options = $activities->map(function ($activity) {
      return [
        'id' => $activity->id,
        'text' => $activity->name,
      ];
    });

    $this->dispatch('updateSelect2Options', id: 'contact_economic_activity_id', options: $options);

    $this->contact_economic_activity_id = $record->contact_economic_activity_id;

    if ($this->contact_economic_activity_id) {
      $activity = EconomicActivity::find($this->contact_economic_activity_id);
      if ($activity) {
        $this->dispatch('setSelect2Value', id: 'contact_economic_activity_id', value: $activity->id, text: $activity->name);
      }
    }
    $this->cuenta_id              = $record->cuenta_id;
    $this->department_id          = $record->department_id;
    $this->currency_id            = $record->currency_id;
    $this->area_id                = $record->area_id;
    $this->bank_id                = $record->bank_id;
    $this->caso_id                = $record->caso_id;
    $this->codigo_contable_id     = $record->codigo_contable_id;
    $this->created_by             = $record->created_by;
    $this->document_type          = $record->document_type;
    $this->proforma_type          = $record->proforma_type;
    $this->proforma_status        = $record->proforma_status;
    $this->status                 = $record->status;
    $this->payment_status         = $record->payment_status;
    $this->pay_term_type          = $record->pay_term_type;
    $this->customer_name          = $record->customer_name;
    $this->customer_comercial_name = $record->customer_comercial_name;
    $this->customer_email         = $record->customer_email;
    $this->email_cc               = $record->email_cc;
    $this->proforma_no            = $record->proforma_no;
    $this->consecutivo            = $record->consecutivo;
    $this->key                    = $record->key;
    $this->nombre_caso            = $record->nombre_caso;
    $this->access_token           = $record->access_token;
    $this->response_xml           = $record->response_xml;
    $this->filexml                = $record->filexml;
    $this->filepdf                = $record->filepdf;
    $this->transaction_reference  = $record->transaction_reference;
    $this->transaction_reference_id = $record->transaction_reference_id;
    $this->condition_sale         = $record->condition_sale;
    $this->condition_sale_other   = $record->condition_sale_other;
    $this->numero_deposito_pago   = $record->numero_deposito_pago;
    $this->numero_traslado_honorario = $record->numero_traslado_honorario;
    $this->numero_traslado_gasto  = $record->numero_traslado_gasto;
    $this->contacto_banco         = $record->contacto_banco;
    $this->pay_term_number        = $record->pay_term_number;
    $this->tipo_facturacion       = $record->tipo_facturacion;
    $this->proforma_change_type   = Helpers::formatDecimal($record->proforma_change_type);
    //$this->proforma_change_type   = $record->proforma_change_type;
    $this->factura_change_type    = $record->factura_change_type;
    $this->num_request_hacienda_set = $record->num_request_hacienda_set;
    $this->num_request_hacienda_get = $record->num_request_hacienda_get;
    $this->comision_pagada        = $record->comision_pagada;
    $this->is_retencion           = $record->is_retencion;
    $this->message                = $record->message;
    $this->notes                  = $record->notes;
    $this->oc                     = $record->oc;
    $this->migo                   = $record->migo;
    $this->or                     = $record->or;
    $this->gln                    = $record->gln;
    $this->prebill                = $record->prebill;
    $this->detalle_adicional      = $record->detalle_adicional;
    $this->transaction_date       = $record->transaction_date;
    $this->fecha_pago             = $record->fecha_pago;
    $this->fecha_deposito_pago    = $record->fecha_deposito_pago;
    $this->fecha_traslado_honorario = $record->fecha_traslado_honorario;
    $this->fecha_traslado_gasto   = $record->fecha_traslado_gasto;
    $this->fecha_solicitud_factura = $record->fecha_solicitud_factura;
    $this->showInstruccionesPago   = $record->showInstruccionesPago;
    $this->invoice_type            = $record->invoice_type;

    // Totales
    $this->totalHonorarios = $record->totalHonorarios;
    $this->totalTimbres = $record->totalTimbres;
    $this->totalAditionalCharge = $record->totalAditionalCharge;

    $this->totalServGravados = $record->totalServGravados;
    $this->totalServExentos = $record->totalServExentos;
    $this->totalServExonerado = $record->totalServExonerado;
    $this->totalServNoSujeto = $record->totalServNoSujeto;

    $this->totalMercGravadas = $record->totalMercGravadas;
    $this->totalMercExentas = $record->totalMercExentas;
    $this->totalMercExonerada = $record->totalMercExonerada;
    $this->totalMercNoSujeta = $record->totalMercNoSujeta;

    $this->totalGravado = $record->totalGravado;
    $this->totalExento = $record->totalExento;
    $this->totalExonerado = $record->totalExonerado;
    $this->totalNoSujeto = $record->totalExonerado;

    $this->totalVenta = $record->totalVenta;
    $this->totalDiscount = $record->totalDiscount;
    $this->totalVentaNeta = $record->totalVentaNeta;
    $this->totalTax = $record->totalTax;
    $this->totalImpAsumEmisorFabrica = $record->totalImpAsumEmisorFabrica;
    $this->totalIVADevuelto = $record->totalIVADevuelto;
    $this->totalOtrosCargos = $record->totalOtrosCargos;
    $this->totalComprobante = $record->totalComprobante;

    $this->show_transaction_date = Carbon::parse($record->transaction_date)->format('d-m-Y');
    $this->original_currency_id = $record->currency_id;

    $this->old_contact_id = $record->contact_id;

    $contact = Contact::find($record->contact_id);

    if ($contact) {
      $this->tipoIdentificacion = $contact->identificationType->name;
      $this->identificacion = $contact->identification;
      $this->clientEmail = $record->contact ? $record->contact->email : '';

      $this->customer_text = $contact->name;
      $text = $contact->name;
      $this->dispatch('setSelect2Value', id: 'contact_id', value: $this->contact_id, text: $text);
    }


    // Emitir evento a los componentes hijos (NO usar sesión para evitar contaminación cruzada)
    $this->dispatch('updateTransactionContext', [
      'transaction_id'    => $record->id,
      'bank_id'           => $record->bank_id,
      'type_notarial_act' => $record->proforma_type,
      'tipo_facturacion' => $record->tipo_facturacion,
    ]);

    $this->payments = $record->payments->map(fn($p) => [
      'id'              => $p->id,
      'tipo_medio_pago' => $p->tipo_medio_pago,
      'medio_pago_otros' => $p->medio_pago_otros,
      'total_medio_pago' => Helpers::formatDecimal($p->total_medio_pago),
    ])->toArray();

    if (empty($this->payments))
      $this->payments = [[
        'tipo_medio_pago' => '04', // Transferencia
        'medio_pago_otros' => '',
        'total_medio_pago' => '0',
      ]];

    $this->recalcularVuelto();

    //$this->setEnableControl();

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';

    $this->setlocationEconomicActivities();
    $this->setcontactEconomicActivities();

    if ($this->location_economic_activity_id) {
      $activity = EconomicActivity::find($this->location_economic_activity_id);
      if ($activity) {
        $this->dispatch('setSelect2Value', id: 'location_economic_activity_id', value: $activity->id, text: $activity->name);
      }
    }

    if ($this->contact_economic_activity_id) {
      $activity = EconomicActivity::find($this->contact_economic_activity_id);
      if ($activity) {
        $this->dispatch('setSelect2Value', id: 'contact_economic_activity_id', value: $activity->id, text: $activity->name);
      }
    }

    if ($record->caso) {
      $this->caso_text = strtoupper(
        implode(' / ', array_filter([
          $record->caso->pnumero,
          $record->caso->pnumero_operacion1,
          ($record->caso->pnombre_demandado || $record->caso->pnombre_apellidos_deudor)
            ? trim($record->caso->pnombre_demandado . ' ' . $record->caso->pnombre_apellidos_deudor)
            : null
        ], fn($value) => $value !== null && $value !== ''))
      );
      $this->dispatch('setSelect2Value', id: 'caso_id', value: $this->caso_id, text: $this->caso_text);
    } else {
      $this->dispatch('setSelect2Value', id: 'caso_id', value: '', text: '');
    }

    $this->setInfoCaso();

    $this->dispatch('reinitSelect2Controls');
    $this->dispatch('reinitSelect2Caso');
  }

  public function update() {
    $recordId = $this->recordId;

    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    // Eliminar comas del número en el servidor
    //dd($this->proforma_change_type);
    //$this->proforma_change_type = str_replace(',', '', $this->proforma_change_type);
    //$this->transaction_date = Carbon::parse($this->show_transaction_date)->format('Y-m-d');

    $this->transaction_date = Carbon::parse($this->show_transaction_date)
      ->setTime(now()->hour, now()->minute, now()->second)
      ->format('Y-m-d H:i:s');

    $this->pay_term_number = trim($this->pay_term_number);

    if ($this->pay_term_number === '' || $this->pay_term_number === null) {
      $this->pay_term_number = 0;
    }

    $this->payments = collect($this->payments)->map(function ($pago) {
      $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']);
      return $pago;
    })->toArray();

    // Validar
    $validatedData = $this->validate();

    try {
      // Iniciar la transacción para garantizar la atomicidad
      DB::beginTransaction();

      // Encuentra el registro existente
      $record = Transaction::findOrFail($recordId);

      // Actualizar
      $record->update($validatedData);

      $this->dispatch('updateTransactionContext', [
        'transaction_id'    => $record->id,
        'bank_id'           => $record->bank_id,
        'type_notarial_act' => $record->proforma_type,
        'tipo_facturacion'  => $record->tipo_facturacion,
      ]);

      // --- Sincronizar pagos ---
      // 1. Obtener los IDs actuales en la BD
      $existingPaymentIds = $record->payments()->pluck('id')->toArray();

      // 2. Obtener los IDs que aún están en $this->payments
      $submittedPaymentIds = collect($this->payments)
        ->pluck('id')
        ->filter() // elimina null
        ->toArray();

      // 3. Detectar los eliminados (los que ya no están)
      $idsToDelete = array_diff($existingPaymentIds, $submittedPaymentIds);

      // 4. Eliminar los pagos que ya no están
      if (!empty($idsToDelete)) {
        TransactionPayment::whereIn('id', $idsToDelete)->delete();
      }

      // 5. Crear o actualizar los que se enviaron
      foreach ($this->payments as $pago) {
        $pago['total_medio_pago'] = str_replace(',', '', $pago['total_medio_pago']);

        if (!empty($pago['id'])) {
          TransactionPayment::updateOrCreate(['id' => $pago['id']], $pago);
        } else {
          $record->payments()->create($pago);
        }
      }

      $closeForm = $this->closeForm;

      // Confirmar la transacción
      DB::commit();

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();
      $this->dispatch('scroll-to-top');

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);

      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }
    } catch (\Exception $e) {
      // Revertir la transacción en caso de error
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }
  }

  #[On('solicitarFacturacion')]
  public function solicitarFacturacion($recordId) {
    try {
      DB::transaction(function () use ($recordId) {
        $record = Transaction::findOrFail($recordId);

        $msgs = Helpers::validateProformaToRequestInvoice($record);
        if (!empty($msgs)) {
          $this->dispatch('show-notification', [
            'type' => 'warning',
            'message' => implode('<br>', $msgs),
          ]);
        } else {
          $record->proforma_status = Transaction::SOLICITADA;
          $record->fecha_solicitud_factura = \Carbon\Carbon::now();

          // Iterar sobre las líneas para buscar exoneraciones y agregarlas a las notas
          $exonerationNotes = [];
          foreach ($record->lines as $line) {
            if ($line->porcentaje_exoneracion > 0) {
              // Formatear la fecha
              $fechaExoneracion = \Carbon\Carbon::parse($line->fecha_emision_doc)->format('d-m-Y');
              $montoExonerado = number_format($line->monto_impuesto_exonerado, 2, '.', ',');

              $note = "Se exonera: {$line->detail} con el documento No {$line->numero_documento_exoneracion} de la institución o entidad {$line->nombre_institucion_exoneracion} fecha exoneración {$fechaExoneracion} Monto exonerado {$montoExonerado}";
              $exonerationNotes[] = $note;
            }
          }

          if (!empty($exonerationNotes)) {
            $record->notes .= "\n" . implode("\n", $exonerationNotes);
          }

          if ($record->save()) {
            $this->dispatch('show-notification', [
              'type' => 'success',
              'message' => __('Billing request was successfully completed'),
            ]);
          } else
            $this->dispatch('show-notification', [
              'type' => 'error',
              'message' => __('An error occurred and the request could not be made'),
            ]);
        }
      });
    } catch (QueryException $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage(),
      ]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage(),
      ]);
    }
    $this->resetControls();
  }

  #[On('facturar')]
  public function facturar($recordId) {
    $record = Transaction::findOrFail($recordId);

    // Validación por tipo
    if ($record->proforma_type === 'HONORARIO') {
      $msgs = Helpers::validateProformaToConvertInvoice($record);
    } elseif ($record->proforma_type === 'GASTO') {
      $msgs = Helpers::validateProformaToConvertInvoice($record); // puedes usar otro helper si difieren
    } else {
      $this->dispatch('show-notification', [
        'type' => 'warning',
        'message' => __('Unknown proforma type'),
      ]);
      return;
    }

    if ($record->proforma_status === Transaction::FACTURADA) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('The proforma is already invoiced'),
      ]);
      return;
    }

    // Validación con mensajes
    if (!empty($msgs)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => implode('<br>', $msgs),
      ]);
      return;
    }

    // Lógica transaccional
    DB::beginTransaction();  // Comienza la transacción principal

    try {
      // Llamar a las funciones correspondientes basadas en el tipo
      if ($record->proforma_type === 'HONORARIO') {
        $this->facturarHonorario($record);
      } elseif ($record->proforma_type === 'GASTO') {
        $this->facturarGasto($record);
      }

      DB::commit();  // Commit de la transacción principal

      // Después del commit se envian los emails para evitar que si falla el envio de email la acción no se realice
      if ($record->proforma_type === 'GASTO') {
        //Enviar email
        $this->afterFacturarGasto($record);
        // Si todo fue exitoso, mostrar notificación de éxito
        $this->dispatch('show-notification', [
          'type' => 'success',
          'message' => __('Invoicing has been successfully completed and is ready to be sent to the tax authorities')
        ]);
      } else {
        // Enviar email
        // esto se hace en el callback
        //$this->afterFacturarHonorario($record);
      }
    } catch (\Throwable $e) {
      DB::rollBack();  // Si ocurre un error, hacer rollback de la transacción

      // Enviar notificación de error
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An unexpected error occurred:') . ' ' . $e->getMessage()
      ]);

      // Registrar el error en el log
      logger()->error('Error en facturar:' . ' ' . $e->getMessage(), ['exception' => $e]);
    }
    $this->resetControls();
  }

  private function facturarHonorario($transaction) {
    /*
    - Asignar el document_type a FE !importante para generar la key y el consecutivo
    - Obtener la key y el consecutivo del Documento
    - Obetener el xml del documento
    - Firmar el documento
    - Loguearme para obtener el token
    - Enviar hacienda y recibir la respuesta
    - Cambiar el estado de la factura según la respuesta de hacienda campo status
    - Obtener el tipo de cambio y asignarlo a factura_change_type
    */

    // En este caso, no necesitamos iniciar una nueva transacción aquí
    // Simplemente hacer la lógica y dejar que la transacción principal controle todo

    // Asignar el tipo de documento
    $transaction->document_type = $transaction->invoice_type == 'FACTURA' ? Transaction::FACTURAELECTRONICA : Transaction::TIQUETEELECTRONICO;

    // Asignar el estado
    $transaction->proforma_status = Transaction::FACTURADA;

    //Asignar la fecha de emision
    $transaction->transaction_date = Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s');

    // Tipo de cambio del día
    $transaction->factura_change_type = Session::get('exchange_rate');
    if (!$transaction->factura_change_type)
      $transaction->factura_change_type = $transaction->proforma_change_type;

    // Obtener la secuencia que le corresponde según tipo de comprobante
    $secuencia = DocumentSequenceService::generateConsecutive(
      $transaction->document_type,
      $transaction->location_id
    );

    // Asignar el consecutivo a la transacción
    $transaction->consecutivo = $transaction->getConsecutivo($secuencia);
    $transaction->key = $transaction->generateKey();  // Generar la clave del documento

    // Obtener el xml firmado y en base64
    $encode = true;
    $xml = Helpers::generateComprobanteElectronicoXML($transaction, $encode, 'content');

    //Loguearme en hacienda para obtener el token
    $username = $transaction->location->api_user_hacienda;
    $password = $transaction->location->api_password;
    $authService = new AuthService();
    $token = $authService->getToken($username, $password); // Esto ya lanza una excepción detallada en caso de fallo

    $api = new ApiHacienda();
    $result = $api->send($xml, $token, $transaction, $transaction->location, Transaction::FE);
    if ($result['error'] == 0) {
      $transaction->status = Transaction::RECIBIDA;
      $transaction->invoice_date = \Carbon\Carbon::now();
    } else {
      throw new \Exception($result['mensaje']);
    }

    // Guardar la transacción
    if (!$transaction->save()) {
      throw new \Exception(__('An error occurred while saving the transaction'));
    } else {
      // Si todo fue exitoso, mostrar notificación de éxito
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => $result['mensaje'],
      ]);
    }
  }

  private function facturarGasto($transaction) {
    $consecutive = DocumentSequenceService::generateConsecutiveGasto($transaction->document_type, null);

    if (!$consecutive) {
      throw new \Exception(__('An error occurred while generating the invoice consecutive number'));
    }

    $transaction->consecutivo = $consecutive;
    $transaction->proforma_status = Transaction::FACTURADA;
    $transaction->transaction_date = Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s');
    $transaction->invoice_date = \Carbon\Carbon::now();

    if (!$transaction->save()) {
      throw new \Exception(__('An error occurred while creating the expense invoice'));
    }
  }

  private function afterFacturarGasto($transaction) {
    $sent = Helpers::sendReciboGastoEmail($transaction);

    if ($sent) {
      $transaction->fecha_envio_email = now();
      $transaction->save();

      $menssage = __('An email has been sent to the following addresses:') . ' ' . $transaction->contact->email;
      if (!empty($transaction->email_cc)) {
        $menssage .= ' ' . __('with copy to') . ' ' . $transaction->email_cc;
      }

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The expense invoice has been successfully issued') . ' ' . $menssage
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The expense invoice has been successfully issued')
      ]);

      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }

  public function resetControls() {
    // Fix: Limpiar el contexto de la sesión para evitar que se carguen líneas de transacciones pasadas
    session()->forget('transaction_context');

    // Fix: Emitir evento para limpiar inmediatamente el componente hijo (TransactionLineManager)
    $this->dispatch('updateTransactionContext', [
        'transaction_id'    => null,
        'department_id'     => null,
        'bank_id'           => null,
        'type_notarial_act' => null,
        'tipo_facturacion'  => null,
    ]);
    $this->reset(
      //'business_id',
      'location_id',
      'location_economic_activity_id',
      'contact_id',
      'contact_economic_activity_id',
      'currency_id',
      'department_id',
      'caso_text',
      'area_id',
      'bank_id',
      'caso_id',
      'codigo_contable_id',
      'created_by',
      'proforma_type',
      'proforma_status',
      'status',
      'payment_status',
      'pay_term_type',
      'customer_name',
      'customer_comercial_name',
      'customer_email',
      'proforma_no',
      'consecutivo',
      'key',
      'access_token',
      'response_xml',
      'filexml',
      'filepdf',
      'transaction_reference',
      'transaction_reference_id',
      'condition_sale',
      'condition_sale_other',
      'numero_deposito_pago',
      'numero_traslado_honorario',
      'numero_traslado_gasto',
      'contacto_banco',
      'pay_term_number',
      'tipo_facturacion',
      'proforma_change_type',
      'factura_change_type',
      'num_request_hacienda_set',
      'num_request_hacienda_get',
      'comision_pagada',
      'is_retencion',
      'message',
      'notes',
      'oc',
      'or',
      'migo',
      'prebill',
      'detalle_adicional',
      'gln',
      'cuenta_id',
      'transaction_date',
      'fecha_pago',
      'fecha_deposito_pago',
      'fecha_traslado_honorario',
      'fecha_traslado_gasto',
      'fecha_solicitud_factura',
      'activeTab',
      'closeForm',
      'payments',
      'vuelto',
      'invoice_type',
      'tipoIdentificacion',
      'identificacion',
      'document_type',
      'customer_text',
      'email_cc',
      'showInstruccionesPago',
      'clientEmail',
      'totalHonorarios',
      'totalTimbres',
      'totalDiscount',
      'totalTax',
      'totalAditionalCharge',
      'totalServGravados',
      'totalServExentos',
      'totalServExonerado',
      'totalServNoSujeto',
      'totalMercGravadas',
      'totalMercExentas',
      'totalMercExonerada',
      'totalMercNoSujeta',
      'totalGravado',
      'totalExento',
      'totalVenta',
      'totalVentaNeta',
      'totalExonerado',
      'totalNoSujeto',
      'totalImpAsumEmisorFabrica',
      'totalImpuesto',
      'totalIVADevuelto',
      'totalOtrosCargos',
      'totalComprobante',
      'old_contact_id',
      'pnombre_demandado',
      'producto',
      'numero_operacion',
      'proceso',
    );

    $this->bank_id = null;
    $this->currency_id = null;
    $this->proforma_type = null;

    // Forzar actualización de Select2
    $this->dispatch('resetSelect2', [
      'ids' => ['bank_id', 'currency_id', 'proforma_type']
    ]);

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function updated($propertyName) {
    // Si el campo condition_sale cambia
    if ($propertyName == 'condition_sale') {
      if ($this->condition_sale !== '02') {
        // Limpiar el valor de pay_term_number
        $this->pay_term_number = null;
      }
      if ($this->condition_sale !== '99') {
        $this->condition_sale_other = null;
      }
    }

    if ($propertyName == 'location_id') {
      if (!empty($this->location_id)) {
        $location = BusinessLocation::find($this->location_id);
        if ($location && $location->notes && empty($this->notes)) {
          $this->notes = $location->notes;
        }

        $this->location_economic_activity_id = null;
        $this->setlocationEconomicActivities();

        $activities = $this->locationsEconomicActivities;
        $options = $activities->map(function ($activity) {
          return [
            'id' => $activity->id,
            'text' => $activity->name,
          ];
        });

        $this->dispatch('updateSelect2Options', id: 'location_economic_activity_id', options: $options);

        if (count($activities) == 1) {
          $this->location_economic_activity_id = $activities[0]->id;
          $this->dispatch('setSelect2Value', id: 'location_economic_activity_id', value: $activities[0]->id, text: $activities[0]->name);
        } else {
          $this->dispatch('setSelect2Value', id: 'location_economic_activity_id', value: '', text: 'Seleccione...');
        }
      } else {
        $this->location_economic_activity_id = null;
        $this->locationsEconomicActivities = [];
        $this->dispatch('updateSelect2Options', id: 'location_economic_activity_id', options: []);
        $this->dispatch('setSelect2Value', id: 'location_economic_activity_id', value: '', text: 'Seleccione...');
      }
    }

    if ($propertyName == 'bank_id' || $propertyName == 'tipo_facturacion') {
      // Se emite este evento para los componentes hijos
      $this->dispatch('updateTransactionContext', [
        'transaction_id'    => $this->recordId,
        'bank_id'           => $this->bank_id,
        'type_notarial_act' => $this->proforma_type,
        'tipo_facturacion' => $this->tipo_facturacion,
      ]);
    }

    if ($propertyName == 'proforma_type' && $this->recordId > 0) {
      // Se emite este evento para los componentes hijos
      $this->dispatch('updateTransactionContext', [
        'transaction_id'    => $this->recordId,
        'bank_id'           => $this->bank_id,
        'type_notarial_act' => $this->proforma_type,
        'tipo_facturacion' => $this->tipo_facturacion,
      ]);
    }

    if ($propertyName == 'contact_id' && $this->old_contact_id != $this->contact_id) {
      $this->old_contact_id = $this->contact_id;
      $contact = Contact::find($this->contact_id);
      if ($contact) {
        $this->customer_name = $contact->name;
        if (is_null($this->customer_comercial_name) || empty($this->customer_comercial_name))
          $this->customer_comercial_name = $contact->commercial_name;
        $this->invoice_type = $contact->invoice_type;
        $this->condition_sale = $contact->conditionSale ? $contact->conditionSale->code : NULL;
        $this->pay_term_number = $contact->pay_term_number;
        $this->email_cc = $contact->email_cc;
        $this->clientEmail = $contact->email;
        $this->customer_email = $contact->email;
        $this->tipoIdentificacion = $contact->identificationType ? $contact->identificationType->name : NULL;
        $this->identificacion = $contact->identification;
      }

      if ($this->contact_id == '' | is_null($this->contact_id)) {
        $this->contact_economic_activity_id = null;
        $this->dispatch('updateSelect2Options', id: 'contact_economic_activity_id', options: []);
      } else {
        $this->setcontactEconomicActivities();

        $activities = $this->contactEconomicActivities;
        $options = $activities->map(function ($activity) {
          return [
            'id' => $activity->id,
            'text' => $activity->name,
          ];
        });
        $this->dispatch('updateSelect2Options', id: 'contact_economic_activity_id', options: $options);

        if (count($activities) == 1) {
          $this->contact_economic_activity_id = $activities[0]->id;
          $this->dispatch('setSelect2Value', id: 'contact_economic_activity_id', value: $activities[0]->id, text: $activities[0]->name);
        } else {
          if ($this->contact_economic_activity_id) {
            $activity = EconomicActivity::find($this->contact_economic_activity_id);
            if ($activity) {
              $this->dispatch('setSelect2Value', id: 'contact_economic_activity_id', value: $activity->id, text: $activity->name);
            }
          } else {
            $this->dispatch('setSelect2Value', id: 'contact_economic_activity_id', value: '', text: 'Seleccione...');
          }
        }
      }
      //$this->dispatch('reinitSelect2Controls');
    }
    else
    {
      $contact = Contact::find($this->contact_id);
      if ($contact){
        $this->customer_name = $contact->name;
        if (is_null($this->customer_comercial_name) || empty($this->customer_comercial_name))
          $this->customer_comercial_name = $contact->commercial_name;
      }
    }

    if ($propertyName == 'email_cc') {
      $this->updatedEmails();
    }

    if ($propertyName == 'bank_id') {
      //$this->setEnableControl();
    }

    if ($propertyName == 'tipo_facturacion') {
      $this->dispatch('reinitSelect2Caso');
      //$text = '';
      //$this->dispatch('setSelect2Value', id: 'caso_id', value: '', text: $text);
    }

    $this->dispatch('reinitSelect2Controls');

    $this->syncExportFilters();

    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
  }

  public function updatedCurrencyId($value) {
    if ($value != $this->original_currency_id) {
      // Si la moneda cambia hay que recalcular todo
      $transacion = Transaction::find($this->recordId);
      if ($transacion) {
        $transacion->currency_id = $this->currency_id;
        $transacion->save();
        $this->original_currency_id = $this->currency_id;

        $lines = TransactionLine::where('transaction_id', $this->recordId)->get();
        foreach ($lines as $line) {
          $line->updateTransactionTotals($this->currency_id);
        }
      }
      $activeTabProduct = false;

      $this->dispatch('productUpdated', $this->recordId, $activeTabProduct);  // Emitir evento para otros componentes
    }
  }

  public function updatedEmails() {
    // Divide la cadena en correos separados por , o ;
    $emailList = preg_split('/[,;]+/', $this->email_cc);

    // Resetear las listas de correos válidos e inválidos
    $this->validatedEmails = [];
    $this->invalidEmails = [];

    // Validar cada correo
    foreach ($emailList as $email) {
      $email = trim($email); // Elimina espacios en blanco
      if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->validatedEmails[] = $email; // Correo válido
      } elseif (!empty($email)) {
        $this->invalidEmails[] = $email; // Correo inválido
      }
    }

    // Si hay correos inválidos, añadir error al campo email_cc
    if (!empty($this->invalidEmails)) {
      $this->addError('email_cc', 'Hay correos inválidos: ' . implode(', ', $this->invalidEmails));
    } else {
      $this->resetErrorBag('email_cc'); // Limpiar errores si todos son válidos
    }
  }

  public function getStatics() {
    $allowedRoles = User::ROLES_ALL_BANKS;
    $user = Auth::user();
    $currentRole = session('current_role_name');
    $isAllBanks = $user->hasAnyRole($allowedRoles) || in_array($currentRole, $allowedRoles);

    if ($isAllBanks) {
      $rolesList = "'" . implode("','", $allowedRoles) . "'";
      $stats = Transaction::where('document_type', $this->document_type)
        ->select([
          DB::raw("SUM(CASE WHEN proforma_status = 'PROCESO' AND EXISTS (SELECT 1 FROM role_user ru JOIN roles r ON r.id = ru.role_id WHERE ru.user_id = transactions.created_by AND r.name IN ($rolesList)) THEN 1 ELSE 0 END) AS total_facturas_proceso"),
          DB::raw("SUM(CASE WHEN proforma_status = 'SOLICITADA' THEN 1 ELSE 0 END) AS facturas_por_aprobar"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::DOLARES . " AND proforma_type = 'HONORARIO' THEN totalComprobante ELSE 0 END) AS totalUsdHonorario"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::COLONES . " AND proforma_type = 'HONORARIO' THEN totalComprobante ELSE 0 END) AS totalCrcHonorario"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::DOLARES . " AND proforma_type = 'GASTO' THEN totalComprobante ELSE 0 END) AS totalUsdGasto"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::COLONES . " AND proforma_type = 'GASTO' THEN totalComprobante ELSE 0 END) AS totalCrcGasto")
        ])
        ->first();
    } else {
      $banks = $user->banks->pluck('id');
      $stats = Transaction::where('document_type', $this->document_type)
        ->where(function ($query) use ($banks, $allowedRoles) {
          if (!empty($banks)) {
            $query->whereIn('bank_id', $banks);
          }
          $query->where('created_by', auth()->user()->id);
          $query->whereDoesntHave('createdBy.roles', function ($q) use ($allowedRoles) {
            $q->whereIn('name', $allowedRoles);
          });
        })
        ->select([
          DB::raw("SUM(CASE WHEN proforma_status = 'PROCESO' THEN 1 ELSE 0 END) AS total_facturas_proceso"),
          DB::raw("SUM(CASE WHEN proforma_status = 'SOLICITADA' THEN 1 ELSE 0 END) AS facturas_por_aprobar"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::DOLARES . " AND proforma_type = 'HONORARIO' THEN totalComprobante ELSE 0 END) AS totalUsdHonorario"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::COLONES . " AND proforma_type = 'HONORARIO' THEN totalComprobante ELSE 0 END) AS totalCrcHonorario"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::DOLARES . " AND proforma_type = 'GASTO' THEN totalComprobante ELSE 0 END) AS totalUsdGasto"),
          DB::raw("SUM(CASE WHEN proforma_status = 'FACTURADA' AND currency_id = " . Currency::COLONES . " AND proforma_type = 'GASTO' THEN totalComprobante ELSE 0 END) AS totalCrcGasto")
        ])
        ->first();
    }

    return $stats;
  }

  public function beforeclonar() {
    $this->confirmarAccion(
      null,
      'clonar',
      '¿Está seguro que desea clonar este registro?',
      'Después de confirmar, el registro será clonado',
      __('Sí, proceed'),
      true
    );
  }

  public function beforedelete() {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  public function beforefacturar($id, $proformaNo) {
    $this->confirmarAccion(
      $id,
      'facturar',
      "¿Está seguro que desea facturar la proforma número: " . $proformaNo . "?",
      'Después de confirmar la proforma será convertida en factura',
      __('Sí, proceed')
    );
  }

  public function beforesolicitar($id, $proformaNo) {
    $this->confirmarAccion(
      $id,
      'solicitarFacturacion',
      "¿Está seguro que desea solicitar la facturación de la proforma número: " . $proformaNo . "?",
      'Después de confirmar, la proforma será revisada por administración',
      __('Sí, proceed')
    );
  }

  public function updatedShowTransactionDate($value) {
    $this->show_transaction_date = $value;
  }

  #[On('clonar')]
  public function clonar($recordId) {
    $recordId = $this->getRecordAction($recordId, true);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    DB::beginTransaction();

    try {
      $original = Transaction::with(['lines', 'otherCharges', 'commisions', 'documents'])->findOrFail($recordId);

      $document_type = 'PR';

      // Generar consecutivo
      $consecutive = DocumentSequenceService::generateConsecutive(
        $document_type,
        NULL
      );

      // Clonar transaction
      $cloned = $original->replicate();
      $cloned->proforma_no = $consecutive;
      $cloned->created_by = auth()->user()->id;
      $cloned->proforma_status = Transaction::PROCESO;
      $cloned->document_type = 'PR';
      $cloned->location_id = NULL;
      $cloned->status = NULL;
      $cloned->payment_status = 'due';
      $cloned->consecutivo = NULL;
      $cloned->key = NULL;
      $cloned->access_token = NULL;
      $cloned->response_xml = NULL;
      $cloned->filexml = NULL;
      $cloned->filepdf = NULL;
      $cloned->transaction_reference = NULL;
      $cloned->transaction_reference_id = NULL;
      $cloned->numero_deposito_pago = NULL;
      $cloned->numero_traslado_honorario = NULL;
      $cloned->numero_traslado_gasto = NULL;
      //$cloned->proforma_change_type = Session::get('exchange_rate') ? Session::get('exchange_rate') : $original->proforma_change_type;
      $exchangeRate = Session::get('exchange_rate');
      $cloned->proforma_change_type = (!empty($exchangeRate) && is_numeric($exchangeRate) && $exchangeRate > 0)
        ? $exchangeRate
        : $original->proforma_change_type;
      $cloned->factura_change_type = NULL;
      $cloned->num_request_hacienda_set = 0;
      $cloned->num_request_hacienda_get = 0;
      $cloned->comision_pagada = 0;
      $cloned->transaction_date = Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s');
      $cloned->invoice_date = NULL;
      $cloned->fecha_pago = NULL;
      $cloned->fecha_deposito_pago = NULL;
      $cloned->fecha_traslado_honorario = NULL;
      $cloned->fecha_traslado_gasto = NULL;
      $cloned->fecha_solicitud_factura = NULL;
      $cloned->fecha_envio_email = NULL;
      $cloned->fecha_comision_pagada = NULL;
      $cloned->totalPagado = 0;
      $cloned->pendientePorPagar = $original->totalComprobante;
      $cloned->vuelto = 0;
      $cloned->RefRazon = NULL;
      $cloned->RefCodigoOtro = NULL;
      $cloned->RefCodigo = NULL;
      $cloned->RefFechaEmision = NULL;
      $cloned->RefNumero = NULL;
      $cloned->RefTipoDocOtro = NULL;
      $cloned->RefTipoDoc = NULL;
      $cloned->RefTipoDoc = NULL;
      $cloned->save();

      // Clonar lines
      foreach ($original->lines as $item) {
        $copy = $item->replicate();
        $copy->transaction_id = $cloned->id;
        $copy->fecha_reporte_gasto = NULL;
        $copy->fecha_pago_registro = NULL;
        $copy->numero_pago_registro = NULL;
        $copy->registro_currency_id = NULL;
        $copy->registro_change_type = NULL;
        $copy->registro_monto_escritura = NULL;
        $copy->registro_valor_fiscal = NULL;
        $copy->registro_cantidad = NULL;
        $copy->monto_cargo_adicional = NULL;
        $copy->calculo_registro_normal = NULL;
        $copy->calculo_registro_iva = NULL;
        $copy->calculo_registro_no_iva = NULL;
        $copy->calculo_registro_no_iva = NULL;
        $copy->save();

        // clonar los taxes
        foreach ($item->taxes as $tax) {
          $copyTax = $tax->replicate();
          $copyTax->transaction_line_id = $copy->id;
          $copyTax->save();
        }

        // clonar los descuentos
        foreach ($item->discounts as $discount) {
          $copyDiscount = $discount->replicate();
          $copyDiscount->transaction_line_id = $copy->id;
          $copyDiscount->save();
        }
      }

      // Clonar otros cargos
      foreach ($original->otherCharges as $item) {
        $copy = $item->replicate();
        $copy->transaction_id = $cloned->id;
        $copy->save();
      }

      // Clonar commisions
      foreach ($original->commisions as $item) {
        $copy = $item->replicate();
        $copy->transaction_id = $cloned->id;
        $copy->save();
      }

      $payment = new TransactionPayment;
      $payment->transaction_id = $cloned->id;
      $payment->tipo_medio_pago = '04';  // transaferencia
      $payment->medio_pago_otros = '';
      $payment->total_medio_pago = 0;
      $payment->save();

      // Clona los documentos asociados (colección 'documents')
      /*
      foreach ($original->getMedia('documents') as $media) {
        // Verifica que el archivo físico existe en el disco configurado
        if (Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
          $media->copy($cloned, 'documents');
        } else {
          Log::warning("Archivo no encontrado al clonar media ID {$media->id}: " . $media->getPath());
        }
      }
      */

      DB::commit();

      $this->selectedIds = [];
      $this->dispatch('updateSelectedIds', $this->selectedIds);

      //$this->recordId = '';
      $this->recordId = $cloned->id;
      $this->action = 'edit';
      $this->edit($cloned->id);

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The proforma has been successfully cloned')]);

      return response()->json(['success' => true, 'message' => 'Proforma clonada exitosamente', 'id' => $cloned->id]);
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. While cloning the proforma') . ' ' . $e->getMessage()]);
      Log::error('Error al clonar producto.', ['error' => $e->getMessage()]);
    }
  }


  public function updatedPage($page)
  {
    $this->syncExportFilters();
  }

  public function setSortBy($sortByField)
  {
    parent::setSortBy($sortByField);
    $this->syncExportFilters();
  }

  public function syncExportFilters()
  {
    $this->dispatch('updateExportFilters', [
      'search' => $this->search,
      'filters' => $this->filters,
      'selectedIds' => $this->selectedIds,
      'sortBy' => $this->sortBy,
      'sortDir' => $this->sortDir,
      'perPage' => $this->perPage,
      'page' => $this->getPage(),
      'exportType' => 'PROFORMA',
      'managerClass' => self::class,
    ]);
  }

  public function getQueryForExport(array $params = []): \Illuminate\Database\Eloquent\Builder
  {
    if (isset($params['search'])) $this->search = $params['search'];
    if (isset($params['filters']) && is_array($params['filters'])) $this->filters = $params['filters'];
    if (isset($params['document_type'])) $this->document_type = $params['document_type'];

    return $this->getFilteredQuery();
  }
}
