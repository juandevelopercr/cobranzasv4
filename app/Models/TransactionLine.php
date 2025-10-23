<?php

namespace App\Models;

use App\Models\Caso;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\HonorarioReceta;
use App\Models\TransactionLineTax;
use Illuminate\Support\Facades\DB;
use App\Models\ProductHonorariosTimbre;
use App\Models\TransactionLineDiscount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Hacienda\ComprobanteElectronico\ImpuestoType\DatosImpuestoEspecificoAType;

class TransactionLine extends Model
{
  use HasFactory;

  protected $table = 'transactions_lines';

  public $monto_escritura_colones = 0;
  public $monto_escritura_colones_grid = 0;

  protected $fillable = [
    'transaction_id',
    'product_id',
    'caso_id',
    'codigo',
    'codigocabys',
    'detail',
    'quantity',
    'price',
    'fecha_reporte_gasto',
    'fecha_pago_registro',
    'numero_pago_registro',

    'honorarios',
    'timbres',
    'discount',
    'subtotal',
    'baseImponible',
    'tax',
    'impuestoAsumidoEmisorFabrica',
    'impuestoNeto',
    'total',
    'exoneration',

    'servGravados',
    'servExentos',
    'servExonerados',
    'servNoSujeto',

    'mercGravadas',
    'mercExentas',
    'mercExoneradas',
    'mercNoSujeta',

    'desglose_timbre_formula',
    'desglose_tabla_abogados',
    'desglose_calculos_fijos',
    'desglose_calculo_monto_timbre_manual',
    'desglose_honorarios',
    'desglose_calculo_monto_honorario_manual',
    'registro_currency_id',
    'registro_change_type',
    'registro_monto_escritura',
    'registro_valor_fiscal',
    'registro_cantidad',
    'monto_cargo_adicional',
    'calculo_registro_normal',
    'calculo_registro_iva',
    'calculo_registro_no_iva',

    'impuestoServGravados',
    'impuestoMercGravadas',
    'impuestoServExonerados',
    'impuestoMercExoneradas',

    'partida_arancelaria',
    'impuestosEspeciales',
    'hasRegaliaOrBonificacion',
    'hasImpuestoEspecifico',
    'porcientoDescuento'
  ];

  protected $casts = [
    'desglose_timbre_formula' => 'array',
    'desglose_tabla_abogados' => 'array',
    'desglose_calculos_fijos' => 'array',
    'desglose_calculo_monto_timbre_manual' => 'array',
    'desglose_honorarios' => 'array',
    'desglose_calculo_monto_honorario_manual' => 'array',
  ];

  // Relaciones
  public function transaction()
  {
    return $this->belongsTo(Transaction::class, 'transaction_id');
  }

  // Productos
  public function product()
  {
    return $this->belongsTo(Product::class, 'product_id');
  }

  // Relación con TransactionLineTax (uno a muchos)
  public function taxes()
  {
    return $this->hasMany(TransactionLineTax::class, 'transaction_line_id');
  }

  // Relación con descuentos
  public function discounts()
  {
    return $this->hasMany(TransactionLineDiscount::class, 'transaction_line_id');
  }

  public function caso()
  {
    return $this->belongsTo(Caso::class);
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'transactions_lines.id',
      'transactions_lines.transaction_id',
      'transactions_lines.product_id',
      'transactions_lines.codigo',
       DB::raw("CONCAT_WS(' / ',
          NULLIF(casos.pnumero, ''),
          NULLIF(casos.pnumero_operacion1, ''),
          NULLIF(casos.pnombre_demandado, ''),
          NULLIF(casos.pnombre_apellidos_deudor, '')
      ) as caso_info"),
      'transactions_lines.codigocabys',
      'transactions_lines.detail',
      'transactions_lines.quantity',
      'transactions_lines.price',
      'transactions_lines.discount',
      'transactions_lines.tax',
      'transactions_lines.fecha_reporte_gasto',
      'transactions_lines.fecha_pago_registro',
      'transactions_lines.numero_pago_registro',
      'transactions_lines.honorarios',
      'transactions_lines.timbres',
      'transactions_lines.desglose_timbre_formula',
      'transactions_lines.desglose_tabla_abogados',
      'transactions_lines.desglose_calculos_fijos',
      'transactions_lines.desglose_calculo_monto_timbre_manual',
      'transactions_lines.desglose_honorarios',
      'transactions_lines.desglose_calculo_monto_honorario_manual',
      'transactions_lines.registro_currency_id',
      'transactions_lines.registro_change_type',
      'transactions_lines.registro_monto_escritura',
      'transactions_lines.registro_valor_fiscal',
      'transactions_lines.registro_cantidad',
      'transactions_lines.monto_cargo_adicional',
      'transactions_lines.calculo_registro_normal',
      'transactions_lines.calculo_registro_iva',
      'transactions_lines.calculo_registro_no_iva',
      'transactions_lines.exoneration',
      'transactions_lines.subtotal',
      'transactions_lines.total',
      'transactions_lines.servGravados',
      'transactions_lines.mercGravadas',
      'transactions_lines.impuestoServGravados',
      'transactions_lines.impuestoMercGravadas',
      'transactions_lines.impuestoServExonerados',
      'transactions_lines.impuestoMercExoneradas',
      'transactions_lines.impuestoNeto',
      'transactions_lines.servExentos',
      'transactions_lines.mercExentas',
      'transactions_lines.partida_arancelaria',

      'transactions_lines.baseImponible',
      'transactions_lines.impuestosEspeciales',
      'transactions_lines.impuestoAsumidoEmisorFabrica',
      'transactions_lines.hasRegaliaOrBonificacion',
      'transactions_lines.hasImpuestoEspecifico',
      'transactions_lines.servNoSujeto',
      'transactions_lines.mercNoSujeta',

      'transactions_lines.servExonerados',
      'transactions_lines.mercExoneradas',
      'transactions_lines.porcientoDescuento'
    ];

    $query->select($columns)
      ->join('products', 'transactions_lines.product_id', '=', 'products.id')
      ->leftJoin('casos', 'transactions_lines.caso_id', '=', 'casos.id')
      ->where(function ($q) use ($value) {
        $q->where('codigocabys', 'like', "%{$value}%")
          ->orWhere('detail', 'like', "%{$value}%")
          ->orWhere('transactions_lines.price', 'like', "%{$value}%")
          ->orWhere('timbres', 'like', "%{$value}%")
          ->orWhere('honorarios', 'like', "%{$value}%")
          ->orWhere('discount', 'like', "%{$value}%")
          ->orWhere('tax', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_codigocabys'])) {
      $query->where('codigocabys', 'like', '%' . $filters['filter_codigocabys'] . '%');
    }

    if (!empty($filters['filter_detail'])) {
      $query->where('detail', 'like', '%' . $filters['filter_detail'] . '%');
    }

    if (!empty($filters['filter_numero_caso'])) {
        $searchTerm = '%' . $filters['filter_numero_caso'] . '%';

        $query->where(function ($q) use ($searchTerm) {
            $q->whereRaw("
                CONCAT_WS(' / ',
                    NULLIF(casos.pnumero, ''),
                    NULLIF(casos.pnumero_operacion1, ''),
                    NULLIF(casos.pnombre_demandado, ''),
                    NULLIF(casos.pnombre_apellidos_deudor, '')
                ) LIKE ?
            ", [$searchTerm]);
        });
    }

    if (!empty($filters['filter_price'])) {
      $query->where('transactions_lines.price', 'like', '%' . $filters['filter_price'] . '%');
    }

    if (!empty($filters['filter_quantity'])) {
      $query->where('quantity', 'like', '%' . $filters['filter_quantity'] . '%');
    }

    if (!empty($filters['filter_timbres'])) {
      $query->where('timbres', 'like', '%' . $filters['filter_timbres'] . '%');
    }

    if (!empty($filters['filter_honorarios'])) {
      $query->where('honorarios', 'like', '%' . $filters['filter_honorarios'] . '%');
    }

    if (!empty($filters['filter_discount'])) {
      $query->where('discount', 'like', '%' . $filters['filter_discount'] . '%');
    }

    if (!empty($filters['filter_monto_cargo_adicional'])) {
      $query->where('monto_cargo_adicional', 'like', '%' . $filters['filter_monto_cargo_adicional'] . '%');
    }

    if (!empty($filters['filter_subtotal'])) {
      $query->where('subtotal', 'like', '%' . $filters['filter_subtotal'] . '%');
    }

    if (!empty($filters['filter_tax'])) {
      $query->where('tax', 'like', '%' . $filters['filter_tax'] . '%');
    }

    if (!empty($filters['filter_exoneration'])) {
      $query->where('exoneration', 'like', '%' . $filters['filter_exoneration'] . '%');
    }

    if (!empty($filters['filter_total'])) {
      $query->where('total', 'like', '%' . $filters['filter_total'] . '%');
    }

    return $query;
  }

  public function updateTransactionTotals($currency)
  {
    //$currency = $this->transaction->currency_id;
    $changeType = in_array($this->transaction->document_type, [Transaction::PROFORMA, Transaction::NOTACREDITO, Transaction::NOTADEBITO])
      ? $this->transaction->proforma_change_type
      : $this->transaction->factura_change_type;
    $bank_id = $this->transaction->bank_id;
    $discounts = !is_null($this->discounts) ? $this->discounts : collect([]);
    //$taxes = !is_null($this->taxes) ? $this->taxes : collect([]);

    $tipo = 'HONORARIO';
    $this->honorarios = $this->getHonorarios($bank_id, $tipo, $currency, $changeType, $this->porcientoDescuento) ?? 0;

    $tipo = 'GASTO';
    $this->timbres = $this->getTimbres($bank_id, $tipo, $currency, $changeType, $this->porcientoDescuento) ?? 0;

    $this->discount = $this->getDescuento() ?? 0;
    $this->subtotal = $this->getSubtotal() ?? 0;

    $this->impuestosEspeciales = $this->getImpuestosEspeciales();

    $this->baseImponible = $this->getBaseImponible();

    $this->hasRegaliaOrBonificacion = $this->getHasRegaliaOrBonificacion();
    $this->hasImpuestoEspecifico = $this->getHasImpuestoEspecifico();

    $this->tax = $this->getImpuesto() ?? 0;

    $this->impuestoAsumidoEmisorFabrica = $this->transaction->document_type == 'FEC' ? 0 : $this->getImpuestoAsumidoEmisorFabrica();

    // Servicios
    $this->servGravados = $this->getServGravado() ?? 0;

    $this->servExentos = $this->getServExento() ?? 0;

    $this->servExonerados = $this->getServExonerado() ?? 0;
    //$this->getImpuestoServExonerado() ?? 0;  // para borrarlo

    $this->servNoSujeto = $this->getServNoSujeto() ?? 0;

    // Mercancias
    $this->mercGravadas = $this->getMercanciaGravada() ?? 0;

    $this->mercExentas = $this->getMercanciaExenta() ?? 0;

    $this->mercExoneradas = $this->getMercExonerada() ?? 0;

    $this->mercNoSujeta = $this->getMercNoSujeta() ?? 0;

    $this->exoneration = $this->servExonerados + $this->mercExoneradas;

    $this->impuestoNeto = $this->getMontoImpuestoNeto() ?? 0;

    $this->total = $this->getMontoTotalLinea() ?? 0;

    $this->save();
  }

  protected function getMontoColones($currency, $amount, $changeType)
  {
    $result = $currency != Currency::COLONES ? $amount * $changeType : $amount;
    return $result;
  }

  //*******************************************************************//
  //********************Inicio Calculo de Honorarios********************//
  //*******************************************************************//
  public function getPrecioHonorario(){
    return round($this->honorarios - ($this->discount ?? 0), 2);
  }

  public function getHonorarios($bank_id, $tipo, $currency, $changeType, $discountPercent)
  {
    $honorario = 0;

    //if ($this->transaction->proforma_type == 'HONORARIO') {
    $monto_honorarios = $this->desgloseHonorarios($bank_id, $tipo, $currency, $changeType, $discountPercent);
    $this->desglose_honorarios = $monto_honorarios;

    // Monto Manual
    $monto_manual = $this->desgloseCalculaMontoManual($bank_id, $tipo, $currency, $changeType, $discountPercent);
    $this->desglose_calculo_monto_honorario_manual = $monto_manual;

    $honorario = $monto_honorarios['monto_sin_descuento'] + $monto_manual['monto_sin_descuento'];

    $honorario = $honorario + $this->monto_cargo_adicional;

    return round($honorario, 2);
  }

  public function desgloseHonorarios($bank_id, $tipo, $currency, $changeType, $discountPercent)
  {
    $honorarios = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
      ->where('products_banks.bank_id', $bank_id)
      ->where('product_honorarios_timbres.product_id', $this->product_id)
      ->where(function ($query) use ($tipo) {
        $query->where([
          'product_honorarios_timbres.tipo' => $tipo,
          'product_honorarios_timbres.fijo' => 1
        ])
          ->orWhere(function ($query) use ($tipo) {
            $query->where([
              'product_honorarios_timbres.tipo' => $tipo,
              'product_honorarios_timbres.fijo' => 0,
              'product_honorarios_timbres.porciento' => 1
            ]);
          })
          ->orWhere(function ($query) use ($tipo) {
            $query->where([
              'product_honorarios_timbres.tipo' => $tipo,
              'product_honorarios_timbres.fijo' => 0,
              'product_honorarios_timbres.monto_manual' => 0
            ]);
          });
      })
      ->select('product_honorarios_timbres.*')
      ->get();

    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $datos = array();
    // Los calculos siempre se hacen en colones
    $precio = $this->getMontoColones($currency, $this->getPrice(), $changeType);

    foreach ($honorarios as $honorario) {
      if ($honorario->fijo == 1 && $honorario->base >= 0) {
        $monto  = $honorario->base;
      } else
				if ($honorario->fijo == 0 && is_null($honorario->honorario_id) && $honorario->porciento == 1 && $honorario->base > 0) {
        $monto = ($precio * $honorario->base) / 100;
      } else
				if ($honorario->fijo == 0 && !is_null($honorario->honorario_id) && $honorario->honorario_id > 0 && $honorario->monto_manual == 0) {
        $monto = $this->desgloseCalculaHonorarioConTablaHonorarioBanco($honorario, $bank_id, $currency, $changeType);
      }

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $this->quantity;
      $summonto_sin_descuento += round($monto, 2);

      if (!empty($discountPercent) && $discountPercent > 0) {
        $descuento = $this->calculaMontoDescuentos($monto, $discountPercent);

        $monto_con_descuento = $monto - ($descuento ?? 0);
        $summonto_con_descuento += round($monto_con_descuento, 2);
      } else {
        $summonto_con_descuento += round($monto, 2);
        $monto_con_descuento = round($monto, 2);
      }

      $datos[] = ['titulo' => $honorario->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }

    if (empty($honorarios))
      $datos[] = ['titulo' => '', 'monto_sin_descuento' => round(0, 2), 'monto_con_descuento' => round(0, 2)];

    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'datos' => $datos,
    ];
  }

  function desgloseCalculaHonorarioConTablaHonorarioBanco($honorario, $bank_id, $currency, $changeType)
  {
    $monto = 0;
    $honorarios_bancos = HonorarioReceta::join('honorarios_banks', 'honorarios_banks.honorario_id', '=', 'honorarios_recetas.honorario_id')
      ->where('honorarios_banks.bank_id', $bank_id)
      ->where('honorarios_recetas.honorario_id', $honorario->honorario_id)
      ->orderBy('honorarios_recetas.orden', 'asc')
      ->select('honorarios_recetas.*')
      ->get();

    $precio = $this->getMontoColones($currency, $this->getPrice(), $changeType);
    $i = 1;
    foreach ($honorarios_bancos as $honorarios_banco) {
      if ($i == 1)
        $formula = ($precio < $honorarios_banco->hasta) ? $precio : $honorarios_banco->hasta;
      else
			if ($i < count($honorarios_bancos))
        $formula = ($precio < $honorarios_banco->desde) ? $honorarios_banco->desde : (($precio < $honorarios_banco->hasta && $precio >= $honorarios_banco->desde) ? $precio : $honorarios_banco->hasta);
      else
        $formula = ($precio > $honorarios_banco->desde) ? $precio : $honorarios_banco->desde;

      $formula = round($formula, 2);
      $tracto_para_calculo = $formula - $honorarios_banco->desde;
      $tracto_para_calculo = round($tracto_para_calculo, 2);
      $monto_a_cobrar = $tracto_para_calculo * $honorarios_banco->porcentaje / 100;
      $monto += round($monto_a_cobrar, 2);
      $i++;
    }
    // Se retorna el monto en colones
    return round($monto, 2);
  }
  //*******************************************************************//
  //**********************Fin Calculo de Honoarios*********************//
  //*******************************************************************//


  //*******************************************************************//
  //********************Inicio Calculo de Timbres**********************//
  //*******************************************************************//
  public function getTimbres($bank_id, $tipo, $currency, $changeType, $discountPercent)
  {
    $timbre = 0;

    $monto_formula = $this->desgloseTimbreFormula($bank_id, $tipo, $currency, $changeType, $discountPercent);
    $this->desglose_timbre_formula = $monto_formula;

    // Tabla Timbre Abogados
    $monto_grada = $this->desgloseTablaAbogados($bank_id, $tipo, $currency, $changeType, $discountPercent);
    $this->desglose_tabla_abogados = $monto_grada;

    // Fijo
    $monto_fijo = $this->desgloseCalculosFijos($bank_id, $tipo, $currency, $changeType, $discountPercent);
    $this->desglose_calculos_fijos = $monto_fijo;

    // Monto Manual
    $monto_manual = $this->desgloseCalculaMontoManual($bank_id, $tipo, $currency, $changeType, $discountPercent);
    $this->desglose_calculo_monto_timbre_manual = $monto_manual;

    //$timbre = $monto_formula + $monto_grada + $monto_fijo + $monto_manual;
    $timbre = $monto_formula['monto_sin_descuento'] + $monto_grada['monto_sin_descuento'] + $monto_fijo['monto_sin_descuento'] + $monto_manual['monto_sin_descuento'];

    $timbre = $timbre + $this->monto_cargo_adicional;

    return $timbre;
  }

  public function desgloseTimbreFormula($bank_id, $tipo, $currency, $changeType, $discountPercent)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();
    $changeType = $changeType > 0 ? $changeType : 1;
    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo

    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honoarios no hay calculo de formula
      // Consulta cuando el tipo no es 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 0,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      // Aqui antes no se filtraba por banco pero ahora en el form lo hize obligarotio
      // Consulta cuando el tipo es 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 0,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    // Los calculos siempre se hacen en colones
    $precio = $this->getMontoColones($currency, $this->getPrice(), $changeType);

    foreach ($honorario_timbres as $dato) {
      if ($dato->porciento == 1) {
        $monto = ($precio * $dato->base) / 100;
      } else
				if ($dato->porcada > 0)
        $monto = ($precio / $dato->porcada) * $dato->base;

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $this->quantity;
      $summonto_sin_descuento += round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      if (!empty($discountPercent) && $discountPercent > 0) {
        $descuento = $this->calculaMontoDescuentos($monto, $discountPercent);

        $monto_con_descuento = $monto - ($descuento ?? 0);
        $summonto_con_descuento += round($monto_con_descuento, 2);
      } else {
        $summonto_con_descuento += round($monto, 2);
        $monto_con_descuento = round($monto, 2);
      }

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseTablaAbogados($bank_id, $tipo, $currency, $changeType, $discountPercent)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();

    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honoarios no hay calculo de formula
      // Cuando el tipo no es 'GASTO'
      $tabla_abogado = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where(function ($query) {
          $query->where('product_honorarios_timbres.tabla_abogado_inscripciones', 1)
            ->orWhere('product_honorarios_timbres.tabla_abogado_traspasos', 1);
        })
        ->select('product_honorarios_timbres.*')
        ->first();
    } else {
      //aqui antes no se filtraba por banco pero en el form lo hice obligatorio
      // Cuando el tipo es 'GASTO'
      $tabla_abogado = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where(function ($query) {
          $query->where('product_honorarios_timbres.tabla_abogado_inscripciones', 1)
            ->orWhere('product_honorarios_timbres.tabla_abogado_traspasos', 1);
        })
        ->select('product_honorarios_timbres.*')
        ->first();
    }

    if (!is_null($tabla_abogado)) {
      if ($tabla_abogado->tabla_abogado_inscripciones == 1)
        $tipo_tabla = 1; // para tomar los datos de las inscripciones
      else
        $tipo_tabla = 2; // para tomar los datos de los traspasos de vehículos

      $honorario_timbres = Timbre::where('tipo', $tipo_tabla)
        ->orderBy('orden', 'asc')
        ->get();

      $index = 0;
      $founded = false;
      // Los calculos siempre se hacen en colones
      $precio = $this->getMontoColones($currency, $this->getPrice(), $changeType);

      if (!empty($honorario_timbres)) {
        while ($founded == false && $index <= count($honorario_timbres)) {
          if ($precio <= $honorario_timbres[0]->base) {
            $monto = 0;
            $founded = true;
          } else
						if ($index + 1 < count($honorario_timbres)) {
            if ($precio >= $honorario_timbres[$index]->base && $precio <= $honorario_timbres[$index + 1]->base) {
              $monto = $honorario_timbres[$index + 1]->porcada;
              $founded = true;
            } else
							if ($precio >= $honorario_timbres[$index]->base && $honorario_timbres[$index + 1]->base == 0) {
              $monto = $honorario_timbres[$index + 1]->porcada;
              $founded = true;
            }
          }
          $index++;
        }
      }

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $this->quantity;
      $monto_con_descuento = round($monto, 2);
      $summonto_sin_descuento = round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $tabla_abogado->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      if (!empty($discountPercent) && $discountPercent > 0) {
        $descuento = $this->calculaMontoDescuentos($monto, $discountPercent);

        $monto_con_descuento = $monto - ($descuento ?? 0);
        $summonto_con_descuento    = round($monto_con_descuento);
      } else {
        $summonto_con_descuento = round($monto, 2);
        $monto_con_descuento = round($monto, 2);
      }

      $datos[] = ['titulo' => $tabla_abogado->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }

    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseCalculosFijos($bank_id, $tipo, $currency, $changeType, $discountPercent)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();
    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo
    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honorarios no hay calculo de formula
      // Para tipos diferentes de 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      //aqui antes no se filtraba por banco pero en el form lo hice requerido
      // Para 'GASTO' (sin importar banco)
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    foreach ($honorario_timbres as $dato) {
      $monto = $dato->base;
      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;
      $monto = $monto * $this->quantity;
      $summonto_sin_descuento += round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      if (!empty($discountPercent) && $discountPercent > 0) {
        $descuento = $this->calculaMontoDescuentos($monto, $discountPercent);

        $monto_con_descuento = $monto - ($descuento ?? 0);
        $summonto_con_descuento += round($monto_con_descuento, 2);
      } else {
        $summonto_con_descuento += round($monto, 2);
        $monto_con_descuento = round($monto, 2);
      }

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseCalculaMontoManual($bank_id, $tipo, $currency, $changeType, $discountPercent)
  {
    $monto = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sum_item_con_descuento_seis_porciento = 0;
    $sum_item_sin_descuento_seis_porciento = 0;
    $datos = array();

    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo
    if ($tipo != 'GASTO') {
      // Query cuando $tipo no es 'GASTO' y considera el banco
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      // Aqui no se filtraba por banco pero en el formulario se puse requerido
      // Query cuando $tipo es 'GASTO' (no considera el banco)
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    // Los calculos siempre se hacen en colones
    $precio = $this->getMontoColones($currency, $this->getPrice(), $changeType);

    foreach ($honorario_timbres as $dato) {
      $monto = $precio;

      $monto = $currency != Currency::COLONES ? ($monto / $changeType) : $monto;

      $monto = $monto * $this->quantity;
      $summonto_sin_descuento += round($monto, 2);

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $sum_item_con_descuento_seis_porciento += round($monto, 2);
      } else
        $sum_item_sin_descuento_seis_porciento += round($monto, 2);

      if (!empty($discountPercent) && $discountPercent > 0) {
        $descuento = $this->calculaMontoDescuentos($monto, $discountPercent);

        $monto_con_descuento = $monto - ($descuento ?? 0);
        $summonto_con_descuento += round($monto_con_descuento, 2);
      } else {
        $summonto_con_descuento += round($monto, 2);
        $monto_con_descuento = round($monto, 2);
      }

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => round($monto, 2), 'monto_con_descuento' => round($monto_con_descuento, 2)];
    }

    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 2),
      'monto_con_descuento' => round($summonto_con_descuento, 2),
      'sum_item_con_descuento_seis_porciento' => round($sum_item_con_descuento_seis_porciento, 2),
      'sum_item_sin_descuento_seis_porciento' => round($sum_item_sin_descuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function getPrice()
  {
    $price = $this->price;
    //if ($this->porcientoDescuento > 0)
    //$price = $this->price * $this->porcientoDescuento / 100;

    return $price;
  }

  public function getMonto()
  {
    $amount = $this->getPrice() * $this->quantity;
    return number_format($amount, 5, '.', '');
  }

  // Es para forma el xml de FE
  public function getMontoTotal()
  {
    $amount = $this->honorarios - ($this->discount ?? 0);
    return number_format($amount, 5, '.', '');
  }

  public function getDescuento()
  {
    //$discounts = !is_null($this->discounts) ? $this->discounts : collect([]);
    $monto = $this->honorarios;
    $descuento = $this->calculaMontoDescuentos($monto, $this->porcientoDescuento);

    return number_format($descuento ?? 0, 5, '.', '');
  }

  public function getSubTotal()
  {
    $subtotal = $this->honorarios + $this->timbres - $this->discount;
    return number_format($subtotal, 5, '.', '');
  }

  // Se puede incluir un máximo de 5 repeticiones de descuentos, cada descuento adicional
  //se calcula sobre la base menos el descuento anterior.
  public function calculaMontoDescuentos($monto, $discountPercent)
  {
    $total_discount = 0;
    /*
    foreach ($discounts as $discount) {
      // Aplica cada descuento sobre el monto restante
      $descuento_aplicado = $monto * ($discount->discount_percent / 100);

      $discount->discount_amount = $descuento_aplicado;
      $discount->save();

      $total_discount += $descuento_aplicado;

      // Resta el descuento del monto
      $monto -= $descuento_aplicado;
    }
    */
    if ($discountPercent > 0)
      $total_discount = round($monto * $discountPercent / 100, 2);

    return $total_discount;
  }


  public function getImpuesto()
  {
    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    $subtotal = $this->getSubTotal();
    $tax = $this->calculaMontoImpuestos($subtotal, $taxes);

    return number_format($tax ?? 0, 5, '.', '');
  }

  //Cada tax se calcula sobre la base menos el impuesto
  protected function calculaMontoImpuestos($subtotal, $taxes)
  {
    $total_tax = 0;

    // CACERES
    foreach ($taxes as $tax) {
      $tax->tax_amount = $this->calculaMontoImpuestoConReglasHacienda($tax);
      $tax->save();
      $total_tax += $tax->tax_amount;
      /*
      // Aplica cada impuesto sobre el monto restante
      $tax_aplicado = $subtotal * ($tax->tax / 100);

      // Se actualiza el monto del tax
      $tax->tax_amount = $tax_aplicado;
      $tax->save();

      $total_tax += $tax_aplicado;

      // Resta el impuesto del monto
      $subtotal -= $tax_aplicado;
      */
    }

    $total_tax = round($total_tax, 2);

    return $total_tax;
  }

  // Devuelve el monto de precio * cantidad si el servicio está gravado
  public function getServGravado()
  {
    // Obtiene el impuesto si es un servicio
    $gravado = 0;
    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    if ($this->product->type == 'service') {
      if ($this->calculaMontoImpuestoExonerado() > 0) {
        //(1-porcentaje de exoneración) por el monto de la venta
        //▪Porcentaje de exoneración: (Tarifa Exonerada /Tarifa IVA)
        //$gravado = (1 - $this->exoneration_percent / 100) * $this->getSubtotal();
        $gravado = $this->getMontoTotal() - $this->calculaMontoImpuestoExonerado();
      } else if (!empty($taxes)) {
        $gravado = $this->getMontoTotal();
      }
    }
    return number_format($gravado, 5, '.', '');
  }

  // Devuelve el monto de precio * cantidad si la mercancia está gravado
  public function getMercanciaGravada()
  {
    // Obtiene el impuesto si es un producto
    $gravado = 0;
    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    if ($this->product->type != 'service') {
      if ($this->calculaMontoImpuestoExonerado() > 0) {
        //(1-porcentaje de exoneración) por el monto de la venta
        //▪Porcentaje de exoneración: (Tarifa Exonerada /Tarifa IVA)
        //$gravado = (1 - $this->exoneration_percent / 100) * $this->getSubtotal();
        $gravado = $this->getMontoTotal() - $this->calculaMontoImpuestoExonerado();
      } else if (!empty($taxes)) {
        $gravado = $this->getMontoTotal();
      }
    }
    return number_format($gravado, 5, '.', '');
  }

  public function getServExonerado()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type == 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getMercExonerada()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type != 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  protected function calculaMontoImpuestoExonerado()
  {
    $monto_exonerado = 0;

    $taxes = !is_null($this->taxes) ? $this->taxes : collect([]);
    //$subtotal = $this->getSubTotal();

    foreach ($taxes as $tax) {
      /*
      // Aplica cada impuesto sobre el monto restante
      $tax_aplicado = $subtotal * ($tax->tax / 100);

      // Resta el descuento del monto
      $subtotal -= $tax_aplicado;
      */

      if (!is_null($tax->exoneration_type_id) && !is_null($tax->exoneration_institution_id) && $tax->exoneration_percent > 0) {
        $monto_exonerado += $tax->tax_amount * $tax->exoneration_percent / 100;
      }
    }

    //$monto_exonerado = round($monto_exonerado, 2);
    return $monto_exonerado;
  }

  public function getImpuestoServGravado()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type == 'service')
      $impuesto = $this->getImpuesto();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getImpuestoMercanciaGravada()
  {
    // Obtiene el impuesto si es un producto
    if ($this->product->type != 'service')
      $impuesto = $this->getImpuesto();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getImpuestoServExonerado()
  {
    // Obtiene el impuesto si es un servicio
    if ($this->product->type == 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getImpuestoMercanciaExonerado()
  {
    // Obtiene el impuesto si es un product
    if ($this->product->type != 'service')
      $impuesto = $this->calculaMontoImpuestoExonerado();
    else
      $impuesto = 0;
    return number_format($impuesto, 5, '.', '');
  }

  public function getMontoImpuestoNeto()
  {
    //Este monto se obtiene al restar el campo “Monto del Impuesto” menos “Monto del Impuesto Exonerado” o el
    //campo “Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica” cuando corresponda. ▪En caso de no contar con datos en los campos “Monto del
    //Impuesto Exonerado” o “Impuestos Asumidos por el Emisor o cobrado a Nivel de Fábrica” el monto será el mismo al del impuesto calculado
    $impuestoNeto = $this->tax - ($this->exoneration ? $this->exoneration : 0);

    if ($this->impuestoAsumidoEmisorFabrica > 0) {
      $impuestoNeto = $this->tax - $this->impuestoAsumidoEmisorFabrica;
    }

    return number_format($impuestoNeto, 5, '.', '');
  }

  public function getServExento()
  {
    $exento = 0;
    // Obtiene el monto exento si es un servicio
    if ($this->product->type == 'service') {
      $taxes = $this->taxes;
      foreach ($taxes as $tax) {
        if (in_array($tax->taxRate->code, ['01', '11'])) {
          return number_format($this->getMontoTotal(), 5, '.', '');
        }
      }
    }
    return number_format($exento, 5, '.', '');
  }

  public function getMercanciaExenta()
  {
    $exento = 0;
    // Obtiene el monto exento si es un servicio
    if ($this->product->type != 'service') {
      $taxes = $this->taxes;
      foreach ($taxes as $tax) {
        if (in_array($tax->taxRate->code, ['01', '11'])) {
          return number_format($this->getMontoTotal(), 5, '.', '');
        }
      }
    }
    return number_format($exento, 5, '.', '');
  }

  public function getMontoTotalLinea()
  {
    //Es la sumatoria de los campos “Subtotal” e “Impuesto Neto”
    $montoTotalLinea = $this->subtotal + $this->impuestoNeto;

    return number_format($montoTotalLinea, 5, '.', '');
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-lineas-proformas') && $this->transaction && $this->transaction->proforma_status == Transaction::PROCESO) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-primary"
                title="Editar"
                wire:click="edit({$this->id})"
                wire:loading.attr="disabled"
                wire:target="edit">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit"></i>
                <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit"></i>
            </button>
        HTML;
    }

    // Eliminar
    if ($user->can('delete-lineas-proformas') && $this->transaction && $this->transaction->proforma_status == Transaction::PROCESO) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-danger"
                title="Eliminar"
                wire:click.prevent="confirmarAccion({$this->id}, 'delete',
                    '¿Está seguro que desea eliminar este registro?',
                    'Después de confirmar, el registro será eliminado',
                    'Sí, proceder')">
                <i class="bx bx-trash {$iconSize}"></i>
            </button>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }

  public function getMontoUnitarioFactura($moneda, $banco_id, $tipo_cambio, $estado_id)
  {
    $monto = $this->timbres;
    $monto = $monto / $this->quantity;
    return $monto;
  }

  public function getMontoOriginalValorEscritura($moneda, $tipo_cambio)
  {
    $monto = 0;
    if (!is_null($this->product)) {

      $quantity = $this->quantity;
      if (is_null($this->quantity) || $this->quantity == 0 || empty($this->quantity))
        $quantity = 1;

      $habilitar_eddi = empty($this->product->percent_eddi) ? 0.0 : (float) ($this->product->percent_eddi);
      if (!$this->product->enable_registration_calculation && !$this->product->enable_quantity && $habilitar_eddi <= 0) {

        // Tomar el valor del precio
        $monto = $this->getPrice() * $this->quantity;

        if ($this->registro_change_type > 0)
          $tipo_cambio = $this->registro_change_type;

        if ($moneda == 'DOLARES') {
          $monto = $monto * $tipo_cambio;
        }
        $monto = round($monto, 2);
      } else
			if (!$this->product->enable_registration_calculation && ($this->product->enable_quantity || $habilitar_eddi > 0)) {

        $this->registro_change_type = (float)$this->registro_change_type;

        $monto = $this->timbres / $quantity;
        $monto = round($monto, 2);

        if ($moneda == 'DOLARES') {
          if ($this->registro_change_type > 0)
            $monto = $monto * $this->registro_change_type;
          else
            $monto = $monto * $tipo_cambio;
        }
      } else {
        $monto = $this->registro_monto_escritura;
        if ($monto <= 0) {
          $monto = $this->getPrice() * $this->quantity;

          if ($this->registro_change_type > 0)
            $tipo_cambio = $this->registro_change_type;

          if ($moneda == 'DOLARES') {
            $monto = $monto * $tipo_cambio;
          }
        }
      }
    }
    return $monto;
  }

  public function getMontoEscrituraColones($currency_id, $tipo_cambio)
  {
    $this->monto_escritura_colones = 0;
    $this->registro_change_type = (float)$this->registro_change_type;
    $this->registro_valor_fiscal = (float)$this->registro_valor_fiscal;
    $this->registro_monto_escritura = (float)$this->registro_monto_escritura;
    $habilitar_eddi = empty($this->product->percent_eddi) ? 0.0 : (float) ($this->product->percent_eddi);

    $moneda = 'COLONES';
    if ($currency_id == 1)
      $moneda = 'DOLARES';

    $monto = round($this->getMontoOriginalValorEscritura($moneda, $tipo_cambio), 2);
    $monto = $monto * $this->registro_cantidad;
    $this->monto_escritura_colones_grid  = $monto;

    // Si está habilitado el calculo del registro y el campo Eddi entonces tomar el valor mayor entre el monto y el valor fiscal
    if ($this->product->enable_registration_calculation) {

      // SI entra aqui es porque no se ha definido el monto de la escritura por tanto hay que tomar el que se combró
      if ($this->registro_monto_escritura <= 0) {
      } else {
        if ($this->registro_currency_id == 1 && $this->registro_change_type > 0) {
          $monto = ($monto * $this->registro_change_type) * $this->registro_cantidad;
        }

        $this->monto_escritura_colones_grid  = $monto;
        if ($habilitar_eddi > 0 && $this->registro_valor_fiscal > $monto)
          $monto = $this->registro_valor_fiscal;
      }
    }

    $monto = round($monto, 2);
    $this->monto_escritura_colones = $monto;

    return $this->monto_escritura_colones;
  }

  public function getMontoEddi()
  {
    $habilitar_eddi = empty($this->product->percent_eddi) ? 0.0 : (float) ($this->product->percent_eddi);

    if ($habilitar_eddi > 0) {
      $this->monto_eddi = 0;
      $this->monto_escritura_colones = (float)$this->monto_escritura_colones;
      $this->registro_valor_fiscal = (float)$this->registro_valor_fiscal;

      if ($this->monto_escritura_colones > $this->registro_valor_fiscal)
        $this->monto_eddi = $this->monto_escritura_colones * $habilitar_eddi / 100;
      else
        $this->monto_eddi = $this->registro_valor_fiscal * $habilitar_eddi / 100;
    } else
      $this->monto_eddi = 0;
    return $this->monto_eddi;
  }

  public function getEstadoRegistro()
  {
    $status = "PENDIENTE";
    if (!is_null($this->fecha_reporte_gasto) && !empty($this->fecha_reporte_gasto))
      $status = "PAGADO";

    $this->estado_escritura  = $status;

    return $status;
  }

  public function getMontoTimbreEscritura($invoice)
  {
    // Se deben hacer los calculos con el monto combertido a colones siempre
    $this->monto_timbre_escritura = 0;
    $this->registro_currency_id = (int)$this->registro_currency_id;
    $this->registro_change_type = (float)$this->registro_change_type;
    $precio = $this->monto_escritura_colones = (float)$this->monto_escritura_colones;
    //die(var_dump($precio));
    $timbre = 0;

    $timbre = $this->getTimbresRegistro($precio, $invoice->bank_id);
    if ($this->product->enable_quantity == 1) {
      $cantidad = 1;
      if ($this->registro_cantidad > 0)
        $cantidad = $this->registro_cantidad;
    } else
      $cantidad = $this->quantity;

    // Obtengo el valor unitario
    $timbre = $timbre / $this->quantity;

    // Obtengo el timbre según la cantidad del calculo del registro
    $timbre = $timbre * $cantidad;

    $this->monto_timbre_escritura = $timbre;
    return $timbre;
  }

  // Calculo de Timbres
  public function getTimbresRegistro($precio, $banco_id)
  {
    $timbre = 0;
    $tipo = 'GASTO'; // Timbre
    $monto_formula = $this->desgloseTimbreFormulaRegistro($banco_id, $tipo, $precio);

    // Tabla Timbre Abogados
    $monto_grada = $this->desgloseTablaAbogadosRegistro($banco_id, $tipo, $precio);
    //die(var_dump($monto_grada));

    // Fijo
    $monto_fijo = $this->desgloseCalculosFijosRegistro($banco_id, $tipo);

    // Monto Manual
    $monto_manual = $this->desgloseCalculaMontoManualRegistro($banco_id, $tipo, $precio);

    $timbre_sin_descuento = $monto_formula['monto_sin_descuento'] + $monto_grada['monto_sin_descuento'] + $monto_fijo['monto_sin_descuento'] + $monto_manual['monto_sin_descuento'];
    $timbre_con_descuento = $monto_formula['monto_con_descuento'] + $monto_grada['monto_con_descuento'] + $monto_fijo['monto_con_descuento'] + $monto_manual['monto_con_descuento'];

    $timbre_con_descuento = $timbre_con_descuento - ($timbre_con_descuento * 6) / 100;

    $timbre = $timbre_sin_descuento + $timbre_con_descuento;
    $timbre = round($timbre, 2);

    return $timbre;
  }

  public function desgloseTimbreFormulaRegistro($bank_id, $tipo, $precio, $tipo_reporte_registro = 'normal')
  {
    $monto = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sumdescuento_seis_porciento = 0;

    $datos = array();
    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo
    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honoarios no hay calculo de formula
      // Consulta cuando el tipo no es 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 0,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      // Aqui antes no se filtraba por banco pero ahora en el form lo hize obligarotio
      // Consulta cuando el tipo es 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 0,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }
    $index = 0;

    foreach ($honorario_timbres as $dato) {
      if ($dato->porciento == 1) {
        $monto = ($precio * $dato->base) / 100;
      } else
			if ($dato->porcada > 0)
        $monto = ($precio / $dato->porcada) * $dato->base;

      $monto = $monto * $this->registro_cantidad;

      // Es solo para el calculo de registro
      switch ($tipo_reporte_registro) {
        case 'iva':
          if (!$dato->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        case 'no_iva':
          if ($dato->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        default:
          # code...
          break;
      }

      $monto_sin_descuento = 0;
      $monto_con_descuento = 0;

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $descuento_seis_porciento = $monto - ($monto * 6 / 100);

        $monto_con_descuento = $monto;
        $summonto_con_descuento += ceil($monto);
        $sumdescuento_seis_porciento += $descuento_seis_porciento;
      } else {
        $monto_sin_descuento = $monto;
        $summonto_sin_descuento += ceil($monto);
      }

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => ceil($monto_sin_descuento), 'monto_con_descuento' => ceil($monto_con_descuento)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 0),
      'monto_con_descuento' => round($summonto_con_descuento, 0),
      'sumdescuento_seis_porciento' => $sumdescuento_seis_porciento,
      'datos' => $datos,
    ];
  }

  public function desgloseTablaAbogadosRegistro($bank_id, $tipo, $precio, $tipo_reporte_registro = 'normal')
  {
    $monto = 0;
    $monto_sin_descuento = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sumdescuento_seis_porciento = 0;

    $datos = array();
    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honoarios no hay calculo de formula
      // Cuando el tipo no es 'GASTO'
      $tabla_abogado = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where(function ($query) {
          $query->where('product_honorarios_timbres.tabla_abogado_inscripciones', 1)
            ->orWhere('product_honorarios_timbres.tabla_abogado_traspasos', 1);
        })
        ->select('product_honorarios_timbres.*')
        ->first();
    } else {
      //aqui antes no se filtraba por banco pero en el form lo hice obligatorio
      // Cuando el tipo es 'GASTO'
      $tabla_abogado = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where(function ($query) {
          $query->where('product_honorarios_timbres.tabla_abogado_inscripciones', 1)
            ->orWhere('product_honorarios_timbres.tabla_abogado_traspasos', 1);
        })
        ->select('product_honorarios_timbres.*')
        ->first();
    }

    if (!is_null($tabla_abogado)) {
      if ($tabla_abogado->tabla_abogado_inscripciones == 1)
        $tipo_tabla = 1; // para tomar los datos de las inscripciones
      else
        $tipo_tabla = 2; // para tomar los datos de los traspasos de vehículos

      $honorario_timbres = Timbre::where('tipo', $tipo_tabla)
        ->orderBy('orden', 'asc')
        ->get();

      $index = 0;
      $founded = false;

      if (!empty($honorario_timbres)) {
        while ($founded == false && $index <= count($honorario_timbres)) {
          if ($precio <= $honorario_timbres[0]->base) {
            $monto = 0;
            $founded = true;
          } else
						if ($index + 1 < count($honorario_timbres)) {
            if ($precio >= $honorario_timbres[$index]->base && $precio <= $honorario_timbres[$index + 1]->base) {
              $monto = $honorario_timbres[$index + 1]->porcada;
              $founded = true;
            } else
							if ($precio >= $honorario_timbres[$index]->base && $honorario_timbres[$index + 1]->base == 0) {
              $monto = $honorario_timbres[$index + 1]->porcada;
              $founded = true;
            }
          }
          $index++;
        }
      }
      $monto_sin_descuento = 0;
      $monto_con_descuento = 0;

      $monto = $monto * $this->registro_cantidad;

      // Es solo para el calculo de registro
      switch ($tipo_reporte_registro) {
        case 'iva':
          if (!$tabla_abogado->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        case 'no_iva':
          if ($tabla_abogado->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        default:
          # code...
          break;
      }

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $tabla_abogado->descuento_timbre == true) {
        $descuento_seis_porciento = $monto - ($monto * 6 / 100);
        $monto_con_descuento = $monto;
        $summonto_con_descuento += ceil($monto);
        $sumdescuento_seis_porciento += $descuento_seis_porciento;
      } else {
        $monto_sin_descuento = $monto;
        $summonto_sin_descuento += ceil($monto);
      }

      $datos[] = ['titulo' => $tabla_abogado->description, 'monto_sin_descuento' => ceil($monto_sin_descuento), 'monto_con_descuento' => ceil($monto_con_descuento)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 0),
      'monto_con_descuento' => round($summonto_con_descuento, 0),
      'sumdescuento_seis_porciento' => round($sumdescuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function getMontoRegistroCobrar()
  {
    return $this->monto_registro_cobrar = $this->monto_escritura_colones;
  }

  public function desgloseCalculosFijosRegistro($bank_id, $tipo, $tipo_reporte_registro = 'normal')
  {
    $monto = 0;
    $monto_sin_descuento = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sumdescuento_seis_porciento = 0;

    $datos = array();
    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo
    if ($tipo != 'GASTO') { // Timbre no importa el banco, Honorario por si acaso lo puse para que quede general, pero para los honorarios no hay calculo de formula
      // Para tipos diferentes de 'GASTO'
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      //aqui antes no se filtraba por banco pero en el form lo hice requerido
      // Para 'GASTO' (sin importar banco)
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where('product_honorarios_timbres.base', '>', 0)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    foreach ($honorario_timbres as $dato) {
      $monto = $dato->base;
      $monto = $monto * $this->registro_cantidad;

      // Es solo para el calculo de registro
      switch ($tipo_reporte_registro) {
        case 'iva':
          if (!$dato->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        case 'no_iva':
          if ($dato->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        default:
          # code...
          break;
      }

      $monto_sin_descuento = 0;
      $monto_con_descuento = 0;

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $descuento_seis_porciento = $monto - ($monto * 6 / 100);
        $monto_con_descuento = $monto;
        $summonto_con_descuento += ceil($monto);
        $sumdescuento_seis_porciento += $descuento_seis_porciento;
      } else {
        $monto_sin_descuento = $monto;
        $summonto_sin_descuento += ceil($monto);
      }

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => ceil($monto_sin_descuento), 'monto_con_descuento' => ceil($monto_con_descuento)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 0),
      'monto_con_descuento' => round($summonto_con_descuento, 0),
      'sumdescuento_seis_porciento' => round($sumdescuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function desgloseCalculaMontoManualRegistro($bank_id, $tipo, $precio, $tipo_reporte_registro = 'normal')
  {
    $monto = 0;
    $monto_sin_descuento = 0;
    $monto_con_descuento = 0;
    $summonto_sin_descuento = 0;
    $summonto_con_descuento = 0;
    $sumdescuento_seis_porciento = 0;
    $datos = array();

    // Es fórmula si define el monto y por cada, además no tiene marcado cascada, grada, Fin Cascada/Grada, Escalonado, Fijo
    if ($tipo != 'GASTO') {
      // Query cuando $tipo no es 'GASTO' y considera el banco
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    } else {
      // Aqui no se filtraba por banco pero en el formulario se puse requerido
      // Query cuando $tipo es 'GASTO' (no considera el banco)
      $honorario_timbres = ProductHonorariosTimbre::join('products_banks', 'products_banks.product_id', '=', 'product_honorarios_timbres.product_id')
        ->where('products_banks.bank_id', $bank_id)
        ->where('product_honorarios_timbres.product_id', $this->product_id)
        ->where('product_honorarios_timbres.tipo', $tipo)
        ->where([
          'product_honorarios_timbres.tabla_abogado_inscripciones' => 0,
          'product_honorarios_timbres.tabla_abogado_traspasos' => 0,
          'product_honorarios_timbres.fijo' => 0,
          'product_honorarios_timbres.monto_manual' => 1,
        ])
        ->select('product_honorarios_timbres.*')
        ->get();
    }

    foreach ($honorario_timbres as $dato) {
      $monto = $precio;
      $monto = $monto * $this->registro_cantidad;

      // Es solo para el calculo de registro
      switch ($tipo_reporte_registro) {
        case 'iva':
          if (!$dato->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        case 'no_iva':
          if ($dato->es_impuesto) {   // Esto es para el reporte del calculo de registro calcular solo lo que sea impuesto
            $monto = 0;
          }
          break;
        default:
          # code...
          break;
      }

      $monto_sin_descuento = 0;
      $monto_con_descuento = 0;

      // El descuento se aplica al Enviar a pagar al Registro Nacional dicho caso  (aquí es donde se aplica el 6% a los timbres que así se configuraron)
      if ($tipo == 'GASTO' && $dato->descuento_timbre == true) {
        $descuento_seis_porciento = $monto - ($monto * 6 / 100);
        $monto_con_descuento = $monto;
        $summonto_con_descuento += ceil($monto);
        $sumdescuento_seis_porciento += $descuento_seis_porciento;
      } else {
        $monto_sin_descuento = $monto;
        $summonto_sin_descuento += ceil($monto);
      }

      $datos[] = ['titulo' => $dato->description, 'monto_sin_descuento' => round($monto_sin_descuento, 0), 'monto_con_descuento' => round($monto_con_descuento, 0)];
    }
    return [
      'monto_sin_descuento' => round($summonto_sin_descuento, 0),
      'monto_con_descuento' => round($summonto_con_descuento, 0),
      'sumdescuento_seis_porciento' => round($sumdescuento_seis_porciento, 2),
      'datos' => $datos,
    ];
  }

  public function calculaHonorarios($bank_id, $tipo, $currency, $changeType, $discounts)
  {
    $honorario = 0;

    //if ($this->transaction->proforma_type == 'HONORARIO') {
    $monto_honorarios = $this->desgloseHonorarios($bank_id, $tipo, $currency, $changeType, $discounts);
    $this->desglose_honorarios = $monto_honorarios;

    // Monto Manual
    $monto_manual = $this->desgloseCalculaMontoManual($bank_id, $tipo, $currency, $changeType, $discounts);
    $this->desglose_calculo_monto_honorario_manual = $monto_manual;

    $honorario = $monto_honorarios['monto_sin_descuento'] + $monto_manual['monto_sin_descuento'];

    $honorario = $honorario + $this->monto_cargo_adicional;

    return round($honorario, 2);
  }

  //*************************************************//
  //******Funciones para el calculo de la FE ********//
  private function getBaseImponible()
  {
    $baseImponible = $this->getSubTotal() + $this->impuestosEspeciales;
    return $baseImponible;
  }

  private function getImpuestosEspeciales()
  {
    $iva = 0;

    foreach ($this->taxes as $tax) {
      if ($tax->taxType->code == '02') { // Impuesto Selectivo de Consumo
        $iva += number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Selectivo de Consumo individuales de las líneas de detalle del surtido que se deben
        // incluir en estos casos, en caso de contar con más de una
      }

      if ($tax->taxType->code == '04') { // Impuesto específico de Bebidas Alcohólicas
        $iva += number_format($this->getCantidad() * $tax->proporcion * $tax->impuesto_unidad, 5, '.', '');
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
        // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
        // multiplicar por la cantidad de la línea principal
      }

      if ($tax->taxType->code == '05') { // Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador
        // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
        // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
        // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
        // multiplicar por la cantidad de la línea principal

        // si el producto es jabón de tocador
        if ($this->itemIsBebida($this->codigocabys)) {
          $div = $tax->impuesto_unidad / ($tax->volumen_unidad_consumo ?? 1);
          $iva += number_format($this->getCantidad() * $tax->count_unit_type * $div, 5, '.', '');
        } else
        if ($this->itemIsJabon($this->codigocabys)) {
          $iva += number_format($this->getCantidad() * $tax->volumen_unidad_consumo * $tax->impuesto_unidad, 5, '.', '');
        }
      }

      if ($tax->taxType->code == '12') { // Impuesto Específico al Cemento
        $iva += number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
      }
    }
    return $iva;
  }

  private function getHasRegaliaOrBonificacion()
  {
    /*
    Al utilizar el código de Naturaleza del Descuento 01
    correspondiente a “Regalías” o 03 de “Bonificaciones” y el
    código de impuesto 01, se debe utilizar para el cálculo del
    impuesto el campo denominado “Monto Total” y la tarifa
    */
    $discounts = $this->discounts;
    foreach ($discounts as $discount) {
      if (in_array($discount->discountType->code, ['01', '03'])) {
        return true; // Devuelve true inmediatamente al encontrar una coincidencia
      }
    }
    return false; // Solo llega aquí si no encontró coincidencias
  }

  private function getHasImpuestoEspecifico()
  {
    $taxes = $this->taxes;
    foreach ($taxes as $tax) {
      if (in_array($tax->taxType->code, ['03', '04', '05', '06', '12'])) {
        return true;
      }
    }
    return false;
  }

  private function calculaMontoImpuestoConReglasHacienda($tax)
  {
    $hasRegaliaOrBonificacion = $this->getHasRegaliaOrBonificacion();

    // Calcular el monto del impuesto
    $iva = $tax->tax_amount;
    if ($tax->taxType->code == '10') // Tarifa exenta
      $iva = 0.00000;

    if ($tax->taxType->code == '01' && $hasRegaliaOrBonificacion) {
      $iva = number_format((float)($this->getMontoTotal() * $tax->tax) / 100, 5, '.', '');
    }

    if ($tax->taxType->code == '08') { // IVA Régimen de Bienes Usados (Factor)
      $iva = number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
    }

    if ($tax->taxType->code == '07') { // IVA (cálculo especial)
      $iva = number_format((float)($this->getMontoTotal() * $tax->tax) / 100, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este
      // campo se calcula como la sumatoria de los montos de IVA individuales de las líneas de detalle del surtido que se deben
      // incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de multiplicar por la
      // cantidad de la línea principal.
    }

    if ($tax->taxType->code == '02') { // Impuesto Selectivo de Consumo
      $iva = number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido, paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Selectivo de Consumo individuales de las líneas de detalle del surtido que se deben
      // incluir en estos casos, en caso de contar con más de una
    }

    if ($tax->taxType->code == '03') { // Impuesto Único a los Combustibles
      $iva = number_format($tax->count_unit_type * $tax->impuesto_unidad, 5, '.', '');
    }

    if ($tax->taxType->code == '04') { // Impuesto específico de Bebidas Alcohólicas
      $iva = number_format($this->getCantidad() * $tax->proporcion * $tax->impuesto_unidad, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
      // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
      // multiplicar por la cantidad de la línea principal
    }

    if ($tax->taxType->code == '05') { // Impuesto Específico sobre las bebidas envasadas sin contenido alcohólico y jabones de tocador
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
      // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
      // multiplicar por la cantidad de la línea principal

      // si el producto es jabón de tocador
      if ($this->itemIsBebida($this->codigocabys)) {
        $div = $tax->impuesto_unidad / ($tax->volumen_unidad_consumo ?? 1);
        $iva = number_format($this->getCantidad() * $tax->count_unit_type * $div, 5, '.', '');
      } else
        if ($this->itemIsJabon($this->codigocabys)) {
        $iva = number_format($this->getCantidad() * $tax->volumen_unidad_consumo * $tax->impuesto_unidad, 5, '.', '');
      }
    }

    if ($tax->taxType->code == '06') { // Impuesto a los Productos de Tabaco
      $iva = number_format($this->getCantidad() * $tax->count_unit_type, 5, '.', '');
      // Nota: En el caso de utilizar el nodo “Detalle de productos del surtido,paquetes o combos”, este campo se calcula como la
      // sumatoria de los montos del Impuesto Específico a las Bebidas Alcohólicas individuales de las líneas de detalle del
      // surtido que se deben incluir en estos casos, en caso de contar con más de una unidad de surtido dicho monto se debe de
      // multiplicar por la cantidad de la línea principal
    }

    if ($tax->taxType->code == '12') { // Impuesto Específico al Cemento
      $iva = number_format($this->getSubTotal() * $tax->factor_calculo_tax, 5, '.', '');
    }

    return $iva;
  }

  private function getImpuestoAsumidoEmisorFabrica()
  {
    $hasRegaliaOrBonificacion = $this->hasRegaliaOrBonificacion;
    $hasImpuestoEspecifico = $this->hasImpuestoEspecifico;
    $impuestoAsumido = 0;
    if ($hasRegaliaOrBonificacion || $hasImpuestoEspecifico) {
      foreach ($this->taxes as $tax) {
        if ($hasRegaliaOrBonificacion || in_array($tax->taxType->code, ['03', '04', '05', '06', '12']))
          $impuestoAsumido += $tax->tax_amount;
      }
    }

    return number_format($impuestoAsumido, 5, '.', '');
  }

  private function itemIsBebida($codigocabys)
  {
    // Obtener los primeros 3 caracteres
    $primerosTres = substr($codigocabys, 0, 3);

    // Lista de valores permitidos
    $valoresPermitidos = ['244'];

    // Retorna true si está en la lista
    return in_array($primerosTres, $valoresPermitidos);
  }

  private function itemIsJabon($codigocabys)
  {
    // Obtener los primeros 3 caracteres
    $primerosTres = substr($codigocabys, 0, 3);

    // Lista de valores permitidos
    $valoresPermitidos = ['353'];

    // Retorna true si está en la lista
    return in_array($primerosTres, $valoresPermitidos);
  }

  private function getServNoSujeto()
  {
    if ($this->product->type != 'service')
      return number_format(0, 5, '.', '');

    $taxes = $this->taxes;
    foreach ($taxes as $tax) {
      if (in_array($tax->taxRate->code, ['01', '11'])) {
        return number_format($this->getMontoTotal(), 5, '.', '');
      }
    }
    return number_format(0, 5, '.', '');
  }

  private function getMercNoSujeta()
  {
    if ($this->product->type == 'service')
      return number_format(0, 5, '.', '');

    $taxes = $this->taxes;
    foreach ($taxes as $tax) {
      if (in_array($tax->taxRate->code, ['01', '11'])) {
        return number_format($this->getMontoTotal(), 5, '.', '');
      }
    }
    return number_format(0, 5, '.', '');
  }
}
