<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportComprobantesController extends Controller
{
  public function index()
  {
    return view('content.reports.comprobantes');
  }
}
