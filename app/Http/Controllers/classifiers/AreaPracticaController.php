<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AreaPracticaController extends Controller
{
  public function index()
  {
    return view('content.classifiers.areas-practicas.index', []);
  }
}
