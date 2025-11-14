<?php

namespace App\Livewire\Dashboards;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Caso;
use App\Models\User;
use Livewire\Component;
use App\Helpers\Helpers;
use App\Models\CasoProducto;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

    $heatmapCasosPorProduct = $this->getHeatmapCasosPorProductData();

    return [
      'pie_formalizaciones_product_mes_usd'  => $pie_formalizaciones_product_mes_usd,
      'pie_formalizaciones_product_mes_crc'  => $pie_formalizaciones_product_mes_crc,
      'pie_formalizaciones_product_year_usd' => $pie_formalizaciones_product_year_usd,
      'pie_formalizaciones_product_year_crc' => $pie_formalizaciones_product_year_crc,

      'heatmap_product_usd' => $heatmapCasosPorProduct['data_usd'],
      'heatmap_product_crc' => $heatmapCasosPorProduct['data_crc'],
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

  public function getHeatmapCasosPorProductData(): array
  {
    // Obtener los datos base agrupados por banco y mes
    $baseQuery = Caso::select(
      'casos.product_id', // Agregamos el campo product_id
      DB::raw('YEAR(afecha_terminacion) AS year'),
      DB::raw('MONTH(afecha_terminacion) AS month'),
      DB::raw("
            SUM(
                CASE
                    WHEN casos.currency_id = " . Currency::DOLARES . " THEN 1
                    ELSE 0
                END
            ) AS total_usd
        "),
      DB::raw("
            SUM(
                CASE
                    WHEN casos.currency_id = " . Currency::COLONES . " THEN 1
                    ELSE 0
                END
            ) AS total_crc
        ")
    )
      ->whereYear('afecha_terminacion', '=', $this->year)
      ->with('producto') // Cargar relación con el producto
      ->groupBy('product_id', DB::raw('YEAR(afecha_terminacion)'), DB::raw('MONTH(afecha_terminacion)'))
      ->orderBy('product_id')
      ->orderBy('year')
      ->orderBy('month');

    // Ejecutar consulta y obtener resultados
    $results = $baseQuery->get();

    // Obtener todos los productos únicos
    $productIds = $results->pluck('product_id')->unique();
    $products = CasoProducto::whereIn('id', $productIds)->get()->keyBy('id');

    // Obtener todos los meses posibles en el rango
    $months = range(1, 12);

    // Preparar estructura para filas (bancos)
    $rows = [];
    foreach ($products as $product) {
      $rows[] = [
        'id' => (string)$product->id,
        'label' => $product->nombre
      ];
    }

    // Preparar estructura para columnas (meses)
    $columns = [];
    foreach ($months as $month) {
      $monthName = ucfirst(Carbon::createFromDate(null, $month, null)
        ->locale('es')
        ->shortMonthName);

      $columns[] = [
        'id' => (string)$month,
        'label' => $monthName
      ];
    }

    $columns[] = [
      'id' => -1,
      'label' => 'Total'
    ];

    // Preparar dataset con valores
    $dataset_usd = [];
    $dataset_crc = [];

    // Para cada banco y cada mes, buscar el valor
    foreach ($products as $productId => $product) {
      $total_usd = 0;
      $total_crc = 0;
      foreach ($columns as $col) {
        // Buscar el resultado correspondiente
        $result = $results->first(function ($item) use ($productId, $col) {
          return $item->product_id == $productId && $item->month == $col['id'];
        });

        // Determinar el valor a mostrar
        $value_usd = $result ? (float)$result->total_usd : 0;
        $value_crc = $result ? (float)$result->total_crc : 0;

        if ($col['id'] != -1) {
          $total_usd += $value_usd;
          $total_crc += $value_crc;
        } else {
          $value_usd = $total_usd;
          $value_crc = $total_crc;
        }

        $dataset_usd[] = [
          'rowid' => (string)$productId,
          'columnid' => (string)$col['id'],
          'value' => $value_usd,
          'displayvalue' => number_format($value_usd, 2)
        ];

        $dataset_crc[] = [
          'rowid' => (string)$productId,
          'columnid' => (string)$col['id'],
          'value' => $value_crc,
          'displayvalue' => number_format($value_crc, 2)
        ];
      }
    }

    // Calcular valor máximo para la escala de colores
    $maxValueUsd = $results->max('total_usd') ?? 1;
    $maxValueCrc = $results->max('total_crc') ?? 1;

    // Obtener el tema activo
    $theme = $this->chartTheme ?? 'zune';

    $caption_usd = 'Formalizaciones por Producto y Mes (USD)';
    $caption_crc = 'Formalizaciones por Producto y Mes (CRC)';
    $subCaption = [];

    $subCaption[] = "Año: {$this->year}";

    $data_usd = [
      'caption' => $caption_usd,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset_usd]],
      'colorrange' => $this->generateColorRange($maxValueUsd, $theme)
    ];

    $data_crc = [
      'caption' => $caption_crc,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset_crc]],
      'colorrange' => $this->generateColorRange($maxValueCrc, $theme)
    ];

    return [
      'data_usd' => $data_usd,
      'data_crc' => $data_crc
    ];
  }

  private function generateColorRange(float $maxValue, string $theme): array
  {
    // Definir paletas de colores para el heatmap por tema
    $themeColors = [
      'candy' => ['#36B5D8', '#F0DC46', '#F066AC', '#6EC85A', '#6E80CA'],
      'carbon' => ['#444444', '#666666', '#888888', '#aaaaaa', '#cccccc'],
      'fint' => ['#0075c2', '#1aaf5d', '#f2c500', '#f45b00', '#8e0000'],
      'fusion' => ['#5D62B5', '#29C3BE', '#F2726F', '#FFC533', '#62B58F'],
      'gammel' => ['#7CB5EC', '#434348', '#8EED7D', '#F7A35C', '#8085E9'],
      'ocean' => ['#04476c', '#4d998d', '#77be99', '#a7dca6', '#cef19a'],
      'umber' => ['#5D4037', '#7B1FA2', '#0288D1', '#388E3C', '#E64A19'],
      'zune' => ['#0075c2', '#1aaf5d', '#f2c500', '#f45b00', '#8e0000'],
    ];

    // Seleccionar paleta basada en el tema
    $colors = $themeColors[$theme] ?? $themeColors['zune'];

    // Si no hay datos o el valor máximo es 0, usar un rango simple
    if ($maxValue <= 0) {
      return [
        'gradient' => "1",
        'minvalue' => "0",
        'code' => $colors[0],
        'color' => [
          ['code' => $colors[0], 'minvalue' => "0", 'maxvalue' => "1"]
        ]
      ];
    }

    // Calcular los rangos de valores
    $range1 = $maxValue * 0.2;
    $range2 = $maxValue * 0.4;
    $range3 = $maxValue * 0.6;
    $range4 = $maxValue * 0.8;

    return [
      'gradient' => "1",
      'minvalue' => "0",
      'code' => $colors[0],
      'color' => [
        ['code' => $colors[0], 'minvalue' => "0", 'maxvalue' => $range1],
        ['code' => $colors[1], 'minvalue' => $range1, 'maxvalue' => $range2],
        ['code' => $colors[2], 'minvalue' => $range2, 'maxvalue' => $range3],
        ['code' => $colors[3], 'minvalue' => $range3, 'maxvalue' => $range4],
        ['code' => $colors[4], 'minvalue' => $range4, 'maxvalue' => $maxValue]
      ]
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
