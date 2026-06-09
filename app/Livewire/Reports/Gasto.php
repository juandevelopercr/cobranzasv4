<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Gasto extends Component
{
  public $filter_date;
  public $filter_emisor;
  public $filter_status;
  public $filter_type;
  public $filter_currency;
  public $filter_tax_type;
  public $status;
  public $currencies;
  public $tax_types;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.gastos');
  }

  public function mount()
  {
    $this->filter_status = Transaction::ACEPTADA;

    $this->status = $this->getStatusOptions();

    $this->filter_type = '01';

    $this->currencies = Currency::orderBy('code', 'ASC')->get();

    $this->tax_types = [['id'=>'GRAVADO', 'name'=>'GRAVADO'], ['id'=>'EXONERADO', 'name'=>'EXONERADO']];

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

    $estados = Transaction::getStatusOptionsforReportGasto($is_invoice);
    return $estados;
  }

  public function exportExcel(string $rawDate = '')
  {
    if ($rawDate !== '') {
        $this->filter_date = $rawDate;
    }

    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'     => $this->filter_date,
        'filter_emisor'   => $this->filter_emisor,
        'filter_type'     => $this->filter_type,
        'filter_status'   => $this->filter_status,
        'filter_currency' => $this->filter_currency,
        'filter_tax_type' => $this->filter_tax_type,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.gastos.download', ['key' => $key]));
  }
}