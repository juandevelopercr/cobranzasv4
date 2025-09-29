<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasoEstadoNotificadores extends Model
{
  // Nombre de la tabla
  protected $table = 'casos_estados_notificaciones';

  protected $fillable = [
    'nombre',
  ];


  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'id',
      'name',
    ];

    $query->select($columns);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('nombre', 'like', '%' . $filters['filter_name'] . '%');
    }

    return $query;
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
}
