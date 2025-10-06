<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Transaction;
use App\Exports\EstadoCuentaReport;
use Maatwebsite\Excel\Facades\Excel;

class EstadoCuenta extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_currency;
  public $currencies;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.estado-cuenta');
  }

  public function mount()
  {
    $this->currencies = Currency::orderBy('code', 'ASC')->get();

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

  public function getStatusOptions()
  {
    // Retornar los estados
    $is_invoice = false;

    $estados = Transaction::getStatusOptionsforReports($is_invoice);
    return $estados;
  }

  public function exportExcel()
  {
    $this->loading = true;

    /*
    $title = 'ESTADO CUENTA';
    if (!empty($this->filter_date)) {
        // Extraer solo mes y año del rango
        $range = explode(' to ', $this->filter_date);
        if (count($range) === 2) {
            $start = Carbon::createFromFormat('d-m-Y', trim($range[0]));
            $title .= ' ' . $start->format('m-Y');
        }
    }
        */

    // Generar y descargar el Excel
    return Excel::download(new EstadoCuentaReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_currency' => $this->filter_currency,
      ],
      'REPORTE DE ESTADO DE CUENTA' . $this->filter_date
    ), 'reporte-estado-cuenta.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
