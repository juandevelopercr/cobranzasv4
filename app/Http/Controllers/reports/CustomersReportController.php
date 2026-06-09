<?php

namespace App\Http\Controllers\reports;

use App\Exports\CustomersReport;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class CustomersReportController extends Controller
{
  public function index()
  {
    //$clients = Contact::all();
    return view('content.reports.customer');
  }

  public function downloadCustomers(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '512M');
    set_time_limit(1000);

    return Excel::download(
        new CustomersReport([], 'REPORTE DE CLIENTES'),
        'reporte-clientes.xlsx'
    );
  }
}
