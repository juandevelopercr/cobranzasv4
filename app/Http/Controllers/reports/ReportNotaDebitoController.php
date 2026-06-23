<?php

namespace App\Http\Controllers\reports;

use App\Exports\NotaDebitoReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportNotaDebitoController extends Controller
{
  public function index()
  {
    return view('content.reports.nota-debito');
  }

  public function downloadNotaDebito(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new NotaDebitoReport($filters, 'REPORTE DE NOTAS DE DÉBITO' . $titleDate),
        'reporte-notas-debito.xlsx'
    );
  }
}
