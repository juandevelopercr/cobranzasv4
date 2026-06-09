<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Exports\ComisionReport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportComisionesController extends Controller
{
  public function index()
  {
    //$clients = Contact::all();
    return view('content.reports.comisiones');
  }

  public function downloadComisiones(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '512M');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new ComisionReport($filters, 'REPORTE DE COMISIONES' . $titleDate),
        'reporte-comisiones.xlsx'
    );
  }
}
