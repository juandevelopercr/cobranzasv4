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

  public function casoBacCuentasGestionada()
  {
    return view('content.reports.casos-bac-cuentas-gestionadas');
  }

  public function casoBacCuentasTerminada()
  {
    return view('content.reports.casos-bac-cuentas-terminadas');
  }

  public function casoLafiseActivo()
  {
    return view('content.reports.casos-lafise-activos');
  }

  public function casoLafiseTerminado()
  {
    return view('content.reports.casos-lafise-terminados');
  }

  public function casoLafiseIncobrable()
  {
    return view('content.reports.casos-lafise-incobrables');
  }

  public function casoDaviviendaPagoCe()
  {
    return view('content.reports.casos-davivienda-pago-ce');
  }

  public function casoDaviviendaPagoTc()
  {
    return view('content.reports.casos-davivienda-pago-tc');
  }

  public function casoDaviviendaFileMaster()
  {
    return view('content.reports.casos-davivienda-file-master');
  }

  public function casoDaviviendaMatriz()
  {
    return view('content.reports.casos-davivienda-matriz');
  }
}
