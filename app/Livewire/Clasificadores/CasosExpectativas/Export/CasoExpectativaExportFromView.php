<?php

namespace App\Livewire\Clasificadores\CasosExpectativas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoExpectativaExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-expectativas.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
