<?php

namespace App\Livewire\Reports;

use App\Models\Department;
use App\Models\Transaction;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CustomerReport extends Component
{
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.customer');
  }

  public function mount()
  {
    $this->dispatch('reinitFormControls');
  }

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'dateRangeSelected' => 'dateRangeSelected',
  ];

  public function dateRangeSelected($id, $range)
  {
    $this->$id = $range;
  }

  public function exportExcel()
  {
    $key = Str::uuid()->toString();
    Cache::put($key, [], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.customers.download', ['key' => $key]));
  }
}