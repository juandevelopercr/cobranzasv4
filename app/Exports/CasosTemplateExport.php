<?php

namespace App\Exports;

use App\Helpers\ImportColumns;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CasosTemplateExport implements FromArray, WithHeadings, WithColumnWidths
{
    protected $banco;

    public function __construct($banco)
    {
        $this->banco = strtoupper($banco);
    }

    // Contenido del archivo: vacío
    public function array(): array
    {
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

    // Ancho de columnas basado en la longitud del encabezado
    public function columnWidths(): array
    {
        $columns = ImportColumns::getColumnasPorBanco($this->banco);
        $widths = [];

        foreach (array_keys($columns) as $index => $heading) {
            // Ajuste simple: ancho proporcional a la longitud del encabezado + padding
            $widths[$this->getExcelColumnLetter($index)] = strlen($heading) + 5;
        }

        return $widths;
    }

    // Convierte índice numérico a letra de columna Excel
    protected function getExcelColumnLetter($index)
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }
}
