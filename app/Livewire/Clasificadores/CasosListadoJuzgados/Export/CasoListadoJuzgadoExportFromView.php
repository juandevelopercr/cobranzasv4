<?php

namespace App\Livewire\Clasificadores\CasosListadoJuzgados\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoListadoJuzgadoExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-listado-juzgados.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
