<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\User;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CasoCoociqueIncobrableReport;

class CasoCoociqueIncobrable extends Component
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
    return view('livewire.reports.casos-coocique-incobrables');
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

  public function exportExcel()
  {
    // Validar que los campos requeridos estén llenos
    $this->validate([
        'filter_date' => 'required',
    ], [
        'filter_date.required' => 'Debe seleccionar un rango de fechas.',
    ]);

    $this->loading = true;

    // Generar y descargar el Excel
    return Excel::download(new CasoCoociqueIncobrableReport(
      [
        'filter_date' => $this->filter_date,
        'filter_numero_caso' => $this->filter_numero_caso,
        'filter_abogado' => $this->filter_abogado,
        'filter_asistente' => $this->filter_asistente,
        'filter_currency' => $this->filter_currency,
      ],
      'REPORTE DE CASOS DE COOCIQUE INCOBRABLES ' . $this->filter_date
    ), 'reporte-casos-coocique-incobrables.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
