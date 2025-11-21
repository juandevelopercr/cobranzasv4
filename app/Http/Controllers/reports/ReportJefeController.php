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

  public function casoCafsaActivo()
  {
    return view('content.reports.casos-cafsa-activos');
  }

  public function casoCafsaTerminado()
  {
    return view('content.reports.casos-cafsa-terminados');
  }

  public function casoCafsaIncobrable()
  {
    return view('content.reports.casos-cafsa-incobrables');
  }

  public function casoTerceroActivo()
  {
    return view('content.reports.casos-tercero-activos');
  }

  public function casoTerceroTerminado()
  {
    return view('content.reports.casos-tercero-terminados');
  }

  public function casoTerceroIncobrable()
  {
    return view('content.reports.casos-tercero-incobrables');
  }

  public function casoTerceroPago()
  {
    return view('content.reports.casos-tercero-pago');
  }

  public function casoTerceroPrescrito()
  {
    return view('content.reports.casos-tercero-prescrito');
  }

  public function casoCoociqueActivo()
  {
    return view('content.reports.casos-coocique-activos');
  }

  public function casoCoociqueTerminado()
  {
    return view('content.reports.casos-coocique-terminados');
  }

  public function casoCoociqueIncobrable()
  {
    return view('content.reports.casos-coocique-incobrables');
  }

  public function casoCoociquePago()
  {
    return view('content.reports.casos-coocique-pago');
  }

  public function casoCoociquePrescrito()
  {
    return view('content.reports.casos-coocique-prescrito');
  }
}
