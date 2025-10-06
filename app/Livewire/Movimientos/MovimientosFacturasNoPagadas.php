<?php

namespace App\Livewire\Movimientos;

use App\Livewire\Transactions\TransactionManager;
use App\Models\DataTableConfig;
use App\Models\Movimiento;
use App\Models\MovimientoFactura;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class MovimientosFacturasNoPagadas extends TransactionManager
{
  public $movimientoId;

  public $filters = [
    'filter_transaction_date' => NULL,
    'filter_action' => NULL,
    'filter_proforma_no' => NULL,
    'filter_consecutivo' => NULL,
    'filter_customer_name' => NULL,
    'filter_department_name' => NULL,
    'filter_user_name' => NULL,
    'filter_fecha_solicitud_factura' => NULL,
    'filter_issuer_name' => NULL,
    'filter_codigosContables' => NULL,
    'filter_numero_caso' => NULL,
    'filter_referencia' => NULL,
    'filter_oc' => NULL,
    'filter_migo' => NULL,
    'filter_bank_name' => NULL,
    'filter_currency_code' => NULL,
    'filter_proforma_type' => NULL,
    'filter_status' => NULL,
    'filter_totalComprobante' => NULL,
    'filter_total_usd' => NULL,
    'filter_total_crc' => NULL
  ];

  public function mount()
  {
    $this->document_type = ['PR', 'FE', 'TE'];
    parent::mount();
    // Aquí puedes agregar lógica específica para proformas
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'movimientos-facturas-no-pagadas-datatable')
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

  public function getDefaultColumns(): array
  {
    $this->defaultColumns = [
      [
        'field' => 'transaction_date',
        'orderName' => 'transactions.transaction_date',
        'label' => __('Emmision Date'),
        'filter' => 'filter_transaction_date',
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
        'function' => 'getMovimientoFacturasNoPagadasHtmlColumnAction',
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
        'field' => 'totalComprobante',
        'orderName' => '',
        'label' => __('Total'),
        'filter' => 'filter_totalComprobante',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
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
        'field' => 'currency_code',
        'orderName' => 'currencies.code',
        'label' => __('Currency'),
        'filter' => 'filter_currency_code',
        'filter_type' => 'select',
        'filter_sources' => 'currencies',
        'filter_source_field' => 'code',
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
        'field' => 'proforma_type',
        'orderName' => 'transactions.proforma_type',
        'label' => __('Type of Notarial Act'),
        'filter' => 'filter_proforma_type',
        'filter_type' => 'select',
        'filter_sources' => 'proformaTypes',
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
        'field' => 'status',
        'orderName' => 'transactions.proforma_status',
        'label' => __('Status'),
        'filter' => 'filter_status',
        'filter_type' => 'select',
        'filter_sources' => 'statusOptions',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlStatus',
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

  protected function getFilteredQuery()
  {
    $movimiento = Movimiento::findOrFail($this->movimientoId);
    $cuenta = $movimiento->cuenta;

    $bancos        = $cuenta->banks->pluck('id')->toArray();
    $departamentos = $cuenta->departments->pluck('id')->toArray();
    $emisores      = $cuenta->locations->pluck('id')->toArray();

    /*
    $subquery = DB::table('movimientos_facturas')
        ->select('transaction_id')
        ->where('movimiento_id', $this->movimientoId);

    $query = Transaction::search($this->search, $this->filters)
        ->select('transactions.*', 'c.name as contact_name')
        ->join('transactions_commissions', 'transactions_commissions.transaction_id', '=', 'transactions.id')
        ->leftJoin('contacts as c', 'c.id', '=', 'transactions.contact_id')
        ->whereIn('document_type', $this->document_type)
        ->whereIn('proforma_status', [Transaction::FACTURADA])
        ->where(function ($q) {
            $q->whereNull('transactions.numero_deposito_pago')
              ->orWhere('transactions.numero_deposito_pago', '');
        })
        ->whereNotIn('transactions.id', $subquery)
        ->groupBy('transactions.id'); // evita duplicados
        */
    $subquery = DB::table('movimientos_facturas')
        ->select('transaction_id')
        ->where('movimiento_id', $this->movimientoId);

    $query = Transaction::search($this->search, $this->filters)
        ->select('transactions.*', 'c.name as contact_name')
        ->join('transactions_commissions', 'transactions_commissions.transaction_id', '=', 'transactions.id')
        ->leftJoin('contacts as c', 'c.id', '=', 'transactions.contact_id')
        ->whereIn('document_type', $this->document_type)
        ->whereIn('proforma_status', [Transaction::FACTURADA])
        ->where(function ($q) {
            $q->whereNull('transactions.numero_deposito_pago')
              ->orWhere('transactions.numero_deposito_pago', '');
        })
        ->whereNotIn('transactions.id', $subquery)
        ->distinct(); // ⚡ evita duplicados sin groupBy

    // Filtros dinámicos
    if (!empty($bancos)) {
        $query->whereIn('transactions.bank_id', $bancos);
    }

    if (!empty($departamentos)) {
        $query->whereIn('transactions.department_id', $departamentos);
    }

    if (!empty($emisores)) {
        $query->whereIn('transactions.location_id', $emisores);
    }

    $allowedRoles = User::ROLES_ALL_DEPARTMENTS;
    if (!in_array(Session::get('current_role_name'), $allowedRoles)) {
        $departments = Session::get('current_department', []);
        if (!empty($departments)) {
            $query->whereIn('transactions.department_id', $departments);
        }
    }

    // Orden final: usar alias 'c.name' en lugar de 'contacts.name'
    return $query->orderByDesc('transactions.transaction_date')
                 ->orderByDesc('transactions.consecutivo')
                 ->orderBy('contact_name');
  }

  public function render()
  {
    $query = $this->getFilteredQuery();

    // Ordenamiento y paginación final
    $records = $query
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.movimientos.movimientos-facturas-no-pagadas', [
      'records' => $records,
    ]);
  }

  public function assignToMovement()
  {
    if (empty($this->selectedIds)) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('You must select at least one invoice')
      ]);
      return;
    }

    $now = now();

    // Buscar transacciones ya registradas
    $existing = MovimientoFactura::where('movimiento_id', $this->movimientoId)
      ->whereIn('transaction_id', $this->selectedIds)
      ->pluck('transaction_id')
      ->toArray();

    // Preparar solo los que NO existan
    $data = collect($this->selectedIds)
      ->diff($existing) // elimina duplicados
      ->map(fn($id) => [
        'movimiento_id'   => $this->movimientoId,
        'transaction_id'  => $id,
        'created_at'      => $now,
        'updated_at'      => $now,
      ])
      ->all();

    // Insertar solo si hay datos
    if (!empty($data)) {
      MovimientoFactura::insert($data);
    }

    // Actualiza el saldo a cancelar del componente principal de movimiento
    $this->dispatch('updateSaldoCancelar');

    // Actualiza las facturas asociadas al movimiento
    $this->dispatch('actualizarFacturasMovimientos');

    $this->dispatch('actualizarFacturasNoPagadas');

    $this->dispatch('show-notification', [
      'type' => 'success',
      'message' => __('Invoices assigned to the movement')
    ]);
  }

  #[On('actualizarFacturasNoPagadas')]
  public function refresh()
  {
    $this->resetPage(); // opcional: vuelve a la primera página
    $this->dispatch('$refresh'); // fuerza render
  }
}
