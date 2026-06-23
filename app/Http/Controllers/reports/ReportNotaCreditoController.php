<?php

namespace App\Http\Controllers\reports;

use App\Exports\NotaCreditoReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportNotaCreditoController extends Controller
{
  public function index()
  {
    return view('content.reports.nota-credito');
  }

  public function downloadNotaCredito(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new NotaCreditoReport($filters, 'REPORTE DE NOTAS DE CRÉDITO' . $titleDate),
        'reporte-notas-credito.xlsx'
    );
  }
}
