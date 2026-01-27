<?php

namespace App\Livewire\Reports;

use App\Exports\NotaDebitoReport;
use App\Models\Department;
use App\Models\Transaction;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class NotaDebito extends Component
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
    return view('livewire.reports.nota-debito');
  }

  public function mount()
  {
    $this->filter_status = Transaction::FACTURADA;
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

    $this->status = $this->getStatusOptions();

    $this->dispatch('reinitFormControls');
  }

  protected $listeners = [
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  public function dateRangeSelected($id, $range)
  {
    $this->$id = $range;
  }

  public function getStatusOptions()
  {
    $is_invoice = false;
    $estados = Transaction::getStatusOptionsforReports($is_invoice);
    return $estados;
  }

  public function exportExcel()
  {
    $this->validate([
      'filter_date' => 'required',
    ], [
      'filter_date.required' => 'La fecha es obligatoria'
    ]);

    $this->loading = true;

    return Excel::download(new NotaDebitoReport(
      [
        'filter_date' => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_department' => $this->filter_department,
        'filter_status' => $this->filter_status
      ],
      'REPORTE DE NOTAS DE DÉBITO ' . $this->filter_date
    ), 'reporte-notas-debito.xlsx');
  }
}
