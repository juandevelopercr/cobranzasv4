<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CasoExpectativaController extends Controller
{
  public function index()
  {
    return view('content.classifiers.casos-expectativas.index', []);
  }
}
