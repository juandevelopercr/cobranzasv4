<?php

namespace App\Http\Controllers\reports;

use App\Exports\ComprobantesReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportComprobantesController extends Controller
{
  public function index()
  {
    return view('content.reports.comprobantes');
  }

  public function downloadComprobantes(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '512M');
    set_time_limit(1000);

    return Excel::download(
        new ComprobantesReport($filters),
        'reporte-comprobantes-' . now()->format('YmdHis') . '.xlsx'
    );
  }
}
