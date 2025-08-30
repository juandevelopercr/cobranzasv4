<?php

namespace App\Livewire\Clasificadores\CasosJuzgados\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoJuzgadoExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-juzgados.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
