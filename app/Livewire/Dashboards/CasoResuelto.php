<?php

namespace App\Livewire\Dashboards;

use App\Models\Bank;
use App\Models\Caratula;
use App\Models\Caso;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class CasoResuelto extends Component
{
  public $years = [];      // Lista de años disponibles para el filtro
  public $months = [];      // Lista de meses disponibles para el filtro
  public $year;            // Año del filtro (por defecto, año anterior al actual)
  public $month;        // Mes del filtro (por defecto, mes actual)
  public $chartTheme = 'zune'; // Valor por defecto
  public $chartsPerRow = 1; // por defecto 2 gráficos por fila

  public function mount()
  {
    // Obtener años únicos desde la columna created_at
    $this->years = Caso::select(DB::raw('YEAR(pfecha_asignacion_caso) as year'))
      ->whereNotNull('pfecha_asignacion_caso')
      ->distinct()
      ->orderBy('year', 'asc')
      ->pluck('year')
      ->toArray();

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

    // Año actual y anterior como valores por defecto
    $this->year =  Carbon::now()->year;
    $this->month =  Carbon::now()->format('m');

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
    $mscolumn3d_resueltos_year = $this->getDataMsColumn3dCasosResueltosYear();
    $mscolumn3d_cargatrabajo = $this->getDataMsColumn3dCargatRabajo();

    return [
      'mscolumn3d_resueltos_year' => $mscolumn3d_resueltos_year,
      'mscolumn3d_cargatrabajo' => $mscolumn3d_cargatrabajo
    ];
  }

  public function getDataMsColumn3dCasosResueltosYear(): array
  {
    $query = Caso::select(
      'users.name as abogado',
      DB::raw('COUNT(*) AS total')
    )
      ->join('users', 'casos.abogado_id', '=', 'users.id')
      ->whereNotNull('pfecha_asignacion_caso')
      ->whereYear('pfecha_asignacion_caso', '=', $this->year)
      ->where(function ($q) {
        $q->whereHas('transactions')
          ->orWhereHas('transactionLines');
      });      

    $data = $query
      ->groupBy('users.name')
      ->orderBy('total')
      ->get();

    // Crear estructura para FusionCharts
    $categories = $data->map(function ($item) {
      return ['label' => $item->abogado];
    })->toArray();

    $values = $data->map(function ($item) {
      return ['value' => (int)$item->total];
    })->toArray();

    $estructura = [
      'categories' => [['category' => $categories]],
      'dataset' => [
        [
          'seriesname' => 'Firmas',
          'data' => $values
        ]
      ]
    ];

    $caption = 'Casos resueltos por abogados';
    $subCaptionParts = [];

    $subCaptionParts[] = "$this->year";

    return [
      'data' => $estructura,
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaptionParts),
    ];
  }

  public function getDataMsColumn3dCargatRabajo(): array
  {
      $currentMonth = (int) $this->month;   // Asegurar entero
      $currentYear  = (int) $this->year;

      $fecha = Carbon::create($currentYear, $currentMonth, 1);
      $fechaAnterior = $fecha->copy()->subMonth();

      $previousMonth = $fechaAnterior->month; // 1–12
      $previousYear  = $fechaAnterior->year;  // Cambia si pasó al año anterior

      /* ======================================================
      *   CONSULTA MES ACTUAL
      * ====================================================== */
      $query_month_actual = Caso::select(
          'users.name as abogado',
          DB::raw('COUNT(*) AS total')
      )
          ->join('users', 'casos.abogado_id', '=', 'users.id')
          ->whereNotNull('pfecha_asignacion_caso')
          ->whereYear('pfecha_asignacion_caso', '=', $currentYear)
          ->whereMonth('pfecha_asignacion_caso', '=', $currentMonth)
          ->where(function ($q) {
              $q->whereHas('transactions')
                ->orWhereHas('transactionLines');
          });          

      $data_month_actual = $query_month_actual
          ->groupBy('users.name')
          ->orderBy('total')
          ->get()
          ->keyBy('abogado'); // ← la llave del resultado es el nombre del abogado


      /* ======================================================
      *   CONSULTA MES ANTERIOR  (CORRECTA)
      * ====================================================== */
      $query_month_previous = Caso::select(
          'users.name as abogado',
          DB::raw('COUNT(*) AS total')
      )
          ->join('users', 'casos.abogado_id', '=', 'users.id')
          ->whereNotNull('pfecha_asignacion_caso')
          ->whereYear('pfecha_asignacion_caso', '=', $previousYear)
          ->whereMonth('pfecha_asignacion_caso', '=', $previousMonth)
          ->where(function ($q) {
              $q->whereHas('transactions')
                ->orWhereHas('transactionLines');
          });          

      $data_month_previous = $query_month_previous
          ->groupBy('users.name')
          ->orderBy('total')
          ->get()
          ->keyBy('abogado');


      /* ======================================================
      *   COMBINAR RESULTADOS
      * ====================================================== */
      $results = User::select('id', 'name')
          ->get()
          ->map(function ($user) use ($data_month_actual, $data_month_previous) {

              return (object) [
                  'abogado' => $user->name,
                  'casos_month_actual'   => $data_month_actual->get($user->name)->total ?? 0,
                  'casos_month_previous' => $data_month_previous->get($user->name)->total ?? 0,
              ];
          })
          ->filter(fn($item) => $item->casos_month_actual > 0 || $item->casos_month_previous > 0)
          ->sortBy('abogado');


      /* ======================================================
      *   FORMAR LEYENDA
      * ====================================================== */

      // Convertir meses a texto (Enero, Febrero...)
      $monthNameCurrent  = ucfirst(Carbon::create()->month($currentMonth)->locale('es')->monthName);
      $monthNamePrevious = ucfirst(Carbon::create()->month($previousMonth)->locale('es')->monthName);

      $caption = 'Casos Resueltos por Abogado';

      $subCaption = "{$monthNameCurrent} {$currentYear} | {$monthNamePrevious} {$previousYear}";

      $captionCurrent  = $monthNameCurrent. ' '. $currentYear;
      $captionPrevious = $monthNamePrevious. ' '. $previousYear;

      return [
          'data' => $this->getEstructuraGraficoMscolumn3d($results, $captionCurrent, $captionPrevious),
          'caption' => $caption,
          'subCaption' => $subCaption,
      ];
  }

  public function getEstructuraGraficoMscolumn3d($results, $captionCurrent, $captionPrevious)
  {
    $categories = [];
    $resueltosPreviousData = [];
    $resueltosCurrentData = [];

    // Recopilar datos por abogado
    foreach ($results as $registro) {
      $categories[] = ['label' => $registro->abogado];
      $resueltosPreviousData[] = ['value' => (int)$registro->casos_month_previous];
      $resueltosCurrentData[] = ['value' => (int)$registro->casos_month_actual];
    }

    // Construir dataset para FusionCharts
    $dataset = [
      [
        'seriesname' => 'Casos Resueltos '. $captionPrevious,
        'data' => $resueltosPreviousData
      ],
      [
        'seriesname' => 'Casos Resueltos '. $captionCurrent,
        'data' => $resueltosCurrentData
      ]
    ];

    return [
      'categories' => [['category' => $categories]],
      'dataset' => $dataset
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.casos-resueltos');
  }
}
