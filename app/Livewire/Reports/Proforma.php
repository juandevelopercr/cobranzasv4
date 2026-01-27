<?php

namespace App\Livewire\Reports;

use App\Exports\ProformaReport;
use App\Models\Department;
use App\Models\Transaction;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Proforma extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_department;
  public $filter_status;
  public $departments;
  public $status;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.proforma');
  }

  public function mount()
  {
    // Default status might be null to show all, or specific ones.
    // Usually Proformas report shows everything.
    $this->filter_status = '';
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

    // Use full status list for Proformas (including Pending, Process, etc.)
    // Transaction::getStatusOptions(false) returns PROCESO, SOLICITADA, FACTURADA, RECHAZADA, ANULADA
    // Transaction::getStatusOptionsforReports(false) returns FACTURADA, RECHAZADA, ANULADA
    // For Proforma Report, we probably want the comprehensive list.
    $this->status = Transaction::getStatusOptions(false);

    $this->dispatch('reinitFormControls');
  }

  protected $listeners = [
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  public function dateRangeSelected($id, $range)
  {
    $this->$id = $range;
  }

  public function exportExcel()
  {
    $this->validate([
      'filter_date' => 'required',
    ], [
      'filter_date.required' => 'La fecha es obligatoria'
    ]);

    $this->loading = true;

    return Excel::download(new ProformaReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_department' => $this->filter_department,
        'filter_status' => $this->filter_status
      ],
      'REPORTE DE PROFORMAS ' . $this->filter_date
    ), 'reporte-proformas.xlsx');
  }
}
