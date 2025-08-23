<?php

namespace App\Livewire\Clasificadores\GruposEmpresariales;

use App\Livewire\Clasificadores\GruposEmpresariales\Export\GrupoEmpresarialExport;
use App\Livewire\Clasificadores\GruposEmpresariales\Export\GrupoEmpresarialExportFromView;
use App\Models\GrupoEmpresarial;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class GrupoEmpresarialDatatableExport extends Component
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
    $dataQuery = GrupoEmpresarial::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('grupos_empresariales.id', $this->selectedIds);
    }
    // Genera y descarga el archivo Excel desde la vista Blade
    return Excel::download(new GrupoEmpresarialExportFromView($dataQuery->get()), 'grupos-empresariales.xlsx');
  }

  public function exportCsv()
  {
    $dataQuery = GrupoEmpresarial::search($this->search);

    // Aplica la selección de usuarios, si existen seleccionados
    if (!empty($this->selectedIds)) {
      $dataQuery->whereIn('grupos_empresariales.id', $this->selectedIds);
    }
    return Excel::download(new GrupoEmpresarialExport($dataQuery->get()), 'grupos-empresariales.csv');
  }

  public function exportPdf()
  {
    $records = empty($this->selectedIds) ? GrupoEmpresarial::all() : GrupoEmpresarial::whereIn('id', $this->selectedIds)->get();
    $pdf = Pdf::loadView('livewire.clasificadores.grupos-empresariales.export.data-pdf', compact('records'))
      ->setPaper('a4')
      ->setOptions(['defaultFont' => 'DejaVu Sans']);

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, 'grupos-empresariales.pdf');
  }

  public function render()
  {
    return view('livewire.datatable.button-export');
  }
}
