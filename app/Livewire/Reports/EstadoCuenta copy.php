<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Transaction;
use App\Exports\EstadoCuentaReport;
use Maatwebsite\Excel\Facades\Excel;

class EstadoCuenta extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_department;
  public $filter_currency;
  public $departments;
  public $currencies;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.estado-cuenta');
  }

  public function mount()
  {
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

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

    // Generar y descargar el Excel
    return Excel::download(new EstadoCuentaReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_department' => $this->filter_department,
        'filter_currency' => $this->filter_currency,
      ],
      'REPORTE DE ESTADO DE CUENTA' . $this->filter_date
    ), 'reporte-estado-cuenta.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
