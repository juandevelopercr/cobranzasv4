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
    $this->currencies = Currency::whereIn('id', [1,16])->orderBy('code', 'ASC')->get();

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
    // Validar que los campos requeridos estén llenos
    $this->validate([
        'filter_currency' => 'required',
        'filter_date' => 'required',
    ], [
        'filter_currency.required' => 'Debe seleccionar una moneda.',
        'filter_date.required' => 'Debe seleccionar un rango de fechas.',
    ]);

    $this->loading = true;

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
