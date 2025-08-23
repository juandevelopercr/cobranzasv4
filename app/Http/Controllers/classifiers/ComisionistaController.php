<?php

namespace App\Http\Controllers\classifiers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComisionistaController extends Controller
{
  public function index()
  {
    return view('content.classifiers.comisionistas.index', []);
  }
}
