<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Currency;
use App\Models\Department;
use App\Models\CentroCosto;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Antiguedad extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_centroCosto = [1, 18, 15, 16, 17];
  public $filter_currency;
  public $departments;
  public $currencies;
  public $centrosCosto;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.antiguedad');
  }

  public function mount()
  {
    $this->currencies = Currency::orderBy('code', 'ASC')->get();

    $this->centrosCosto = CentroCosto::orderBy('descrip', 'ASC')->get();

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

    try {
        $this->validate([
            'filter_currency' => 'required',
            'filter_date' => 'required',
        ], [
            'filter_currency.required' => 'Debe seleccionar una moneda.',
            'filter_date.required' => 'Debe seleccionar un rango de fechas.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->dispatch('download-ready');
        throw $e;
    }

    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'        => $this->filter_date,
        'filter_contact'     => $this->filter_contact,
        'filter_centroCosto' => $this->filter_centroCosto,
        'filter_currency'    => $this->filter_currency,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.antiguedad-saldo.download', ['key' => $key]));
  }
}
