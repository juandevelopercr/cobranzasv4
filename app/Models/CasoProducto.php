<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasoProducto extends Model
{
  // Definición de constantes
  public const HIPOTECARIO = 2;
  public const LEASING = 3;
  public const LEASING_DESINSCRIPCION = 4;
  public const LEASING_PROCESOS_COBRATORIOS = 5;
  public const PRENDARIO = 11;
  public const CUENTA_CORRIENTE = 13;
  public const FIDEICOMISO = 14;
  public const PERSONAL = 15;
  public const PYME = 16;
  public const TARJETA_CREDITO = 17;
  public const CREDITO_CONSUMO = 18;
  public const LEASING_COBRO_ADMINISTRATIVO = 19;
  public const LEASING_BIENES_CANCELADOS_NO_TRASPASADOS = 20;
  public const LEASING_MARCHAMOS = 21;
  public const CREDITO_PERSONAL = 22;
  public const CREDITO_COMERCIAL = 23;
  public const CREDITO_PRENDARIO = 24;
  public const CREDITO_HIPOTECARIO = 25;
  public const CREDITO_LIBRANZA = 26;
  public const MONITORIO = 27;
  public const LEVANTAMIENTO_GRAVAMENES = 28;
  public const LEASING_LEVANTAMIENTO_GRAVAMEN = 29;
  public const GARANTIA_MOBILIARIA = 30;
  public const LETRA_CAMBIO = 31;
  public const PAGARE = 32;
  public const AUTOCRED = 45;
  public const BANCA_DESARROLLO = 46;
  public const CONSUMO = 47;
  public const CORPORATIVO = 48;
  public const CREDITO_CREDIFACIL = 49;
  public const CPA = 50;
  public const NO_APLICA = 56;
  public const CREDITO_CONSUMO_ALT = 58;
  public const PYMES = 61;
  public const HIPOTECA = 62;
  public const PRENDA = 63;
  public const DFGDE = 64;
  public const SOBREGIRO = 65;
  public const FACTURAS = 66;
  public const CHEQUE = 67;
  public const COBRO_ADMINISTRATIVO = 68;
  public const FRAUDE = 69;
  public const EJECUCION_NOTARIAL_EJECUCION_PRENDARIA = 70;
  public const DACION_PAGO = 71;
  public const MICROCREDITOS = 72;
  public const CONCURSALES = 78;

  // Nombre de la tabla
  protected $table = 'casos_productos';

  protected $fillable = [
    'nombre',
    'activo',
  ];

  public function banks()
  {
    return $this->belongsToMany(Bank::class, 'casos_productos_bancos', 'product_id', 'bank_id');
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'casos_productos.id',
      'nombre',
      'activo'
    ];

    $query->select($columns);

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_name'])) {
      $query->where('nombre', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_bank'])) {
      $query->whereHas('banks', function ($q) use ($filters) {
        $q->where('name', 'like', '%' . $filters['filter_bank'] . '%');
      });
    }

    if (isset($filters['filter_active']) && !is_null($filters['filter_active'])  && $filters['filter_active'] !== '') {
      $query->where('activo', '=', $filters['filter_active']);
    }

    return $query;
  }

  public function getHtmlColumnActive()
  {
    if ($this->activo) {
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

  public function getHtmlBancos()
  {
      $bancos = $this->banks()->pluck('name');

      return $bancos->isNotEmpty()
          ? $bancos->implode(', ')
          : "<span class=\"text-gray-500\">-</span>";
  }
}
