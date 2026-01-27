<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;

class ReportNotaDebitoController extends Controller
{
  public function index()
  {
    return view('content.reports.nota-debito');
  }
}
