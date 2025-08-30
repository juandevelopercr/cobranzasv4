<?php

namespace App\Livewire\Clasificadores\CasosExpectativas;

use App\Livewire\Clasificadores\CasosExpectativas\Export\CasoExpectativaExport;
use App\Livewire\Clasificadores\CasosExpectativas\Export\CasoExpectativaExportFromView;
use App\Models\CasoExpectativa;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CasoExpectativaDatatableExport extends Component
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
    $dataQuery = CasoExpectativa::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-expectativas.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoExpectativaExportFromView($dataQuery->get()), 'casos-expectativas.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoExpectativa::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-expectativas.id', $this->selectedIds);
    }
    return Excel::download(new CasoExpectativaExport($dataQuery->get()), 'casos-expectativas.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoExpectativa::all() : CasoExpectativa::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-expectativas.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-expectativas.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
