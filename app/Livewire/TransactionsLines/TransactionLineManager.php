<?php

namespace App\Livewire\TransactionsLines;

use Exception;
use App\Models\Caso;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\TaxType;
use Livewire\Component;
use App\Helpers\Helpers;
use App\Models\Currency;
use App\Models\ProductTax;
use App\Models\Institution;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Models\DiscountType;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\BaseComponent;
use App\Models\DataTableConfig;
use App\Models\ExonerationType;
use App\Models\TransactionLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use App\Models\TransactionLineTax;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TransactionLineDiscount;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class TransactionLineManager extends BaseComponent {
  use WithFileUploads;
  use WithPagination;

  #[Url(as: 'ptSearch', history: true)]
  public $search = '';

  #[Url(as: 'ptSortBy', history: true)]
  public $sortBy = 'transactions_lines.id';

  #[Url(as: 'ptSortDir', history: true)]
  public $sortDir = 'ASC';

  #[Url(as: 'ptPerPage')]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  // Variables públicas
  public $transaction_id;
  public $transaction;
  public $product_id = NULL;
  public $caso_id = NULL;
  public $codigo = NULL;
  public $codigocabys = NULL;
  public $detail = NULL;
  public $quantity = NULL;
  public $price = NULL;
  public $fecha_reporte_gasto = NULL;
  public $fecha_pago_registro = NULL;
  public $numero_pago_registro = NULL;
  public $desglose_timbre_formula = NULL;
  public $desglose_tabla_abogados = NULL;
  public $desglose_calculos_fijos = NULL;
  public $desglose_calculo_monto_timbre_manual = NULL;
  public $desglose_honorarios = NULL;
  public $desglose_calculo_monto_honorario_manual = NULL;
  public $registro_currency_id = NULL;
  public $registro_change_type = NULL;
  public $registro_monto_escritura = NULL;
  public $registro_valor_fiscal = NULL;
  public $registro_cantidad = NULL;
  public $monto_cargo_adicional = NULL;
  public $calculo_registro_normal = NULL;
  public $calculo_registro_iva = NULL;
  public $calculo_registro_no_iva = NULL;

  public $honorarios = 0;
  public $timbres = 0;
  public $discount = 0;
  public $subtotal = 0;
  public $baseImponible = 0;
  public $tax = 0;
  public $impuestoAsumidoEmisorFabrica = 0;
  public $impuestoNeto = 0;
  public $total = 0;
  public $servGravados = 0;
  public $servExentos = 0;
  public $servExonerados = 0;
  public $servNoSujeto = 0;
  public $mercGravadas = 0;
  public $mercExentas = 0;
  public $mercExoneradas = 0;
  public $mercNoSujeta = 0;
  public $exoneration = 0;
  public $porcientoDescuento = 0;

  //public $cargoAdicional;

  //Propiedades de la transaction
  public $bank_id = NULL;
  public $type_notarial_act = NULL;
  public $caso_text = NULL;
  public $tipo_facturacion = NULL;

  //Listados
  public $taxes = [];
  public $discounts = [];

  public $closeForm = false;

  public $columns = [];
  public $defaultColumns = [];

  public $canview = false;
  public $cancreate = false;
  public $canedit = false;
  public $candelete = false;
  public $canexport = false;
  public $record = NULL;

  public $facturaCompra = NULL;

  public $degloseHtml = '';

  protected $listeners = [
    'cabyCodeSelected' => 'handleCabyCodeSelected',
    'datatableSettingChange' => 'refresDatatable',
  ];

  protected function getModelClass(): string {
    return TransactionLine::class;
  }

  public function handleCabyCodeSelected($code) {
    $this->codigocabys = $code['code'];
  }

  #[Computed()]
  public function products() {
    $query = Product::query()
      ->select(['products.id as id', 'products.name as name'])
      ->join('product_honorarios_timbres', 'product_honorarios_timbres.product_id', '=', 'products.id')
      ->join('products_banks', 'products_banks.product_id', '=', 'products.id') // 🔵 Agregar este LEFT JOIN a products_banks
      ->where(function ($q) {
        // Siempre filtra por type_notarial_act principal
        $q->where('products.type_notarial_act', '=', $this->type_notarial_act)
          ->where('products_banks.bank_id', '=', $this->bank_id);
      });

    // Evitar filas duplicadas
    $query->distinct();

    return $query->orderBy('products.name', 'ASC')->get();
  }

  #[Computed]
  public function taxTypes() {
    return TaxType::orderBy('code', 'ASC')->get();
  }


  #[Computed]
  public function taxRates() {
    return TaxRate::where('active', 1)->orderBy('code', 'ASC')->get();
  }

  #[Computed]
  public function exhonerations() {
    return ExonerationType::where('active', 1)->orderBy('code', 'ASC')->get();
  }

  #[Computed]
  public function institutes() {
    return Institution::orderBy('code', 'ASC')->get();
  }

  #[Computed]
  public function discountTypes() {
    return DiscountType::orderBy('code', 'ASC')->get();
  }

  #[On('updateTransactionContext')]
  public function handleUpdateContext($data) {
    // Aqui si entra cuando edito
    $this->transaction_id = $data['transaction_id'];
    $this->bank_id = $data['bank_id'];
    $this->type_notarial_act = $data['type_notarial_act'];
    $this->tipo_facturacion = $data['tipo_facturacion'] ?? null;
    Log::info('TransactionLineManager handleUpdateContext', ['data' => $data]);
    // Aquí puedes recargar los datos si es necesario

    $this->search = '';
    //$this->refresDatatable(); // Opcional: si quieres resetear las columnas también
  }

  public function mount($canview, $cancreate, $canedit, $candelete, $canexport, $facturaCompra = false, $transaction_id = null) {
    $this->addTax();  // Inicializa con un tax vacío
    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;
    $this->facturaCompra = $facturaCompra;
    $this->transaction_id = $transaction_id;

    // Intentar obtener de sesión primero
    if ($this->transaction_id) {
        $this->loadTransactionDetails();
    } elseif (session()->has('transaction_context')) {
      $this->handleUpdateContext(session()->get('transaction_context'));
    }

    $this->refresDatatable();
    Log::info('TransactionLineManager mounted', ['transaction_id' => $this->transaction_id, 'recordId' => $this->recordId]);
  }

  protected function loadTransactionDetails() {
      if (!$this->transaction_id) return;

      $transaction = Transaction::find($this->transaction_id);
      if ($transaction) {
          $this->bank_id = $transaction->bank_id;
          $this->type_notarial_act = $transaction->proforma_type;
          $this->tipo_facturacion = $transaction->tipo_facturacion;
      }
  }

  public function render() {
    Log::info('TransactionLineManager render', [
      'transaction_id' => $this->transaction_id,
      'search' => $this->search,
      'user_id' => Auth::id(),
      'canview' => $this->canview,
      'filters' => $this->filters,
    ]);

    $records = TransactionLine::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      ->where('transaction_id', '=', $this->transaction_id)
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.transactions-lines.datatable', [
      'records' => $records,
      ///'transaction' => $this->transaction,
      'canview' => $this->canview,
      'cancreate' => $this->cancreate,
      'canedit' => $this->canedit,
      'candelete' => $this->candelete,
      'canexport' => $this->canexport
    ]);
  }

  public function create() {
    Log::info('═══════════════════════════════════════════════════════');
    Log::info('🆕 TransactionLineManager::create() called');
    Log::info('═══════════════════════════════════════════════════════');

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetControls();
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->action = 'create';
    $this->quantity = 1;

    Log::info('📤 Dispatching reinitFormControls event');
    $this->dispatch('reinitFormControls');

    $text = '';
    Log::info('📤 Dispatching setSelect2Value event', ['id' => 'caso_id', 'value' => '', 'text' => $text]);
    $this->dispatch('setSelect2Value', id: 'caso_id', value: '', text: $text);

    $this->dispatch('scroll-to-top');
    Log::info('✅ create() method completed');
  }

  #[On('bankChange')]
  public function bankChange($bankId) {
    $this->bank_id = $bankId;
  }

  // Definir reglas, mensajes y atributos
  protected function rules() {
    $rules = [
      'transaction_id' => 'required|exists:transactions,id',
      'product_id' => 'required|exists:products,id',
      'caso_id'   => 'nullable|integer|exists:casos,id',
      'codigo' => 'required|string|max:13',
      'codigocabys' => 'required|string|max:13',
      'detail' => 'required|string',
      'quantity' => 'required|numeric|min:1',
      'price' => 'required|numeric|min:0',
      'porcientoDescuento' => 'numeric|min:0',
      'fecha_reporte_gasto' => 'nullable|string|max:20',
      'fecha_pago_registro' => 'nullable|date',
      'numero_pago_registro' => 'nullable|string|max:20',
      'registro_currency_id' => 'nullable|exists:currencies,id',
      'registro_change_type' => 'nullable|numeric|min:0',
      'registro_monto_escritura' => 'nullable|numeric|min:0',
      'registro_valor_fiscal' => 'nullable|numeric|min:0',
      'registro_cantidad' => 'nullable|integer|min:1',
      'monto_cargo_adicional' => 'nullable|numeric|min:0',
      'calculo_registro_normal' => 'nullable|boolean',
      'calculo_registro_iva' => 'nullable|boolean',
      'calculo_registro_no_iva' => 'nullable|boolean',

      // Totales
      'honorarios' => 'nullable|numeric|min:0',
      'timbres' => 'nullable|numeric|min:0',
      'discount' => 'nullable|numeric|min:0',
      'subtotal' => 'nullable|numeric|min:0',
      'baseImponible' => 'nullable|numeric|min:0',
      'tax' => 'nullable|numeric|min:0',
      'impuestoAsumidoEmisorFabrica' => 'nullable|numeric|min:0',
      'impuestoNeto' => 'nullable|numeric|min:0',
      'total' => 'nullable|numeric|min:0',
      'servGravados' => 'nullable|numeric|min:0',
      'servExentos' => 'nullable|numeric|min:0',
      'servExonerados' => 'nullable|numeric|min:0',
      'servNoSujeto' => 'nullable|numeric|min:0',
      'mercGravadas' => 'nullable|numeric|min:0',
      'mercExentas' => 'nullable|numeric|min:0',
      'mercExoneradas' => 'nullable|numeric|min:0',
      'mercNoSujeta' => 'nullable|numeric|min:0',
      'exoneration' => 'nullable|numeric|min:0'
    ];

    // Reglas dinámicas para taxes
    foreach ($this->taxes as $index => $tax) {
      $rules["taxes.$index.tax_type_id"] = 'required|exists:tax_types,id';
      $rules["taxes.$index.tax_rate_id"] = 'required|exists:tax_rates,id';
      $rules["taxes.$index.tax"]         = 'required|numeric|min:0|max:100';
      //$rules["taxes.$index.tax_amount"]  = 'required|numeric|min:0';

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 99) {
        $rules["taxes.$index.tax_type_other"] = 'required|min:5|max:100';
      } else {
        $rules["taxes.$index.tax_type_other"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 8) {
        $rules["taxes.$index.factor_calculo_tax"] = 'required|numeric|min:0.01|max:9.9999';
      } else {
        $rules["taxes.$index.factor_calculo_tax"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && in_array($tax['tax_type_id'], [3, 4, 5, 6])) {
        $rules["taxes.$index.count_unit_type"] = 'required|numeric|min:0.01|max:99999.99';
        $rules["taxes.$index.impuesto_unidad"] = 'required|numeric|min:0.01|max:99999.99';
      } else {
        $rules["taxes.$index.count_unit_type"] = 'nullable';
        $rules["taxes.$index.impuesto_unidad"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 4) {
        $rules["taxes.$index.percent"] = 'required|numeric|min:0.01|max:9.9999';
        $rules["taxes.$index.proporcion"] = 'required|numeric|min:0.01|max:9.9999';
      } else {
        $rules["taxes.$index.percent"] = 'nullable';
        $rules["taxes.$index.proporcion"] = 'nullable';
      }

      if (isset($tax['tax_type_id']) && $tax['tax_type_id'] == 5) {
        $rules["taxes.$index.volumen_unidad_consumo"] = 'required|numeric|min:0.01|max:9.9999';
      } else {
        $rules["taxes.$index.volumen_unidad_consumo"] = 'nullable';
      }

      if ($tax['exoneration_type_id']) {
        $rules["taxes.$index.exoneration_percent"] = 'required|numeric|min:0.01|max:100';
        $rules["taxes.$index.exoneration_doc"] = 'required|max:40';
        $rules["taxes.$index.exoneration_date"] = 'required|date';
        $rules["taxes.$index.exoneration_institution_id"] = 'required|exists:institutions,id';
      }

      if ($tax['exoneration_type_id'] == '99') {
        $rules["taxes.$index.exoneration_doc_other"] = 'required|min:5|max:100';
      }

      if (in_array($tax['exoneration_type_id'], [2, 3, 6, 7, 8])) {
        $rules["taxes.$index.exoneration_article"] = 'required|max:1000';
        $rules["taxes.$index.exoneration_inciso"]  = 'required|max:1000';
      }

      if ($tax['exoneration_type_id'] == '99') {
        $rules["taxes.$index.exoneration_institute_other"] = 'required|min:5|max:100';
      }

      if ((float)$tax['exoneration_percent'] > 0) {
        $rules["taxes.$index.exoneration_doc"] = 'required|max:40';
        $rules["taxes.$index.exoneration_date"] = 'required|date';
        $rules["taxes.$index.exoneration_institution_id"] = 'required|exists:institutions,id';
      }
    }

    // Reglas dinámicas para discounts
    foreach ($this->discounts as $index => $discount) {
      $rules["discounts.$index.discount_type_id"] = 'required|exists:discount_types,id';
      $rules["discounts.$index.discount_percent"] = 'required|numeric|min:0.01|max:100';
      //$rules["discounts.$index.discount_amount"] = 'required|numeric';

      if (isset($discount['discount_type_id']) && $discount['discount_type_id'] == 99) {
        $rules["discounts.$index.discount_type_other"] = 'required|min:5|max:100';
        $rules["discounts.$index.nature_discount"] = 'required|min:3|max:80';
      } else {
        $rules["discounts.$index.discount_type_other"] = 'nullable';
        $rules["discounts.$index.nature_discount"] = 'nullable';
      }
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
    ];
  }

  // Atributos personalizados para los campos
  protected function validationAttributes() {
    $attributes = [
      'transaction_id' => 'ID de transacción',
      'product_id' => 'ID de producto',
      'codigo' => 'código',
      'codigocabys' => 'código CABYS',
      'detail' => 'detalle',
      'quantity' => 'cantidad',
      'price' => 'precio unitario',
      'porcientoDescuento' => 'Porciento de descuento',
      'discount' => 'descuento',
      'tax' => 'impuesto',
      'fecha_reporte_gasto' => 'fecha reporte gasto',
      'fecha_pago_registro' => 'fecha de pago',
      'numero_pago_registro' => 'número de pago',
      'honorarios' => 'honorarios',
      'timbres' => 'timbres',
      'registro_currency_id' => 'moneda del registro',
      'registro_change_type' => 'tipo de cambio',
      'registro_monto_escritura' => 'monto de escritura',
      'registro_valor_fiscal' => 'valor fiscal',
      'registro_cantidad' => 'cantidad de registro',
      'monto_cargo_adicional' => 'monto adicional',
      'calculo_registro_normal' => 'cálculo normal',
      'calculo_registro_iva' => 'cálculo con IVA',
      'calculo_registro_no_iva' => 'cálculo sin IVA',
    ];

    // Agregar dinámicamente los impuestos
    foreach ($this->taxes as $index => $tax) {
      $attributes["taxes.$index.tax_type_id"] = "Tipo de impuesto #" . ($index + 1);
      $attributes["taxes.$index.tax_rate_id"] = "Tasa de impuesto #" . ($index + 1);
      $attributes["taxes.$index.tax"] = "Impuesto #" . ($index + 1);
      $attributes["taxes.$index.tax_type_other"] = "Otro tipo de impuesto #" . ($index + 1);
      $attributes["taxes.$index.factor_calculo_tax"] = "Factor de cálculo #" . ($index + 1);
      $attributes["taxes.$index.count_unit_type"] = "Cantidad de unidad #" . ($index + 1);
      $attributes["taxes.$index.percent"] = "Porcentaje #" . ($index + 1);
      $attributes["taxes.$index.proporcion"] = "Proporción #" . ($index + 1);
      $attributes["taxes.$index.volumen_unidad_consumo"] = "Volumen por unidad #" . ($index + 1);
      $attributes["taxes.$index.impuesto_unidad"] = "Impuesto por unidad #" . ($index + 1);
      $attributes["taxes.$index.tax_amount"] = "Monto IVA #" . ($index + 1);

      $attributes["taxes.$index.exoneration_type_id"] = "tipo de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_doc"] = "documento de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_doc_other"] = "otro documento #" . ($index + 1);
      $attributes["taxes.$index.exoneration_institution_id"] = "código de institución #" . ($index + 1);
      $attributes["taxes.$index.exoneration_institute_other"] = "otra institución #" . ($index + 1);
      $attributes["taxes.$index.exoneration_article"] = "artículo de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_inciso"] = "inciso de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_date"] = "fecha de exoneración #" . ($index + 1);
      $attributes["taxes.$index.exoneration_percent"] = "porcentaje de exoneración #" . ($index + 1);
    }

    // Agregar dinámicamente los impuestos
    foreach ($this->discounts as $index => $discount) {
      $attributes["discounts.$index.discount_type_id"] = "Tipo de descuento #" . ($index + 1);
      $attributes["discounts.$index.discount_percent"] = "Porciento de descuento #" . ($index + 1);
      $attributes["discounts.$index.discount_amount"] = "Monto de descuento #" . ($index + 1);
      $attributes["discounts.$index.discount_type_other"] = "Tipo de descuento Otro #" . ($index + 1);
      $attributes["discounts.$index.nature_discount"] = "Naturaleza de descuento #" . ($index + 1);
    }

    return $attributes;
  }

  public function store() {
    $this->cleanEmptyForeignKeys();
    $transaction = Transaction::find($this->transaction_id);

    if (!$transaction) {
        $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Transaction incorrectly loaded or not found.')]);
        return false;
    }

    $product = Product::where('id', $this->product_id)->first();
    if ($product) {
      $this->codigo = $product->code;
      //$this->codigocabys = $product->caby_code;
      $currency_id = $transaction->currency_id;
      $additionalCharge = (float)($product->additional_charge ?? 0);
      $changeType = (float)($transaction->proforma_change_type ?? 1);
      $this->monto_cargo_adicional = $currency_id == 1 ? $additionalCharge : ($additionalCharge * $changeType);
    }

    if (empty($this->porcientoDescuento) || is_null($this->porcientoDescuento))
      $this->porcientoDescuento = 0;

    // Validar
    $validatedData = $this->validate();

    if ($transaction->proforma_type == 'HONORARIO' && empty($this->taxes)) {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('Debe definir el impuesto')]);
      return false;
    }

    try {
      DB::beginTransaction();

      // Crear el registro
      $record = TransactionLine::create($validatedData);
      Log::info('TransactionLine created in DB', ['id' => $record->id, 'transaction_id' => $record->transaction_id]);

      $closeForm = $this->closeForm;

      $this->validateTaxes($this->taxes);

      if (in_array($transaction->document_type, [Transaction::PROFORMACOMPRA, Transaction::FACTURACOMPRAELECTRONICA]))
        $monto = $record->getMonto();
      else {
        $honorarios = $record->getHonorarios($transaction->bank_id, 'HONORARIO', $transaction->currency_id, $transaction->proforma_change_type, $record->porcientoDescuento);
        $porcientoDescuento = $record->porcientoDescuento;
        $descuentos = $record->calculaMontoDescuentos($honorarios, $porcientoDescuento);

        $monto = $honorarios - $descuentos;
      }

      // Calcular los montos de impuesto
      $this->actualizarTaxAmount($monto);

      // Sincronizar impuestos
      foreach ($this->taxes as $tax) {
        if (empty($tax['exoneration_percent']))
          $tax['exoneration_percent'] = 0;
        $record->taxes()->updateOrCreate(
          ['id' => $tax['id'] ?? null], // Si el id existe, actualiza; si no, crea
          $tax  // Pasamos el arreglo directamente
        );
      }

      // Sincronizar descuentos
      foreach ($this->discounts as $discount) {
        if (!is_null($discount['discount_type_id'])) {
          $record->discounts()->updateOrCreate(
            ['id' => $discount['id'] ?? null],
            $discount
          );
        }
      }

      $record->updateTransactionTotals($record->transaction->currency_id);
      $this->dispatch('productUpdated', $record->transaction_id);  // Emitir evento para otros componentes

      $this->resetControls();
      if ($closeForm) {
        Log::info('💾 store() - closeForm is TRUE, setting action to list');
        $this->action = 'list';
      } else {
        Log::info('💾 store() - closeForm is FALSE, setting action to edit');
        $this->action = 'edit';
        $this->edit($record->id);
      }

      DB::commit();
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);
      Log::info('✅ store() completed successfully', ['record_id' => $record->id, 'closeForm' => $closeForm]);
    } catch (\Exception $e) {
      DB::rollBack();
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function edit($recordId) {
    $this->cleanEmptyForeignKeys();
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $record = TransactionLine::find($recordId);
    $this->record = $record;
    $this->recordId = $recordId;

    // Asignar valores del registro a las variables públicas
    $this->transaction_id = $record->transaction_id;
    $this->product_id = $record->product_id;
    $this->caso_id = $record->caso_id;
    $this->codigo = $record->codigo;
    $this->codigocabys = $record->codigocabys;
    $this->detail = $record->detail;
    $this->quantity = $record->quantity;
    $this->price = $record->price;
    $this->porcientoDescuento = $record->porcientoDescuento;
    $this->fecha_reporte_gasto = $record->fecha_reporte_gasto;
    $this->fecha_pago_registro = $record->fecha_pago_registro;
    $this->numero_pago_registro = $record->numero_pago_registro;
    $this->desglose_timbre_formula = $record->desglose_timbre_formula;
    $this->desglose_tabla_abogados = $record->desglose_tabla_abogados;
    $this->desglose_calculos_fijos = $record->desglose_calculos_fijos;
    $this->desglose_calculo_monto_timbre_manual = $record->desglose_calculo_monto_timbre_manual;
    $this->desglose_honorarios = $record->desglose_honorarios;
    $this->desglose_calculo_monto_honorario_manual = $record->desglose_calculo_monto_honorario_manual;
    $this->registro_currency_id = $record->registro_currency_id;
    $this->registro_change_type = $record->registro_change_type;
    $this->registro_monto_escritura = $record->registro_monto_escritura;
    $this->registro_valor_fiscal = $record->registro_valor_fiscal;
    $this->registro_cantidad = $record->registro_cantidad;
    $this->monto_cargo_adicional = $record->monto_cargo_adicional;
    $this->calculo_registro_normal = $record->calculo_registro_normal;
    $this->calculo_registro_iva = $record->calculo_registro_iva;
    $this->calculo_registro_no_iva = $record->calculo_registro_no_iva;

    $this->honorarios = $record->honorarios;
    $this->timbres = $record->timbres;
    $this->discount = $record->discount;
    $this->subtotal = $record->subtotal;
    $this->baseImponible = $record->baseImponible;
    $this->tax = $record->tax;
    $this->impuestoAsumidoEmisorFabrica = $record->impuestoAsumidoEmisorFabrica;
    $this->impuestoNeto = $record->impuestoNeto;
    $this->total = $record->total;
    $this->servGravados = $record->servGravados;
    $this->servExentos = $record->servExentos;
    $this->servExonerados = $record->servExonerados;
    $this->servNoSujeto = $record->servNoSujeto;
    $this->mercGravadas = $record->mercGravadas;
    $this->mercExentas = $record->mercExentas;
    $this->mercExoneradas = $record->mercExoneradas;
    $this->mercNoSujeta = $record->mercNoSujeta;
    $this->exoneration = $record->exoneration;

    // Cargar taxes
    $this->taxes = $record->taxes->map(function ($tax) {
      return [
        'id' => $tax->id,
        'tax_type_id' => $tax->tax_type_id,
        'tax_rate_id' => $tax->tax_rate_id,
        'tax' => number_format((float)$tax->tax, 2, '.', ''),
        'tax_type_other' => $tax->tax_type_other ?? '',
        'factor_calculo_tax' => $tax->factor_calculo_tax ?? null,
        'count_unit_type' => $tax->count_unit_type ?? null,
        'percent' => $tax->percent ?? null,
        'proporcion' => $tax->proporcion ?? null,
        'volumen_unidad_consumo' => $tax->volumen_unidad_consumo ?? null,
        'impuesto_unidad' => $tax->impuesto_unidad ?? null,
        //'tax_amount' => $tax->tax_amount ?? null,
        'tax_amount' => Helpers::formatDecimal($tax->tax_amount ?? 0),
        'exoneration_type_id' => $tax->exoneration_type_id ?? null,
        'exoneration_doc' => $tax->exoneration_doc ?? null,
        'exoneration_doc_other' => $tax->exoneration_doc_other ?? null,
        'exoneration_institution_id' => $tax->exoneration_institution_id ?? null,
        'exoneration_institute_other' => $tax->exoneration_institute_other ?? null,
        'exoneration_article' => $tax->exoneration_article ?? null,
        'exoneration_inciso' => $tax->exoneration_inciso ?? null,
        'exoneration_date' => $tax->exoneration_date ?? null,
        'exoneration_percent' => $tax->exoneration_percent ?? null,
      ];
    })->toArray();

    // **Cargar descuentos existentes**
    $this->discounts = $record->discounts->map(function ($discount) {
      return [
        'id' => $discount->id,
        'discount_type_id' => $discount->discount_type_id ?? null,
        'discount_percent' => $discount->discount_percent ?? null,
        'discount_amount' => $discount->discount_amount ?? null,
        'discount_type_other' => $discount->discount_type_other ?? null,
        'nature_discount' => $discount->nature_discount ?? null,
      ];
    })->toArray();

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
        $this->dispatch('setSelect2Value', id: 'caso_id', value: $this->caso_id, text: $this->caso_text)->self();
      }
    } else {
      $this->caso_text = '';
      $this->dispatch('setSelect2Value', id: 'caso_id', value: '', text: $this->caso_text)->self();
    }

    $this->calcularDesglose();

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->action = 'edit';
    $this->dispatch('refreshCleave');
    $this->dispatch('reinitFormControls');
  }

  public function update() {
    $recordId = $this->recordId;
    // Limpia las claves foráneas antes de validar
    $this->cleanEmptyForeignKeys();

    $transaction = Transaction::find($this->transaction_id);

    if (!$transaction) {
        $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Transaction incorrectly loaded or not found.')]);
        return false;
    }

    $product = Product::where('id', $this->product_id)->first();
    if ($product) {
      $this->codigo = $product->code;
      //$this->codigocabys = $product->caby_code;
      $currency_id = $transaction->currency_id;
      $additionalCharge = (float)($product->additional_charge ?? 0);
      $changeType = (float)($transaction->proforma_change_type ?? 1);
      $this->monto_cargo_adicional = $currency_id == 1 ? $additionalCharge : ($additionalCharge * $changeType);
    }

    if (empty($this->porcientoDescuento) || is_null($this->porcientoDescuento))
      $this->porcientoDescuento = 0;

    //dd($this->product_id);

    // Validar
    $validatedData = $this->validate();

    if ($transaction->proforma_type == 'HONORARIO' && empty($this->taxes)) {
      $this->dispatch('show-notification', ['type' => 'warning', 'message' => __('Debe definir el impuesto')]);
      return false;
    }

    try {
      DB::beginTransaction();

      // Encuentra el registro existente
      $record = TransactionLine::findOrFail($recordId);

      // Actualiza el registro
      $record->update($validatedData);

      $closeForm = $this->closeForm;

      //*************************************************************
      //**********************Sincronizar taxes**********************
      //*************************************************************

      // Sincronizar impuestos (eliminar los que no estén en el array)
      $existingTaxIds = $record->taxes()->pluck('id')->toArray();

      // Filtrar impuestos enviados (excluyendo los que no tienen ID porque son nuevos)
      $incomingTaxIds = collect($this->taxes)->pluck('id')->filter()->toArray();

      // Eliminar impuestos que ya no están presentes
      $taxesToDelete = array_diff($existingTaxIds, $incomingTaxIds);

      if (!empty($taxesToDelete)) {
        $record->taxes()->whereIn('id', $taxesToDelete)->delete();
      }

      $this->validateTaxes($this->taxes);

      if (in_array($transaction->document_type, [Transaction::PROFORMACOMPRA, Transaction::FACTURACOMPRAELECTRONICA]))
        $monto = $record->getMonto();
      else {
        $honorarios = $record->getHonorarios($transaction->bank_id, 'HONORARIO', $transaction->currency_id, $transaction->proforma_change_type, $record->porcientoDescuento);
        $porcientoDescuento = $record->porcientoDescuento;
        $descuentos = $record->calculaMontoDescuentos($honorarios, $porcientoDescuento);

        $monto = $honorarios - $descuentos;
      }

      // Calcular los montos de impuesto
      $this->actualizarTaxAmount($monto);

      // Sincronizar impuestos
      foreach ($this->taxes as $tax) {
        if (empty($tax['exoneration_percent']))
          $tax['exoneration_percent'] = 0;
        $tax['tax_amount'] = str_replace(',', '', $tax['tax_amount']);
        $record->taxes()->updateOrCreate(
          ['id' => $tax['id'] ?? null],
          $tax
        );
      }

      //*************************************************************
      //*******************Sincronizar descuentos********************
      //*************************************************************

      // Sincronizar descuentos (eliminar los que no estén en el array)
      $existingDiscountIds = $record->discounts()->pluck('id')->toArray();

      // Filtrar descuentos enviados (excluyendo los que no tienen ID porque son nuevos)
      $incomingDiscountIds = collect($this->discounts)->pluck('id')->filter()->toArray();

      // Eliminar descuentos que ya no están presentes
      $discountsToDelete = array_diff($existingDiscountIds, $incomingDiscountIds);

      if (!empty($discountsToDelete)) {
        $record->discounts()->whereIn('id', $discountsToDelete)->delete();
      }

      // Actualizar o crear descuentos nuevos
      foreach ($this->discounts as $discount) {
        if (!is_null($discount['discount_type_id'])) {
          $record->discounts()->updateOrCreate(
            ['id' => $discount['id'] ?? null],
            $discount
          );
        }
      }

      $record->updateTransactionTotals($record->transaction->currency_id);
      $this->dispatch('productUpdated', $record->transaction_id);  // Emitir evento para otros componentes

      // Restablece los controles y emite el evento para desplazar la página al inicio
      $this->resetControls();
      $this->dispatch('scroll-to-top');
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);

      DB::commit();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($record->id);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }
  }

  private function validateTaxes($taxes) {
    foreach ($taxes as $tax) {
      $taxType = TaxType::find($tax['tax_type_id']);
      if (in_array($taxType->code, ['03', '04', '05', '06'])) {
        if (!$tax['count_unit_type'])
          throw new Exception("El campo 'CantidadUnidadMedida' para el calculo del impuesto es obligatorio cuando se usan los códigos de impuesto (03, 04, 05 y 06)");

        if ($taxType->code == '04' && !$tax['percent'])
          throw new Exception("El campo 'Porcentaje' para el calculo del impuesto es obligatorio cuando se usa el código de impuesto (04)");

        if ($taxType->code == '04' && !$tax['proporcion'])
          throw new Exception("El campo 'Proporcion' para el calculo del impuesto es obligatorio cuando se usa el código de impuesto (04)");

        if ($taxType->code == '05' && !$tax['volumen_unidad_consumo'])
          throw new Exception("El campo 'VolumenUnidadConsumo' para el calculo del impuesto es obligatorio cuando se usa el código de impuesto (05)");

        if (!$tax['impuesto_unidad'])
          throw new Exception("El campo 'ImpuestoUnidad' para el calculo del impuesto es obligatorio cuando se usan los códigos de impuesto (03, 04, 05 y 06)");
      }
    }
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton) {
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

  public function beforedelete() {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId) {
    try {
      $record = TransactionLine::findOrFail($recordId);
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

        // Emitir actualización
        $this->dispatch('updateSelectedIds', $this->selectedIds);

        $this->dispatch('productUpdated', $transaction_id);  // Emitir evento para otros componentes

        // Puedes emitir un evento para redibujar el datatable o actualizar la lista
        $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
        // Re-inicializar controles y select2 tras eliminar
        $this->resetControls();
        $this->dispatch('reinitFormControls');
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function updatedPerPage($value) {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }

  public function cancel() {
    Log::info('═══════════════════════════════════════════════════════');
    Log::info('❌ TransactionLineManager::cancel() called');
    Log::info('═══════════════════════════════════════════════════════');

    $this->action = 'list';
    $this->resetControls();

    Log::info('📤 Dispatching reinitFormControls event from cancel()');
    $this->dispatch('reinitFormControls');
    $this->dispatch('scroll-to-top');

    Log::info('✅ cancel() method completed');
  }

  public function resetControls() {
    $this->reset(
      'product_id',
      'codigocabys',
      'detail',
      'quantity',
      'price',
      'porcientoDescuento',
      'tax',
      'discount',
      'discounts',
      'taxes',
      'honorarios',
      'timbres',
      'discount',
      'subtotal',
      'baseImponible',
      'tax',
      'impuestoAsumidoEmisorFabrica',
      'impuestoNeto',
      'total',
      'servGravados',
      'servExentos',
      'servExonerados',
      'servNoSujeto',
      'mercGravadas',
      'mercExentas',
      'mercExoneradas',
      'mercNoSujeta',
      'exoneration',
      'closeForm',
      'degloseHtml',
      'caso_text',
      'caso_id'
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
  }

  public function setSortBy($sortByField) {
    if ($this->sortBy === $sortByField) {
      $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
      return;
    }

    $this->sortBy = $sortByField;
    $this->sortDir = 'DESC';
  }

  public function updatedSearch() {
    $this->resetPage();
  }

  public function updated($property) {
    // $property: The name of the current property that was updated
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    if ($property === 'product_id') {
      $product = Product::find($this->product_id);
      $taxes = ProductTax::where('product_id', $this->product_id)->get();
      $discounts = [];
      if ($this->recordId)
        $discounts = TransactionLineDiscount::where('transaction_line_id', $this->recordId)->get();

      if (!is_null($product)) {
        $this->detail = $product->name;
        $this->codigo = $product->code;
        $this->codigocabys = $product->caby_code;
        $this->monto_cargo_adicional = NULL;
      }
      // Limpiar el array de taxes actual
      $this->taxes = [];

      $transaction = Transaction::find($this->transaction_id);
      $customer = $transaction->contact;
      $hasExhoneracion = $customer->exoneration_type_id ?? 0;

      // Cargar los impuestos como objetos
      foreach ($taxes as $tax) {
        if ($hasExhoneracion) {
          // Obtener los datos del cliente
          $exoneration_type_id = $customer->exoneration_type_id ?? null;
          $exoneration_doc = $customer->exoneration_doc ?? null;
          $exoneration_doc_other = $customer->exoneration_doc_other ?? null;
          $exoneration_institution_id = $customer->exoneration_institution_id ?? null;
          $exoneration_institute_other = $customer->exoneration_institute_other ?? null;
          $exoneration_article = $customer->exoneration_article ?? null;
          $exoneration_inciso = $customer->exoneration_inciso ?? null;
          $exoneration_date = $customer->exoneration_date ?? null;
          $exoneration_percent = $customer->exoneration_percent > 0 ? number_format((float)$customer->exoneration_percent, 2, '.', '') : 0;
        } else {
          $exoneration_type_id = $tax->exoneration_type_id ?? null;
          $exoneration_doc = $tax->exoneration_doc ?? null;
          $exoneration_doc_other = $tax->exoneration_doc_other ?? null;
          $exoneration_institution_id = $tax->exoneration_institution_id ?? null;
          $exoneration_institute_other = $tax->exoneration_institute_other ?? null;
          $exoneration_article = $tax->exoneration_article ?? null;
          $exoneration_inciso = $tax->exoneration_inciso ?? null;
          $exoneration_date = $tax->exoneration_date ?? null;
          $exoneration_percent = $tax->exoneration_percent > 0 ? number_format((float)$tax->exoneration_percent, 2, '.', '') : 0;
        }

        $this->taxes[] = [
          'id' => $tax->id,  // Mantén el ID si existe para actualizaciones
          'tax_type_id' => $tax->tax_type_id ?? 1,
          'tax_rate_id' => $tax->tax_rate_id ?? 1,
          'tax' => number_format((float)$tax->tax, 2, '.', ''),
          'tax_type_other' => $tax->tax_type_other ?? '',
          'factor_calculo_tax' => $tax->factor_calculo_tax ?? null,
          'count_unit_type' => $tax->count_unit_type ?? null,
          'percent' => $tax->percent ?? null,
          'proporcion' => $tax->proporcion ?? null,
          'volumen_unidad_consumo' => $tax->volumen_unidad_consumo ?? null,
          'impuesto_unidad' => $tax->impuesto_unidad ?? null,
          'tax_amount' => $tax->tax_amount ?? null,

          'exoneration_type_id' => $exoneration_type_id,
          'exoneration_doc' => $exoneration_doc,
          'exoneration_doc_other' => $exoneration_doc_other,
          'exoneration_institution_id' => $exoneration_institution_id,
          'exoneration_institute_other' => $exoneration_institute_other,
          'exoneration_article' => $exoneration_article,
          'exoneration_inciso' => $exoneration_inciso,
          'exoneration_date' => $exoneration_date,
          'exoneration_percent' => $exoneration_percent,
        ];
      }

      // Limpiar el array de discounts actual
      $this->discounts = [];

      // Cargar los impuestos como objetos
      foreach ($discounts as $discount) {
        $this->discounts[] = [
          'id' => $discount->id,
          'discount_type_id' => $discount->discount_type_id ?? null,
          'discount_percent' => $discount->discount_percent ?? null,
          'discount_amount' => $discount->discount_amount ?? null,
          'discount_type_other' => $discount->discount_type_other ?? '',
          'nature_discount' => $discount->nature_discount ?? '',
        ];
      }
    }

    if ($property === 'price' || $property === 'quantity') {
      if (empty($this->price))
        $this->price = 0;
      if (empty($this->quantity))
        $this->quantity = 0;
      if (!is_null($this->record)) {
        $this->record->price = $this->price;
        $this->record->quantity = $this->quantity;
        //$this->record->updateTransactionTotals($this->record->transaction->currency_id);
        //$this->dispatch('productUpdated', $this->record->transaction_id);  // Emitir evento para otros componentes
        //$this->actualizarTaxAmount($this->record->honorarios);
      }
    }

    // Detectar si el cambio corresponde a un tax_type_id
    if (preg_match('/^taxes\.(\d+)\.tax_type_id$/', $property, $matches)) {
      $index = $matches[1]; // Extrae el índice del tax

      if (isset($this->taxes[$index])) {
        $taxTypeId = $this->taxes[$index]['tax_type_id'] ?? null;

        if ($taxTypeId != 99) {
          $this->taxes[$index]['tax_type_other'] = NULL;
        }

        if ($taxTypeId != 8) {
          $this->taxes[$index]['factor_calculo_tax'] = NULL;
        }

        if (!in_array($taxTypeId, [3, 4, 5, 6])) {
          $this->taxes[$index]['count_unit_type'] = NULL;
          $this->taxes[$index]['impuesto_unidad'] = NULL;
        }

        if ($taxTypeId != 4) {
          $this->taxes[$index]['percent'] = NULL;
          $this->taxes[$index]['proporcion'] = NULL;
        }

        if ($taxTypeId != 5) {
          $this->taxes[$index]['volumen_unidad_consumo'] = NULL;
        }
      }
    }

    $this->calcularDesglose();
  }

  public function setCabyCode($code) {
    $this->codigocabys = $code;
  }

  public function addTax() {
    $this->taxes[] =
      [
        'id' => null,
        'tax_type_id' => null,
        'tax_rate_id' => null,
        'tax' => null,
        'tax_type_other' => null,
        'factor_calculo_tax' => null,
        'count_unit_type' => null,
        'percent' => null,
        'proporcion' => null,
        'volumen_unidad_consumo' => null,
        'impuesto_unidad' => null,

        'exoneration_type_id' => null,
        'exoneration_doc' => null,
        'exoneration_doc_other' => null,
        'exoneration_institution_id' => null,
        'exoneration_institute_other' => null,
        'exoneration_article' => null,
        'exoneration_inciso' => null,
        'exoneration_date' => null,
        'exoneration_percent' => null,
      ];
  }

  public function removeTax($index) {
    unset($this->taxes[$index]);
    $this->taxes = array_values($this->taxes);
  }

  public function actualizarTaxAmount($honorarios) {
    $this->honorarios = $honorarios;
    foreach ($this->taxes as $index => $tax) {
      switch ($tax['tax_rate_id']) {
        case 1:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
        case 2:
          $this->taxes[$index]['tax'] = 1;
          $this->taxes[$index]['tax_amount'] = (float)($honorarios * 1) / 100;
          break;
        case 3:
          $this->taxes[$index]['tax'] = 2;
          $this->taxes[$index]['tax_amount'] = (float)($honorarios * 2) / 100;
          break;
        case 4:
          $this->taxes[$index]['tax'] = 4;
          $this->taxes[$index]['tax_amount'] = (float)($honorarios * 4) / 100;
          break;
        case 5:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
        case 6:
          $this->taxes[$index]['tax'] = 4;
          $this->taxes[$index]['tax_amount'] = (float)($honorarios * 4) / 100;
          break;
        case 7:
          $this->taxes[$index]['tax'] = 8;
          $this->taxes[$index]['tax_amount'] = (float)($honorarios * 8) / 100;
          break;
        case 8:
          $this->taxes[$index]['tax'] = 13;
          $this->taxes[$index]['tax_amount'] = (float)($honorarios * 13) / 100;
          //$this->taxes[$index]['tax_amount'] = $honorarios;
          break;
        case 9:
          $this->taxes[$index]['tax'] = 0.5;
          $this->taxes[$index]['tax_amount'] = (float)($honorarios * 0.5) / 100;
          break;
        case 10:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
        case 11:
          $this->taxes[$index]['tax'] = 0;
          $this->taxes[$index]['tax_amount'] = 0;
          break;
      }
    }

    $this->dispatch('refreshCleave');
  }

  #[On('tax-rate-changed')]
  public function updateTaxRateFields($index, $value) {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    if (!isset($this->taxes[$index])) {
      logger("Índice inválido: $index");
      return;
    }

    switch ($value) {
      case 1:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
      case 2:
        $this->taxes[$index]['tax'] = 1;
        $this->taxes[$index]['tax_amount'] = (float)($this->honorarios * 1) / 100;
        break;
      case 3:
        $this->taxes[$index]['tax'] = 2;
        $this->taxes[$index]['tax_amount'] = (float)($this->honorarios * 2) / 100;
        break;
      case 4:
        $this->taxes[$index]['tax'] = 4;
        $this->taxes[$index]['tax_amount'] = (float)($this->honorarios * 4) / 100;
        break;
      case 5:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
      case 6:
        $this->taxes[$index]['tax'] = 4;
        $this->taxes[$index]['tax_amount'] = (float)($this->honorarios * 4) / 100;
        break;
      case 7:
        $this->taxes[$index]['tax'] = 8;
        $this->taxes[$index]['tax_amount'] = (float)($this->honorarios * 8) / 100;
        break;
      case 8:
        $this->taxes[$index]['tax'] = 13;
        $this->taxes[$index]['tax_amount'] = (float)($this->honorarios * 13) / 100;
        break;
      case 9:
        $this->taxes[$index]['tax'] = 0.5;
        $this->taxes[$index]['tax_amount'] = (float)($this->honorarios * 0.5) / 100;
        break;
      case 10:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
      case 11:
        $this->taxes[$index]['tax'] = 0;
        $this->taxes[$index]['tax_amount'] = 0;
        break;
    }

    $this->dispatch('refreshCleave');
  }

  public function addDiscount() {
    $this->discounts[] = [
      'discount_type_id' => null,
      'discount_percent' => null,
      'discount_amount' => null,
      'discount_type_other' => null,
      'nature_discount' => null,
    ];
  }

  public function removeDiscount($index) {
    unset($this->discounts[$index]);
    $this->discounts = array_values($this->discounts);
  }

  #[On('discount-type-changed')]
  public function updateDiscountTypeFields($index, $value) {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->discounts[$index]['discount_type_id'] = $value;
  }

  #[On('percent-changed')]
  public function calculateDiscountAmount($index, $value) {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    $this->discounts[$index]['discount_amount'] = 10;
    if (!empty($this->discounts[$index]['discount_percent']) && is_numeric($value) && $this->price > 0) {
      $price = (float) $this->honorarios;
      $percent = (float) $value;
      $this->discounts[$index]['discount_amount'] = round(($price * $percent) / 100, 2);
    }
  }

  public function refresDatatable() {
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
    'filter_codigocabys' => NULL,
    'filter_detail' => NULL,
    'filter_numero_caso' => NULL,
    'filter_price' => NULL,
    'filter_quantity' => NULL,
    'filter_timbres' => NULL,
    'filter_honorarios' => NULL,
    'filter_discount' => NULL,
    'filter_monto_cargo_adicional' => NULL,
    'filter_subtotal' => NULL,
    'filter_tax' => NULL,
    'filter_exoneration' => NULL,
    'filter_total' => NULL,
  ];

  public function getDefaultColumns() {
    $this->defaultColumns = [
      [
        'field' => 'codigocabys',
        'orderName' => 'codigocabys',
        'label' => __('Caby Code'),
        'filter' => 'filter_codigocabys',
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
        'field' => 'detail',
        'orderName' => 'detail',
        'label' => __('Description'),
        'filter' => 'filter_detail',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => 'wrap-col-400',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
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
        'field' => 'price',
        'orderName' => 'transactions_lines.price',
        'label' => __('Price'),
        'filter' => 'filter_price',
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
        'field' => 'timbres',
        'orderName' => 'timbres',
        'label' => __('Timbre'),
        'filter' => 'filter_timbres',
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
        'field' => 'honorarios',
        'orderName' => 'honorarios',
        'label' => __('Honorario'),
        'filter' => 'filter_honorarios',
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
        'field' => 'discount',
        'orderName' => 'discount',
        'label' => __('Discount'),
        'filter' => 'filter_discount',
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
        'field' => 'monto_cargo_adicional',
        'orderName' => 'monto_cargo_adicional',
        'label' => __('Additional Charge'),
        'filter' => 'filter_monto_cargo_adicional',
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
        'field' => 'subtotal',
        'orderName' => 'subtotal',
        'label' => __('Subtotal'),
        'filter' => 'filter_subtotal',
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
        'field' => 'tax',
        'orderName' => 'tax',
        'label' => __('Tax'),
        'filter' => 'filter_tax',
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
        'field' => 'exoneration',
        'orderName' => 'exoneration',
        'label' => __('Exonerated'),
        'filter' => 'filter_exoneration',
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
        'field' => 'total',
        'orderName' => 'total',
        'label' => __('Total Line'),
        'filter' => 'filter_total',
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

  public function storeAndClose() {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de almacenamiento
    $this->store();
  }

  public function updateAndClose() {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function resetFilters() {
    $this->reset('filters');
    $this->selectedIds = [];
  }

  public function dateRangeSelected($id, $range) {
    $this->filters[$id] = $range;
  }

  public function calcularDesglose() {
    // Variables iniciales
    $montoTotal = 0;
    $totalTimbres = 0;
    $totalHonorarios = 0;
    $cargoAdicional = 0;
    $this->degloseHtml = '';

    // Construir el contenido de la tabla
    $tableContent = '';

    $servicio = Product::find($this->product_id);
    if ($servicio) {
      $transaction = Transaction::find($this->transaction_id);

      $quantity = 1;
      $price = $this->price;

      if ($this->porcientoDescuento > 0)
        $price = $this->price * $this->porcientoDescuento / 100;

      $bank_id = $this->bank_id;
      $currency = $transaction->currency_id == 1 ? Currency::DOLARES : Currency::COLONES;
      $currencySymbol = $transaction->currency_id == 1 ? 'USD' : 'CRC';
      $tipo = $servicio->type_notarial_act;
      $changeType = Session::get('exchange_rate');
      if ($bank_id && $price && $currency && $tipo) {
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
  }

  protected function cleanEmptyForeignKeys() {
    // Lista de campos que pueden ser claves foráneas
    $foreignKeys = [
      'product_id',
      'caso_id',
      // Agrega otros campos aquí
    ];

    foreach ($foreignKeys as $key) {
      if (isset($this->$key) && $this->$key === '') {
        $this->$key = null;
      }
    }
  }
}
