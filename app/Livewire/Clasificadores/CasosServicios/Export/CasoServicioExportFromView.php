<?php

namespace App\Livewire\Clasificadores\CasosServicios\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoServicioExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-servicios.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
