<?php

namespace App\Livewire\Dashboards;

use App\Helpers\Helpers;
use App\Models\Caso;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class FormalizacionProducto extends Component
{
  public $years = [];      // Lista de años disponibles para el filtro
  public $year;            // Año
  public $month;           // Mes de análisis (por defecto mes actual)
  public $chartTheme = 'zune'; // Valor por defecto
  public $chartsPerRow = 1; // por defecto 2 gráficos por fila
  public $months;
  public $monthName;

  public function mount()
  {
    // Obtener años únicos desde la columna created_at
    $this->years = Caso::select(DB::raw('YEAR(afecha_terminacion) as year'))
      ->whereNotNull('afecha_terminacion')
      ->distinct()
      ->orderBy('year', 'asc')
      ->pluck('year')
      ->toArray();

    // Año actual y anterior como valores por defecto
    // Obtener la fecha actual con Carbon
    $now = Carbon::now();

    // Obtener el mes actual (formato: '01' a '12')
    $this->month = $now->format('m');
    $this->year = $now->year; // o $now->format('Y');

    $this->months = [
      ['id' => '01', 'name' => 'Enero'],
      ['id' => '02', 'name' => 'Febrero'],
      ['id' => '03', 'name' => 'Marzo'],
      ['id' => '04', 'name' => 'Abril'],
      ['id' => '05', 'name' => 'Mayo'],
      ['id' => '06', 'name' => 'Junio'],
      ['id' => '07', 'name' => 'Julio'],
      ['id' => '08', 'name' => 'Agosto'],
      ['id' => '09', 'name' => 'Septiembre'],
      ['id' => '10', 'name' => 'Octubre'],
      ['id' => '11', 'name' => 'Noviembre'],
      ['id' => '12', 'name' => 'Diciembre']
    ];

    $this->js(<<<JS
        Livewire.dispatch('updateFusionCharts', {$this->getChartDataJson()});
    JS);
  }

  public function updated($property)
  {
    if (in_array($property, ['year', 'month', 'chartTheme'])) {
      $this->js(<<<JS
          Livewire.dispatch('updateFusionCharts', {$this->getChartDataJson()});
      JS);
    }
  }

  public function getChartDataJson()
  {
    return json_encode([
      ...$this->getChartData(),
      'theme' => $this->chartTheme
    ]);
  }

  public function getChartData(): array
  {
    $this->monthName = $this->getNombreMes();

    $pie_formalizaciones_product_mes_usd  = $this->getDataPieFormalizacionesByProductAndMes(Currency::DOLARES);
    $pie_formalizaciones_product_mes_crc  = $this->getDataPieFormalizacionesByProductAndMes(Currency::COLONES);

    $pie_formalizaciones_product_year_usd  = $this->getDataPieFormalizacionesByProductAndYear(Currency::DOLARES);
    $pie_formalizaciones_product_year_crc  = $this->getDataPieFormalizacionesByProductAndYear(Currency::COLONES);

    return [
      'pie_formalizaciones_product_mes_usd'  => $pie_formalizaciones_product_mes_usd,
      'pie_formalizaciones_product_mes_crc'  => $pie_formalizaciones_product_mes_crc,
      'pie_formalizaciones_product_year_usd' => $pie_formalizaciones_product_year_usd,
      'pie_formalizaciones_product_year_crc' => $pie_formalizaciones_product_year_crc
    ];
  }

  public function getDataPieFormalizacionesByProductAndMes($currency): array
  {
    $query = Caso::select(
      'casos_productos.nombre AS product',
      DB::raw('COUNT(*) AS total')
    )
      ->join('casos_productos', 'casos.product_id', '=', 'casos_productos.id')
      ->where('casos.currency_id', $currency)
      ->whereNotNull('afecha_terminacion')
      ->whereYear('afecha_terminacion', '=', $this->year)
      ->whereMonth('afecha_terminacion', '=', $this->month);

    $result = $query
      ->groupBy(DB::raw('casos_productos.nombre'))
      ->orderBy('casos_productos.nombre')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => $item->product,
        'value' => $item->total
      ];
    })->toArray();

    $caption = 'Formalizaciones Productos' . (($currency == Currency::DOLARES) ? 'USD' : 'CRC');
    //dd($caption);
    $subCaption = [];

    if (!empty($this->monthName))
      $subCaption[] = "$this->monthName";
    $subCaption[] = "de {$this->year}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
    ];
  }

  public function getDataPieFormalizacionesByProductAndYear($currency): array
  {
    $query = Caso::select(
      'casos_productos.nombre AS product',
      DB::raw('COUNT(*) AS total')
    )
      ->join('casos_productos', 'casos.product_id', '=', 'casos_productos.id')
      ->where('casos.currency_id', $currency)
      ->whereNotNull('afecha_terminacion')
      ->whereYear('afecha_terminacion', '=', $this->year);

    $result = $query
      ->groupBy(DB::raw('casos_productos.nombre'))
      ->orderBy('casos_productos.nombre')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => $item->product,
        'value' => $item->total
      ];
    })->toArray();

    $caption = 'Formalizaciones Productos' . (($currency == Currency::DOLARES) ? 'USD' : 'CRC');
    $subCaption = [];

    $subCaption[] = "Año {$this->year}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
    ];
  }

  public function getNombreMes()
  {
    // Obtener el nombre del mes
    $monthName = collect($this->months)
      ->firstWhere('id', $this->month)['name'] ?? 'Mes Desconocido';
    return $monthName;
  }

  public function render()
  {
    return view('livewire.dashboards.formalizaciones-productos');
  }
}
