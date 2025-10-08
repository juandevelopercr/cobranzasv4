<?php

namespace App\Http\Controllers\reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportIva90Controller extends Controller
{
  public function index()
  {
    return view('content.reports.iva90');
  }
}
