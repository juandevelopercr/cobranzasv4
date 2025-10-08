<?php

namespace App\Http\Controllers\reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportIvaMas90Controller extends Controller
{
  public function index()
  {
    return view('content.reports.ivaMas90');
  }
}
