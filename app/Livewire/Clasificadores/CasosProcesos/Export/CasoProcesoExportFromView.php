<?php

namespace App\Livewire\Clasificadores\CasosProcesos\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoProcesoExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-procesos.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
