<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\CentroCosto;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class General extends Component
{
  public $filter_date;
  public $filter_centroCosto;
  public $filter_type;
  public $centrosCosto;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.generales');
  }

  public function mount()
  {
    $this->filter_type = 1;

    $this->centrosCosto = CentroCosto::orderBy('descrip', 'ASC')->get();

    //Banca Retail Normal
    $this->filter_centroCosto = [26];

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

    if (empty($this->filter_centroCosto) || is_null($this->filter_centroCosto)){
        $this->filter_centroCosto = CentroCosto::pluck('id');
    }

    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'        => $this->filter_date,
        'filter_centroCosto' => $this->filter_centroCosto,
        'filter_type'        => $this->filter_type,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.generales.download', ['key' => $key]));
  }

  public function getReportName($tipo){
    $title = '';
    switch ($tipo) {
      case 1:
        $title = 'con-deposito';
        break;
      case 2:
        $title = 'sin-deposito';
        break;
      case 3:
        $title = 'honoarios';
        break;
      case 4:
        $title = 'gastos';
        break;
    }
    return $title;
  }
}
