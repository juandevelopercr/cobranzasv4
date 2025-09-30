<?php

namespace App\Models;

use App\Models\Caso;
use App\Models\Product;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionOtherCharge extends Model
{
  use HasFactory;

  protected $table = 'transactions_other_charges';

  protected $fillable = [
    'transaction_id',
    'caso_id',
    'product_id',
    'additional_charge_type_id',
    'additional_charge_other',
    'third_party_identification_type',
    'third_party_identification',
    'third_party_name',
    'detail',
    'percent',
    'quantity',
    'amount',
  ];

  public function transaction()
  {
    return $this->belongsTo(Transaction::class);
  }

  // Relación con la línea de transacción
  public function transactionLine()
  {
    return $this->belongsTo(TransactionLine::class, 'transaction_line_id');
  }

  public function caso()
  {
    return $this->belongsTo(Caso::class);
  }

  public function product()
  {
    return $this->belongsTo(Product::class);
  }

  // Relación con el tipo de cargo adicional
  public function additionalChargeType()
  {
    return $this->belongsTo(AdditionalChargeType::class, 'additional_charge_type_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'transactions_other_charges.id',
      'transaction_id',
      'transactions_other_charges.product_id',
       DB::raw("CONCAT_WS(' / ',
          NULLIF(casos.pnumero, ''),
          NULLIF(casos.pnumero_operacion1, ''),
          NULLIF(casos.pnombre_demandado, ''),
          NULLIF(casos.pnombre_apellidos_deudor, '')
      ) as caso_info"),
      'products.name as product_name',
      'additional_charge_type_id',
      'additional_charge_types.code as charge_code',
      'additional_charge_types.name as charge_name',
      'additional_charge_other',
      'third_party_identification_type',
      'identification_types.name as identification_type_name',
      'third_party_identification',
      'third_party_name',
      'detail',
      'percent',
      'quantity',
      'amount'
    ];

    $query->select($columns)
      ->join('additional_charge_types', 'transactions_other_charges.additional_charge_type_id', '=', 'additional_charge_types.id')
      ->join('products', 'transactions_other_charges.product_id', '=', 'products.id')
      ->leftJoin('identification_types', 'transactions_other_charges.third_party_identification_type', '=', 'identification_types.code')
      ->leftJoin('casos', 'transactions_other_charges.caso_id', '=', 'casos.id')
      ->where(function ($q) use ($value) {
        $q->where('additional_charge_other', 'like', "%{$value}%")
          ->orWhere('third_party_identification_type', 'like', "%{$value}%")
          ->orWhere('third_party_identification', 'like', "%{$value}%")
          ->orWhere('third_party_name', 'like', "%{$value}%")
          ->orWhere('detail', 'like', "%{$value}%")
          ->orWhere('percent', 'like', "%{$value}%")
          ->orWhere('amount', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_additional_charge_types'])) {
      $query->where('charge_name', 'like', '%' . $filters['filter_additional_charge_types'] . '%');
    }

    if (!empty($filters['filter_detail'])) {
      $query->where('detail', 'like', '%' . $filters['filter_detail'] . '%');
    }

    if (!empty($filters['filter_product'])) {
      $query->where('transactions_other_charges.product_id', '=', $filters['filter_product']);
    }

    if (!empty($filters['filter_quantity'])) {
      $query->where('quantity', 'like', '%' . $filters['filter_quantity'] . '%');
    }

    if (!empty($filters['filter_amount'])) {
      $query->where('amount', 'like', '%' . $filters['filter_amount'] . '%');
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

    /*
    if (!empty($filters['filter_total'])) {
      $query->where('amount', 'like', '%' . $filters['filter_total'] . '%');
    }
    */

    if (!empty($filters['filter_third_party_name'])) {
      $query->where('third_party_name', 'like', '%' . $filters['filter_third_party_name'] . '%');
    }

    if (!empty($filters['filter_third_party_identification_type'])) {
      $query->where('third_party_identification_type', 'like', '%' . $filters['filter_third_party_identification_type'] . '%');
    }

    if (!empty($filters['filter_third_party_identification'])) {
      $query->where('third_party_identification', 'like', '%' . $filters['filter_third_party_identification'] . '%');
    }

    return $query;
  }

  public function getHtmlTotal()
  {
    return Helpers::formatDecimal($this->quantity * $this->amount);
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-cargos-proformas') && $this->transaction->proforma_status == Transaction::PROCESO) {
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
    if ($user->can('delete-cargos-proformas') && $this->transaction->proforma_status == Transaction::PROCESO) {
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
}
