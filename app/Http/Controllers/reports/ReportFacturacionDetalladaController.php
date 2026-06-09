<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturacionDetalladaReport;

class ReportFacturacionDetalladaController extends Controller
{
  public function index()
  {
    //$clients = Contact::all();
    return view('content.reports.facturacion-detallada');
  }

  public function downloadFacturacionDetallada(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '512M');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new FacturacionDetalladaReport($filters, 'REPORTE DE FACTURACIÓN DETALLADA' . $titleDate),
        'reporte-facturacion-detallada.xlsx'
    );
  }
}
