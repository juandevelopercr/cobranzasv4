<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;

class ReportNotaCreditoController extends Controller
{
  public function index()
  {
    return view('content.reports.nota-credito');
  }
}
