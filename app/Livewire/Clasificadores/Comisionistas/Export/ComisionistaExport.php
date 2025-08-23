<?php

namespace App\Livewire\Clasificadores\Comisionistas\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComisionistaExport implements FromCollection, WithHeadings
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
    return ["ID", "corr", "nombre", "impresion", "active"];
  }
}
