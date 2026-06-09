<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Department;
use App\Models\CentroCosto;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Registro extends Component
{
  public $filter_date;
  public $filter_centroCosto;
  public $filter_department;
  public $departments;
  public $centrosCosto;

  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.registro');
  }

  public function mount()
  {
    $this->departments = Department::whereIn('id', session('current_department'))
      ->where('active', 1)
      ->orderBy('name', 'ASC')
      ->get();

    $this->centrosCosto = CentroCosto::orderBy('descrip', 'ASC')->get();

    //Banca Retail Normal
    $this->filter_centroCosto = 1;

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

  public function exportExcel(string $rawDate = '')
  {
    if ($rawDate !== '') {
        $this->filter_date = $rawDate;
    }

    if (empty($this->filter_centroCosto) || is_null($this->filter_centroCosto))
      $idsCostos = $this->centrosCosto->pluck('id');
    else
      $idsCostos = $this->filter_centroCosto;

    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'        => $this->filter_date,
        'filter_centroCosto' => $this->filter_centroCosto,
        'filter_department'  => $this->filter_department,
        'idsCostos'          => $idsCostos,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.registro.download', ['key' => $key]));
  }
}