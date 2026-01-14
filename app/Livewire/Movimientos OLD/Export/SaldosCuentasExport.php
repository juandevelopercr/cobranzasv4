<?php

namespace App\Livewire\Movimientos\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SaldosCuentasExport implements FromView, WithColumnFormatting, WithColumnWidths
{
    protected $cuentas301;
    protected $totalColones301;
    protected $totalDolares301;
    protected $otrasCuentasColones;
    protected $otrasCuentasDolares;
    protected $totalDisponibleColones;
    protected $totalDisponibleDolares;
    protected $totalDolarizado;
    protected $tipo_cambio;

    public function __construct(
        $cuentas301,
        $totalColones301,
        $totalDolares301,
        $otrasCuentasColones,
        $otrasCuentasDolares,
        $totalDisponibleColones,
        $totalDisponibleDolares,
        $totalDolarizado,
        $tipo_cambio
    ) {
        $this->cuentas301 = $cuentas301;
        $this->totalColones301 = $totalColones301;
        $this->totalDolares301 = $totalDolares301;
        $this->otrasCuentasColones = $otrasCuentasColones;
        $this->otrasCuentasDolares = $otrasCuentasDolares;
        $this->totalDisponibleColones = $totalDisponibleColones;
        $this->totalDisponibleDolares = $totalDisponibleDolares;
        $this->totalDolarizado = $totalDolarizado;
        $this->tipo_cambio = $tipo_cambio;
    }

    public function columnFormats(): array
    {
        return [
            'C:J' => NumberFormat::FORMAT_NUMBER_00, // Todas las columnas numéricas
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // #
            'B' => 60,  // Cuenta
            'C' => 15,  // Saldo Sistema
            'D' => 15,  // Pendiente
            'E' => 15,  // Gastos
            'F' => 15,  // Honorarios
            'G' => 15,  // Karla
            'H' => 15,  // Certifondo
            'I' => 15,  // Colchón
            'J' => 15,  // Total
        ];
    }

    public function view(): View
    {
        return view('livewire.movimientos.export.saldos_cuentas', [
            'cuentas301' => $this->cuentas301,
            'totalColones301' => $this->totalColones301,
            'totalDolares301' => $this->totalDolares301,
            'otrasCuentasColones' => $this->otrasCuentasColones,
            'otrasCuentasDolares' => $this->otrasCuentasDolares,
            'totalDisponibleColones' => $this->totalDisponibleColones,
            'totalDisponibleDolares' => $this->totalDisponibleDolares,
            'totalDolarizado' => $this->totalDolarizado,
            'tipo_cambio' => $this->tipo_cambio,
        ]);
    }
}
