<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\MediaLibrary\CustomPathGenerator;
use App\Models\Area;
use App\Models\Bank;
use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Caso;
use App\Models\CodigoContable;
use App\Models\ConditionSale;
use App\Models\Contact;
use App\Models\Cuenta;
use App\Models\Currency;
use App\Models\TransactionCommission;
use App\Models\TransactionLine;
use App\Models\TransactionOtherCharge;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\Hacienda\ApiHacienda;
use Carbon\Carbon;
use Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Transaction extends Model implements HasMedia
{
  use InteractsWithMedia;
  use LogsActivity;
  use SoftDeletes;

  // ESTADOS DE PROFORMAS
  const PROCESO   = 'PROCESO';
  const SOLICITADA = 'SOLICITADA';
  const PENDIENTE = 'PENDIENTE';
  const ACEPTADA  = 'ACEPTADA';

  // ESTADOS DE FACTURAS
  const RECIBIDA  = 'RECIBIDA';

  // ESTADOS COMUNES DE PROFORMAS  Y FACTURAS
  const FACTURADA     = 'FACTURADA';
  const RECHAZADA     = 'RECHAZADA';
  const ANULADA       = 'ANULADA';

  // document_type
  const PROFORMA                        = 'PR';
  const COTIZACION                      = 'CT';
  const NOTACREDITO                     = 'NC';
  const NOTADEBITO                      = 'ND';
  const FACTURAELECTRONICA              = 'FE';
  const TIQUETEELECTRONICO              = 'TE';
  const NOTACREDITOELECTRONICA          = 'NCE';
  const NOTADEBITOELECTRONICA           = 'NDE';
  const PROFORMACOMPRA                  = 'PRC';
  const FACTURACOMPRAELECTRONICA        = 'FEC';
  const FACTURAEXPORTACIONELECTRONICA   = 'FEE';
  const RECIBOELECTRONICOPAGO           = 'REP';
  const CASO                            = 'CASO';

  // Esto solo lo uso para la secuencia o consecutivo de los gastos
  const PROFORMAGASTO                   = 'PRG';

  // Tipos de comprobantes electrónicos
  const FE   = '01';
  const NDE  = '02';
  const NCE  = '03';
  const TE   = '04';
  const CAC  = '05';
  const CAPC = '06';
  const CRC  = '07';
  const FEC  = '08';
  const FEE  = '09';
  const REP  = '10';

  const PAGADO = 'paid';
  const DEBIDA = 'due';
  const PARCIAL = 'partial';
  const ANULADO = 'annulled';

  public $infoCaso;

  protected $fillable = [
    'business_id',
    'location_id',
    'location_economic_activity_id',
    'cuenta_id',
    'showInstruccionesPago',
    'document_type',
    'proforma_type',
    'proforma_status',
    'status',
    'payment_status',
    'contact_id',
    'contact_economic_activity_id',
    'customer_name',
    'customer_comercial_name',
    'customer_email',
    'email_cc',
    'proforma_no',
    'consecutivo',
    'key',
    'transaction_date',
    'invoice_date',
    'currency_id',
    'area_id',
    'bank_id',
    'codigo_contable_id',
    'economic_activity_id',
    'condition_sale',
    'condition_sale_other',
    'pay_term_number',
    'pay_term_type',
    'proforma_change_type',
    'factura_change_type',
    'registro_change_type',
    'message',
    'notes',
    'access_token',
    'response_xml',
    'filexml',
    'filepdf',
    'transaction_reference',
    'transaction_reference_id',
    'contingencia',
    'RefTipoDoc',
    'RefTipoDocOtro',
    'RefNumero',
    'RefFechaEmision',
    'RefCodigo',
    'RefCodigoOtro',
    'RefRazon',
    'caso_id',
    'invoice_type',
    'fecha_comision_pagada',
    'fecha_pago',
    'fecha_deposito_pago',
    'numero_deposito_pago',
    'fecha_traslado_honorario',
    'numero_traslado_honorario',
    'fecha_traslado_gasto',
    'fecha_envio_email',
    'numero_traslado_gasto',
    'comision_pagada',
    'contacto_banco',
    'nombre_caso',
    'socio_davivienda',
    'detalle_adicional',
    'tipo_facturacion',
    'oc',
    'migo',
    'or',
    'gln',
    'prebill',
    'fecha_solicitud_factura',
    'is_retencion',
    'created_by',
    'totalHonorarios',
    'totalTimbres',
    'totalAditionalCharge',

    'totalServGravados',
    'totalServExentos',
    'totalServExonerado',
    'totalServNoSujeto',

    'totalmercGravadas',
    'totalmercExentas',
    'totalMercExonerada',
    'totalMercNoSujeta',

    'totalGravado',
    'totalExento',
    'totalExonerado',
    'totalNoSujeto',

    'totalVenta',
    'totalDiscount',
    'totalVentaNeta',
    'totalTax',
    'totalImpuesto',
    'totalImpAsumEmisorFabrica',
    'totalIVADevuelto',
    'totalOtrosCargos',
    'totalComprobante',

    // Estos campos creo se pueden eliminar
    'totalImpuestoServGravados',
    'totalImpuestoMercGravadas',
    'totalImpuestoServExonerados',
    'totalImpuestoMercExoneradas',

    'totalPagado',
    'pendientePorPagar',
    'vuelto',
    'invoice_type',
    'is_old'
  ];

  public function business()
  {
    return $this->belongsTo(Business::class);
  }

  public function location()
  {
    return $this->belongsTo(BusinessLocation::class);
  }

  public function locationEconomicActivity()
  {
    return $this->belongsTo(EconomicActivity::class, 'location_economic_activity_id');
  }

  public function contact()
  {
    return $this->belongsTo(Contact::class);
  }

  public function contactEconomicActivity()
  {
    return $this->belongsTo(EconomicActivity::class, 'contact_economic_activity_id');
  }

  public function currency()
  {
    return $this->belongsTo(Currency::class, 'currency_id');
  }

  public function area()
  {
    return $this->belongsTo(Area::class);
  }

  public function caso()
  {
    return $this->belongsTo(Caso::class);
  }

  public function cuenta()
  {
    return $this->belongsTo(Cuenta::class);
  }

  public function bank()
  {
    return $this->belongsTo(Bank::class);
  }

  public function codigoContable()
  {
    return $this->belongsTo(CodigoContable::class,);
  }

  public function createdBy()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function documents()
  {
    return $this->media()->where('collection_name', 'documents');
  }

  public function lines()
  {
    return $this->hasMany(TransactionLine::class, 'transaction_id');
  }

  public function otherCharges()
  {
    return $this->hasMany(TransactionOtherCharge::class, 'transaction_id');
  }

  public function commisions()
  {
    return $this->hasMany(TransactionCommission::class, 'transaction_id');
  }

  public function payments()
  {
    return $this->hasMany(TransactionPayment::class);
  }

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('documents')
      ->useDisk('public')
      ->acceptsMimeTypes([
        'application/pdf',
        'application/msword',  // .doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // .docx
        'application/vnd.ms-excel',  // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',  // .xlsx
        'image/jpeg',
        'image/png'
      ]);
    //->singleFile();  // Evita múltiples archivos si es necesario (quítalo si no aplica)
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['*'])
      ->setDescriptionForEvent(fn(string $eventName) => "La transaction ha sido {$eventName}")
      ->useLogName('transaction')
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs();
    // Chain fluent methods for configuration options
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    $columns = [
      'transactions.id',
      'transactions.business_id',
      'transactions.location_id',
      'transactions.location_economic_activity_id',
      'business_locations.name as issuer_name',
      'transactions.contact_id',
      'transactions.contact_economic_activity_id',
      'transactions.cuenta_id',
      'transactions.showInstruccionesPago',
      'transactions.currency_id',
      'currencies.code as currency_code',
      'contacts.name as nombreContacto',
      'transactions.area_id',
      'transactions.bank_id',
      'transactions.tipo_facturacion',
      'banks.name as bank_name',
      'transactions.codigo_contable_id',
      'codigo_contables.codigo as codigo_contable_code',
      'codigo_contables.descrip as codigo_contable_descrip',
      'transactions.created_by',
      'users.name as user_name',
      'transactions.document_type',
      'transactions.proforma_type',
      'transactions.proforma_status',
      'transactions.status',
      'transactions.payment_status',
      'transactions.pay_term_type',
      'transactions.customer_name',
      'transactions.customer_comercial_name',
      'transactions.customer_email',
      'transactions.proforma_no',
      'transactions.consecutivo',
      'transactions.key',
      'transactions.access_token',
      'transactions.response_xml',
      'transactions.filexml',
      'transactions.filepdf',
      'transactions.transaction_reference',
      'transactions.transaction_reference_id',
      'transactions.condition_sale',
      'condition_sales.name as condicion_venta',
      'transactions.condition_sale_other',
      'transactions.numero_deposito_pago',
      'transactions.numero_traslado_honorario',
      'transactions.numero_traslado_gasto',
      'transactions.contacto_banco',
      'transactions.nombre_caso',
      'transactions.socio_davivienda',
      'transactions.pay_term_number',
      'transactions.proforma_change_type',
      'transactions.factura_change_type',
      'transactions.registro_change_type',
      'transactions.num_request_hacienda_set',
      'transactions.num_request_hacienda_get',
      'transactions.comision_pagada',
      'transactions.is_retencion',
      'transactions.message',
      'transactions.notes',
      'transactions.oc',
      'transactions.migo',
      'transactions.or',
      'transactions.gln',
      'transactions.detalle_adicional',
      'transactions.transaction_date',
      'transactions.invoice_date',
      'transactions.fecha_pago',
      'transactions.fecha_deposito_pago',
      'transactions.fecha_traslado_honorario',
      'transactions.fecha_traslado_gasto',
      'transactions.fecha_solicitud_factura',
      'transactions.fecha_envio_email',
      'transactions.RefTipoDoc',
      'transactions.RefTipoDocOtro',
      'transactions.RefNumero',
      'transactions.RefFechaEmision',
      'transactions.RefCodigo',
      'transactions.RefCodigoOtro',
      'transactions.RefRazon',
      DB::raw("CONCAT_WS(' / ',
          NULLIF(casos.pnumero, ''),
          NULLIF(casos.pnumero_operacion1, ''),
          NULLIF(casos.pnombre_demandado, ''),
          NULLIF(casos.pnombre_apellidos_deudor, '')
      ) as caso_info"),
      DB::raw("(
            SELECT GROUP_CONCAT(
                       TRIM(
                           CONCAT(
                               COALESCE(cc.codigo, ''),
                               ' ',
                               COALESCE(cc.descrip, '')
                           )
                       ) SEPARATOR ', '
                   )
            FROM transactions_commissions tc
            LEFT JOIN centro_costos cc ON tc.centro_costo_id = cc.id
            WHERE tc.transaction_id = transactions.id
        ) as centros_costos_html"),
      DB::raw("(
            SELECT COALESCE(SUM(tp.total_medio_pago), 0)
            FROM transactions_payments tp
            WHERE tp.transaction_id = transactions.id
        ) as payment"),
      DB::raw("ABS(
            COALESCE(transactions.totalComprobante, 0) -
            COALESCE((
                SELECT SUM(tp.total_medio_pago)
                FROM transactions_payments tp
                WHERE tp.transaction_id = transactions.id
            ), 0)
        ) as pending_payment"),
      DB::raw('DATEDIFF(NOW(), transactions.transaction_date) as dias_trascurridos'),
      DB::raw('(DATEDIFF(NOW(), transactions.transaction_date) - COALESCE(transactions.pay_term_number, 0)) as dias_vencidos'),
      'totalHonorarios',
      'totalTimbres',
      'totalAditionalCharge',
      'totalServGravados',
      'totalServExentos',
      'totalServExonerado',
      'totalServNoSujeto',
      'totalmercGravadas',
      'totalmercExentas',
      'totalMercExonerada',
      'totalMercNoSujeta',
      'totalGravado',
      'totalExento',
      'totalExonerado',
      'totalNoSujeto',
      'totalVenta',
      'totalDiscount',
      'totalVentaNeta',
      'totalTax',
      'totalImpuesto',
      'totalImpAsumEmisorFabrica',
      'totalIVADevuelto',
      'totalOtrosCargos',
      'totalComprobante',
      'totalImpuestoServGravados',
      'totalImpuestoMercGravadas',
      'totalImpuestoServExonerados',
      'totalImpuestoMercExoneradas',
      'totalPagado',
      'pendientePorPagar',
      'vuelto',
    ];

    // Añadimos el reference_id optimizado mediante joins
    $query->select($columns)
      ->join('business', 'transactions.business_id', '=', 'business.id')
      ->leftJoin('business_locations', 'transactions.location_id', '=', 'business_locations.id')
      ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
      ->join('currencies', 'transactions.currency_id', '=', 'currencies.id')
      ->join('banks', 'transactions.bank_id', '=', 'banks.id')
      ->join('users', 'transactions.created_by', '=', 'users.id')
      ->leftJoin('condition_sales', 'transactions.condition_sale', '=', 'condition_sales.id')
      ->leftJoin('casos', 'transactions.caso_id', '=', 'casos.id')
      ->leftJoin('areas', 'transactions.area_id', '=', 'areas.id')
      ->leftJoin('codigo_contables', 'transactions.codigo_contable_id', '=', 'codigo_contables.id')

      // Nuevos joins optimizados para reference_id
      ->leftJoin(DB::raw('(
            SELECT transaction_reference_id, MAX(id) as ref_id
            FROM transactions
            WHERE proforma_type = "GASTO"
            GROUP BY transaction_reference_id
        ) ref_gasto'), 'ref_gasto.transaction_reference_id', '=', 'transactions.id')

      /*
      ->leftJoin(DB::raw('(
            SELECT `key`, MAX(id) as ref_id
            FROM transactions
            WHERE proforma_type = "HONORARIO"
            GROUP BY `key`
        ) ref_other'), function ($join) {
        $join->on('ref_other.RefNumero', '=', 'transactions.key')
          ->where('transactions.proforma_type', '=', 'HONORARIO');
      })
      */

      ->leftJoin(DB::raw('(
            SELECT transactions.RefNumero, MAX(transactions.id) as ref_id
            FROM transactions
            WHERE transactions.proforma_type != "GASTO"
            GROUP BY transactions.RefNumero
        ) ref_other'), function ($join) {
        $join->on('ref_other.RefNumero', '=', 'transactions.key')
          ->where('transactions.proforma_type', '<>', 'GASTO');
      })



      // Nueva columna reference_id optimizada
      ->addSelect(DB::raw('
            COALESCE(
                ref_gasto.ref_id,
                ref_other.ref_id
            ) as reference_id
        '));

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_proforma_no'])) {
      $query->where('proforma_no', 'like', '%' . $filters['filter_proforma_no'] . '%');
    }

    if (!empty($filters['filter_consecutivo'])) {
      $query->where('consecutivo', 'like', '%' . $filters['filter_consecutivo'] . '%');
    }

    if (!empty($filters['filter_customer_name'])) {
      $query->where('customer_name', 'like', '%' . $filters['filter_customer_name'] . '%');
    }

    /*
    if (!empty($filters['filter_nombre_caso'])) {
      $query->whereRaw("
        CASE
            WHEN casos.numero_gestion IS NOT NULL AND TRIM(casos.numero_gestion) != ''
            THEN CONCAT(casos.numero, ' / ', casos.numero_gestion, ' / ', casos.deudor)
            ELSE CONCAT(casos.numero, ' / ', casos.deudor)
        END LIKE ?", ['%' . $filters['filter_nombre_caso'] . '%']);
    }
    */
    if (!empty($filters['filter_nombre_caso'])) {
      $query->where('transactions.nombre_caso', 'like', '%' . $filters['filter_nombre_caso'] . '%');
    }

    if (!empty($filters['filter_numero_caso'])) {
      $searchTerm = '%' . $filters['filter_numero_caso'] . '%';

      $query->where(function ($q) use ($searchTerm) {
        $q->whereNotNull('casos.numero_gestion')
          ->where('casos.numero_gestion', '<>', '')
          ->whereRaw("CONCAT(casos.numero, ' / ', casos.numero_gestion, ' / ', casos.deudor) LIKE ?", [$searchTerm]);
      })->orWhere(function ($q) use ($searchTerm) {
        $q->where(function ($subQuery) {
          $subQuery->whereNull('casos.numero_gestion')
            ->orWhere('casos.numero_gestion', '');
        })->whereRaw("CONCAT(casos.numero, ' / ', casos.deudor) LIKE ?", [$searchTerm]);
      });
    }

    if (isset($filters['filter_registro_change_type']) && !empty($filters['filter_registro_change_type'])) {
      $query->where('transactions.registro_change_type', 'like', '%' . $filters['filter_registro_change_type'] . '%');
    }

    if (!empty($filters['filter_document_type'])) {
      $query->where('transactions.document_type', '=', $filters['filter_document_type']);
    }

    if (isset($filters['filter_condition_sale']) && !empty($filters['filter_condition_sale'])) {
      $conditionSale = ConditionSale::find($filters['filter_condition_sale']);
      if ($conditionSale)
        $query->where('transactions.condition_sale', '=', $conditionSale->code);
    }

    if (isset($filters['filter_payment_status']) && !empty($filters['filter_payment_status'])) {
      $query->where('transactions.payment_status', '=', $filters['filter_payment_status']);
    }

    if (isset($filters['filter_pay_term_number']) && !empty($filters['filter_pay_term_number'])) {
      $query->where('transactions.pay_term_number', '=', $filters['filter_pay_term_number']);
    }

    if (isset($filters['filter_totalComprobante']) && !empty($filters['filter_totalComprobante'])) {
      $query->where('transactions.totalComprobante', 'like', '%' . $filters['filter_totalComprobante'] . '%');
    }

    if (isset($filters['filter_centro_costo']) && !empty($filters['filter_centro_costo'])) {
      $query->whereExists(function ($subquery) use ($filters) {
        $subquery->select(DB::raw(1))
          ->from('transactions_commissions as tc')
          ->join('centro_costos as cc', 'tc.centro_costo_id', '=', 'cc.id')
          ->whereRaw('tc.transaction_id = transactions.id')
          ->where('cc.id', '=', $filters['filter_centro_costo']);
      });
    }

    if (!empty($filters['filter_user_name'])) {
      $query->where('transactions.created_by', '=', $filters['filter_user_name']);
    }

    if (!empty($filters['filter_transaction_date'])) {
      $range = explode(' to ', $filters['filter_transaction_date']);

      if (count($range) === 2) {
          try {
              $start = Carbon::createFromFormat('d-m-Y', $range[0])->startOfDay();
              $end   = Carbon::createFromFormat('d-m-Y', $range[1])->endOfDay();

              $query->whereBetween('transaction_date', [$start, $end]);
          } catch (\Exception $e) {
              // log o ignorar
          }
      } else {
          try {
              $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_transaction_date']);

              $query->whereDate('transaction_date', $singleDate);
          } catch (\Exception $e) {
              // log o ignorar
          }
      }
    }

    if (!empty($filters['filter_fecha_solicitud_factura'])) {
      $range = explode(' to ', $filters['filter_fecha_solicitud_factura']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('fecha_solicitud_factura', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_solicitud_factura'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('fecha_solicitud_factura', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_fecha_envio_email'])) {
      $range = explode(' to ', $filters['filter_fecha_envio_email']);

      if (count($range) === 2) {
        try {
          // Validar y convertir las fechas del rango
          $start = Carbon::createFromFormat('d-m-Y', $range[0])->format('Y-m-d');
          $end = Carbon::createFromFormat('d-m-Y', $range[1])->format('Y-m-d');

          // Aplicar filtro si ambas fechas son válidas
          $query->whereBetween('filter_fecha_envio_email', [$start, $end]);
        } catch (\Exception $e) {
          // Manejar el caso de fechas inválidas (opcional: log o ignorar)
        }
      } else {
        try {
          // Validar y convertir la fecha única
          $singleDate = Carbon::createFromFormat('d-m-Y', $filters['filter_fecha_envio_email'])->format('Y-m-d');

          // Aplicar filtro si la fecha es válida
          $query->whereDate('filter_fecha_envio_email', $singleDate);
        } catch (\Exception $e) {
          // Manejar el caso de fecha inválida (opcional: log o ignorar)
        }
      }
    }

    if (!empty($filters['filter_issuer_name'])) {
      $query->where('transactions.location_id', '=', $filters['filter_issuer_name']);
    }

    if (!empty($filters['filter_oc'])) {
      $query->where('transactions.oc', 'like', '%' . $filters['filter_oc'] . '%');
    }

    if (!empty($filters['filter_migo'])) {
      $query->where('transactions.migo', 'like', '%' . $filters['filter_migo'] . '%');
    }

    if (!empty($filters['filter_bank_name'])) {
      $query->where('transactions.bank_id', '=', $filters['filter_bank_name']);
    }

    if (!empty($filters['filter_currency_code'])) {
      $query->where('transactions.currency_id', '=', $filters['filter_currency_code']);
    }

    if (!empty($filters['filter_status'])) {
      $query->whereRaw("
                CASE
                    WHEN document_type IN ('" . Transaction::PROFORMA . "', '" . Transaction::COTIZACION . "', '" . Transaction::NOTACREDITO . "', '" . Transaction::NOTADEBITO . "') THEN transactions.proforma_status
                    ELSE transactions.status
                END = ?", [trim($filters['filter_status'])]);
    }

    if (!empty($filters['filter_proforma_status'])) {
      $query->where('transactions.proforma_status', '=', $filters['filter_proforma_status']);
    }

    if (!empty($filters['filter_status'])) {
      $query->where('transactions.status', $filters['filter_status']);
    }

    if (!empty($filters['filter_proforma_type'])) {
      $query->where('transactions.proforma_type', '=', $filters['filter_proforma_type']);
    }

    if (isset($filters['filter_is_retencion']) && !is_null($filters['filter_is_retencion'])  && $filters['filter_is_retencion'] !== '') {
      $query->where('transactions.is_retencion', '=', $filters['filter_is_retencion']);
    }

    return $query;
  }


  // Retorna los estados de proformas o facturas. Si is_invoice es false retorna estados de proformas
  public static function getStatusOptions($is_invoice = false)
  {
    // Retornar los estados
    if ($is_invoice)
      $status = [
        ['id' => 'PENDIENTE', 'name' => __('PENDIENTE')],
        ['id' => 'RECIBIDA', 'name' => __('RECIBIDA')],
        ['id' => 'ACEPTADA', 'name' => __('ACEPTADA')],
        ['id' => 'RECHAZADA', 'name' => __('RECHAZADA')],
        ['id' => 'ANULADA', 'name' => __('ANULADA')],
      ];
    else
      $status = [
        ['id' => 'PROCESO', 'name' => __('PROCESO')],
        ['id' => 'SOLICITADA', 'name' => __('SOLICITADA')],
        ['id' => 'FACTURADA', 'name' => __('FACTURADA')],
        ['id' => 'RECHAZADA', 'name' => __('RECHAZADA')],
        ['id' => 'ANULADA', 'name' => __('ANULADA')],
      ];

    return collect($status);
  }

  public function getHtmlStatus()
  {
    $htmlData = '';
    if (in_array($this->document_type, [Transaction::PROFORMA, Transaction::COTIZACION, Transaction::NOTACREDITO, Transaction::NOTADEBITO])) {
      if ($this->document_type == Transaction::COTIZACION) {
        $htmlData = "<span class=\"badge bg-secondary\">" . __('PROCESO') . "</span>";
      } else {
        switch ($this->proforma_status) {
          case 'PROCESO':
            $htmlData = "<span class=\"badge bg-secondary\">" . __('PROCESO') . "</span>";
            break;
          case 'SOLICITADA':
            $htmlData = "<span class=\"badge bg-warning\">" . __('SOLICITADA') . "</span>";
            break;
          case 'FACTURADA':
            $htmlData = "<span class=\"badge bg-success\">" . __('FACTURADA') . "</span>";
            break;
          case 'RECHAZADA':
            $htmlData = "<span class=\"badge bg-danger\">" . __('RECHAZADA') . "</span>";
            break;
          case 'ANULADA':
            $htmlData = "<span class=\"badge bg-danger\">" . __('ANULADA') . "</span>";
            break;
        }
      }
    } else {
      switch ($this->status) {
        case 'PENDIENTE':
          $htmlData = "<span class=\"badge bg-light text-dark\">" . __('PENDIENTE') . "</span>";
          break;
        case 'RECIBIDA':
          $htmlData = "<span class=\"badge bg-warning\">" . __('RECIBIDA') . "</span>";
          break;
        case 'ACEPTADA':
          $htmlData = "<span class=\"badge bg-success\">" . __('ACEPTADA') . "</span>";
          break;
        case 'RECHAZADA':
          $htmlData = "<span class=\"badge bg-danger\">" . __('RECHAZADA') . "</span>";
          break;
        case 'ANULADA':
          $htmlData = "<span class=\"badge bg-danger\">" . __('ANULADA') . "</span>";
          break;
      }
    }
    return $htmlData;
  }

  public function getHtmlProformaStatus()
  {
    $htmlData = '';
    switch ($this->proforma_status) {
      case 'PROCESO':
        $htmlData = "<span class=\"badge bg-secondary\">" . __('PROCESO') . "</span>";
        break;
      case 'SOLICITADA':
        $htmlData = "<span class=\"badge bg-warning\">" . __('SOLICITADA') . "</span>";
        break;
      case 'FACTURADA':
        $htmlData = "<span class=\"badge bg-success\">" . __('FACTURADA') . "</span>";
        break;
      case 'RECHAZADA':
        $htmlData = "<span class=\"badge bg-danger\">" . __('RECHAZADA') . "</span>";
        break;
      case 'ANULADA':
        $htmlData = "<span class=\"badge bg-danger\">" . __('ANULADA') . "</span>";
        break;
    }
    return $htmlData;
  }

  public function getTotalComprobante($currencyCode, $format = false)
  {
    $total = 0;
    $changeType = $this->getChangeType();
    if ($currencyCode == $this->currency->code)
      $total = $this->totalComprobante;
    else
        if ($currencyCode != $this->currency->code) {
      if ($currencyCode == 'USD')
        $total = $this->totalComprobante / $changeType;
      else
        $total = $this->totalComprobante * $changeType;
    }

    if ($format)
      $total = Helpers::formatDecimal($total);

    return $total ?? 0;
  }

  public function getChangeType()
  {
    $changeType = $this->factura_change_type;
    if (in_array($this->document_type, [$this::PROFORMA, $this::NOTACREDITO, $this::NOTADEBITO]))
      $changeType = $this->proforma_change_type;
    if ($changeType == 0)
      $changeType = 1;

    return $changeType ?? 1;
  }

  // Genera la key del comprobante electrónico
  public function generateKey()
  {
    //$clave = '506' . date('d') . date('m') . date('y');
    // Inicializamos la clave con el prefijo "506"
    $fecha = Carbon::now('America/Costa_Rica');
    $clave = '506' . $fecha->format('dmy');

    // La identificación debe tener una longitud de 12 dígitos, completando con ceros a la izquierda
    $clave .= str_pad($this->location->identification, 12, '0', STR_PAD_LEFT);

    // Agregar el consecutivo
    $clave .= $this->consecutivo;

    // La situación del comprobante: '1' para normal, '2' para Contingenci y '3' Sin internet
    $clave .= '1';

    // Código de seguridad con la fecha actual en formato Ymd
    $clave .= date('Ymd');  // Generar año, mes, y día

    // Retornar la clave generada
    return $clave;
  }

  public function getConsecutivo($secuencia)
  {
    // Obtener el número de la sucursal, con ceros a la izquierda hasta 3 caracteres
    $a_number = str_pad($this->location->numero_sucursal, 3, "0", STR_PAD_LEFT);

    // Obtener el número del punto de venta, con ceros a la izquierda hasta 5 caracteres
    $b_number = str_pad($this->location->numero_punto_venta, 5, "0", STR_PAD_LEFT);

    // Obtener el código de tipo de comprobante
    $c_number = $this->getComprobanteCode();

    // Generar el consecutivo concatenando todos los componentes
    $consecutivo = $a_number . $b_number . $c_number . $secuencia;

    // Retornar el consecutivo generado
    return $consecutivo;
  }

  public function getComprobanteCode()
  {
    $code = '';
    switch ($this->document_type) {
      case 'FE':
        $code = '01';
        break;
      case 'NDE':
        $code = '02';
        break;
      case 'NCE':
        $code = '03';
        break;
      case 'TE':
        $code = '04';
        break;
      case 'CAC':      //Confirmación de aceptación del comprobante
        $code = '05';
        break;
      case 'CAPC':      //Confirmación de aceptación parcial del comprobante
        $code = '06';
        break;
      case 'CRC':      //Confirmación de rechazo del comprobante
        $code = '07';
        break;
      case 'FEC':      //Factura electrónica de compra
        $code = '08';
        break;
      case 'FEE':      //Factura electrónica de exportación
        $code = '09';
        break;
      case 'REP':      //Recibo electrónico de pago
        $code = '10';
        break;
      case 'ND':
        $code = '88';
        break;
      case 'NC':
        $code = '99';
        break;
      default:
        throw new \Exception(__('Type of document unknown'));
        break;
    }

    return $code;
  }

  public function getImpuestosAsumidos()
  {
    /*
    Este campo será de condición obligatoria cuando:
    ▪Se facturen productos o servicios, en cuya línea de
    detalle se indique en el campo “Código del descuento”
    los códigos 01 de “Regalías” o código 03 de
    “Bonificaciones”.
    ▪Se incluyan en la línea de detalle impuestos específicos
    a los combustibles, Bebidas Alcohólicas, sin contenido
    alcohólico, jabón de tocador y cemento.
    */
    return number_format(0, 5, '.', '');
  }

  public function getProformaHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';
    $html = '<div class="d-flex align-items-center flex-nowrap">'; // 👈 evita saltos de línea


    // PDF sencilla y detallada
    if ($user->can('download-pdf-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Sencilla SS"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Detallada"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Recibos
    if ($user->can('download-pdf-proformas') && in_array($this->proforma_status, [self::FACTURADA, self::ANULADA]) && $this->proforma_type === 'GASTO') {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-secondary p-0 me-2"
                title="Recibo Sencillo"
                wire:click="downloadReciboSencillo({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboSencillo">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboSencillo({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboSencillo({$this->id})"></i>
            </button>

            <button type="button"
                class="btn btn-link text-secondary p-0 me-2"
                title="Recibo Detallado"
                wire:click="downloadReciboDetallado({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboDetallado">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboDetallado({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboDetallado({$this->id})"></i>
            </button>
        HTML;
    }

    // Mostrar NOTA DE CREDITO
    if ($user->can('download-pdf-proformas') && in_array($this->proforma_status, [self::ANULADA]) && $this->proforma_type === 'GASTO') {
      // Mostrar el icono de la nota de crédito
      if ($this->reference_id)
        $html .= <<<HTML
            <button type="button"
                class="btn btn-link p-0 me-2 text-danger"
                title="Nota de crédito"
                wire:click="downloadProformaDetallada({$this->reference_id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->reference_id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->reference_id})"></i>
            </button>
        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-success p-0 me-2"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    // Solicitar Factura
    if ($this->proforma_status === self::PROCESO) {
      $html .= <<<HTML
          <button type="button"
              class="btn btn-link text-primary p-0 me-2"
              title="Solicitar factura"
              wire:click="beforesolicitar('{$this->id}', '{$this->proforma_no}')"
              wire:loading.attr="disabled"
              wire:target="beforesolicitar('{$this->id}', '{$this->proforma_no}')">
              <i class="bx bx-loader bx-spin {$iconSize}"
                  wire:loading
                  wire:target="beforesolicitar('{$this->id}', '{$this->proforma_no}')"></i>
              <i class="bx bx-right-arrow-alt {$iconSize}"
                  wire:loading.remove
                  wire:target="beforesolicitar('{$this->id}', '{$this->proforma_no}')"></i>
          </button>
      HTML;
    }

    // Facturar
    if ($user->can('facturar-proformas') && in_array($this->proforma_status, [self::PROCESO, self::SOLICITADA])) {
      $html .= <<<HTML
          <button type="button"
              class="btn btn-link text-dark p-0 me-2"
              title="Facturar"
              wire:click="beforefacturar('{$this->id}', '{$this->proforma_no}')"
              wire:loading.attr="disabled"
              wire:target="beforefacturar('{$this->id}','{$this->proforma_no}')">
              <i class="bx bx-loader bx-spin {$iconSize}"
                  wire:loading
                  wire:target="beforefacturar('{$this->id}','{$this->proforma_no}')"></i>
              <i class="bx bx-barcode {$iconSize}"
                  wire:loading.remove
                  wire:target="beforefacturar('{$this->id}','{$this->proforma_no}')"></i>
          </button>
      HTML;
    }

    // XML comprobante electrónico
    $html .= <<<HTML
          <button type="button"
              class="btn p-0 me-2 text-warning"
              title="Descargar XML"
              wire:click="downloadXML({$this->id})"
              wire:loading.attr="disabled"
              wire:target="downloadXML">
              <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXML({$this->id})"></i>
              <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadXML({$this->id})"></i>
          </button>
      HTML;


    $html .= '</div>';
    return $html;
  }

  public function getInvoiceHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // PDF comprobante electrónico
    if ($user->can('download-electronicinvoices')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Descargar - PDF"
                wire:click="downloadInvoice({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadInvoice">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadInvoice({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadInvoice({$this->id})"></i>
            </button>
        HTML;
    }

    // XML comprobante electrónico
    if ($user->can('download-electronicinvoices')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-warning"
                title="Descargar XML"
                wire:click="downloadXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-success"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    // Respuesta de hacienda XML
    if ($user->can('download-electronicinvoices') && !in_array($this->status, [Transaction::PENDIENTE, Transaction::RECIBIDA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Descargar XML respuesta de hacienda"
                wire:click="downloadHaciendaResponsaXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadHaciendaResponsaXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar comprobante Hacienda
    if ($user->can('view-electronicinvoices') && in_array($this->status, [self::PENDIENTE])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-warning"
                title="Enviar comprobante a Hacienda"
                wire:click="sendDocumentToHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="sendDocumentToHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="sendDocumentToHacienda({$this->id})"></i>
                <i class="bx bx-send {$iconSize}" wire:loading.remove wire:target="sendDocumentToHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    // Estado en Hacienda
    if ($user->can('facturar-proformas') && in_array($this->status, [self::PENDIENTE, self::RECIBIDA, self::ACEPTADA, self::RECHAZADA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-info"
                title="Obtener estado en Hacienda"
                wire:click="getStatusDocumentInHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="getStatusDocumentInHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="getStatusDocumentInHacienda({$this->id})"></i>
                <i class="bx bx-share {$iconSize}" wire:loading.remove wire:target="getStatusDocumentInHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';

    return $html;
  }

  public function getHistoryHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // PDF sencilla y detallada
    if ($user->can('download-pdf-documenthistory')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Sencilla"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Detallada"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Recibos
    if (
      $user->can('download-pdf-documenthistory') && in_array($this->proforma_status, [self::FACTURADA, self::ANULADA]) && $this->proforma_type === 'GASTO'
    ) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Sencillo"
                wire:click="downloadReciboSencillo({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboSencillo">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboSencillo({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboSencillo({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Detallado"
                wire:click="downloadReciboDetallado({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboDetallado">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboDetallado({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboDetallado({$this->id})"></i>
            </button>
        HTML;
    }

    if ($user->can('download-pdf-documenthistory') && in_array($this->proforma_status, [self::ANULADA, self::RECHAZADA])) {
      // Mostrar el icono de la nota de crédito
      if ($this->reference_id) {
        if ($this->proforma_type == 'GASTO') {
          $title = 'Nota de crédito';
          $html .= <<<HTML
              <button type="button"
                  class="btn btn-link p-0 me-2 text-danger"
                  title= "{$title}"
                  wire:click="downloadProformaDetallada({$this->reference_id})"
                  wire:loading.attr="disabled"
                  wire:target="downloadProformaDetallada">
                  <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->reference_id})"></i>
                  <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->reference_id})"></i>
              </button>
          HTML;
        } else {
          $title = 'Nota de crédito electrónica';
          //dd($this->reference_id);
          $html .= <<<HTML
              <button type="button"
                  class="btn p-0 me-2 text-danger"
                  title="{$title}"
                  wire:click="downloadInvoice({$this->reference_id})"
                  wire:loading.attr="disabled"
                  wire:target="downloadInvoice">
                  <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadInvoice({$this->reference_id})"></i>
                  <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadInvoice({$this->reference_id})"></i>
              </button>
          HTML;
        }
      }
    }

    // Enviar Email
    if ($user->can('send-email-documenthistory')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-success"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';

    return $html;
  }

  public function getElectronicCreditNoteHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // PDF comprobante electrónico
    if ($user->can('download-electronicinvoices')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Descargar PDF"
                wire:click="downloadInvoice({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadInvoice">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadInvoice({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadInvoice({$this->id})"></i>
            </button>
        HTML;
    }

    // XML comprobante electrónico
    if ($user->can('download-electronicinvoices')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-warning"
                title="Descargar XML"
                wire:click="downloadXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-success"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    // Respuesta de hacienda XML
    if ($user->can('download-electronicinvoices')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Descargar XML respuesta de hacienda"
                wire:click="downloadHaciendaResponsaXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadHaciendaResponsaXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar comprobante Hacienda
    if ($user->can('view-electronicinvoices') && in_array($this->status, [self::PENDIENTE, self::RECIBIDA, self::ACEPTADA, self::RECHAZADA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-warning"
                title="Enviar comprobante a Hacienda"
                wire:click="sendDocumentToHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="sendDocumentToHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="sendDocumentToHacienda({$this->id})"></i>
                <i class="bx bx-send {$iconSize}" wire:loading.remove wire:target="sendDocumentToHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    // Estado en Hacienda
    if ($user->can('view-electronicinvoices') && in_array($this->status, [self::RECIBIDA, self::ACEPTADA, self::RECHAZADA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-info"
                title="Obtener estado en Hacienda"
                wire:click="getStatusDocumentInHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="getStatusDocumentInHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="getStatusDocumentInHacienda({$this->id})"></i>
                <i class="bx bx-share {$iconSize}" wire:loading.remove wire:target="getStatusDocumentInHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getElectronicDebitNoteHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // PDF comprobante electrónico
    if ($user->can('download-electronicinvoices')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Descargar PDF"
                wire:click="downloadInvoice({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadInvoice">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadInvoice({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadInvoice({$this->id})"></i>
            </button>
        HTML;
    }

    // XML comprobante electrónico
    if ($user->can('download-electronicinvoices')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-warning"
                title="Descargar XML"
                wire:click="downloadXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-success"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    // Respuesta de hacienda XML
    if ($user->can('download-electronicinvoices') && !in_array($this->status, [Transaction::PENDIENTE, Transaction::RECIBIDA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Descargar XML respuesta de hacienda"
                wire:click="downloadHaciendaResponsaXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadHaciendaResponsaXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar comprobante Hacienda
    if ($user->can('view-electronicinvoices') && in_array($this->status, [self::PENDIENTE, self::RECIBIDA, self::ACEPTADA, self::RECHAZADA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-warning"
                title="Enviar comprobante a Hacienda"
                wire:click="sendDocumentToHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="sendDocumentToHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="sendDocumentToHacienda({$this->id})"></i>
                <i class="bx bx-send {$iconSize}" wire:loading.remove wire:target="sendDocumentToHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    // Estado en Hacienda
    if ($user->can('view-electronicinvoices') && in_array($this->status, [self::RECIBIDA, self::ACEPTADA, self::RECHAZADA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-info"
                title="Obtener estado en Hacienda"
                wire:click="getStatusDocumentInHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="getStatusDocumentInHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="getStatusDocumentInHacienda({$this->id})"></i>
                <i class="bx bx-share {$iconSize}" wire:loading.remove wire:target="getStatusDocumentInHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getFacturacompraHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';
    $html = '<div class="d-flex align-items-center flex-nowrap">'; // 👈 evita saltos de línea

    // PDF sencilla y detallada
    if ($user->can('download-pdf-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Sencilla"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-success p-0 me-2"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    // Descargar XML
    if ($user->can('download-xml-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-warning p-0 me-2"
                title="Descargar XML"
                wire:click="downloadXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Respuesta de hacienda XML
    if ($user->can('download-electronicinvoices') && !in_array($this->status, [Transaction::PENDIENTE, Transaction::RECIBIDA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Descargar XML respuesta de hacienda"
                wire:click="downloadHaciendaResponsaXML({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadHaciendaResponsaXML">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
                <i class="bx bx-code-block {$iconSize}" wire:loading.remove wire:target="downloadHaciendaResponsaXML({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar comprobante Hacienda
    if ($user->can('view-electronicinvoices') && in_array($this->status, [self::PENDIENTE, self::RECIBIDA, self::ACEPTADA, self::RECHAZADA])) {
      $html .= <<<HTML
          <button type="button"
              class="btn btn-link text-warning p-0 me-2"
              title="Enviar Comprobante Hacienda"
              wire:click="beforeSendHacienda('{$this->id}', '{$this->proforma_no}')"
              wire:loading.attr="disabled"
              wire:target="beforeSendHacienda('{$this->id}','{$this->proforma_no}')">
              <i class="bx bx-loader bx-spin {$iconSize}"
                  wire:loading
                  wire:target="beforeSendHacienda('{$this->id}','{$this->proforma_no}')"></i>
              <i class="bx bx-send {$iconSize}"
                  wire:loading.remove
                  wire:target="beforeSendHacienda('{$this->id}','{$this->proforma_no}')"></i>
          </button>
      HTML;
    }

    // Estado en Hacienda
    if ($user->can('view-electronicinvoices') && in_array($this->status, [self::RECIBIDA, self::ACEPTADA, self::RECHAZADA])) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-info"
                title="Obtener estado en Hacienda"
                wire:click="getStatusDocumentInHacienda({$this->id})"
                wire:loading.attr="disabled"
                wire:target="getStatusDocumentInHacienda">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="getStatusDocumentInHacienda({$this->id})"></i>
                <i class="bx bx-share {$iconSize}" wire:loading.remove wire:target="getStatusDocumentInHacienda({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getMovimientoFacturasHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Proforma Sencilla y Detallada
    if ($user->can('download-pdf-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Sencilla"
                @click.prevent="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Detallada"
                @click.prevent="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Recibos si es tipo GASTO facturada
    if ($user->can('download-pdf-proformas') && $this->proforma_status === self::FACTURADA && $this->proforma_type === 'GASTO') {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Sencillo"
                onclick="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadReciboSencillo({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboSencillo">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboSencillo({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboSencillo({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Detallado"
                onclick="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadReciboDetallado({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboDetallado">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboDetallado({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboDetallado({$this->id})"></i>
            </button>
        HTML;
    }

    // Eliminar (sin validación de permiso)
    $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2 text-danger"
            title="Eliminar"
            wire:click.prevent="confirmarAccion({$this->id}, 'deleteFacturaMovimiento', '¿Está seguro que desea eliminar la factura número: {$this->proforma_no}?', 'Después de confirmar se eliminará permanentemente', 'Sí, proceder')">
            <i class="bx bx-trash {$iconSize}"></i>
        </button>
    HTML;

    $html .= '</div>';

    return $html;
  }

  public function getMovimientoFacturasNoPagadasHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Proforma Sencilla y Detallada
    if ($user->can('download-pdf-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Sencilla"
                @click.prevent="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Detallada"
                @click.prevent="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Recibos (facturada y tipo GASTO)
    if ($user->can('download-pdf-proformas') && $this->proforma_status === self::FACTURADA && $this->proforma_type === 'GASTO') {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Sencillo"
                onclick="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadReciboSencillo({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboSencillo">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboSencillo({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboSencillo({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Detallado"
                onclick="window.dispatchEvent(new CustomEvent('proforma-loading-show'))"
                wire:click="downloadReciboDetallado({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboDetallado">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboDetallado({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboDetallado({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getCasoInvoiceHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // PDF sencilla y detallada
    if ($user->can('export-casos')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Sencilla"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Detallada"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Recibos
    if ($user->can('export-casos') && $this->proforma_status === self::FACTURADA && $this->proforma_type === 'GASTO') {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Sencillo"
                wire:click="downloadReciboSencillo({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboSencillo">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboSencillo({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboSencillo({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Detallado"
                wire:click="downloadReciboDetallado({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboDetallado">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboDetallado({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboDetallado({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getCalculoRegistroHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // PDF sencilla y detallada
    if ($user->can('download-pdf-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Sencilla"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2"
                title="Proforma Detallada"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Recibos
    if ($user->can('download-pdf-proformas') && $this->proforma_status === self::FACTURADA && $this->proforma_type === 'GASTO') {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Sencillo"
                wire:click="downloadReciboSencillo({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboSencillo">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboSencillo({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboSencillo({$this->id})"></i>
            </button>

            <button type="button"
                class="btn p-0 me-2 text-secondary"
                title="Recibo Detallado"
                wire:click="downloadReciboDetallado({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboDetallado">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboDetallado({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboDetallado({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getCuentasCobrarHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';
    $html = '<div class="d-flex align-items-center flex-nowrap">'; // 👈 evita saltos de línea

    // PDF sencilla y detallada
    if ($user->can('download-pdf-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Sencilla"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Detallada"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Recibos
    if ($user->can('download-pdf-proformas') && $this->proforma_status === self::FACTURADA && $this->proforma_type === 'GASTO') {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-secondary p-0 me-2"
                title="Recibo Sencillo"
                wire:click="downloadReciboSencillo({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboSencillo">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboSencillo({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboSencillo({$this->id})"></i>
            </button>

            <button type="button"
                class="btn btn-link text-secondary p-0 me-2"
                title="Recibo Detallado"
                wire:click="downloadReciboDetallado({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadReciboDetallado">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadReciboDetallado({$this->id})"></i>
                <i class="bx bxs-receipt {$iconSize}" wire:loading.remove wire:target="downloadReciboDetallado({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-success p-0 me-2"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getNotaCreditoHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';
    $html = '<div class="d-flex align-items-center flex-nowrap">'; // 👈 evita saltos de línea


    // PDF sencilla y detallada
    if ($user->can('download-pdf-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Sencilla"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Detallada"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-proformas')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-success p-0 me-2"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }


  public function getToogleRetencionColumn(): string
  {
    $iconSize = 'bx-md';
    $id = $this->id;
    $isRetencion = $this->is_retencion;

    // Icono dinámico según estado
    $iconClass = $isRetencion
      ? "bx bx-check-circle text-success $iconSize"
      : "bx bx-x-circle text-danger $iconSize";

    $html = '';

    $html .= <<<HTML
        <button type="button"
            class="btn p-0 me-2"
            title="Retención"
            wire:click="toggleRetencion($id)"
            wire:loading.attr="disabled"
            wire:target="toggleRetencion">
            <i class="bx bx-loader bx-spin $iconSize" wire:loading wire:target="toggleRetencion($id)"></i>
            <i class="$iconClass" wire:loading.remove wire:target="toggleRetencion($id)"></i>
        </button>
    HTML;


    return $html;
  }

  public function getFechaDepositoHtmlColumn(): string
  {
    $formatted = $this->fecha_deposito_pago
      ? \Carbon\Carbon::parse($this->fecha_deposito_pago)->format('d-m-Y')
      : 'Asignar fecha';

    $id = $this->id;

    return <<<HTML
        <span class="d-inline-flex align-items-center">
            <a href="#"
                class="text-primary text-decoration-underline me-1"
                wire:click.prevent="openFechaDepositoModal($id)"
                wire:loading.attr="disabled"
                wire:target="openFechaDepositoModal($id)">
                $formatted
            </a>
            <span wire:loading wire:target="openFechaDepositoModal($id)">
                <i class="spinner-border spinner-border-sm text-primary" role="status"></i>
            </span>
        </span>
    HTML;
  }

  public function getNumeroDepositoHtmlColumn(): string
  {
    $numero = $this->numero_deposito_pago ?: 'Asignar número';
    $id = $this->id;

    return <<<HTML
        <span class="d-inline-flex align-items-center">
            <a href="#"
                class="text-primary text-decoration-underline me-1"
                wire:click.prevent="openNumeroDepositoModal($id)"
                wire:loading.attr="disabled"
                wire:target="openNumeroDepositoModal($id)">
                $numero
            </a>
            <span wire:loading wire:target="openNumeroDepositoModal($id)">
                <i class="spinner-border spinner-border-sm text-primary" role="status"></i>
            </span>
        </span>
    HTML;
  }

  public function getFechaTrasladoHonorarioHtmlColumn(): string
  {
    $formatted = $this->fecha_traslado_honorario
      ? \Carbon\Carbon::parse($this->fecha_traslado_honorario)->format('d-m-Y')
      : 'Asignar fecha';

    $id = $this->id;

    return <<<HTML
        <span class="d-inline-flex align-items-center">
            <a href="#"
                class="text-primary text-decoration-underline me-1"
                wire:click.prevent="openFechaTrasladoHonoarioModal($id)"
                wire:loading.attr="disabled"
                wire:target="openFechaTrasladoHonoarioModal($id)">
                $formatted
            </a>
            <span wire:loading wire:target="openFechaTrasladoHonoarioModal($id)">
                <i class="spinner-border spinner-border-sm text-primary" role="status"></i>
            </span>
        </span>
    HTML;
  }

  public function getNumeroTrasladoHonorarioHtmlColumn(): string
  {
    $numero = $this->numero_traslado_honorario ?: 'Asignar número';
    $id = $this->id;

    return <<<HTML
        <span class="d-inline-flex align-items-center">
            <a href="#"
                class="text-primary text-decoration-underline me-1"
                wire:click.prevent="openNumeroTrasladoHonoarioModal($id)"
                wire:loading.attr="disabled"
                wire:target="openNumeroTrasladoHonoarioModal($id)">
                $numero
            </a>
            <span wire:loading wire:target="openNumeroTrasladoHonoarioModal($id)">
                <i class="spinner-border spinner-border-sm text-primary" role="status"></i>
            </span>
        </span>
    HTML;
  }

  public function getFechaTrasladoGastoHtmlColumn(): string
  {
    $formatted = $this->fecha_traslado_gasto
      ? \Carbon\Carbon::parse($this->fecha_traslado_gasto)->format('d-m-Y')
      : 'Asignar fecha';

    $id = $this->id;

    return <<<HTML
        <span class="d-inline-flex align-items-center">
            <a href="#"
                class="text-primary text-decoration-underline me-1"
                wire:click.prevent="openFechaTrasladoGastoModal($id)"
                wire:loading.attr="disabled"
                wire:target="openFechaTrasladoGastoModal($id)">
                $formatted
            </a>
            <span wire:loading wire:target="openFechaTrasladoGastoModal($id)">
                <i class="spinner-border spinner-border-sm text-primary" role="status"></i>
            </span>
        </span>
    HTML;
  }

  public function getNumeroTrasladoGastoHtmlColumn(): string
  {
    $numero = $this->numero_traslado_gasto ?: 'Asignar número';
    $id = $this->id;

    return <<<HTML
        <span class="d-inline-flex align-items-center">
            <a href="#"
                class="text-primary text-decoration-underline me-1"
                wire:click.prevent="openNumeroTrasladoGastoModal($id)"
                wire:loading.attr="disabled"
                wire:target="openNumeroTrasladoGastoModal($id)">
                $numero
            </a>
            <span wire:loading wire:target="openNumeroTrasladoGastoModal($id)">
                <i class="spinner-border spinner-border-sm text-primary" role="status"></i>
            </span>
        </span>
    HTML;
  }

  public function getCotizacionHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';
    $html = '<div class="d-flex align-items-center flex-nowrap">'; // 👈 evita saltos de línea

    // PDF sencilla y detallada
    if ($user->can('download-pdf-cotizaciones')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Sencilla"
                wire:click="downloadProformaSencilla({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaSencilla">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaSencilla({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaSencilla({$this->id})"></i>
            </button>

            <button type="button"
                class="btn btn-link p-0 me-2"
                title="Proforma Detallada"
                wire:click="downloadProformaDetallada({$this->id})"
                wire:loading.attr="disabled"
                wire:target="downloadProformaDetallada">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="downloadProformaDetallada({$this->id})"></i>
                <i class="bx bxs-file-pdf {$iconSize}" wire:loading.remove wire:target="downloadProformaDetallada({$this->id})"></i>
            </button>
        HTML;
    }

    // Enviar Email
    if ($user->can('send-email-cotizaciones')) {
      $html .= <<<HTML
            <button type="button"
                class="btn btn-link text-success p-0 me-2"
                title="Enviar Email"
                wire:click="openEmailModal({$this->id})"
                wire:loading.attr="disabled"
                wire:target="openEmailModal">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="openEmailModal({$this->id})"></i>
                <i class="bx bx-envelope {$iconSize}" wire:loading.remove wire:target="openEmailModal({$this->id})"></i>
            </button>
        HTML;
    }

    // Facturar
    if ($user->can('convertir-proforma-cotizaciones') && in_array($this->proforma_status, [self::PROCESO])) {
      $html .= <<<HTML
          <button type="button"
              class="btn btn-link text-dark p-0 me-2"
              title="Convertir a proforma"
              wire:click="asociarCasoBeforeConvertToProforma('{$this->id}')"
              wire:loading.attr="disabled"
              wire:target="asociarCasoBeforeConvertToProforma('{$this->id}')">
              <i class="bx bx-loader bx-spin {$iconSize}"
                  wire:loading
                  wire:target="asociarCasoBeforeConvertToProforma('{$this->id}')"></i>
              <i class="bx bx-barcode {$iconSize}"
                  wire:loading.remove
                  wire:target="asociarCasoBeforeConvertToProforma('{$this->id}')"></i>
          </button>
      HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getSaldoPendiente($transactionId)
  {
    $monto = 0;
    $abonos = 0;
    $saldo = 0;
    $monto = $this->totalComprobante;
    $abonos = TransactionPayment::where('transaction_id', $transactionId)->sum('total_medio_pago');
    if (is_null($abonos) || empty($abonos))
      $abonos = 0;

    $saldo = $monto - $abonos;
    return $saldo;
  }

  public function completePayment($transactionId)
  {
    $monto = $this->totalComprobante;
    $abonos = TransactionPayment::where('transaction_id', $transactionId)->sum('total_medio_pago');
    if (is_null($abonos) || empty($abonos))
      $abonos = 0;

    $saldo = $monto - $abonos;
    if ($saldo > 0) {
      // Completar el pago
      $payment = new TransactionPayment();
      $payment->transaction_id = $transactionId;
      $payment->tipo_medio_pago = 99;
      $payment->medio_pago_otros = 'Págo automático por pago del registro';
      $payment->total_medio_pago = $saldo;
      $payment->save();
    }
  }

  public function getHtmlVencimiento()
  {
    $mostrar = true;
    if ($this->payment_status == Transaction::PAGADO || $this->payment_status == Transaction::ANULADO)
      $mostrar = false;
    else
		if ($this->dias_vencidos == 0) {
      $color = 'bg-success';
      $dias = 0;
      $texto = 'Para Vencer';
    } else
		if ($this->dias_vencidos > 0) {
      $color = 'bg-danger';
      $dias = abs($this->dias_vencidos);
      $texto = 'Vencida';
    } else
		if (abs($this->dias_vencidos) <= 7) {
      $color = 'bg-warning text-dark';
      $dias = abs($this->dias_vencidos);
      $texto = 'Para Vencer';
    } else {
      $color = 'bg-success';
      $dias = abs($this->dias_vencidos);
      $texto = 'Para Vencer';
    }

    if ($mostrar)
      return "<span class=\"badge " . $color . " me-2\">" . $dias . "</span>" . $texto . "";
    else
      return '';
  }

  public function getPaymentStatusHtmlColumn()
  {
    $htmlData = '-';
    switch ($this->payment_status) {
      case 'annulled':
        $htmlData = "<span class=\"badge bg-secondary\">" . __('ANULADA') . "</span>";
        break;
      case 'partial':
        $htmlData = "<span class=\"badge bg-warning\">" . __('PARCIAL') . "</span>";
        break;
      case 'paid':
        $htmlData = "<span class=\"badge bg-success\">" . __('PAGADO') . "</span>";
        break;
      case 'due':
        $htmlData = "<span class=\"badge bg-danger\">" . __('PENDIENTE') . "</span>";
        break;
    }

    return $htmlData;
  }

  public static function verifyResponseStatusHacienda($responseData, $documentType)
  {
    $transaction = Transaction::where('key', trim($responseData['clave']))->first();
    // Consulta el estado del comprobante
    if (!$transaction) {
      Log::info('No se ha encontrado el comprobante con key en verifyResponseStatusHacienda:', $responseData);
      return false;
    }

    $api = new ApiHacienda();
    //Log::info('Se llama al handleResponse de la api:', $responseData);
    $result = $api->handleResponse($responseData, $transaction, $documentType);

    //Log::info('el resultado de handleResponse de la api:', $result);

    if ($responseData['ind-estado'] == 'aceptado') {
      // Nota de crédito o nota de debito
      if (in_array($transaction->document_type, ['NCE'])) {

        Log::error("Es una nota de crédito electrónica");
        $referencia = Transaction::where('key', trim($transaction->RefNumero))->first();

        if ($referencia) {
          $referencia->status = Transaction::ANULADA;

          if ($referencia->save()) {
            Log::error("Se guardó el estado en transacción ID: {$referencia->id}");
          } else {
            Log::error("Error al guardar estado en transacción ID: {$referencia->id}", [
              'datos' => $referencia->toArray(), // Registra todos los datos
              'errores' => $referencia->getErrors() // Si usas validación
            ]);
          }
        } else {
          Log::warning("No se encontró transacción de referencia", [
            'key_buscada' => trim($transaction->RefNumero),
            'nota_id' => $transaction->id
          ]);
        }
      }
      $sent = Helpers::sendComprobanteElectronicoEmail($transaction->id);

      if ($sent) {
        $transaction->fecha_envio_email = now();
        $transaction->save();

        $menssage = __('An email has been sent to the following addresses:') . ' ' . $transaction->contact->email;
        if (!empty($transaction->email_cc)) {
          $menssage .= ' ' . __('with copy to') . ' ' . $transaction->email_cc;
        }
      }
    } elseif ($responseData['ind-estado'] == 'rechazado') {
      $sent = Helpers::sendNotificationComprobanteElectronicoRejected($transaction->id, $documentType);
      // Opcional: Log de la respuesta para auditoría
      if ($sent)
        Log::info('Se ha enviar una notificación de comprobante rechazado:', $responseData);
      else
        Log::info('No se ha podido enviar una notificación de comprobante rechazado:', $responseData);
    }
  }

  public function getHtmlDocumentType()
  {
    $name = '-';
    switch ($this->document_type) {
      case 'PR':
        $name = 'PROFORMA';
        break;
      case 'CT':
        $name = 'COTIZACION';
        break;
      case 'FE':
        $name = 'FACTURA';
        break;
      case 'TE':
        $name = 'TIQUETE';
        break;
      case 'NDE':
        $name = 'NOTA DE DÉBITO';
        break;
      case 'NCE':
        $name = 'NOTA DE CRÉDITO';
        break;
      case 'PRC':
        $name = 'PROFORMA DE COMPRA';
        break;
      case 'FEC':
        $name = 'FACTURA COMPRA';
        break;
      case 'FEE':
        $name = 'FACTURA EXPORTACIÓN';
        break;
      case 'REP':
        $name = 'RECIBO DE PAGO';
        break;
      case 'REP':
        $name = 'RECIBO DE PAGO';
        break;
    }
    $htmlData = "<span class=\"badge bg-primary\">$name</span>";
    return $htmlData;
  }
}
