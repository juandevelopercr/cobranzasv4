<?php

namespace App\Livewire\Clasificadores\CasosPoderdantes;

use App\Livewire\Clasificadores\CasosPoderdantes\Export\CasoPoderdanteExport;
use App\Livewire\Clasificadores\CasosPoderdantes\Export\CasoPoderdanteExportFromView;
use App\Models\CasoPoderdante;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CasoPoderdanteDatatableExport extends Component
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
    $dataQuery = CasoPoderdante::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-poderdantes.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoPoderdanteExportFromView($dataQuery->get()), 'casos-poderdantes.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoPoderdante::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-poderdantes.id', $this->selectedIds);
    }
    return Excel::download(new CasoPoderdanteExport($dataQuery->get()), 'casos-poderdantes.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoPoderdante::all() : CasoPoderdante::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-poderdantes.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-poderdantes.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
