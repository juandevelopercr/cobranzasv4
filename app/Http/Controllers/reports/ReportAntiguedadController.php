<?php

namespace App\Http\Controllers\reports;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Exports\ComisionReport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportAntiguedadController extends Controller
{
  public function index()
  {
    return view('content.reports.antiguedad');
  }
}
