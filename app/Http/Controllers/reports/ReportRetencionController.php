<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Exports\RetencionReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportRetencionController extends Controller
{
  public function index()
  {
    return view('content.reports.retencion');
  }

  public function downloadRetencion(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '512M');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new RetencionReport($filters, 'REPORTE DE FACTURACIÓN CON RETENCION DEL 2%' . $titleDate),
        'reporte-facturacion-con-retencion.xlsx'
    );
  }
}
