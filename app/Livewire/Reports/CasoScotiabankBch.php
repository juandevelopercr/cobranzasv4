<?php

namespace App\Livewire\Reports;

use App\Models\User;
use Livewire\Component;
use App\Models\Currency;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CasoScotiabankBchReport;

class CasoScotiabankBch extends Component
{
  public $filter_date;
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
    return view('livewire.reports.casos-scotiabank-bch');
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
    $this->loading = true;

    set_time_limit(300);

    $titleDate = $this->filter_date ? ' ' . $this->filter_date : '';

    // Generar y descargar el Excel
    return Excel::download(new CasoScotiabankBchReport(
      [
        'filter_date' => $this->filter_date,
        'filter_numero_caso' => $this->filter_numero_caso,
        'filter_abogado' => $this->filter_abogado,
        'filter_asistente' => $this->filter_asistente,
        'filter_currency' => $this->filter_currency
      ],
      'REPORTE DE CASOS DE DAVIBANK BCH' . $titleDate
    ), 'reporte-casos-davibank-bch.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
