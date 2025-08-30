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
}
