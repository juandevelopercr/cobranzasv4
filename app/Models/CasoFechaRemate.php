<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasoFechaRemate extends Model
{
  // Nombre de la tabla
  protected $table = 'casos_fecha_remate';

    protected $fillable = [
        'caso_id',
        'fecha',
        'titulo',
        'actualizado_por',
    ];

  protected $casts = [
      'fecha' => 'date',
  ];

  public function caso()
  {
      return $this->belongsTo(Caso::class);
  }
}
