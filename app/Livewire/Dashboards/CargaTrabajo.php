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

class CargaTrabajo extends Component
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
    $this->years = Caso::select(DB::raw('YEAR(fecha_creacion) as year'))
      ->whereNotNull('fecha_creacion')
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
    $mscolumn3d_firmas = $this->getDataMsColumn3dFirmas();
    $mscolumn3d_cargatrabajo = $this->getDataMsColumn3dCargatRabajo();

    return [
      'mscolumn3d_firmas' => $mscolumn3d_firmas,
      'mscolumn3d_cargatrabajo' => $mscolumn3d_cargatrabajo
    ];
  }

  public function getDataMsColumn3dFirmas(): array
  {
    $query = Caso::select(
      'users.name as abogado',
      DB::raw('COUNT(*) AS total')
    )
      ->join('users', 'casos.abogado_id', '=', 'users.id')
      ->whereNotNull('pfecha_asignacion_caso')
      ->whereYear('pfecha_asignacion_caso', '=', $this->year)
      ->whereMonth('pfecha_asignacion_caso', '=', $this->month);      

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

    $caption = 'Firmas por abogados';
    $subCaptionParts = [];

    $monthName = Carbon::createFromDate($this->year, $this->month, 1)
      ->locale('es')
      ->monthName;

    if (!empty($this->departmentName)) {
      $subCaptionParts[] = "Departamento: {$this->departmentName}";
    }

    $subCaptionParts[] = "{$monthName} de $this->year";

    return [
      'data' => $estructura,
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaptionParts),
    ];
  }

  public function getDataMsColumn3dCargatRabajo(): array
  {
    $year = $this->year;
    $caratulaType = Caratula::CARATULA;

    // Precalcular los conteos por separado
    $firmas = Caso::select('abogado_id', DB::raw('COUNT(DISTINCT id) as count'))
      ->whereNotNull('pfecha_asignacion_caso')
      ->whereYear('pfecha_asignacion_caso', $year)
      ->groupBy('abogado_id')
      ->get()
      ->keyBy('abogado_id');

    // Combinar los resultados
    $results = User::select('id', 'name')
      ->get()
      ->map(function ($user) use ($firmas) {
        return (object) [
          'abogado' => $user->name,
          'firmas' => $firmas->get($user->id)?->count ?? 0,
        ];
      })
      ->filter(fn($item) => $item->firmas > 0)
      ->sortBy('abogado');

    $estructura = $this->getEstructuraGraficoMscolumn3d($results);

    $caption = 'Carga de trabajo por abogado';
    $subCaptionParts = [];

    $subCaptionParts[] = "{$year}";

    return [
      'data' => $estructura,
      'caption' => $caption,
      'subCaption' => implode(' | ', $subCaptionParts)
    ];
  }

  public function getEstructuraGraficoMscolumn3d($results)
  {
    $categories = [];
    $firmasData = [];

    // Recopilar datos por abogado
    foreach ($results as $registro) {
      $categories[] = ['label' => $registro->abogado];
      $firmasData[] = ['value' => (int)$registro->firmas];
    }

    // Construir dataset para FusionCharts
    $dataset = [
      [
        'seriesname' => 'Firmas',
        'data' => $firmasData
      ],
    ];

    return [
      'categories' => [['category' => $categories]],
      'dataset' => $dataset
    ];
  }

  public function render()
  {
    return view('livewire.dashboards.carga-trabajo');
  }
}
