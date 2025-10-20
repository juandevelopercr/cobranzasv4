<?php

namespace App\Exports;

use App\Helpers\ImportColumns;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CasosTemplateExport implements FromArray, WithHeadings
{
    protected $banco;

    public function __construct($banco)
    {
        $this->banco = strtoupper($banco);
    }

    // Contenido del archivo: vacío
    public function array(): array
    {
        // Una fila vacía
        return [
            []
        ];
    }

    // Encabezados
    public function headings(): array
    {
        $columns = ImportColumns::getColumnasPorBanco($this->banco);
        return array_keys($columns);
    }
}
