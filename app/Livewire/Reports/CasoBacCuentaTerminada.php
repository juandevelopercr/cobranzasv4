<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\User;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CasoBacTerminadaReport;

class CasoBacCuentaTerminada extends Component
{
  public $filter_date;
  public $filter_numero_caso;
  public $filter_abogado;
  public $filter_asistente;
  public $filter_currency;
  public $abogados;
  public $bancos;
  public $currencies;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.casos-bac-cuentas-terminadas');
  }

  public function mount()
  {
    $this->abogados = User::where('active', 1)
      ->whereHas('roles', function ($query) {
        $query->whereIn('name', [User::ABOGADO]);
      })
      ->orderBy('name', 'ASC')
      ->get();

    $this->currencies = Currency::where('active', 1)->get();

    // Primer día del mes actual
    $startOfMonth = Carbon::now()->startOfMonth()->format('d-m-Y');

    // Último día del mes actual
    $endOfMonth = Carbon::now()->endOfMonth()->format('d-m-Y');

    // Asignar al daterange con 'to'
    $this->filter_date = $startOfMonth . ' to ' . $endOfMonth;

    $this->dispatch('reinitFormControls');
  }

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'dateRangeSelected' => 'dateRangeSelected',
    // 'dateSelected' => 'handleDateSelected',
  ];

  public function dateRangeSelected($id, $range)
  {
    $this->$id = $range;
  }

  public function exportExcel()
  {
    $this->loading = true;

    // Generar y descargar el Excel
    return Excel::download(new CasoBacTerminadaReport(
      [
        'filter_date' => $this->filter_date,
        'filter_numero_caso' => $this->filter_numero_caso,
        'filter_abogado' => $this->filter_abogado,
        'filter_asistente' => $this->filter_asistente,
        'filter_currency' => $this->filter_currency
      ],
      'REPORTE DE CASOS DE BAC CUENTAS TERMINADAS ' . $this->filter_date
    ), 'reporte-casos-back-cuentas-terminadas.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
