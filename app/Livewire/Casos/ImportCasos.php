<?php

namespace App\Livewire\Casos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\CasosImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportCasos extends Component
{
    use WithFileUploads;

    public $archivo;
    public $mensaje;
    public $tipo;

    public function render()
    {
        return view('livewire.casos.import-casos');
    }

    public function importar()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $import = new CasosImport();

        try {
            Excel::import($import, $this->archivo->getRealPath());

            $this->mensaje = $import->mensaje;
            $this->tipo = $import->errores ? 'danger' : 'success';
        } catch (\Throwable $e) {
            $this->mensaje = 'Error al procesar el archivo: ' . $e->getMessage();
            $this->tipo = 'danger';
        }
    }
}
