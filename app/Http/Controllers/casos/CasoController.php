<?php

namespace App\Http\Controllers\casos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CasoController extends Controller
{
  public function scotiabank()
  {
    return view('content.casos.scotiabank', []);
  }

  public function scotiabankBch()
  {
    return view('content.casos.scotiabank-bch', []);
  }

  public function bac()
  {
    return view('content.casos.bac', []);
  }

  public function bancoGeneral()
  {
    return view('content.casos.banco-general', []);
  }

  public function terceros()
  {
    return view('content.casos.terceros', []);
  }

  public function coocique()
  {
    return view('content.casos.coocique', []);
  }

  public function davivienda()
  {
    return view('content.casos.davivienda', []);
  }

  public function lafise()
  {
    return view('content.casos.lafise', []);
  }

  public function cafsa()
  {
    return view('content.casos.cafsa', []);
  }
}
