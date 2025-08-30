<?php

namespace App\Livewire\Clasificadores\CasosProducts;

use App\Livewire\Clasificadores\CasosProducts\Export\CasoProductExport;
use App\Livewire\Clasificadores\CasosProducts\Export\CasoProductExportFromView;
use App\Models\CasoProducto;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CasoProductDatatableExport extends Component
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
    $dataQuery = CasoProducto::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-productos.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new CasoProductExportFromView($dataQuery->get()), 'casos-productos.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = CasoProducto::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('casos-productos.id', $this->selectedIds);
    }
    return Excel::download(new CasoProductExport($dataQuery->get()), 'casos-productos.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? CasoProducto::all() : CasoProducto::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.casos-productos.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'casos-productos.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
