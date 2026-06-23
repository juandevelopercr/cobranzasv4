<?php

namespace App\Http\Controllers\reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\CasoScotiabankReport;
use App\Exports\CasoScotiabankBchReport;
use App\Exports\CasoReport;
use App\Exports\FacturacionAbogadoReport;
use App\Exports\CasoBancoGeneralReport;
use App\Exports\CasoBacGestionadaReport;
use App\Exports\CasoBacTerminadaReport;
use App\Exports\CasoLafiseActivoReport;
use App\Exports\CasoLafiseTerminadoReport;
use App\Exports\CasoLafiseIncobrableReport;
use App\Exports\CasoDaviviendaPagoCEReport;
use App\Exports\CasoDaviviendaPagoTCReport;
use App\Exports\CasoDaviviendaFileMasterReport;
use App\Exports\CasoDaviviendaMatrizReport;
use App\Exports\CasoCafsaActivoReport;
use App\Exports\CasoCafsaTerminadoReport;
use App\Exports\CasoCafsaIncobrableReport;
use App\Exports\CasoTerceroActivoReport;
use App\Exports\CasoTerceroTerminadoReport;
use App\Exports\CasoTerceroIncobrableReport;
use App\Exports\CasoTerceroPagoReport;
use App\Exports\CasoTerceroPrescritoReport;
use App\Exports\CasoCoociqueActivoReport;
use App\Exports\CasoCoociqueTerminadoReport;
use App\Exports\CasoCoociqueIncobrableReport;
use App\Exports\CasoCoociquePagoReport;
use App\Exports\CasoCoociquePrescritoReport;
use App\Exports\CasoCoocique2ActivoReport;
use App\Exports\CasoCoocique2TerminadoReport;
use App\Exports\CasoCoocique2IncobrableReport;
use App\Exports\CasoCoocique2PagoReport;
use App\Exports\CasoCoocique2PrescritoReport;
use App\Exports\CasoCarteraCompradaReport;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportJefeController extends Controller
{
  public function casos()
  {
    return view('content.reports.casos');
  }

  public function facturacionAbogado()
  {
    return view('content.reports.facturacion-abogados');
  }

  public function casoScotiabank()
  {
    return view('content.reports.casos-scotiabank');
  }

  public function casoScotiabankBch()
  {
    return view('content.reports.casos-scotiabank-bch');
  }

  public function casoBancoGeneral()
  {
    return view('content.reports.casos-banco-general');
  }

  public function casoBacCuentasGestionada()
  {
    return view('content.reports.casos-bac-cuentas-gestionadas');
  }

  public function casoBacCuentasTerminada()
  {
    return view('content.reports.casos-bac-cuentas-terminadas');
  }

  public function casoLafiseActivo()
  {
    return view('content.reports.casos-lafise-activos');
  }

  public function casoLafiseTerminado()
  {
    return view('content.reports.casos-lafise-terminados');
  }

  public function casoLafiseIncobrable()
  {
    return view('content.reports.casos-lafise-incobrables');
  }

  public function casoDaviviendaPagoCe()
  {
    return view('content.reports.casos-davivienda-pago-ce');
  }

  public function casoDaviviendaPagoTc()
  {
    return view('content.reports.casos-davivienda-pago-tc');
  }

  public function casoDaviviendaFileMaster()
  {
    return view('content.reports.casos-davivienda-file-master');
  }

  public function casoDaviviendaMatriz()
  {
    return view('content.reports.casos-davivienda-matriz');
  }

  public function casoCafsaActivo()
  {
    return view('content.reports.casos-cafsa-activos');
  }

  public function casoCafsaTerminado()
  {
    return view('content.reports.casos-cafsa-terminados');
  }

  public function casoCafsaIncobrable()
  {
    return view('content.reports.casos-cafsa-incobrables');
  }

  public function casoTerceroActivo()
  {
    return view('content.reports.casos-tercero-activos');
  }

  public function casoTerceroTerminado()
  {
    return view('content.reports.casos-tercero-terminados');
  }

  public function casoTerceroIncobrable()
  {
    return view('content.reports.casos-tercero-incobrables');
  }

  public function casoTerceroPago()
  {
    return view('content.reports.casos-tercero-pago');
  }

  public function casoTerceroPrescrito()
  {
    return view('content.reports.casos-tercero-prescrito');
  }

  public function casoCoociqueActivo()
  {
    return view('content.reports.casos-coocique-activos');
  }

  public function casoCoociqueTerminado()
  {
    return view('content.reports.casos-coocique-terminados');
  }

  public function casoCoociqueIncobrable()
  {
    return view('content.reports.casos-coocique-incobrables');
  }

  public function casoCoociquePago()
  {
    return view('content.reports.casos-coocique-pago');
  }

  public function casoCoociquePrescrito()
  {
    return view('content.reports.casos-coocique-prescrito');
  }

  // Coocique2
  public function casoCoocique2Activo()
  {
    return view('content.reports.casos-coocique2-activos');
  }

  public function casoCoocique2Terminado()
  {
    return view('content.reports.casos-coocique2-terminados');
  }

  public function casoCoocique2Incobrable()
  {
    return view('content.reports.casos-coocique2-incobrables');
  }

  public function casoCoocique2Pago()
  {
    return view('content.reports.casos-coocique2-pago');
  }

  public function casoCoocique2Prescrito()
  {
    return view('content.reports.casos-coocique2-prescrito');
  }

  public function casoCarteraComprada()
  {
    return view('content.reports.casos-cartera-comprada');
  }

  public function downloadDavibank(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoScotiabankReport($filters, 'REPORTE DE CASOS DE DAVIBANK' . $titleDate),
        'reporte-casos-davibank.xlsx'
    );
  }

  public function downloadDavibankBch(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoScotiabankBchReport($filters, 'REPORTE DE CASOS DE DAVIBANK BCH' . $titleDate),
        'reporte-casos-davibank-bch.xlsx'
    );
  }

  public function downloadCasos(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoReport($filters, 'REPORTE DE CASOS' . $titleDate),
        'reporte-casos.xlsx'
    );
  }

  public function downloadFacturacionAbogado(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new FacturacionAbogadoReport($filters, 'REPORTE DE FACTURACION POR ABOGADO' . $titleDate),
        'reporte-facturacion-abogado.xlsx'
    );
  }

  public function downloadCasoBancoGeneral(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoBancoGeneralReport($filters, 'REPORTE DE CASOS DE BANCO GENERAL' . $titleDate),
        'reporte-casos-banco-general.xlsx'
    );
  }

  public function downloadCasoBacCuentasGestionadas(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoBacGestionadaReport($filters, 'REPORTE DE CASOS DE BAC CUENTAS GESTIONADAS' . $titleDate),
        'reporte-casos-back-cuentas-gestionadas.xlsx'
    );
  }

  public function downloadCasoBacCuentasTerminadas(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoBacTerminadaReport($filters, 'REPORTE DE CASOS DE BAC CUENTAS TERMINADAS' . $titleDate),
        'reporte-casos-back-cuentas-terminadas.xlsx'
    );
  }

  public function downloadCasoLafiseActivos(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoLafiseActivoReport($filters, 'REPORTE DE CASOS DE LAFISE ACTIVOS' . $titleDate),
        'reporte-casos-lafise-activos.xlsx'
    );
  }

  public function downloadCasoLafiseTerminados(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoLafiseTerminadoReport($filters, 'REPORTE DE CASOS DE LAFISE TERMINADOS' . $titleDate),
        'reporte-casos-lafise-terminados.xlsx'
    );
  }

  public function downloadCasoLafiseIncobrables(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoLafiseIncobrableReport($filters, 'REPORTE DE CASOS DE LAFISE INCOBRABLE' . $titleDate),
        'reporte-casos-lafise-incobrables.xlsx'
    );
  }

  public function downloadCasoDaviviendaPagoCe(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoDaviviendaPagoCEReport($filters, 'REPORTE DE CASOS DE DAVIVIENDA PAGO CE' . $titleDate),
        'reporte-casos-davivienda-pago-ce.xlsx'
    );
  }

  public function downloadCasoDaviviendaPagoTc(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoDaviviendaPagoTCReport($filters, 'REPORTE DE CASOS DE DAVIVIENDA PAGO TC' . $titleDate),
        'reporte-casos-davivienda-pago-tc.xlsx'
    );
  }

  public function downloadCasoDaviviendaFileMaster(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoDaviviendaFileMasterReport($filters, 'REPORTE DE CASOS DE DAVIVIENDA FILE MASTER' . $titleDate),
        'reporte-casos-davivienda-file-master.xlsx'
    );
  }

  public function downloadCasoDaviviendaMatriz(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoDaviviendaMatrizReport($filters, 'REPORTE DE CASOS DE DAVIVIENDA MATRIZ TC y CE' . $titleDate),
        'reporte-casos-davivienda-matriz-tc-ce.xlsx'
    );
  }

  public function downloadCasoCafsaActivos(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);

    return Excel::download(
        new CasoCafsaActivoReport($filters, 'REPORTE DE CASOS DE CAFSA ACTIVOS'),
        'reporte-casos-cafsa-activos.xlsx'
    );
  }

  public function downloadCasoCafsaTerminados(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);

    return Excel::download(
        new CasoCafsaTerminadoReport($filters, 'REPORTE DE CASOS DE CAFSA TERMINADOS'),
        'reporte-casos-cafsa-terminados.xlsx'
    );
  }

  public function downloadCasoCafsaIncobrables(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);

    return Excel::download(
        new CasoCafsaIncobrableReport($filters, 'REPORTE DE CASOS DE CAFSA INCOBRABLE'),
        'reporte-casos-cafsa-incobrables.xlsx'
    );
  }

  public function downloadCasoTerceroActivos(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoTerceroActivoReport($filters, 'REPORTE DE CASOS DE TERCERO ACTIVOS' . $titleDate),
        'reporte-casos-tercero-activos.xlsx'
    );
  }

  public function downloadCasoTerceroTerminados(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoTerceroTerminadoReport($filters, 'REPORTE DE CASOS DE TERCERO TERMINADOS' . $titleDate),
        'reporte-casos-tercero-terminados.xlsx'
    );
  }

  public function downloadCasoTerceroIncobrables(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoTerceroIncobrableReport($filters, 'REPORTE DE CASOS DE TERCERO INCOBRABLE' . $titleDate),
        'reporte-casos-tercero-incobrables.xlsx'
    );
  }

  public function downloadCasoTerceroPago(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoTerceroPagoReport($filters, 'REPORTE DE CASOS DE TERCERO ARREGLO DE PAGO' . $titleDate),
        'reporte-casos-tercero-pago.xlsx'
    );
  }

  public function downloadCasoTerceroPrescrito(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoTerceroPrescritoReport($filters, 'REPORTE DE CASOS DE TERCERO PRESCRITO' . $titleDate),
        'reporte-casos-tercero-prescrito.xlsx'
    );
  }

  public function downloadCasoCoociqueActivos(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoociqueActivoReport($filters, 'REPORTE DE CASOS DE COOCIQUE ACTIVOS' . $titleDate),
        'reporte-casos-coocique-activos.xlsx'
    );
  }

  public function downloadCasoCoociqueTerminados(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoociqueTerminadoReport($filters, 'REPORTE DE CASOS DE COOCIQUE TERMINADOS' . $titleDate),
        'reporte-casos-coocique-terminados.xlsx'
    );
  }

  public function downloadCasoCoociqueIncobrables(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoociqueIncobrableReport($filters, 'REPORTE DE CASOS DE COOCIQUE INCOBRABLES' . $titleDate),
        'reporte-casos-coocique-incobrables.xlsx'
    );
  }

  public function downloadCasoCoociquePago(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoociquePagoReport($filters, 'REPORTE DE CASOS DE COOCIQUE ARREGLO DE PAGO' . $titleDate),
        'reporte-casos-coocique-pago.xlsx'
    );
  }

  public function downloadCasoCoociquePrescrito(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoociquePrescritoReport($filters, 'REPORTE DE CASOS DE COOCIQUE PRESCRITO' . $titleDate),
        'reporte-casos-coocique-prescrito.xlsx'
    );
  }

  public function downloadCasoCoocique2Activos(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoocique2ActivoReport($filters, 'REPORTE DE CASOS DE COOCIQUE 2 ACTIVOS' . $titleDate),
        'reporte-casos-coocique2-activos.xlsx'
    );
  }

  public function downloadCasoCoocique2Terminados(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoocique2TerminadoReport($filters, 'REPORTE DE CASOS DE COOCIQUE 2 TERMINADOS' . $titleDate),
        'reporte-casos-coocique2-terminados.xlsx'
    );
  }

  public function downloadCasoCoocique2Incobrables(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoocique2IncobrableReport($filters, 'REPORTE DE CASOS DE COOCIQUE 2 INCOBRABLES' . $titleDate),
        'reporte-casos-coocique2-incobrables.xlsx'
    );
  }

  public function downloadCasoCoocique2Pago(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoocique2PagoReport($filters, 'REPORTE DE CASOS DE COOCIQUE 2 ARREGLO DE PAGO' . $titleDate),
        'reporte-casos-coocique2-pago.xlsx'
    );
  }

  public function downloadCasoCoocique2Prescrito(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCoocique2PrescritoReport($filters, 'REPORTE DE CASOS DE COOCIQUE 2 PRESCRITO' . $titleDate),
        'reporte-casos-coocique2-prescrito.xlsx'
    );
  }

  public function downloadCasoCarteraComprada(string $key)
  {
    $filters = Cache::pull($key);
    if (!is_array($filters)) {
        abort(404, 'Enlace de descarga inválido o expirado.');
    }

    ini_set('memory_limit', '-1');
    set_time_limit(1000);
    $titleDate = !empty($filters['filter_date']) ? ' ' . $filters['filter_date'] : '';

    return Excel::download(
        new CasoCarteraCompradaReport($filters, 'REPORTE DE CASOS DE CARTERA COMPRADA' . $titleDate),
        'reporte-casos-cartera-comprada.xlsx'
    );
  }
}
