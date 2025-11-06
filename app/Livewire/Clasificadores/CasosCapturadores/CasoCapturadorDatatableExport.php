<?php

namespace App\Livewire\Clasificadores\CasosCapturadores;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CasoCapturador;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Livewire\Clasificadores\CasosCapturadores\Export\CasoCapturadorExport;
use App\Livewire\Clasificadores\CasosCapturadores\Export\CasoCapturadorExportFromView;

class CasoCapturadorDatatableExport extends Component
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
    $dataQuery = CasoCapturador::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-capturadores.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoCapturadorExportFromView($dataQuery->get()), 'casos-capturadores.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoCapturador::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-capturadores.id', $this->selectedIds);
    }
    return Excel::download(new CasoCapturadorExport($dataQuery->get()), 'casos-capturadores.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoCapturador::all() : CasoCapturador::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-capturadores.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-capturadores.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
