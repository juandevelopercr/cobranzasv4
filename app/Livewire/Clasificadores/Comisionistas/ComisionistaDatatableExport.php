<?php

namespace App\Livewire\Clasificadores\Comisionistas;

use App\Livewire\Clasificadores\Comisionistas\Export\ComisionistaExport;
use App\Livewire\Clasificadores\Comisionistas\Export\ComisionistaExportFromView;
use App\Models\Comisionista;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ComisionistaDatatableExport extends Component
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
    $dataQuery = Comisionista::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('comisionista.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new ComisionistaExportFromView($dataQuery->get()), 'Caratulas.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = Comisionista::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('comisionista.id', $this->selectedIds);
    }
    return Excel::download(new ComisionistaExport($dataQuery->get()), 'comisionista.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? Comisionista::all() : Comisionista::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.comisionistas.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'comisionista.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
