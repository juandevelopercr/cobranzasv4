<?php

namespace App\Livewire\Clasificadores\CasosNotificadores\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CasoNotificadorExportFromView implements FromView
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function view(): View
  {
    return view('livewire.clasificadores.casos-notificadores.export.data-excel', [
      'records' => $this->records
    ]);
  }
}
