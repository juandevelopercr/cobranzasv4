<?php

namespace App\Http\Controllers\reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportIvaController extends Controller
{
  public function index()
  {
    return view('content.reports.iva');
  }
}
