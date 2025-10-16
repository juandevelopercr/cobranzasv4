<?php

namespace App\Livewire\Transactions;

use App\Helpers\Helpers;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\DataTableConfig;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\DocumentSequenceService;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class InvoiceManager extends TransactionManager
{
  public $customer_text; // para mostrar el texto inicial

  public $sortBy = 'transactions.consecutivo';

  public $document_type = ['FE', 'TE'];

  public $filters = [
    'filter_proforma_no',
    'filter_consecutivo' => NULL,
    'filter_document_type' => NULL,
    'filter_customer_name' => NULL,
    'filter_department_name' => NULL,
    'filter_user_name' => NULL,
    'filter_transaction_date' => NULL,
    'filter_issuer_name' => NULL,
    'filter_numero_caso' => NULL,
    'filter_referencia' => NULL,
    'filter_oc' => NULL,
    'filter_migo' => NULL,
    'filter_bank_name' => NULL,
    'filter_currency_code' => NULL,
    'filter_fecha_envio_email' => NULL,
    'filter_status' => NULL,
    'filter_totalComprobante' => NULL,
    'filter_total_usd' => NULL,
    'filter_total_crc' => NULL,
    'filter_action' => NULL,
  ];

  public $documentTypes;

  public function mount()
  {
    parent::mount();
    // Aquí puedes agregar lógica específica para proformas
    $this->documentTypes = [
      ['id' => 'FE', 'name' => 'FACTURA'],
      ['id' => 'TE', 'name' => 'TIQUETE']
    ];
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'invoice-datatable')
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
        'field' => 'document_type',
        'orderName' => 'transactions.document_type',
        'label' => __('Tipo'),
        'filter' => 'filter_document_type',
        'filter_type' => 'select',
        'filter_sources' => 'documentTypes',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlDocumentType',
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
        'field' => 'nombre_caso',
        'orderName' => '',
        'label' => __('Case/Reference'),
        'filter' => 'filter_referencia',
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
        'field' => 'fecha_envio_email',
        'orderName' => 'transactions.fecha_envio_email',
        'label' => __('Fecha de envio de email'),
        'filter' => 'filter_fecha_envio_email',
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
        'field' => 'total_usd',
        'orderName' => '',
        'label' => __('Total USD'),
        'filter' => 'filter_total_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
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
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getTotalComprobante',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tComprobanteCrc',
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
        'function' => 'getInvoiceHtmlColumnAction',
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

  protected function afterTransactionSaved()
  {
    // Lógica específica tras guardar una proforma
    // Ejemplo: generar PDF, enviar notificación, etc.
  }

  protected function getFilteredQuery()
  {
    $documentType = ['FE', 'TE'];
    $query = Transaction::search($this->search, $this->filters)
      ->whereIn('document_type', $documentType);

    // Condiciones según el rol del usuario
    $allowedRoles = User::ROLES_ALL_BANKS;
    $user = auth()->user();
    if ($user->hasAnyRole($allowedRoles)) {
      $query->where(function ($q) {
        $q->whereIn('status', [Transaction::PENDIENTE, Transaction::RECIBIDA, Transaction::ACEPTADA, Transaction::RECHAZADA, Transaction::ANULADA]);
      });
    } else {
      //Obtener bancos
      $allowedBanks = $user->banks->pluck('id');
      if (!empty($allowedBanks)) {
        $query->whereIn('transactions.bank_id', $allowedBanks);
      }
      $query->whereIn('status', [Transaction::PENDIENTE, Transaction::RECIBIDA, Transaction::ACEPTADA, Transaction::RECHAZADA, Transaction::ANULADA]);
    }
    return $query;
  }

  public function render()
  {
    $query = $this->getFilteredQuery();

    // Ordenamiento y paginación final
    $records = $query
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.transactions.invoice-datatable', [
      'records' => $records,
    ]);
  }

  public function updated($propertyName)
  {
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

    if ($propertyName == 'bank_id') {
      // emitir el evento para que actualice la info en las lineas
      $this->dispatch('bankChange', $this->bank_id); // Enviar evento al frontend
    }

    if ($propertyName == 'email_cc') {
      $this->updatedEmails();
    }

    if ($propertyName == 'bank_id') {
      $this->setEnableControl();
    }

    if ($propertyName == 'location_id') {
      if ($this->location_id == '' | is_null($this->location_id))
        $this->location_economic_activity_id = null;
    }

    if ($propertyName == 'contact_id') {
      if ($this->contact_id == '' | is_null($this->contact_id))
        $this->contact_economic_activity_id = null;
    }

    $this->dispatch('updateExportFilters', [
      'search' => $this->search,
      'filters' => $this->filters,
      'selectedIds' => $this->selectedIds,
    ]);

    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
  }

  public function updatedCurrencyId($value)
  {
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

  public function updatedEmails()
  {
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

  public function setEnableControl()
  {
    $this->enableoc = false;
    $this->enablemigo = false;
    $this->enableor = false;
    $this->enablegln = false;
    $this->enableprebill = false;

    if ($this->bank_id == Bank::SANJOSE) {
      $this->enableoc = true;
      $this->enablemigo = true;

      $this->or = '';
      $this->gln = '';
      $this->prebill = '';
    } else
    if ($this->bank_id == Bank::TERCEROS) {
      $this->enableoc = true;
      $this->enablemigo = true;
      $this->enableor = true;
      $this->enablegln = true;
      $this->enableprebill = true;
    } else {
      $this->oc = '';
      $this->migo = '';
      $this->or = '';
      $this->gln = '';
      $this->prebill = '';
    }
  }

  public function confirmarWithFormAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $this->dispatch('show-creditnote-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);

    /*
    // static::getName() devuelve automáticamente el nombre del componente Livewire actual, útil para dispatchTo.
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
    */
  }

  public function beforeCreditNote()
  {
    $this->confirmarWithFormAccion(
      null,
      'createCreditNote',
      "¿Está seguro que desea anular la factura electrónica?",
      'Después de confirmar, la factura será anulada mediante nota de crédito electrónica',
      __('Sí, proceed')
    );
  }

  #[On('createCreditNote')]
  public function createCreditNote($recordId, $motivo)
  {
    DB::beginTransaction();

    try {
      // Bloquear el registro original para evitar modificaciones concurrentes
      $original = Transaction::with([
        'lines.taxes',
        'lines.discounts',
        'otherCharges',
        //'commissions', // Si es necesario clonar
        //'documents'   // Si es necesario clonar
      ])->lockForUpdate()->findOrFail($recordId);

      // Validar que la transacción original sea válida para nota de crédito
      if (!$this->isCreditNoteEligible($original->status)) {
        throw new \Exception(__('El comprobante no es elegible para nota de crédito. Seleccione un comprobante con estado ACEPTADO'));
      }

      $cloned = $original->replicate();
      $now = Carbon::now('America/Costa_Rica');

      // Configuración básica
      $cloned->forceFill([
        'document_type' => Transaction::NOTACREDITOELECTRONICA,
        'transaction_date' => $now->format('Y-m-d H:i:s'),
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'created_by' => auth()->id(),

        'RefTipoDoc' => '01',
        'RefNumero' => $original->key,
        'RefFechaEmision' => $original->transaction_date,
        'RefCodigo' => '01',
        'RefRazon' => trim($motivo),

        'created_by' => auth()->user()->id,
        'proforma_status' => null,
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'access_token' => NULL,
        'response_xml' => NULL,
        'filexml' => NULL,
        'filepdf' => NULL,

        'numero_deposito_pago' => NULL,
        'numero_traslado_honorario' => NULL,
        'numero_traslado_gasto' => NULL,
        'num_request_hacienda_set' => 0,
        'num_request_hacienda_get' => 0,
        'comision_pagada' => 0,
        'transaction_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'invoice_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'fecha_pago' => NULL,
        'fecha_deposito_pago' => NULL,
        'fecha_traslado_honorario' => NULL,
        'fecha_traslado_gasto' => NULL,
        'fecha_solicitud_factura' => NULL,
        'fecha_envio_email' => NULL,
        'totalPagado' => 0,
        'pendientePorPagar' => $original->totalComprobante,
        'vuelto' => 0,
      ]);

      // Generar consecutivo y clave
      $secuencia = DocumentSequenceService::generateConsecutive(
        $cloned->document_type,
        $cloned->location_id
      );
      $cloned->consecutivo = $cloned->getConsecutivo($secuencia);
      $cloned->key = $cloned->generateKey();
      $cloned->save();

      // Clonar líneas con montos negativos
      foreach ($original->lines as $line) {
        $clonedLine = $line->replicate();
        $clonedLine->forceFill([
          'transaction_id' => $cloned->id,
          //'quantity' => -abs($line->quantity), // Negativo
          //'unit_price' => -abs($line->unit_price), // Negativo
        ]);
        $clonedLine->save();

        // Clonar impuestos (negativos)
        foreach ($line->taxes as $tax) {
          $clonedTax = $tax->replicate();
          $clonedTax->transaction_line_id = $clonedLine->id;
          //$clonedTax->amount = -abs($tax->amount);
          $clonedTax->save();
        }

        // Clonar descuentos (negativos)
        foreach ($line->discounts as $discount) {
          $clonedDiscount = $discount->replicate();
          $clonedDiscount->transaction_line_id = $clonedLine->id;
          //$clonedDiscount->amount = -abs($discount->amount);
          $clonedDiscount->save();
        }

        $clonedLine->updateTransactionTotals($original->currency_id);
      }

      // Clonar otros cargos (negativos)
      foreach ($original->otherCharges as $charge) {
        $clonedCharge = $charge->replicate();
        $clonedCharge->transaction_id = $cloned->id;
        //$clonedCharge->amount = -abs($charge->amount);
        $clonedCharge->save();
      }

      $payment = new TransactionPayment;
      $payment->transaction_id = $cloned->id;
      $payment->tipo_medio_pago = '04';  // transaferencia
      $payment->medio_pago_otros = '';
      $payment->total_medio_pago = $cloned->totalComprobante;
      $payment->save();

      // Clonar comisiones y documentos si es necesario
      // ... (agregar lógica similar según requerimientos)

      // Generar XML
      $xml = Helpers::generateComprobanteElectronicoXML($cloned, true, 'content');

      // Autenticación en Hacienda
      try {
        $authService = new AuthService();
        $token = $authService->getToken(
          $cloned->location->api_user_hacienda,
          $cloned->location->api_password
        );
      } catch (\Exception $e) {
        throw new \Exception("An error occurred when trying to obtain the token in the hacienda api" . ' ' . $e->getMessage());
      }

      // Enviar a Hacienda
      $api = new ApiHacienda();
      $result = $api->send(
        $xml,
        $token,
        $cloned,
        $cloned->location,
        Transaction::NCE
      );

      if ($result['error'] != 0) {
        throw new \Exception($result['mensaje']);
      }

      // Actualizar estado si es exitoso
      $cloned->update([
        'status' => Transaction::RECIBIDA,
        'invoice_date' => $now
      ]);

      DB::commit();

      // Para que recalule los totales de la factura
      //$cloned->recalculeteTotals();

      // Livewire: Notificación y limpieza
      $this->reset(['selectedIds', 'recordId']);
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('Se ha creado la nota de crédito satisfactoriamente')]);

      return response()->json([
        'success' => true,
        'id' => $cloned->id
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error creating credit note', ['error' => $e, 'recordId' => $recordId]);

      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Ha ocurrido un error al crear a nota de crédito') . ' ' . $e->getMessage()]);
    }
  }

  private function isCreditNoteEligible($status): bool
  {
    return in_array($status, [
      Transaction::ACEPTADA,
      Transaction::RECHAZADA
    ]);
  }

  public function beforeDebitNote()
  {
    $this->confirmarWithFormAccion(
      null,
      'createDebitNote',
      "¿Está seguro que desea realizar nota de débito a la factura electrónica?",
      'Después de confirmar, se creará la nota de débito electrónica',
      __('Sí, proceed')
    );
  }

  #[On('createDebitNote')]
  public function createDebitNote($recordId, $motivo)
  {
    DB::beginTransaction();

    try {
      // Bloquear el registro original para evitar modificaciones concurrentes
      $original = Transaction::with([
        'lines.taxes',
        'lines.discounts',
        'otherCharges',
        //'commissions', // Si es necesario clonar
        //'documents'   // Si es necesario clonar
      ])->lockForUpdate()->findOrFail($recordId);

      // Validar que la transacción original sea válida para nota de débito
      if (!$this->isCreditNoteEligible($original->status)) {
        throw new \Exception(__('El comprobante no es elegible para nota de débito. Seleccione un comprobante con estado ACEPTADO'));
      }

      $cloned = $original->replicate();
      $now = Carbon::now('America/Costa_Rica');

      // Configuración básica
      $cloned->forceFill([
        'document_type' => Transaction::NOTADEBITOELECTRONICA,
        'transaction_date' => $now->format('Y-m-d H:i:s'),
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'created_by' => auth()->id(),
        'key' => null,
        'consecutivo' => null,

        'RefTipoDoc' => '01',
        'RefNumero' => $original->key,
        'RefFechaEmision' => $original->transaction_date,
        'RefCodigo' => '02',
        'RefRazon' => trim($motivo),

        'created_by' => auth()->user()->id,
        'proforma_status' => null,
        'status' => Transaction::PENDIENTE,
        'payment_status' => 'due',
        'access_token' => NULL,
        'response_xml' => NULL,
        'filexml' => NULL,
        'filepdf' => NULL,

        'numero_deposito_pago' => NULL,
        'numero_traslado_honorario' => NULL,
        'numero_traslado_gasto' => NULL,
        'num_request_hacienda_set' => 0,
        'num_request_hacienda_get' => 0,
        'comision_pagada' => 0,
        'transaction_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'invoice_date' => Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s'),
        'fecha_pago' => NULL,
        'fecha_deposito_pago' => NULL,
        'fecha_traslado_honorario' => NULL,
        'fecha_traslado_gasto' => NULL,
        'fecha_solicitud_factura' => NULL,
        'fecha_envio_email' => NULL,
        'totalPagado' => 0,
        'pendientePorPagar' => $original->totalComprobante,
        'vuelto' => 0,
      ]);

      $cloned->save();

      DB::commit();

      // Livewire: Notificación y limpieza
      $this->reset(['selectedIds', 'recordId']);
      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('Se ha creado la nota de débito satisfactoriamente')]);

      // Redirigir al componente de edición de la nota de débito
      return $this->redirectRoute('billing-debit-note', ['id' => $cloned->id, 'action' => 'edit']);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error creating debit note', ['error' => $e, 'recordId' => $recordId]);

      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('Ha ocurrido un error al crear a nota de débito') . ' ' . $e->getMessage()]);
    }
  }
}
