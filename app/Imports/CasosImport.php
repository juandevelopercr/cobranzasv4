<?php

namespace App\Imports;

use App\Models\Caso;
use App\Helpers\ImportColumns;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class CasosImport implements ToCollection, WithHeadingRow
{
    public $mensaje;
    public $insertados = 0;
    public $errores = [];

    public function collection(Collection $rows)
    {
        $columnas = ImportColumns::COLUMNAS_BAC;

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // Si la fila está completamente vacía, saltar
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                $caso = new Caso();

                foreach ($columnas as $nombreCol => $obj) {
                    $key = strtolower($nombreCol); // nombres normalizados por WithHeadingRow
                    $valor = $row[$key] ?? null;

                    if (!is_null($valor)) {
                        switch ($obj['tipo']) {
                            case 'date':
                                $valor = $this->parseFecha($valor);
                                break;
                            case 'float':
                                $valor = (float)$valor;
                                break;
                            default:
                                $valor = trim((string)$valor);
                        }
                    }

                    $caso->{$obj['campo']} = $valor;
                }

                $caso->fecha_importacion = now();
                $caso->fecha_creacion = now();

                if ($caso->save()) {
                    $this->insertados++;
                } else {
                    $this->errores[] = "Fila {$index}: error al guardar.";
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errores[] = "Error general: " . $e->getMessage();
        }

        $this->mensaje = "Se han importado {$this->insertados} registros.";
        if ($this->errores) {
            $this->mensaje .= "<br>Errores:<br>" . implode('<br>', $this->errores);
        }
    }

    private function parseFecha($valor)
    {
        if (empty($valor)) return null;

        try {
            if (is_numeric($valor)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor))
                    ->format('Y-m-d');
            }
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
