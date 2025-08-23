<?php

namespace App\Livewire\Clasificadores\GruposEmpresariales\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GrupoEmpresarialExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.grupos-empresariales.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
