<?php

namespace App\Livewire\Clasificadores\CasosNotificadores;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CasoNotificador;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Livewire\Clasificadores\CasosNotificadores\Export\CasoNotificadorExport;
use App\Livewire\Clasificadores\CasosNotificadores\Export\CasoNotificadorExportFromView;

class CasoNotificadorDatatableExport extends Component
{
  public $search = ''; // Almacena el término de búsqueda
  public $selectedIds = []; // Almacena los IDs de usuarios seleccionados

  //#[On('updateSelectedIds')]
  protected $listeners = ['updateSelectedIds', 'updateSearch'];

  public function updateSelectedIds($selectedIds)
  {
    $this->selectedIds = $selectedIds;
  }

  public function updateSearch($search)
  {
    $this->search = $search;
  }

  public function prepareExportExcel()
  {
    $dataQuery = CasoNotificador::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-notificadores.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoNotificadorExportFromView($dataQuery->get()), 'casos-notificadores.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoNotificador::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-notificadores.id', $this->selectedIds);
    }
    return Excel::download(new CasoNotificadorExport($dataQuery->get()), 'casos-notificadores.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoNotificador::all() : CasoNotificador::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-notificadores.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-notificadores.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
