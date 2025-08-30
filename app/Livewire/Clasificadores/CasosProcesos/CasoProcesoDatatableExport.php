<?php

namespace App\Livewire\Clasificadores\CasosProcesos;

use App\Livewire\Clasificadores\CasosProcesos\Export\CasoProcesoExport;
use App\Livewire\Clasificadores\CasosProcesos\Export\CasoProcesoExportFromView;
use App\Models\CasoProceso;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CasoProcesoDatatableExport extends Component
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
    $dataQuery = CasoProceso::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-procesos.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoProcesoExportFromView($dataQuery->get()), 'casos-procesos.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoProceso::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-procesos.id', $this->selectedIds);
    }
    return Excel::download(new CasoProcesoExport($dataQuery->get()), 'casos-procesos.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoProceso::all() : CasoProceso::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-procesos.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-procesos.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
