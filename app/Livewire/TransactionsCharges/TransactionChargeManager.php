<?php

namespace App\Livewire\TransactionsCharges;

use App\Models\Caso;
use App\Models\Product;
use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\BaseComponent;
use App\Models\DataTableConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use App\Models\IdentificationType;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Models\AdditionalChargeType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TransactionOtherCharge;
use Illuminate\Support\Facades\Storage;

class TransactionChargeManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'chtSearch', history: true)]
  public $search = '';

  #[Url(as: 'chtSortBy', history: true)]
  public $sortBy = 'transactions_other_charges.id';

  #[Url(as: 'chtSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'chtPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Variables públicas
  public $transaction_id = NULL;
  public $caso_id = NULL;
  public $product_id = NULL;
  public $additional_charge_type_id = NULL;
  public $additional_charge_other = NULL;
  public $third_party_identification_type = NULL;
  public $third_party_identification = NULL;
  public $third_party_name = NULL;
  public $detail = NULL;
  public $percent = NULL;
  public $quantity = NULL;
  public $amount = NULL;

  public $bank_id = NULL;
  public $type_notarial_act = NULL;
  public $caso_text = NULL;
  public $tipo_facturacion = NULL;

  public $closeForm = false;

  //Listados
  public $chargeTypes = [];
  public $identificationTypes;

  public $total = 0;

  public $columns = [];
  public $defaultColumns = [];

  public $canview;
  public $cancreate;
  public $canedit;
  public $candelete;
  public $canexport;
  public $oldproduct_id = NULL;

  public $products = [];

  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
  ];

  protected function getModelClass(): string
  {
    return TransactionOtherCharge::class;
  }

  #[On('updateTransactionContext')]
  public function handleUpdateContext($data)
  {
    $this->transaction_id = $data['transaction_id'];
    $this->bank_id = $data['bank_id'];
    $this->type_notarial_act = $data['type_notarial_act'];
    $this->tipo_facturacion = $data['tipo_facturacion'];
    $this->dispatch('reinitFormControls');
    //Log::debug('handleUpdateContext transaction_id', [$this->transaction_id]);
  }


  public function mount($transaction_id, $canview, $cancreate, $canedit, $candelete, $canexport)
  {
    $this->transaction_id = $transaction_id;

    if ($this->transaction_id) {
      $transaction = Transaction::find($this->transaction_id);
      if ($transaction) {
        $this->bank_id = $transaction->bank_id;
        $this->type_notarial_act = $transaction->proforma_type;
        $this->tipo_facturacion = $transaction->tipo_facturacion;
      }
    }

    $this->chargeTypes = AdditionalChargeType::orderBy('code', 'ASC')->get();
    $this->identificationTypes = IdentificationType::orderBy('code', 'ASC')->get();

    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;

    //Log::debug('MOUNT transaction_id', [$this->transaction_id]);

    $this->products = Product::query()
      ->select(['products.id as id', 'products.name as name'])
      ->join('product_honorarios_timbres', 'product_honorarios_timbres.product_id', '=', 'products.id')
      ->where(function ($q) {
        // Siempre filtra por type_notarial_act principal
        $q->where('products.type_notarial_act', '=', 'GASTO');
      })
      ->orderBy('products.name', 'asc')
      ->get();

    $this->refresDatatable();
  }

  public function render()
  {
    //DB::enableQueryLog();

    $records = TransactionOtherCharge::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('transaction_id', '=', (int)$this->transaction_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    //Log::debug('RENDER transaction_id', [$this->transaction_id]);

    //Log::debug(DB::getQueryLog());

    return view('livewire.transactions-charges.datatable', [
      'records' => $records,
      'canview' => $this->canview,
      'cancreate' => $this->cancreate,
      'canedit' => $this->canedit,
      'candelete' => $this->candelete,
      'canexport' => $this->canexport
    ]);
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetControls();
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $transaction = Transaction::with('otherCharges')->find($this->transaction_id);
    if (count($transaction->otherCharges) >= 15) {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('You have exceeded the maximum number of charges allowed. Only up to 15 charges are allowed')]);
    }

    $this->additional_charge_type_id = 99;
    $this->quantity = 1;

    $this->action = 'create';

    $this->dispatch('reinitFormControls');

    $text = '';
    $this->dispatch('setSelect2Value', id: 'caso_id', value: '', text: $text);
    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    return [
      'transaction_id' => 'required|exists:transactions,id',
      'caso_id' => 'nullable|integer|exists:casos,id',
      'product_id' => 'required|integer|exists:products,id',
      'additional_charge_type_id' => 'required|exists:additional_charge_types,id',
      'additional_charge_other' => 'nullable|required_if:additional_charge_type_id,99|string|max:100',
      'third_party_identification_type' => 'nullable|required_if:additional_charge_type_id,4|string|size:2',
      'third_party_identification' => 'nullable|required_if:additional_charge_type_id,4|string|max:20',
      'third_party_name' => 'nullable|required_if:additional_charge_type_id,4|string|max:100',
      'detail' => 'required|string|max:160',
      'percent' => 'nullable|numeric|min:0|max:100',
      'quantity' => 'required|numeric|min:1|max:999999999999',
      'amount' => 'required|numeric|min:0.00001|max:999999999999.99999',
    ];
  }

  // Mensajes de error personalizados
  protected function messages()
  {
    return [
      'transaction_id.required' => 'El campo ID de transacción es obligatorio.',
      'transaction_id.exists' => 'La transacción seleccionada no existe.',

      'additional_charge_type_id.required' => 'El tipo de cargo adicional es obligatorio.',
      'additional_charge_type_id.exists' => 'El tipo de cargo adicional no es válido.',

      'additional_charge_other.required_if' => 'El campo "Otro" es obligatorio cuando el tipo de cargo es "Otros".',
      'additional_charge_other.max' => 'El campo "Otro" no debe exceder los 100 caracteres.',

      'third_party_identification_type.required_if' => 'El tipo de identificación de terceros es obligatorio cuando el tipo de cargo es cobro de un tercero.',
      'third_party_identification_type.size' => 'El tipo de identificación debe tener 2 caracteres.',

      'third_party_identification.required_if' => 'El campo identificación de terceros es obligatorio cuando el tipo de cargo es cobro de un tercero.',
      'third_party_identification.max' => 'La identificación de terceros no debe exceder los 20 caracteres.',

      'third_party_name.required_if' => 'El campo nombre de tercero es obligatorio cuando el tipo de cargo es cobro de un tercero.',
      'third_party_name.max' => 'El nombre del tercero no debe exceder los 100 caracteres.',

      'detail.required' => 'El campo detalle es obligatorio.',
      'detail.max' => 'El detalle no debe exceder los 160 caracteres.',

      'percent.numeric' => 'El campo porcentaje debe ser un valor numérico.',
      'percent.min' => 'El porcentaje no puede ser menor que 0.',
      'percent.max' => 'El porcentaje no puede exceder el 100%.',

      'quantity.required' => 'La cantidad es obligatorio.',
      'quantity.numeric' => 'La cantidad debe ser un valor numérico.',
      'quantity.min' => 'La cantidad debe ser mayor que cero.',
      'quantity.max' => 'La cantidad no puede exceder el límite permitido.',

      'amount.required' => 'El monto es obligatorio.',
      'amount.numeric' => 'El monto debe ser un valor numérico.',
      'amount.min' => 'El monto debe ser mayor que cero.',
      'amount.max' => 'El monto no puede exceder el límite permitido.',
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes()
  {
    return [
      'transaction_id' => 'ID de transacción',
      'additional_charge_type_id' => 'tipo de cargo adicional',
      'additional_charge_other' => 'otro cargo adicional',
      'third_party_identification_type' => 'tipo de identificación de terceros',
      'third_party_identification' => 'identificación de terceros',
      'third_party_name' => 'nombre del tercero',
      'detail' => 'detalle',
      'percent' => 'porcentaje',
      'quantity' => 'cantidad',
      'amount' => 'monto',
    ];
  }

  public function store()
  {
    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    // Validar
    if ($this->additional_charge_type_id == 99) {
      $this->additional_charge_other = $this->detail;
    }
    $validatedData = $this->validate();

    try {

      // Crear el usuario con la contraseña encriptada
      $record = TransactionOtherCharge::create($validatedData);

      $closeForm = $this->closeForm;

      $this->dispatch('chargeUpdated', $record->transaction_id);  // Emitir evento para otros componentes

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $this->cleanEmptyForeignKeys();
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = TransactionOtherCharge::find($recordId);
    $this->recordId = $recordId;

    // Asignar valores del registro a las variables públicas
    $this->transaction_id = $record->transaction_id;
    $this->caso_id = $record->caso_id;
    $this->additional_charge_type_id = $record->additional_charge_type_id;
    $this->additional_charge_other = $record->additional_charge_other;
    $this->third_party_identification_type = $record->third_party_identification_type;
    $this->third_party_identification = $record->third_party_identification;
    $this->third_party_name = $record->third_party_name;
    $this->detail = $record->detail;
    $this->percent = $record->percent;
    $this->quantity = $record->quantity;
    $this->amount = $record->amount;
    $this->product_id = $record->product_id;

    $this->oldproduct_id = $this->product_id;

    if ($this->caso_id) {
      $caso = Caso::select(
          'casos.*',
          DB::raw("CONCAT_WS(' / ',
              CONCAT_WS(' / ', pnumero, pnumero_operacion1),
              TRIM(CONCAT_WS(' ', pnombre_demandado, pnombre_apellidos_deudor))
          ) AS pnumero_text")
      )
      ->where('id', $this->caso_id)
      ->first();

      if ($caso) {
        $this->caso_text = $caso->pnumero_text;
        $this->dispatch('setSelect2Value', id: 'caso_id', value: $this->caso_id, text: $this->caso_text);
      }
    }
    else{
      $this->caso_text = '';
      $this->dispatch('setSelect2Value', id: 'caso_id', value: '', text: $this->caso_text);
    }

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->dispatch('reinitFormControls');

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;
    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    // Validar
    if ($this->additional_charge_type_id == 99) {
      $this->additional_charge_other = $this->detail;
    }

    $validatedData = $this->validate();

    try {
      // Encuentra el registro existente
      $record = TransactionOtherCharge::findOrFail($recordId);

      // Actualiza el registro
      $record->update($validatedData);

      $closeForm = $this->closeForm;

      $this->dispatch('chargeUpdated', $record->transaction_id);  // Emitir evento para otros componentes

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
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    //Log::debug('confirmarAccion ANTES', ['recordId' => $recordId, 'metodo' => $metodo, 'selectedIds' => $this->selectedIds]);

    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    Log::debug('confirmarAccion DESPUES', ['recordId' => $recordId, 'metodo' => $metodo, 'selectedIds' => $this->selectedIds]);

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
      $record = TransactionOtherCharge::findOrFail($recordId);
      $transaction_id = $record->transaction_id;

      if ($record->delete()) {

        $this->selectedIds = array_filter(
          $this->selectedIds,
          fn($selectedId) => $selectedId != $recordId
        );

        // Opcional: limpiar "seleccionar todo" si ya no aplica
        if (empty($this->selectedIds)) {
          $this->selectAll = false;
        }

        $this->dispatch('chargeUpdated', $transaction_id);  // Emitir evento para otros componentes

        // Emitir actualización
        $this->dispatch('updateSelectedIds', $this->selectedIds);

        // ✅ Eliminar el ID del array de seleccionados si estaba presente
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

        //dd($this->selectedIds);
        // Puedes emitir un evento para redibujar el datatable o actualizar la lista
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
  }

  public function resetControls()
  {
    $this->reset(
      'additional_charge_type_id',
      'additional_charge_other',
      'third_party_identification_type',
      'third_party_identification',
      'third_party_name',
      'detail',
      'percent',
      'quantity',
      'amount',
      'closeForm',
      'caso_text',
      'caso_id',
      'product_id',
      'oldproduct_id'
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function setSortBy($sortByField)
  {
    if ($this->sortBy === $sortByField) {
      $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
      return;
    }

    $this->sortBy = $sortByField;
    $this->sortDir = 'DESC';
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function updated($property)
  {
    // $property: The name of the current property that was updated
    if ($property == 'product_id'){
      $product = Product::find($this->product_id);
      if ($product && $this->oldproduct_id != $this->product_id)
         $this->detail = $product->name;
    }

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'proformas-lines-datatable')
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

  public $filters = [
    'filter_additional_charge_types' => NULL,
    'filter_product' => NULL,
    'filter_detail' => NULL,
    'filter_quantity' => NULL,
    'filter_amount' => NULL,
    'filter_numero_caso' => NULL,
    'filter_total' => NULL,
    'filter_third_party_name' => NULL,
    'filter_third_party_identification_type' => NULL,
    'filter_third_party_identification' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'charge_name',
        'orderName' => 'additional_charge_types.name',
        'label' => __('Charge Type'),
        'filter' => 'filter_additional_charge_types',
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
        'field' => 'product_name',
        'orderName' => 'products.name',
        'label' => __('Product'),
        'filter' => 'filter_product',
        'filter_type' => 'select',
        'filter_sources' => 'products',
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
        'field' => 'detail',
        'orderName' => 'detail',
        'label' => __('Detail'),
        'filter' => 'filter_additional_charge_types',
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
        'field' => 'quantity',
        'orderName' => 'quantity',
        'label' => __('Quantity'),
        'filter' => 'filter_quantity',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'integer',
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
        'field' => 'amount',
        'orderName' => 'amount',
        'label' => __('Amount'),
        'filter' => 'filter_amount',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
        'field' => '',
        'orderName' => '',
        'label' => __('Total'),
        'filter' => 'filter_total',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlTotal',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'third_party_name',
        'orderName' => 'third_party_name',
        'label' => __('Third Party Name'),
        'filter' => 'filter_third_party_name',
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
        'field' => 'third_party_identification_type',
        'orderName' => 'third_party_identification_type',
        'label' => __('Third Party Identification'),
        'filter' => 'filter_third_party_identification_type',
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
        'field' => 'third_party_identification',
        'orderName' => 'third_party_identification',
        'label' => __('Third Party Identification'),
        'filter' => 'filter_third_party_identification',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
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
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function resetFilters()
  {
    $this->reset('filters');
    $this->selectedIds = [];
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  protected function cleanEmptyForeignKeys()
  {
    // Lista de campos que pueden ser claves foráneas
    $foreignKeys = [
      'product_id',
      'caso_id',
      'additional_charge_type_id',
      // Agrega otros campos aquí
    ];

    foreach ($foreignKeys as $key) {
      if (isset($this->$key) && $this->$key === '') {
        $this->$key = null;
      }
    }
  }
}
