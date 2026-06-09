<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\User;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CasoCafsaIncobrable extends Component
{
  //public $filter_date;
  public $filter_numero_caso;
  public $filter_abogado;
  public $filter_asistente;
  public $filter_currency;
  public $abogados;
  public $bancos;
  public $currencies;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.casos-cafsa-incobrables');
  }

  public function mount()
  {
    $this->abogados = User::where('active', 1)
      ->whereHas('roles', function ($query) {
        $query->whereIn('name', [User::ABOGADO]);
      })
      ->orderBy('name', 'ASC')
      ->get();

    $this->currencies = Currency::where('active', 1)->get();

    // Primer día del mes actual
    $startOfMonth = Carbon::now()->startOfMonth()->format('d-m-Y');

    // Último día del mes actual
    $endOfMonth = Carbon::now()->endOfMonth()->format('d-m-Y');

    // Asignar al daterange con 'to'
    //$this->filter_date = $startOfMonth . ' to ' . $endOfMonth;

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

  public function exportExcel()
  {
    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_numero_caso' => $this->filter_numero_caso,
        'filter_abogado'     => $this->filter_abogado,
        'filter_asistente'   => $this->filter_asistente,
        'filter_currency'    => $this->filter_currency,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.casos-cafsa-incobrables.download', ['key' => $key]));
  }
}