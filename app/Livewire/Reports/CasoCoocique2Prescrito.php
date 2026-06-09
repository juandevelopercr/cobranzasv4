<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\User;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CasoCoocique2Prescrito extends Component
{
  public $filter_date;
  public $filter_numero_caso;
  public $filter_abogado;
  public $filter_asistente;
  public $filter_currency;
  public $filter_contact;
  public $filter_type;
  public $abogados;
  public $bancos;
  public $currencies;
  public $contacts;
  //public $document_type;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.casos-coocique2-prescrito');
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

    $this->contacts = [
      ['id'=>4, 'name'=>'CEFA CENTRAL FARMACEUTICA S.A.'],
      ['id'=>723, 'name'=>'COCA COLA FEMSA DE COSTA RICA SOCIEDAD ANONIMA'],
      ['id'=>31, 'name'=>'ASOCIACIÓN SOLIDARISTA DE EMPLEADOS DE AUTOMERCADO S.A. Y AFINES'],
      ['id'=>1, 'name'=>'Otros']
    ];

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

    try {
        $this->validate([
            'filter_date' => 'required',
        ], [
            'filter_date.required' => 'Debe seleccionar un rango de fechas.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->dispatch('download-ready');
        throw $e;
    }

    $key = Str::uuid()->toString();
    Cache::put($key, [
        'filter_date'        => $this->filter_date,
        'filter_numero_caso' => $this->filter_numero_caso,
        'filter_abogado'     => $this->filter_abogado,
        'filter_asistente'   => $this->filter_asistente,
        'filter_currency'    => $this->filter_currency,
    ], now()->addMinutes(15));

    $this->dispatch('start-download', url: route('reports.casos-coocique2-prescrito.download', ['key' => $key]));
  }
}