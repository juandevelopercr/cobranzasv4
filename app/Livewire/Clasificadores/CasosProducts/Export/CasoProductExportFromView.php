<?php

namespace App\Livewire\Clasificadores\CasosProducts\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoProductExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-products.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
