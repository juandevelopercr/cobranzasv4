<?php

namespace App\Models;

use App\Models\CatalogoCuenta;
use App\Models\CentroCosto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
  use HasFactory;

  const RETAIL = 1;
  const BANCACORPORATIVA = 2;
  const TERCERO = 3;
  const LAFISE = 4;

  protected $fillable = [
    'code',
    'nombre',
    'email',
    'centro_costo_id',
    'codigo_contable_id',
    'active',
  ];

  public function banks()
  {
    return $this->belongsToMany(Bank::class, 'department_banks', 'department_id', 'bank_id');
  }

  public function products()
  {
    return $this->belongsToMany(Product::class, 'department_products', 'department_id', 'product_id');
  }

  public function users()
  {
    return $this->hasMany(User::class);
  }

  public function centroCosto()
  {
    return $this->belongsTo(CentroCosto::class, 'centro_costo_id');
  }

  public function codigoContable()
  {
    return $this->belongsTo(CatalogoCuenta::class, 'codigo_contable_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'departments.id',
      'departments.code',
      'departments.name',
      'departments.email',
      'centro_costos.descrip as centro_costo',
      'codigo_contables.descrip as codigo_contable',
      'departments.active',
    ];

    $query->select($columns)
      ->leftJoin('centro_costos', 'departments.centro_costo_id', '=', 'centro_costos.id')
      ->leftJoin('codigo_contables', 'departments.codigo_contable_id', '=', 'codigo_contables.id');

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_code'])) {
      $query->where('departments.code', 'like', '%' . $filters['filter_code'] . '%');
    }

    if (!empty($filters['filter_name'])) {
      $query->where('departments.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_email'])) {
      $query->where('departments.email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_codigo_contable'])) {
      $query->where('codigo_contables.id', 'like', '%' . $filters['filter_codigo_contable'] . '%');
    }

    if (!empty($filters['filter_centro_costo'])) {
      $query->where('centro_costos.id', 'like', '%' . $filters['filter_centro_costo'] . '%');
    }

    if (isset($filters['filter_active']) && !is_null($filters['filter_active'])  && $filters['filter_active'] !== '') {
      $query->where('departments.active', '=', $filters['filter_active']);
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
                wire:target="edit">
                <i class="bx bx-loader bx-spin {$iconSize}" wire:loading wire:target="edit"></i>
                <i class="bx bx-edit {$iconSize}" wire:loading.remove wire:target="edit"></i>
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
}
