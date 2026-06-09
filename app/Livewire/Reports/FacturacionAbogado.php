<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FacturacionAbogado extends Component
{
  public $filter_date;
  public $filter_abogado;
  public $abogados;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.facturacion-abogado');
  }

  public function mount()
  {
    $this->abogados = User::where('active', 1)
      ->whereHas('roles', function ($query) {
        $query->whereIn('name', [User::ABOGADO]);
      })
      ->orderBy('name', 'ASC')
      ->get();

    // Primer día del mes actual
    $startOfMonth = Carbon::now()->startOfMonth()->format('d-m-Y');

    // Último día del mes actual
    $endOfMonth = Carbon::now()->endOfMonth()->format('d-m-Y');

    // Asignar al daterange con 'to'
    $this->filter_date = $startOfMonth . ' to ' . $endOfMonth;

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

  public function exportExcel(string $rawDate = '')
  {
    if ($rawDate !== '') {
        $this->filter_date = $rawDate;
    }

    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'    => $this->filter_date,
        'filter_abogado' => $this->filter_abogado,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.facturacion-abogado.download', ['key' => $key]));
  }
}
