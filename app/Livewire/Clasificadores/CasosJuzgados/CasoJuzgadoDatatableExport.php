<?php

namespace App\Livewire\Clasificadores\CasosJuzgados;

use App\Livewire\Clasificadores\CasosJuzgados\Export\CasoJuzgadoExport;
use App\Livewire\Clasificadores\CasosJuzgados\Export\CasoJuzgadoExportFromView;
use App\Models\CasoJuzgado;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CasoJuzgadoDatatableExport extends Component
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
    $dataQuery = CasoJuzgado::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-juzgados.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoJuzgadoExportFromView($dataQuery->get()), 'casos-juzgados.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoJuzgado::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-juzgados.id', $this->selectedIds);
    }
    return Excel::download(new CasoJuzgadoExport($dataQuery->get()), 'casos-juzgados.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoJuzgado::all() : CasoJuzgado::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-juzgados.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-juzgados.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
