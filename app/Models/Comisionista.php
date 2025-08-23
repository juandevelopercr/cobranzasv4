<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comisionista extends Model
{

  protected $fillable = [
    'corr',
    'nombre',
    'impresion',
    'active',
  ];

  protected $casts = [
    'active' => 'boolean',
  ];

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'corr',
      'nombre',
      'impresion',
      'active',
    ];

    $query->select($columns);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_corr'])) {
      $query->where('corr', 'like', '%' . $filters['filter_corr'] . '%');
    }

    if (!empty($filters['filter_nombre'])) {
      $query->where('nombre', 'like', '%' . $filters['filter_nombre'] . '%');
    }

    if (!empty($filters['filter_impresion'])) {
      $query->where('impresion', 'like', '%' . $filters['filter_impresion'] . '%');
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
    $iconSize = 'bx-md'; // Opcional: usa 'bx-md' si deseas más grandes

    $html = '<div class="d-flex align-items-center justify-content-start gap-2">';

    // Editar
    if ($user->can('edit-classifiers')) {
      $html .= <<<HTML
        <a href="#" class="text-primary" title="Editar"
           wire:click="edit({$this->id})"
           wire:loading.attr="disabled"
           wire:target="edit({$this->id})">
            <i class="bx bx-edit {$iconSize}"></i>
        </a>
        HTML;
    }

    // Eliminar
    if ($user->can('delete-classifiers')) {
      $html .= <<<HTML
        <a href="#" class="text-danger" title="Eliminar"
           wire:click.prevent="confirmarAccion({$this->id}, 'delete',
             '¿Está seguro que desea eliminar este registro?',
             'Después de confirmar, el registro será eliminado',
             'Sí, proceder')">
            <i class="bx bx-trash {$iconSize}"></i>
        </a>
        HTML;
    }

    $html .= '</div>';
    return $html;
  }
}
