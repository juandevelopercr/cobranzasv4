<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Exports\EstadoCuentaReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportEstadoCuentaController extends Controller
{
  public function index()
  {
    return view('content.reports.estado-cuenta');
  }

  public function downloadEstadoCuenta(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new EstadoCuentaReport($filters, 'REPORTE DE ESTADO DE CUENTA' . $titleDate),
        'reporte-estado-cuenta.xlsx'
    );
  }
}
