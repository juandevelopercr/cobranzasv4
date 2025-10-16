<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CasoServicioController extends Controller
{
  public function index()
  {
    return view('content.classifiers.casos-servicios.index', []);
  }
}
