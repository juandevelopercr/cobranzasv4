<?php

namespace App\Livewire\Clasificadores\Comisionistas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ComisionistaExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.comisionistas.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
