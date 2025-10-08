<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use Livewire\Component;
use App\Exports\IvaReport;
use App\Models\Department;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;

class Iva extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_status;
  public $departments;
  public $status;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.iva');
  }

  public function mount()
  {
    $this->filter_status = Transaction::FACTURADA;

    // Primer día del mes actual
    $startOfMonth = Carbon::now()->startOfMonth()->format('d-m-Y');

    // Último día del mes actual
    $endOfMonth = Carbon::now()->endOfMonth()->format('d-m-Y');

    // Asignar al daterange con 'to'
    $this->filter_date = $startOfMonth . ' to ' . $endOfMonth;

    $this->status = $this->getStatusOptions();

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
    return Excel::download(new IvaReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_status' => $this->filter_status
      ],
      'REPORTE DE FACTURACIÓN CON PAGO ' . $this->filter_date
    ), 'reporte-facturacion-con-pago.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
