<?php

namespace App\Livewire\Reports;

use App\Exports\FacturacionReport;
use App\Models\Transaction;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Facturacion extends Component
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
    return view('livewire.reports.facturacion');
  }

  public function mount()
  {
    $this->filter_status = Transaction::FACTURADA;

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
    return Excel::download(new FacturacionReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_status' => $this->filter_status
      ],
      'REPORTE DE FACTURACIÓN ' . $this->filter_date
    ), 'reporte-facturacion.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
