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

class FormalizacionBanco extends Component
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

    $pie_formalizaciones_bank_mes_usd  = $this->getDataPieFormalizacionesByBankAndMes(Currency::DOLARES);
    $pie_formalizaciones_bank_mes_crc  = $this->getDataPieFormalizacionesByBankAndMes(Currency::COLONES);

    $pie_formalizaciones_bank_year_usd  = $this->getDataPieFormalizacionesByBankAndYear(Currency::DOLARES);
    $pie_formalizaciones_bank_year_crc  = $this->getDataPieFormalizacionesByBankAndYear(Currency::COLONES);

    $heatmapCurrency = $this->getHeatmapCurrencyByMonthData();

    return [
      'pie_formalizaciones_bank_mes_usd'  => $pie_formalizaciones_bank_mes_usd,
      'pie_formalizaciones_bank_mes_crc'  => $pie_formalizaciones_bank_mes_crc,
      'pie_formalizaciones_bank_year_usd' => $pie_formalizaciones_bank_year_usd,
      'pie_formalizaciones_bank_year_crc' => $pie_formalizaciones_bank_year_crc,

      'heatmapCurrency' => $heatmapCurrency,
    ];
  }

  public function getDataPieFormalizacionesByBankAndMes($currency): array
  {
    $query = Caso::select(
      'banks.name AS bank',
      DB::raw('COUNT(*) AS total')
    )
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->where('casos.currency_id', $currency)
      ->whereNotNull('afecha_terminacion')
      ->whereYear('afecha_terminacion', '=', $this->year)
      ->whereMonth('afecha_terminacion', '=', $this->month);

    $result = $query
      ->groupBy(DB::raw('banks.name'))
      ->orderBy('banks.name')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => $item->bank,
        'value' => $item->total
      ];
    })->toArray();

    $caption = 'Formalizaciones Bancos ' . (($currency == Currency::DOLARES) ? 'USD' : 'CRC');
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

  public function getDataPieFormalizacionesByBankAndYear($currency): array
  {
    $query = Caso::select(
      'banks.name AS bank',
      DB::raw('COUNT(*) AS total')
    )
      ->join('banks', 'casos.bank_id', '=', 'banks.id')
      ->where('casos.currency_id', $currency)
      ->whereNotNull('afecha_terminacion')
      ->whereYear('afecha_terminacion', '=', $this->year);

    $result = $query
      ->groupBy(DB::raw('banks.name'))
      ->orderBy('banks.name')
      ->get();

    $data = $result->map(function ($item) {
      return [
        'label' => $item->bank,
        'value' => $item->total
      ];
    })->toArray();

    $caption = 'Formalizaciones Bancos ' . (($currency == Currency::DOLARES) ? 'USD' : 'CRC');
    $subCaption = [];

    $subCaption[] = "Año {$this->year}";

    return [
      'caption'    => $caption,
      'subCaption' => implode('  ', $subCaption),
      'data' => $data
    ];
  }

  public function getHeatmapCurrencyByMonthData(): array
  {
    // Obtener los datos base de la misma consulta que el gráfico de barras
    $baseQuery = Caso::select(
      DB::raw('currencies.code AS currency'),
      DB::raw('MONTH(afecha_terminacion) AS month'),
      DB::raw('COUNT(*) AS total')
    )
    ->whereYear('afecha_terminacion', '=', $this->year);

    // Ejecutar consulta y obtener resultados
    $results = $baseQuery
      ->join('currencies', 'currencies.id', '=', 'casos.currency_id')
      ->groupBy(DB::raw('currencies.code, MONTH(afecha_terminacion)'))
      ->orderBy('currencies.code')
      ->orderBy('month')
      ->get();

    // Obtener todos los años y meses posibles en el rango
    $currencies = Currency::whereIn('id', [1,16])->orderBy('code', 'ASC')->get();
    $months = range(1, 12);

    // Preparar estructura para filas (años)
    $rows = [];
    foreach ($currencies as $currency) {
      $rows[] = [
        'id' => (string)$currency->code,
        'label' => (string)$currency->code
      ];
    }

    // Preparar estructura para columnas (meses)
    $columns = [];
    foreach ($months as $month) {

      // Forma alternativa para nombre abreviado:
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
    $dataset = [];
    foreach ($currencies as $currency) {
      $total = 0;
      foreach ($columns as $col) {
        // Buscar el resultado correspondiente
        $result = $results->first(function ($item) use ($currency, $col) {
          return $item->currency == $currency->code && $item->month == $col['id'];
        });

        // Determinar el valor a mostrar
        $value = $result ? (float)$result->total : 0;

        if ($col['id'] != -1) {
          $total += $value;
        } else {
          $value = $total;
        }

        $dataset[] = [
          'rowid' => (string)$currency->code,
          'columnid' => (string)$col['id'],
          'value' => $value,
          'displayvalue' => (int)$value
        ];
      }
    }

    // Calcular valor máximo para la escala de colores
    $maxValue = $results->max('total') ?? 1;

    // Obtener el tema activo (asumiendo que está disponible)
    $theme = $this->chartTheme ?? 'zune';

    $caption = 'Casos por monedas';
    $subCaption = [];

    $subCaption[] = "Año: {$this->year}";


    $data = [
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaption),
      'rows' => ['row' => $rows],
      'columns' => ['column' => $columns],
      'dataset' => [['data' => $dataset]],
      'colorrange' => $this->generateColorRange($maxValue, $theme)
    ];

    return $data;
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
    return view('livewire.dashboards.formalizaciones-bancos');
  }
}
