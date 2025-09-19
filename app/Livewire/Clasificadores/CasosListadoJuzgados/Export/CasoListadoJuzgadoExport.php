<?php

namespace App\Livewire\Clasificadores\CasosListadoJuzgados\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CasoListadoJuzgadoExport implements FromCollection, WithHeadings
{
  protected $records;

  public function __construct($records)
  {
    $this->records = $records;
  }

  public function collection()
  {
    return $this->records;
  }

  public function headings(): array
  {
    return ["ID", "nombre"];
  }
}
