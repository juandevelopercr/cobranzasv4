<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\CasoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CasoEstado extends Model
{
  // Nombre de la tabla
  protected $table = 'casos_estados';

  protected $fillable = [
    'name',
    'description',
    'active',
  ];

  protected $casts = [
    'active' => 'boolean',
  ];

  /**
   * Obtener bancos a través de las asignaciones
   */
  public function banks()
  {
    return $this->belongsToMany(Bank::class, 'casos_estados_bancos', 'estado_id', 'bank_id');
  }

  /**
   * Obtener bancos a través de las asignaciones
   */
  public function products()
  {
    return $this->belongsToMany(CasoProducto::class, 'casos_estados_productos', 'estado_id', 'product_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'name',
      'description',
      'active',
    ];

    $query->select($columns);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_bank'])) {
      $query->whereHas('banks', function ($q) use ($filters) {
        $q->where('name', 'like', '%' . $filters['filter_bank'] . '%');
      });
    }

    if (!empty($filters['filter_producto'])) {
      $query->whereHas('products', function ($q) use ($filters) {
        $q->where('nombre', 'like', '%' . $filters['filter_producto'] . '%');
      });
    }

    if (isset($filters['filter_active']) && !is_null($filters['filter_active'])  && $filters['filter_active'] !== '') {
      $query->where('active', '=', $filters['filter_active']);
    }

    return $query;
  }

  public function getHtmlColumnActive()
  {
    if ($this->active) {
      $output = '<i class="bx bx-check-circle text-success fs-4" title="Activo"></i>';
    } else {
      $output = '<i class="bx bx-x-circle text-danger fs-4" title="Inactivo"></i>';
    }
    return $output;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-classifiers')) {
      $html .= <<<HTML
            <button type="button"
                class="btn p-0 me-2 text-primary"
                title="Editar"
                wire:click="edit({$this->id})"
                wire:loading.attr="disabled"
                wire:target="edit({$this->id})">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit({$this->id})"></i>
                <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit({$this->id})"></i>
            </button>
        HTML;
    }

    // Eliminar
    if ($user->can('delete-classifiers')) {
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

  public function getHtmlProductos()
  {
      $productos = $this->products()->pluck('nombre');

      return $productos->isNotEmpty()
          ? $productos->implode(', ')
          : "<span class=\"text-gray-500\">-</span>";
  }

  public function getHtmlBancos()
  {
      $bancos = $this->banks()->pluck('name');

      return $bancos->isNotEmpty()
          ? $bancos->implode(', ')
          : "<span class=\"text-gray-500\">-</span>";
  }
}
