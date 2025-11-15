<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GraficoController extends Controller
{
  public function formalizacionesBanco()
  {
    return view('dashboard.formalizaciones-bancos', []);
  }

  public function formalizacionesProducto()
  {
    return view('dashboard.formalizaciones-productos', []);
  }

  public function honorariosMes()
  {
    return view('dashboard.honorarios-mes', []);
  }

  public function honorariosAnno()
  {
    return view('dashboard.honorarios-anno', []);
  }

  public function cargaTrabajo()
  {
    return view('dashboard.carga-trabajo', []);
  }

  public function casosResuelto()
  {
    return view('dashboard.casos-resueltos', []);
  }

  public function controlMensual()
  {
    return view('dashboard.control-mensual', []);
  }

  public function formalizaciones()
  {
    return view('dashboard.formalizaciones', []);
  }

  public function tiposCaratulas()
  {
    return view('dashboard.tipos-caratulas', []);
  }

  public function volumenBanco()
  {
    return view('dashboard.volumen-banco', []);
  }

  public function facturacionAbogado()
  {
    return view('dashboard.facturacion-abogado', []);
  }

  public function tiposGarantias()
  {
    return view('dashboard.tipos-garantias', []);
  }

  public function facturacionCentroCosto()
  {
    return view('dashboard.facturacion-centro-costo', []);
  }
}
