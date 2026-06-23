<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Exports\ComisionReport;
use App\Exports\FacturacionReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportFacturacionController extends Controller
{
  public function index()
  {
    //$clients = Contact::all();
    return view('content.reports.facturacion');
  }

  public function downloadFacturacion(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new FacturacionReport($filters, 'REPORTE DE FACTURACIÓN' . $titleDate),
        'reporte-facturacion.xlsx'
    );
  }
}
