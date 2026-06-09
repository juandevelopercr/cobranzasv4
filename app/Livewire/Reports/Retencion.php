<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Retencion extends Component
{
  public $filter_date;
  public $filter_contact;
  public $filter_status;
  public $status;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.retencion');
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

  public function exportExcel(string $rawDate = '')
  {
    if ($rawDate !== '') {
        $this->filter_date = $rawDate;
    }

    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'    => $this->filter_date,
        'filter_contact' => $this->filter_contact,
        'filter_status'  => $this->filter_status,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.retencion.download', ['key' => $key]));
  }
}