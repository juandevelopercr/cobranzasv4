<?php

namespace App\Models;

use App\Models\AreaPractica;
use App\Models\Contact;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContactContacto extends Model
{
  protected $table = 'contacts_contactos';

  protected $fillable = [
    'contact_id',
    'name',
    'email',
    'telefono',
    'ext',
    'celular',
    'grupo_empresarial_id',
    'clasificacion',
    'tipo_cliente',
    'fecha_nacimiento',
    'anno_ingreso'
  ];

  protected $casts = [
    'fecha_nacimiento' => 'date',
  ];

  // Definiciones de ENUMs
  public const CLASIFICACIONES = ['RECURRENTE', 'OCASIONAL'];
  public const TIPOS_CLIENTE = ['ACTUAL', 'EXCLIENTE'];

  // Relaciones
  public function contact(): BelongsTo
  {
    return $this->belongsTo(Contact::class);
  }

  // Relaciones CORREGIDAS:
  public function sector(): BelongsTo
  {
    return $this->belongsTo(Sector::class, 'sector_id');
  }

  public function areaPractica(): BelongsTo
  {
    return $this->belongsTo(AreaPractica::class, 'area_practica_id');
  }

  public function grupoEmpresarial(): BelongsTo
  {
    return $this->belongsTo(GrupoEmpresarial::class, 'grupo_empresarial_id');
  }

  // Relaciones con áreas de práctica
  public function areasPracticas(): BelongsToMany
  {
    return $this->belongsToMany(
      AreaPractica::class,
      'contacts_contactos_area_practica',
      'contacto_id',
      'area_practica_id'
    );
  }

  // Relaciones con sectores industriales
  public function sectoresIndustriales(): BelongsToMany
  {
    return $this->belongsToMany(
      Sector::class,
      'contacts_contactos_sector',
      'contacto_id',
      'sector_id'
    );
  }

  public function scopeSearch($query, $value, $filters = [])
  {
    // Definir las columnas que quieres seleccionar
    $columns = [
      'contacts_contactos.id',
      'contacts_contactos.contact_id',
      'contacts_contactos.name',
      'contacts_contactos.email',
      'contacts_contactos.telefono',
      'contacts_contactos.ext',
      'contacts_contactos.celular',
      'contacts_contactos.grupo_empresarial_id',
      'contacts_contactos.clasificacion',
      'contacts_contactos.tipo_cliente',
      'contacts_contactos.fecha_nacimiento',
      'contacts_contactos.anno_ingreso',
      'grupos_empresariales.name as grupo',
      'contacts_contactos.created_at'
    ];

    $query->select($columns)
      ->leftJoin('grupos_empresariales', 'grupo_empresarial_id', '=', 'grupos_empresariales.id')
      ->where(function ($q) use ($value) {
        $q->where('contacts_contactos.name', 'like', "%{$value}%")
          ->orWhere('contacts_contactos.email', 'like', "%{$value}%")
          ->orWhere('contacts_contactos.telefono', 'like', "%{$value}%");
      });

    // Aplica filtros adicionales si están definidos
    if (!empty($filters['filter_grupo'])) {
      $query->where('grupo_empresarial_id', '=', $filters['filter_grupo']);
    }

    if (!empty($filters['filter_name'])) {
      $query->where('contacts_contactos.name', 'like', '%' . $filters['filter_name'] . '%');
    }

    if (!empty($filters['filter_email'])) {
      $query->where('contacts_contactos.email', 'like', '%' . $filters['filter_email'] . '%');
    }

    if (!empty($filters['filter_tipo'])) {
      $query->where('contacts_contactos.tipo_cliente', '=', $filters['filter_tipo']);
    }

    if (!empty($filters['filter_identification'])) {
      $query->where('contacts.identification', 'like', '%' . $filters['filter_identification'] . '%');
    }

    if (!empty($filters['filter_area'])) {
      //$query->where('contacts.phone', 'like', '%' . $filters['filter_phone'] . '%');
    }

    if (!empty($filters['filter_sector'])) {
      //$query->where('contacts.phone', 'like', '%' . $filters['filter_phone'] . '%');
    }

    return $query;
  }

  public function getHtmlColumnAction(): string
  {
    $user = auth()->user();
    $iconSize = 'bx-md';

    $html = '<div class="d-flex align-items-center flex-nowrap">';

    // Editar
    if ($user->can('edit-clients')) {
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
    if ($user->can('delete-clients')) {
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

  public function getHtmlColumnArea()
  {
    $htmlColumn = '';
    if ($this->areaPractica) {
      $htmlColumn = $this->areaPractica->pluck('name')->join(', ');
    } else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
  }

  public function getHtmlColumnSector()
  {
    $htmlColumn = '';
    if ($this->sectores)
      $htmlColumn = $this->sectores->pluck('name')->join(', ');
    else
      $htmlColumn = "<span class=\"text-gray-500\">-</span>";
    return $htmlColumn;
  }
}
