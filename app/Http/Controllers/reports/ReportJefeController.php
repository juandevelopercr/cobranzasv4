<?php

namespace App\Http\Controllers\reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportJefeController extends Controller
{
  public function casos()
  {
    return view('content.reports.casos');
  }

  public function facturacionAbogado()
  {
    return view('content.reports.facturacion-abogados');
  }

  public function casoScotiabank()
  {
    return view('content.reports.casos-scotiabank');
  }

  public function casoScotiabankBch()
  {
    return view('content.reports.casos-scotiabank-bch');
  }
}
