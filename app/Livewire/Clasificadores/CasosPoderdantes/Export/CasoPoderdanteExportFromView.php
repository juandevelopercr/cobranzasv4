<?php

namespace App\Livewire\Clasificadores\CasosPoderdantes\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoPoderdanteExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-poderdantes.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
