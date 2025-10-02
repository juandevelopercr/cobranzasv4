<?php

namespace App\Livewire\Clasificadores\Cuentas\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CuentaExportFromView implements FromView, WithColumnFormatting, ShouldAutoSize
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function columnFormats(): array
  {
    return [
      'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
    ];
  }

  public function view(): View
  {
    $chunks = [];
    $this->query->chunk(500, function ($rows) use (&$chunks) {
      $chunks[] = $rows;
    });

    return view('livewire.clasificadores.cuentas.export.data-excel', [
      //'query' => $this->query // NOTA: se itera con ->cursor() en la vista
      'chunks' => $chunks
    ]);
  }
}
