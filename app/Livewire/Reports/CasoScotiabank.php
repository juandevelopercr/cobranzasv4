<?php

namespace App\Livewire\Reports;

use App\Models\Bank;
use App\Models\User;
use Livewire\Component;
use App\Models\Currency;
use App\Models\CasoProducto;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CasoScotiabankReport;

class CasoScotiabank extends Component
{
  public $filter_date;
  public $filter_numero_caso;
  public $filter_abogado;
  public $filter_asistente;
  public $filter_currency;
  public $filter_exclude_products = [];
  public $abogados;
  public $bancos;
  public $currencies;
  public $productos;
  public $loading = false;

  public function render()
  {
    return view('livewire.reports.casos-scotiabank');
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

    $this->productos = CasoProducto::join('casos_productos_bancos', 'casos_productos_bancos.product_id', '=', 'casos_productos.id')
      ->where('casos_productos_bancos.bank_id', Bank::SCOTIABANKCR)
      ->orderBy('casos_productos.nombre', 'ASC')
      ->select('casos_productos.id', 'casos_productos.nombre')
      ->get();

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

    $this->loading = true;

    set_time_limit(300);

    $titleDate = $this->filter_date ? ' ' . $this->filter_date : '';

    // Generar y descargar el Excel
    return Excel::download(new CasoScotiabankReport(
      [
        'filter_date'             => $this->filter_date,
        'filter_numero_caso'      => $this->filter_numero_caso,
        'filter_abogado'          => $this->filter_abogado,
        'filter_asistente'        => $this->filter_asistente,
        'filter_currency'         => $this->filter_currency,
        'filter_exclude_products' => $this->filter_exclude_products,
      ],
      'REPORTE DE CASOS DE DAVIBANK' . $titleDate
    ), 'reporte-casos-davibank.xlsx');

    // No necesitas $this->loading = false aquí,
    // Livewire maneja la acción de descarga automáticamente
  }
}
