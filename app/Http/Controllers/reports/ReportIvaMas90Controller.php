<?php

namespace App\Http\Controllers\reports;

use Illuminate\Http\Request;
use App\Exports\IvaMas90Report;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportIvaMas90Controller extends Controller
{
  public function index()
  {
    return view('content.reports.ivaMas90');
  }

  public function downloadIvaMas90(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new IvaMas90Report($filters, 'REPORTE DE FACTURACIÓN CON MAS DE 90 DIAS' . $titleDate),
        'reporte-facturacion-mas-90-dias.xlsx'
    );
  }
}
