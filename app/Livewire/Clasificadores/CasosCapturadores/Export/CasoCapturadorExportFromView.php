<?php

namespace App\Livewire\Clasificadores\CasosCapturadores\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoCapturadorExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-capturadores.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
