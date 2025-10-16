<?php

namespace App\Livewire\Clasificadores\CasosServicios;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CasoServicio;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Livewire\Clasificadores\CasosServicios\Export\CasoServicioExport;
use App\Livewire\Clasificadores\CasosServicios\Export\CasoServicioExportFromView;

class CasoServicioDatatableExport extends Component
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
    $dataQuery = CasoServicio::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-servicios.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoServicioExportFromView($dataQuery->get()), 'casos-servicios.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoServicio::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-servicios.id', $this->selectedIds);
    }
    return Excel::download(new CasoServicioExport($dataQuery->get()), 'casos-servicios.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoServicio::all() : CasoServicio::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-servicios.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-servicios.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
