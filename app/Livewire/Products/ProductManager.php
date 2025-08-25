<?php

namespace App\Livewire\Products;

use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\Country;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Product;
use App\Models\ProductTax;
use App\Models\UnitType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class ProductManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'products.created_at';

  #[Url(history: true)]
  public $sortDir = 'DESC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Listados
  public $unitTypes;
  public $listbanks;

  public $name;
  public $code;
  public $description;
  public $business_id;
  public $type = 'service';
  public $type_notarial_act;
  public $unit_type_id;
  public $caby_code;
  public $price = 0;
  public $cuantos;
  public $is_expense;
  public $enable_quantity;
  public $enable_registration_calculation;
  public $percent_eddi;
  public $additional_charge;
  public $impuesto_and_timbres_separados;

  public $sku;
  public $image;
  public $created_by;

  public $banks;
  public $closeForm = false;

  public $columns;
  public $defaultColumns;
  public $listActives;

  // para el calculo del desglo se servicios
  public $desgloseMonto;
  public $desgloseMoneda;
  public $desgloseBanco;

  public $currencies;

  // En tu componente Livewire
  public $activeTab = 1; // 1, 2 o 3
  public $degloseHtml;

  protected $listeners = [
    'cabyCodeSelected' => 'handleCabyCodeSelected',
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  protected function getModelClass(): string
  {
    return Product::class;
  }

  public function handleCabyCodeSelected($code)
  {
    $this->caby_code = $code['code'];
  }

  public function mount()
  {
    $this->business_id = 1;
    $this->unitTypes = UnitType::where('active', 1)->orderBy('name', 'ASC')->get();
    $this->listbanks = Bank::orderBy('name', 'ASC')->get();
    $this->listActives = [['id' => 1, 'name' => 'Activo'], ['id' => 0, 'name' => 'Inactivo']];

    $this->currencies = Currency::orderBy('code', 'ASC')->get();

    $this->refresDatatable();
  }

  public function render()
  {
    $records = Product::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->when($this->active !== '', function ($query) {
        $query->where('products.active', $this->active);
      })
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.products.datatable', [
      'records' => $records,
    ]);
  }

  public function updatedActive($value)
  {
    $this->active = (int) $value;
  }

  public function updatedEnableRegistrationCalculation($value)
  {
    $this->enable_registration_calculation = (int) $value;
  }

  public function updatedEnableQuantity($value)
  {
    $this->enable_quantity = (int) $value;
  }

  public function updatedImpuestoAndTimbresSeparados($value)
  {
    $this->impuesto_and_timbres_separados = (int) $value;
  }

  public function create()
  {
    $this->resetControls();
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'create';
    $this->active = 1;
    $this->unit_type_id = UnitType::SERVICIO_PROFESIONAL;

    $this->dispatch('scroll-to-top');
  }

  // Definir reglas, mensajes y atributos
  protected function rules()
  {
    return [
      'name' => 'required|string|max:191',
      'code' => 'required|string|max:5',
      'type_notarial_act' => 'required|in:single,HONORARIO,GASTO',
      'unit_type_id' => 'required|exists:unit_types,id',
      //'price' => 'required|numeric|min:0',
      'caby_code' => 'required|string|max:13',
      'enable_registration_calculation' => 'nullable|numeric|min:0',
      'percent_eddi' => 'nullable|numeric',
      'enable_quantity' => 'nullable|numeric|min:0',
      'impuesto_and_timbres_separados' => 'nullable|numeric|min:0',
      'additional_charge' => 'nullable|numeric|min:0',
      'banks' => 'required|array',
      'banks.*' => 'exists:banks,id',
      //'sku' => 'required|string|max:20|unique:products,sku',
      'active' => 'required|integer|in:0,1',
    ];
  }

  protected function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'required_if' => 'El campo :attribute es obligatorio cuando el tipo es :value.',
      'required_with' => 'El campo :attribute es obligatorio.',
      'numeric' => 'El campo :attribute debe ser un número válido.',
      'integer' => 'El campo :attribute debe ser un número válido.',
      'min' => 'El campo :attribute debe ser al menos :min caracteres',
      'max' => 'El campo :attribute no puede exceder :max caracteres',
      'in' => 'El campo :attribute no es válido.',
      'exists' => 'El campo :attribute no existe en el sistema.',
      'string' => 'El campo :attribute debe ser un texto.',
      'date' => 'El campo :attribute debe ser una fecha válida.',
      'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    ];
  }

  protected function validationAttributes()
  {
    return [
      'name' => 'name',
      'code' => 'code',
      'type_notarial_act' => 'type notarial act',
      'unit_type_id' => 'unit type',
      //'price' => 'price',
      'caby_code' => 'caby code',
      'enable_registration_calculation' => 'registration calculation',
      'percent_eddi' => 'percent',
      'enable_quantity' => 'anable quantity',
      'impuesto_and_timbres_separados' => 'impuesto',
      'additional_charge' => 'additional charge',
      'banks' => 'bancos',
      //'sku' => 'required|string|max:20|unique:products,sku',
      'active' => 'active',
    ];
  }

  public function store()
  {
    $this->active = empty($this->active) ? 0 : $this->active;
    $this->percent_eddi = empty($this->percent_eddi) ? 0 : $this->percent_eddi;
    $this->additional_charge = empty($this->additional_charge) ? 0 : $this->additional_charge;
    $this->validate();

    $this->created_by = Auth::user()->id;

    try {
      // Crear el usuario con la contraseña encriptada
      $record = Product::create($this->only([
        'name',
        'code',
        'business_id',
        'type',
        'type_notarial_act',
        'unit_type_id',
        'caby_code',
        //'price'
        'enable_quantity',
        'enable_registration_calculation',
        'percent_eddi',
        'additional_charge',
        'impuesto_and_timbres_separados',
        'created_by',
        'active',
      ]));

      if ($record) {
        $record->banks()->sync($this->banks);
      }

      $closeForm = $this->closeForm;

      if (empty(trim($record->sku))) {
        $sku = $record->generateProductSku($record->id);
        $record->sku = $sku;
        $record->save();
      }

      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
        $this->dispatch('$refresh');
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
    } catch (\Exception $e) {

      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = Product::with('banks')->findOrFail($recordId);
    $this->recordId = $recordId;

    $this->name = $record->name;
    $this->code = $record->code;
    $this->type_notarial_act = $record->type_notarial_act;
    $this->unit_type_id = $record->unit_type_id;
    //$this->price = $record->price;
    $this->caby_code = $record->caby_code;
    $this->enable_registration_calculation = $record->enable_registration_calculation;
    $this->percent_eddi = $record->percent_eddi;
    $this->enable_quantity = $record->enable_quantity;
    $this->impuesto_and_timbres_separados = $record->impuesto_and_timbres_separados;
    $this->additional_charge = $record->additional_charge;
    //'sku' => 'required|string|max:20|unique:products,sku',
    $this->banks = $record->banks->pluck('id')->toArray();
    $this->active = $record->active;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
  }

  public function update()
  {
    $recordId = $this->recordId;
    $this->active = empty($this->active) ? 0 : $this->active;
    $this->percent_eddi = empty($this->percent_eddi) ? 0 : $this->percent_eddi;
    $this->additional_charge = empty($this->additional_charge) ? 0 : $this->additional_charge;
    $this->validate();

    try {
      // Encuentra el registro existente
      $record = Product::findOrFail($recordId);

      //dd($this);
      // Actualiza el producto
      $record->update($this->only([
        'name',
        'code',
        'business_id',
        'type',
        'type_notarial_act',
        'unit_type_id',
        'caby_code',
        //'price'
        'enable_quantity',
        'enable_registration_calculation',
        'percent_eddi',
        'additional_charge',
        'impuesto_and_timbres_separados',
        'active',
      ]));

      $closeForm = $this->closeForm;

      $record->banks()->sync($this->banks);

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
      $record = Product::findOrFail($recordId);

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
      'name',
      'type',
      'type_notarial_act',
      'unit_type_id',
      'caby_code',
      'code',
      //'price',
      'cuantos',
      'is_expense',
      'enable_quantity',
      'enable_registration_calculation',
      'percent_eddi',
      'additional_charge',
      'impuesto_and_timbres_separados',
      'sku',
      'image',
      'description',
      'active',
      'created_by',
      'closeForm',
      'activeTab',
      'degloseHtml'
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

  public function updated($propertyName)
  {
    if ($propertyName == 'desgloseMonto') {
      $this->activeTab = 4;
    }
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
  }

  public function setCabyCode($code)
  {
    $this->caby_code = $code;
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'product-datatable')
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
    'filter_code' => NULL,
    'filter_name' => NULL,
    'filter_caby_code' => NULL,
    'filter_bank' => NULL,
    'filter_type_notarial_act' => NULL,
    'filter_unit_type' => NULL,
    'filter_active' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'code',
        'orderName' => 'code',
        'label' => __('Code'),
        'filter' => 'filter_code',
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
        'field' => 'name',
        'orderName' => 'products.name',
        'label' => __('Name'),
        'filter' => 'filter_name',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-300',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'caby_code',
        'orderName' => 'caby_code',
        'label' => __('Caby Code'),
        'filter' => 'filter_caby_code',
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
        'field' => 'bank',
        'orderName' => '',
        'label' => __('Bank'),
        'filter' => 'filter_bank',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-500',
        'function' => 'getHtmlcolumnBank',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'type_notarial_act',
        'orderName' => 'products.type_notarial_act',
        'label' => __('Type of Notarial Act'),
        'filter' => 'filter_type_notarial_act',
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
        'field' => 'unit_type',
        'orderName' => 'unit_types.code',
        'label' => __('Unit Type'),
        'filter' => 'filter_unit_type',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => 'center',
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
        'field' => 'active',
        'orderName' => 'products.active',
        'label' => __('Active'),
        'filter' => 'filter_active',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
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

  public function setActiveTab($tab)
  {
    $this->activeTab = $tab;
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  #[On('clonar')]
  public function clonar($recordId)
  {
    DB::beginTransaction();

    try {
      $original = Product::with(['honorariosTimbres', 'banks', 'taxes'])->findOrFail($recordId);

      // Clonar el producto principal
      $cloned = $original->replicate();
      $cloned->name = $original->name . ' (Copia)';
      $cloned->code = '9999'; //
      $cloned->save();

      // Clonar honorarios/timbres
      foreach ($original->honorariosTimbres as $item) {
        $copy = $item->replicate();
        $copy->product_id = $cloned->id;
        $copy->save();
      }

      // Clonar bancos relacionados
      foreach ($original->productsBanks as $item) {
        $copy = $item->replicate();
        $copy->product_id = $cloned->id;
        $copy->save();
      }

      // Clonar impuestos
      foreach ($original->taxes as $item) {
        $copy = $item->replicate();
        $copy->product_id = $cloned->id;
        $copy->save();
      }

      DB::commit();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The product has been successfully cloned')]);

      return response()->json(['success' => true, 'message' => 'Producto clonado exitosamente', 'id' => $cloned->id]);
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error has occurred. While cloning the service') . ' ' . $e->getMessage()]);
      Log::error('Error al clonar producto.', ['error' => $e->getMessage()]);
    }
  }

  public function calcularDesglose()
  {
    // Variables iniciales
    $montoTotal = 0;
    $totalTimbres = 0;
    $totalHonorarios = 0;
    $cargoAdicional = 0;

    // Construir el contenido de la tabla
    $tableContent = '';

    $servicio = Product::find($this->recordId);

    $quantity = 1;
    $price = $this->desgloseMonto;
    $bank_id = $this->desgloseBanco;
    $currency = $this->desgloseMoneda == 1 ? Currency::DOLARES : Currency::COLONES;
    $currencySymbol = $this->desgloseMoneda == 1 ? 'USD' : 'CRC';
    $tipo = $servicio->type_notarial_act;
    $changeType = Session::get('exchange_rate');


    $monto_sin_descuento = 0;
    $tipo = 'GASTO';
    $desglose_formula_timbres = $servicio->desgloseTimbreFormula($price, $quantity, $bank_id, $tipo, $currency, $changeType);
    $desglose_tabla_abogados_timbres = $servicio->desgloseTablaAbogados($price, $quantity, $bank_id, $tipo, $currency, $changeType);
    $desglose_calculos_fijos_timbres = $servicio->desgloseCalculosFijos($price, $quantity, $bank_id, $tipo, $currency, $changeType);
    $desglose_calculos_monto_manual_timbres = $servicio->desgloseCalculaMontoManual($price, $quantity, $bank_id, $tipo, $currency, $changeType);

    $tipo = 'HONORARIO';

    $desglose_honorarios = $servicio->desgloseHonorarios($price, $quantity, $bank_id, $tipo, $currency, $changeType);
    $desglose_calculo_monto_manual_honorarios = $servicio->desgloseCalculaMontoManual($price, $quantity, $bank_id, $tipo, $currency, $changeType);

    /*
    dd([
      $desglose_formula_timbres,
      $desglose_tabla_abogados_timbres,
      $desglose_calculos_fijos_timbres,
      $desglose_calculos_monto_manual_timbres,
      $desglose_honorarios,
      $desglose_calculo_monto_manual_honorarios
    ]);
*/

    $totalTimbres_temp = $desglose_formula_timbres['monto_sin_descuento'] +
      $desglose_tabla_abogados_timbres['monto_sin_descuento'] +
      $desglose_calculos_fijos_timbres['monto_sin_descuento'] +
      $desglose_calculos_monto_manual_timbres['monto_sin_descuento'];


    $totalHonorarios_temp = $desglose_honorarios['monto_sin_descuento'] +
      $desglose_calculo_monto_manual_honorarios['monto_sin_descuento'];

    $total_temp_sin_descuento = $totalTimbres_temp + $totalHonorarios_temp;
    $total_temp_con_descuento = $total_temp_sin_descuento;


    // Calcular $value
    $value = 0;
    foreach ($desglose_formula_timbres['datos'] as $d) {
      $value += $d['monto_con_descuento'];
    }
    foreach ($desglose_tabla_abogados_timbres['datos'] as $d) {
      $value += $d['monto_con_descuento'];
    }
    foreach ($desglose_calculos_fijos_timbres['datos'] as $d) {
      $value += $d['monto_con_descuento'];
    }
    foreach ($desglose_calculos_monto_manual_timbres['datos'] as $d) {
      $value += $d['monto_con_descuento'];
    }

    foreach ($desglose_honorarios['datos'] as $d) {
      $value += $d['monto_con_descuento'];
    }
    foreach ($desglose_calculo_monto_manual_honorarios['datos'] as $d) {
      $value += $d['monto_con_descuento'];
    }

    $description = $servicio->name . ' ' . $currencySymbol . ' ' . Helpers::formatDecimal($total_temp_con_descuento);

    // Fila principal
    $tableContent .= '<tr>
            <td>' . html_entity_decode($description) . '</td>
            <td>
                
            </td>
        </tr>';

    // Desglose formula timbre
    foreach ($desglose_formula_timbres['datos'] as $data) {
      $tableContent .= '<tr>
                    <td>' . ($data['titulo'] ?? '') . '</td>
                    <td>
                        ' . $currencySymbol . ' ' . Helpers::formatDecimal($data['monto_con_descuento']) . '
                    </td>
                </tr>';
      $totalTimbres += $data['monto_con_descuento'];
    }


    // Desglose tabla de abogados
    foreach ($desglose_tabla_abogados_timbres['datos'] as $data) {
      $tableContent .= '<tr>
                    <td>' . ($data['titulo'] ?? '') . '</td>
                    <td>
                        ' . $currencySymbol . ' ' . Helpers::formatDecimal($data['monto_con_descuento']) . '
                    </td>
                </tr>';
      $totalTimbres += $data['monto_con_descuento'];
    }

    // Desglose calculos fijos
    foreach ($desglose_calculos_fijos_timbres['datos'] as $data) {
      $tableContent .= '<tr>
                    <td>' . ($data['titulo'] ?? '') . '</td>
                    <td>
                        ' . $currencySymbol . ' ' . Helpers::formatDecimal($data['monto_con_descuento']) . '
                    </td>
                </tr>';
      $totalTimbres += $data['monto_con_descuento'];
    }

    // Desglose calculos monto manual
    foreach ($desglose_calculos_monto_manual_timbres['datos'] as $data) {
      $tableContent .= '<tr>
                    <td>' . ($data['titulo'] ?? '') . '</td>
                    <td>
                        ' . $currencySymbol . ' ' . Helpers::formatDecimal($data['monto_con_descuento']) . '
                    </td>
                </tr>';
      $totalTimbres += $data['monto_con_descuento'];
    }

    // Desglose honorarios
    foreach ($desglose_honorarios['datos'] as $data) {
      $tableContent .= '<tr>
                    <td>' . ($data['titulo'] ?? '') . '</td>
                    <td>
                        ' . $currencySymbol . ' ' . Helpers::formatDecimal($data['monto_con_descuento']) . '
                    </td>
                </tr>';
      $totalHonorarios += $data['monto_con_descuento'];
    }

    // Desglose honorarios monto manual
    foreach ($desglose_calculo_monto_manual_honorarios['datos'] as $data) {
      $tableContent .= '<tr>
                    <td>' . ($data['titulo'] ?? '') . '</td>
                    <td>
                        ' . $currencySymbol . ' ' . Helpers::formatDecimal($data['monto_con_descuento']) . '
                    </td>
                </tr>';
      $totalHonorarios += $data['monto_con_descuento'];
    }

    $textServicio = $servicio->name . "  " . $currencySymbol . "  " . Helpers::formatDecimal($price) . " ";
    $total = Helpers::formatDecimal($total_temp_con_descuento);

    // Construir HTML final
    $this->degloseHtml = <<<HTML
      <table class="table table-sm mb-0 border-top table-hover dataTable no-footer">
          <thead>
              <tr>
                  <th class="px-4 py-3 icon-hover text-nowrap" colspan="2">
                    <strong>DESGLOSE DEL ACTO NOTARIAL</strong>
                  </th>
              </tr>
          </thead>
          <tbody>
              {$tableContent}
          </tbody>

          <tfoot>
              <tr>
                  <td>

                  </td>
                  <td>
                      <strong>{$currencySymbol} {$total}</strong>
                  </td>
              </tr>
           </tfoot>
      </table>
      HTML;
  }
}
