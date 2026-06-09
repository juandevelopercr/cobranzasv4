<?php

namespace App\Http\Controllers\reports;

use Illuminate\Http\Request;
use App\Exports\IvaReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportIvaController extends Controller
{
  public function index()
  {
    return view('content.reports.iva');
  }

  public function downloadIva(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '512M');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new IvaReport($filters, 'REPORTE DE FACTURACIÓN CON PAGO' . $titleDate),
        'reporte-facturacion-con-pago.xlsx'
    );
  }
}
