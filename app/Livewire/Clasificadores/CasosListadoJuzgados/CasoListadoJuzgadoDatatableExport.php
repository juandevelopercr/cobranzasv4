<?php

namespace App\Livewire\Clasificadores\CasosListadoJuzgados;

use Livewire\Component;
use Livewire\Attributes\On;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CasoListadoJuzgado;
use Maatwebsite\Excel\Facades\Excel;
use App\Livewire\Clasificadores\CasosListadoJuzgados\Export\CasoListadoJuzgadoExport;
use App\Livewire\Clasificadores\CasosListadoJuzgados\Export\CasoListadoJuzgadoExportFromView;

class CasoListadoJuzgadoDatatableExport extends Component
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
    $dataQuery = CasoListadoJuzgado::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('listado_juzgados.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoListadoJuzgadoExportFromView($dataQuery->get()), 'casos-listado-juzgados.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoListadoJuzgado::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('listado_juzgados.id', $this->selectedIds);
    }
    return Excel::download(new CasoListadoJuzgadoExport($dataQuery->get()), 'listado_juzgados.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoListadoJuzgado::all() : CasoListadoJuzgado::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.listado-juzgados.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'listado_juzgados.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
