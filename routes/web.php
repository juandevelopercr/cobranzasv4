<?php

use App\Models\Bank;
use App\Models\User;
use App\Mail\TestMail;
use Livewire\Livewire;
use \App\Models\Contact;
use App\Models\Department;
use App\Models\Movimiento;
use App\Models\Comprobante;
use Illuminate\Http\Request;
use App\Http\Controllers\Home;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\pages\Page2;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\UserController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\casos\CasoController;
use App\Http\Controllers\billing\InvoiceController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\billing\ProformaController;
use App\Http\Controllers\classifiers\BankController;
use App\Http\Controllers\products\ProductController;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\dashboard\GraficoController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\reports\ReportIvaController;
use App\Http\Controllers\Auth\RoleSelectionController;
use App\Http\Controllers\classifiers\CuentaController;
use App\Http\Controllers\classifiers\SectorController;
use App\Http\Controllers\classifiers\TimbreController;
use App\Http\Controllers\customers\CustomerController;
use App\Http\Controllers\reports\ReportCasoController;
use App\Http\Controllers\reports\ReportJefeController;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\reports\ReportGastoController;
use App\Http\Controllers\reports\ReportIva90Controller;
use App\Http\Controllers\rolesPersmissions\AccessRoles;
use App\Http\Controllers\classifiers\CaratulaController;
use App\Http\Controllers\classifiers\GarantiaController;
use App\Http\Controllers\hacienda\ApiHaciendaController;
use App\Http\Controllers\movimientos\RevisionController;
use App\Http\Controllers\classifiers\HonorarioController;
use App\Http\Controllers\reports\ReportInvoiceController;
use App\Http\Controllers\classifiers\CasoEstadoController;
use App\Http\Controllers\classifiers\DepartmentController;
use App\Http\Controllers\movimientos\MovimientoController;
use App\Http\Controllers\reports\ReportIvaMas90Controller;
use App\Http\Controllers\reports\ReportProformaController;
use App\Http\Controllers\reports\ReportRegistroController;
use App\Http\Controllers\billing\CalculoRegistroController;
use App\Http\Controllers\classifiers\CasoJuzgadoController;
use App\Http\Controllers\classifiers\CasoProcesoController;
use App\Http\Controllers\classifiers\CasoProductController;
use App\Http\Controllers\classifiers\CentroCostoController;
use App\Http\Controllers\reports\CustomersReportController;
use App\Http\Controllers\reports\ReportGeneralesController;
use App\Http\Controllers\reports\ReportRetencionController;
use App\Http\Controllers\Auth\DepartmentSelectionController;
use App\Http\Controllers\classifiers\AreaPracticaController;
use App\Http\Controllers\classifiers\CasoServicioController;
use App\Http\Controllers\classifiers\ComisionistaController;
use App\Http\Controllers\classifiers\ProductoCasoController;
use App\Http\Controllers\reports\ReportAntiguedadController;
use App\Http\Controllers\reports\ReportComisionesController;
use App\Http\Controllers\reports\ReportMovimientoController;
use App\Http\Controllers\rolesPersmissions\AccessPermission;
use App\Http\Controllers\reports\ReportFacturacionController;
use App\Http\Controllers\reports\ReportTransactionController;
use App\Livewire\Movimientos\Export\MovimientoExportFromView;
use App\Http\Controllers\classifiers\CasoCapturadorController;
use App\Http\Controllers\classifiers\CasoPoderdanteController;
use App\Http\Controllers\classifiers\CatalogoCuentaController;
use App\Http\Controllers\reports\ReportEstadoCuentaController;
use App\Http\Controllers\classifiers\CasoExpectativaController;
use App\Http\Controllers\classifiers\CasoNotificadorController;
use App\Http\Controllers\classifiers\GrupoEmpresarialController;
use App\Http\Controllers\classifiers\CasoListadoJuzgadoController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\classifiers\MovimientoNotificationController;
use App\Http\Controllers\reports\ReportFacturacionDetalladaController;
use App\Http\Controllers\reports\ReportComprobantesController;

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// Reemplazar la ruta de login de Jetstream/Fortify
/*
Route::post('/login', [LoginController::class, 'login'])->name('login');
*/

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// Decargar factura electrónica mediante qr
Route::get('/download-invoice/{key}', [InvoiceController::class, 'downloadByKey'])
  ->name('invoice.download.public');

// Rutas de autenticación (si tienes Jetstream o Laravel Breeze)
//Route::group(['middleware' => 'auth:sanctum', 'verified'], function () {
// Rutas de autenticación (con contexto)
Route::group(['middleware' => 'auth:sanctum', 'verified', 'session.check'], function () {

  // Main Page Route
  Route::get('/', [Home::class, 'index'])->name('index');

  // CRUD USUARIOS
  Route::get('users', [UserController::class, 'index'])->name('users.index');         // Listar usuarios

  // ROLES y PERMISOS
  Route::get('/app/access-roles', [AccessRoles::class, 'index'])->name('access-roles');
  Route::get('/app/access-permission', [AccessPermission::class, 'index'])->name('access-permission');


  // CRUD CUSTOMERS
  Route::get('customers', [CustomerController::class, 'customer'])->name('customers.index');         // Listar usuarios

  // RECEPCION DE DOCUMENTOS
  Route::get('suppliers', [CustomerController::class, 'supplier'])->name('recepcion-de-documentos.index');         // Listar proveedores
  Route::get('comprobantes-electronicos', [InvoiceController::class, 'comprobante'])->name('recepcion-de-documentos.comprobantes-electronicos');

  // CRUD PRODUCTS
  Route::get('products', [ProductController::class, 'index'])->name('products.index');         // Listar usuarios

  // CRUD PROFORMAS
  Route::get('billing/proformas', [ProformaController::class, 'index'])->name('billing-proformas');
  Route::get('billing/proformas-history', [ProformaController::class, 'history'])->name('billing-history');
  Route::get('billing/proformas-buscador', [ProformaController::class, 'buscador'])->name('billing-buscador');
  Route::get('billing/proformas-seguimiento', [ProformaController::class, 'seguimiento'])->name('billing-seguimiento');
  Route::get('billing/digital-credit-note', [ProformaController::class, 'digitalCreditNote'])->name('billing-digital-credit-note');
  Route::get('billing/digital-debit-note', [ProformaController::class, 'digitalDebitNote'])->name('billing-digital-debit-note');
  Route::get('billing/calculo-registro', [CalculoRegistroController::class, 'index'])->name('billing-calculo-registro');
  Route::get('billing/cotizaciones', [ProformaController::class, 'cotizaciones'])->name('billing-cotizaciones');
  Route::get('administracion/cuentas-por-cobrar', [ProformaController::class, 'cuentasPorCobrar'])->name('administracion-cuentas-por-cobrar');

  // CRUD INVOICE
  Route::get('billing/invoices', [InvoiceController::class, 'index'])->name('billing-invoices');
  Route::get('billing/factura-compra', [InvoiceController::class, 'facturaCompra'])->name('billing-factura-compra');
  Route::get('billing/recibo-pago', [InvoiceController::class, 'reciboPago'])->name('billing-recibo-pago');
  Route::get('billing/credit-note', [InvoiceController::class, 'creditNote'])->name('billing-credit-note');
  Route::get('billing/debit-note', [InvoiceController::class, 'debitNote'])->name('billing-debit-note');


  // CRUD PRODUCTS
  //Route::get('casos', [CasoController::class, 'scotiabank'])->name('casos.index');         // Listar usuarios

  // CRUD PROFORMAS
  Route::get('settings/business', [SettingController::class, 'index'])->name('settings-business');

  Route::get('classifiers/honorarios', [HonorarioController::class, 'index'])->name('classifiers-honorarios');
  Route::get('classifiers/timbres', [TimbreController::class, 'index'])->name('classifiers-timbres');
  Route::get('classifiers/banks', [BankController::class, 'index'])->name('classifiers-banks');
  //Route::get('classifiers/cuentas', [CuentaController::class, 'index'])->name('classifiers-cuentas');
  Route::get('classifiers/catalogo-cuentas', [CatalogoCuentaController::class, 'index'])->name('classifiers-catalogo-cuentas');
  Route::get('classifiers/centro-costos', [CentroCostoController::class, 'index'])->name('classifiers-centro-costos');
  Route::get('classifiers/departments', [DepartmentController::class, 'index'])->name('classifiers-departments');
  Route::get('classifiers/caratulas', [CaratulaController::class, 'index'])->name('classifiers-casos-caratulas');
  Route::get('classifiers/garantias', [GarantiaController::class, 'index'])->name('classifiers-casos-garantias');
  Route::get('classifiers/casos-estados', [CasoEstadoController::class, 'index'])->name('classifiers-casos-estados');
  Route::get('classifiers/casos-products', [CasoProductController::class, 'index'])->name('classifiers-casos-products');
  Route::get('classifiers/casos-procesos', [CasoProcesoController::class, 'index'])->name('classifiers-casos-procesos');
  Route::get('classifiers/casos-juzgados', [CasoJuzgadoController::class, 'index'])->name('classifiers-casos-juzgados');
  Route::get('classifiers/casos-listado-juzgados', [CasoListadoJuzgadoController::class, 'index'])->name('classifiers-casos-listado-juzgados');
  Route::get('classifiers/casos-poderdantes', [CasoPoderdanteController::class, 'index'])->name('classifiers-casos-poderdantes');
  Route::get('classifiers/casos-expectativas', [CasoExpectativaController::class, 'index'])->name('classifiers-casos-expectativas');
  Route::get('classifiers/casos-notificadores', [CasoNotificadorController::class, 'index'])->name('classifiers-casos-notificadores');
  Route::get('classifiers/casos-capturadores', [CasoCapturadorController::class, 'index'])->name('classifiers-casos-capturadores');
  Route::get('classifiers/casos-servicios', [CasoServicioController::class, 'index'])->name('classifiers-casos-servicios');


  Route::get('classifiers/grupos-empresariales', [GrupoEmpresarialController::class, 'index'])->name('classifiers-grupos-empresariales');
  Route::get('classifiers/areas-practicas', [AreaPracticaController::class, 'index'])->name('classifiers-areas-practicas');
  Route::get('classifiers/sectores', [SectorController::class, 'index'])->name('classifiers-sectores');
  Route::get('classifiers/comisionistas', [ComisionistaController::class, 'index'])->name('classifiers-comisionistas');

  // CRUD Casos
  Route::get('casos/scotiabank', [CasoController::class, 'scotiabank'])->name('casos-scotiabank');
  Route::get('casos/scotiabank-bch', [CasoController::class, 'scotiabankBch'])->name('casos-scotiabank-bch');
  Route::get('casos/bac', [CasoController::class, 'bac'])->name('casos-bac');
  Route::get('casos/banco-general', [CasoController::class, 'bancoGeneral'])->name('casos-banco-general');
  Route::get('casos/terceros', [CasoController::class, 'terceros'])->name('casos-terceros');
  Route::get('casos/coocique', [CasoController::class, 'coocique'])->name('casos-coocique');
  Route::get('casos/coocique2', [CasoController::class, 'coocique2'])->name('casos-coocique2');
  Route::get('casos/davivienda', [CasoController::class, 'davivienda'])->name('casos-davivienda');
  Route::get('casos/lafise', [CasoController::class, 'lafise'])->name('casos-lafise');
  Route::get('casos/cafsa', [CasoController::class, 'cafsa'])->name('casos-cafsa');

  // CRUD MODULO BANCOS
  Route::get('banks/movements', [MovimientoController::class, 'index'])->name('banks-movements.index');
  Route::get('banks/revisions', [RevisionController::class, 'index'])->name('banks-revisions.index');
  Route::get('banks/saldo-cuentas', [MovimientoController::class, 'saldoCuentas'])->name('banks-saldo-cuentas.index');
  Route::get('banks/movements-notifications', [MovimientoNotificationController::class, 'index'])->name('banks-movements-notifications');
  Route::get('banks/cuentas', [CuentaController::class, 'index'])->name('banks-cuentas');

  // DASHBOARD
  Route::get('dashboard/formalizaciones-bancos', [GraficoController::class, 'formalizacionesBanco'])->name('dashboard-formalizaciones-bancos.index');
  Route::get('dashboard/formalizaciones-productos', [GraficoController::class, 'formalizacionesProducto'])->name('dashboard-formalizaciones-productos.index');
  Route::get('dashboard/honorarios-mes', [GraficoController::class, 'honorariosMes'])->name('dashboard-honorarios-mes.index');
  Route::get('dashboard/honorarios-anno', [GraficoController::class, 'honorariosAnno'])->name('dashboard-honorarios-anno.index');
  Route::get('dashboard/carga-trabajo', [GraficoController::class, 'cargaTrabajo'])->name('dashboard-carga-trabajo.index');
  Route::get('dashboard/casos-resueltos', [GraficoController::class, 'casosResuelto'])->name('dashboard-casos-resueltos.index');


  Route::get('dashboard/firmas', [GraficoController::class, 'firmas'])->name('dashboard-firmas.index');
  Route::get('dashboard/control-mensual', [GraficoController::class, 'controlMensual'])->name('dashboard-control-mensual.index');
  Route::get('dashboard/formalizaciones', [GraficoController::class, 'formalizaciones'])->name('dashboard-formalizaciones.index');
  Route::get('dashboard/tipos-caratulas', [GraficoController::class, 'tiposCaratulas'])->name('dashboard-tipos-caratulas.index');
  Route::get('dashboard/volumen-banco', [GraficoController::class, 'volumenBanco'])->name('dashboard-volumen-banco.index');
  Route::get('dashboard/facturacion-abogado', [GraficoController::class, 'facturacionAbogado'])->name('dashboard-facturacion-abogado.index');
  Route::get('dashboard/tipos-garantias', [GraficoController::class, 'tiposGarantias'])->name('dashboard-tipos-garantias.index');
  Route::get('dashboard/facturacion-centro-costo', [GraficoController::class, 'facturacionCentroCosto'])->name('dashboard-facturacion-centro-costo.index');

  //Reportes
  Route::get('/preparar-exportacion-movimientos/{key}', [ReportMovimientoController::class, 'prepararExportacion'])
    ->name('exportacion.movimientos.preparar');

  Route::get('/descargar-exportacion-movimientos/{filename}', [ReportMovimientoController::class, 'descargarExportacion'])
    ->name('exportacion.movimientos.descargar');

  Route::get('/preparar-exportacion-proforma/{key}', [ReportProformaController::class, 'prepararExportacionProforma'])
    ->name('exportacion.proforma.preparar');

  Route::get('/descargar-exportacion-proforma/{filename}', [ReportProformaController::class, 'descargarExportacionProforma'])
    ->name('exportacion.proforma.descargar');

  Route::get('/preparar-exportacion-recibo/{key}', [ReportProformaController::class, 'prepararExportacionRecibo'])
    ->name('exportacion.recibo.preparar');

  Route::get('/descargar-exportacion-recibo/{filename}', [ReportProformaController::class, 'descargarExportacionRecibo'])
    ->name('exportacion.recibo.descargar');

  // Reporte recibo de gastos calculo del registro
  Route::get('/preparar-exportacion-calculo-recibo-gasto/{key}', [ReportProformaController::class, 'prepararExportacionCalculoReciboGasto'])
    ->name('exportacion.proforma.calculo.recibo.gasto.preparar');

  Route::get('/descargar-exportacion-calculo-recibo-gasto/{filename}', [ReportProformaController::class, 'descargarExportacionCalculoReciboGasto'])
    ->name('exportacion.proforma.calculo.recibo.gasto.descargar');

  Route::get('/preparar-exportacion-transacciones/{key}', [ReportTransactionController::class, 'prepararExportacionTransacciones'])
    ->name('exportacion.transacciones.preparar');

  Route::get('/descargar-exportacion-transacciones/{filename}', [ReportTransactionController::class, 'descargarExportacionTransacciones'])
    ->name('exportacion.transacciones.descargar');

  Route::get('/preparar-exportacion-transacciones-visible/{key}', [ReportTransactionController::class, 'prepararExportacionTransaccionesVisible'])
    ->name('exportacion.transacciones.preparar.visible');

  // Estado de cuenta
  Route::get('/preparar-exportacion-estado-cuenta/{key}', [ReportProformaController::class, 'prepararExportacionEstadoCuenta'])
    ->name('exportacion.proforma.estado.cuenta.preparar');

  Route::get('/descargar-exportacion-estado-cuenta/{filename}', [ReportProformaController::class, 'descargarExportacionEstadoCuenta'])
    ->name('exportacion.proforma.estado.cuenta.descargar');

  // Factura electrónica
  Route::get('/preparar-exportacion-invoice/{key}', [ReportInvoiceController::class, 'prepararExportacionInvoice'])
    ->name('exportacion.invoice.preparar');

  Route::get('/descargar-exportacion-invoice/{filename}', [ReportInvoiceController::class, 'descargarExportacionInvoice'])
    ->name('exportacion.invoice.descargar');

  // reporte de casos
  Route::get('/preparar-exportacion-casos/{key}', [ReportCasoController::class, 'prepararExportacionCasos'])
    ->name('exportacion.casos.preparar');

  Route::get('/descargar-exportacion-casos/{filename}', [ReportCasoController::class, 'descargarExportacionCasos'])
    ->name('exportacion.casos.descargar');


  Route::get('/preparar-exportacion-caso-pendientes/{key}', [ReportCasoController::class, 'prepararExportacionCasoPendiente'])
    ->name('exportacion.caso.pendientes.preparar');

  Route::get('/descargar-exportacion-caso-pendientes/{filename}', [ReportCasoController::class, 'descargarExportacionCasoPendiente'])
    ->name('exportacion.caso.pendiente.descargar');


  // Reportes generales
  Route::prefix('reports')->name('reports.')->group(function () {
    // Reporte de facturación
    Route::get('/facturacion', [ReportFacturacionController::class, 'index'])
      ->name('facturacion.index');

    //Route::get('/facturacion/export', [ReportFacturacionController::class, 'export'])
    //  ->name('facturacion.export');

    // Reporte de facturación detallada
    Route::get('/facturacion-detallada', [ReportFacturacionDetalladaController::class, 'index'])
      ->name('facturacion-detallada.index');

    //Route::get('/facturacion-detallada/export', [ReportFacturacionDetalladaController::class, 'export'])
    //  ->name('facturacion-detallada.export');

    // Reporte de clientes
    Route::get('/customers', [CustomersReportController::class, 'index'])
      ->name('customers.index');

    //Route::get('/customers/export', [CustomersReportController::class, 'export'])
    //  ->name('customers.export');

    // Reporte de comisiones
    Route::get('/comisiones', [ReportComisionesController::class, 'index'])
      ->name('comisiones.index');

    //Route::get('/comisiones/export', [ReportComisionesController::class, 'export'])
    //  ->name('comisiones.export');

    // Reporte Generales
    Route::get('/generales', [ReportGeneralesController::class, 'index'])
      ->name('generales.index');

// Reporte de Comprobantes
    Route::get('/comprobantes', [ReportComprobantesController::class, 'index'])
      ->name('comprobantes.index');

    // Reporte de Registro
    Route::get('/registro', [ReportRegistroController::class, 'index'])
      ->name('registro.index');

    // Reporte de Retención
    Route::get('/retencion', [ReportRetencionController::class, 'index'])
      ->name('retencion.index');

    // Reporte de Retención
    Route::get('/antiguedad-saldo', [ReportAntiguedadController::class, 'index'])
      ->name('antiguedad-saldo.index');

    // Reporte estado de cuenta
    Route::get('/estado-cuenta', [ReportEstadoCuentaController::class, 'index'])
      ->name('estado-cuenta.index');

    // Reporte de Gasto
    Route::get('/gastos', [ReportGastoController::class, 'index'])
      ->name('gastos.index');

    // Reporte de IVA
    Route::get('/iva-mes', [ReportIvaController::class, 'index'])
        ->name('iva-mes.index');

    Route::get('/iva-90', [ReportIva90Controller::class, 'index'])
        ->name('iva-90.index');

    Route::get('/iva-mas90', [ReportIvaMas90Controller::class, 'index'])
        ->name('iva-mas90.index');

    // Reportes de casos
    Route::get('/casos', [ReportJefeController::class, 'casos'])
        ->name('casos.index');

    Route::get('/facturacion-abogado', [ReportJefeController::class, 'facturacionAbogado'])
        ->name('facturacion-abogado.index');

    Route::get('/casos-scotiabank', [ReportJefeController::class, 'casoScotiabank'])
        ->name('casos-scotiabank.index');

    Route::get('/casos-scotiabank-bch', [ReportJefeController::class, 'casoScotiabankBch'])
        ->name('casos-scotiabank-bch.index');

    Route::get('/casos-banco-general', [ReportJefeController::class, 'casoBancoGeneral'])
        ->name('casos-banco-general.index');

    Route::get('/casos-bac-cuentas-gestionadas', [ReportJefeController::class, 'casoBacCuentasGestionada'])
        ->name('casos-bac-cuentas-gestionadas.index');

    Route::get('/casos-bac-cuentas-terminadas', [ReportJefeController::class, 'casoBacCuentasTerminada'])
        ->name('casos-bac-cuentas-terminadas.index');

    Route::get('/casos-lafise-activos', [ReportJefeController::class, 'casoLafiseActivo'])
        ->name('casos-lafise-activos.index');

    Route::get('/casos-lafise-terminados', [ReportJefeController::class, 'casoLafiseTerminado'])
        ->name('casos-lafise-terminados.index');

    Route::get('/casos-lafise-incobrables', [ReportJefeController::class, 'casoLafiseIncobrable'])
        ->name('casos-lafise-incobrables.index');

    Route::get('/casos-davivienda-pago-ce', [ReportJefeController::class, 'casoDaviviendaPagoCe'])
        ->name('casos-davivienda-pago-ce.index');

    Route::get('/casos-davivienda-pago-tc', [ReportJefeController::class, 'casoDaviviendaPagoTc'])
        ->name('casos-davivienda-pago-tc.index');

    Route::get('/casos-davivienda-file-master', [ReportJefeController::class, 'casoDaviviendaFileMaster'])
        ->name('casos-davivienda-file-master.index');

    Route::get('/casos-davivienda-matriz', [ReportJefeController::class, 'casoDaviviendaMatriz'])
        ->name('casos-davivienda-matriz.index');

    Route::get('/casos-cafsa-activos', [ReportJefeController::class, 'casoCafsaActivo'])
        ->name('casos-cafsa-activos.index');

    Route::get('/casos-cafsa-terminados', [ReportJefeController::class, 'casoCafsaTerminado'])
        ->name('casos-cafsa-terminados.index');

    Route::get('/casos-cafsa-incobrables', [ReportJefeController::class, 'casoCafsaIncobrable'])
        ->name('casos-cafsa-incobrables.index');

    Route::get('/casos-tercero-activos', [ReportJefeController::class, 'casoTerceroActivo'])
        ->name('casos-tercero-activos.index');

    Route::get('/casos-tercero-terminados', [ReportJefeController::class, 'casoTerceroTerminado'])
        ->name('casos-tercero-terminados.index');

    Route::get('/casos-tercero-incobrables', [ReportJefeController::class, 'casoTerceroIncobrable'])
        ->name('casos-tercero-incobrables.index');

    Route::get('/casos-tercero-pago', [ReportJefeController::class, 'casoTerceroPago'])
        ->name('casos-tercero-pago.index');

    Route::get('/casos-tercero-prescrito', [ReportJefeController::class, 'casoTerceroPrescrito'])
        ->name('casos-tercero-prescrito.index');

    Route::get('/casos-coocique-activos', [ReportJefeController::class, 'casoCoociqueActivo'])
        ->name('casos-coocique-activos.index');

    Route::get('/casos-coocique-terminados', [ReportJefeController::class, 'casoCoociqueTerminado'])
        ->name('casos-coocique-terminados.index');

    Route::get('/casos-coocique-incobrables', [ReportJefeController::class, 'casoCoociqueIncobrable'])
        ->name('casos-coocique-incobrables.index');

    Route::get('/casos-coocique-pago', [ReportJefeController::class, 'casoCoociquePago'])
        ->name('casos-coocique-pago.index');

    Route::get('/casos-coocique-prescrito', [ReportJefeController::class, 'casoCoociquePrescrito'])
        ->name('casos-coocique-prescrito.index');

    // Coocique2
    Route::get('/casos-coocique2-activos', [ReportJefeController::class, 'casoCoocique2Activo'])
        ->name('casos-coocique2-activos.index');

    Route::get('/casos-coocique2-terminados', [ReportJefeController::class, 'casoCoocique2Terminado'])
        ->name('casos-coocique2-terminados.index');

    Route::get('/casos-coocique2-incobrables', [ReportJefeController::class, 'casoCoocique2Incobrable'])
        ->name('casos-coocique2-incobrables.index');

    Route::get('/casos-coocique2-pago', [ReportJefeController::class, 'casoCoocique2Pago'])
        ->name('casos-coocique2-pago.index');

    Route::get('/casos-coocique2-prescrito', [ReportJefeController::class, 'casoCoocique2Prescrito'])
        ->name('casos-coocique2-prescrito.index');

    // Reporte de Nota Credito
    Route::get('/nota-credito', [ReportNotaCreditoController::class, 'index'])
      ->name('nota-credito.index');

    // Reporte de Nota Debito
    Route::get('/nota-debito', [ReportNotaDebitoController::class, 'index'])
      ->name('nota-debito.index');

    // Reporte de Proforma
    Route::get('/proforma', [ReportProformaController::class, 'index'])
      ->name('proforma.index');
  });
});
//Route::get('/usuarios', [UserCrud::class, 'index'])->name('usuarios.index');
// ✅ SEGURIDAD: Ruta de prueba eliminada - usar solo en desarrollo local
// Route::get('/test-mail', function () {
//   if (!app()->environment('local') || !auth()->check()) {
//     abort(403, 'No autorizado');
//   }
//   Mail::to(auth()->user()->email)->send(new TestMail());
//   return 'Correo enviado correctamente';
// })->middleware('auth');

// ✅ SEGURIDAD: Protección contra Path Traversal
/*
Route::get('/temporary-file', function (Request $request) {
  $path = $request->get('path');

  // Validar que no contenga path traversal (../) ni caracteres peligrosos
  if (preg_match('/\.\./', $path) || preg_match('/[^a-zA-Z0-9_\-.]/', $path)) {
    abort(403, 'Path inválido');
  }

  $fullPath = storage_path('app/livewire-tmp/' . $path);

  // Verificar que el archivo existe y está dentro del directorio permitido
  if (!file_exists($fullPath) || !str_starts_with(realpath($fullPath), storage_path('app/livewire-tmp/'))) {
    abort(404, 'Archivo no encontrado');
  }

  return response()->file($fullPath);
})->name('temporary.file');
*/

// ✅ SEGURIDAD: Protección con autenticación y validación de ambiente
Route::get('/clear-cache', function () {
  // Validar ambiente - solo permitir en local
  if (!app()->environment('local')) {
    abort(403, 'Esta acción no está permitida en producción.');
  }

  // Validar autenticación y rol de super-admin
  if (!auth()->check() || !auth()->user()->hasRole('super-admin')) {
    abort(403, 'No autorizado. Se requiere rol de super-admin.');
  }

  Artisan::call('cache:clear');
  Artisan::call('config:clear');
  Artisan::call('route:clear');
  Artisan::call('view:clear');

  return "Caché de la aplicación, configuración, rutas y vistas ha sido limpiada correctamente.";
})->middleware('auth');

Route::prefix('api')->group(function () {
  Route::post('factura-call-back', [ApiHaciendaController::class, 'facturaCallback'])->withoutMiddleware(['web', 'csrf']);
  Route::post('nota-debito-call-back', [ApiHaciendaController::class, 'notaDebitoCallback'])->withoutMiddleware(['web', 'csrf']);
  Route::post('nota-credito-call-back', [ApiHaciendaController::class, 'notaCreditoCallback'])->withoutMiddleware(['web', 'csrf']);
  Route::post('tiquete-call-back', [ApiHaciendaController::class, 'tiqueteCallback'])->withoutMiddleware(['web', 'csrf']);
  Route::post('mensaje-call-back', [ApiHaciendaController::class, 'mensajeCallback'])->withoutMiddleware(['web', 'csrf']);
  Route::post('factura-compra-call-back', [ApiHaciendaController::class, 'facturaCompraCallback'])->withoutMiddleware(['web', 'csrf']);
  Route::post('factura-exportacion-call-back', [ApiHaciendaController::class, 'facturaExportacionCallback'])->withoutMiddleware(['web', 'csrf']);
  Route::post('recibo-pago-call-back', [ApiHaciendaController::class, 'facturaReciboPagoCallback'])->withoutMiddleware(['web', 'csrf']);
});


// Rutas públicas
/*
Route::middleware(['guest'])->group(function () {
  Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
  // ... otras rutas públicas
});
*/

// routes/web.php o routes/api.php
/*
Route::get('/api/casos/search', function (\Illuminate\Http\Request $request) {
  $term = $request->get('q');
  $bank_id = $request->get('bank_id');
  if ($bank_id) {
    return \App\Models\Caso::query()
      ->where('pnumero', 'like', "%{$term}%")
      ->orWhere('deudor', 'like', "%{$term}%")
      ->limit(20)
      ->get()
      ->map(fn($caso) => [
        'id' => $caso->id,
        'text' => "{$caso->pnumero} - {$caso->deudor}"
      ]);
  } else
    return [];
});
*/

// ✅ SEGURIDAD: Búsqueda de casos con autenticación y rate limiting
Route::get('/api/casos/search', function (\Illuminate\Http\Request $request) {
  // Validar autenticación
  if (!auth()->check()) {
    return response()->json(['error' => 'No autenticado'], 401);
  }

  $term = $request->get('q');
  $bank_id = $request->get('bank_id');

  if (!$bank_id) {
    return [];
  }

  $models = \App\Models\Caso::query()
    ->select([
      'id',
      DB::raw("CONCAT_WS(' / ',
                        CONCAT_WS(' / ', pnumero, pnumero_operacion1),
                        TRIM(CONCAT_WS(' ', pnombre_demandado, pnombre_apellidos_deudor))
                    ) AS pnumero")
    ])
    ->where(function ($query) use ($term) {
      $query->where('pnumero', 'like', "%{$term}%")
        ->orWhere('pnumero_operacion1', 'like', "%{$term}%")
        ->orWhere('pnombre_demandado', 'like', "%{$term}%")
        ->orWhere('pnombre_apellidos_deudor', 'like', "%{$term}%");
    })
    ->where('bank_id', $bank_id)
    ->limit(50)  // Reducido de 200 a 50 por seguridad
    ->get();

  if ($models->isEmpty()) {
    return [];
  }

  return $models->map(function ($model) {
    $temp_name = $model->pnumero ?? $model->id;
    $temp_name = mb_strtoupper($temp_name);
    return [
      'id' => $model->id,
      'text' => $temp_name,
    ];
  });
})->middleware(['auth', 'throttle:60,1']);  // Máximo 60 peticiones por minuto

// routes/web.php o routes/api.php
// ✅ SEGURIDAD: Búsqueda de clientes con autenticación, validación y rate limiting
Route::get('/api/customers/search', function (\Illuminate\Http\Request $request) {
  // Validar autenticación
  if (!auth()->check()) {
    return response()->json(['error' => 'No autenticado'], 401);
  }

  // Validar entrada
  $validated = $request->validate([
    'q' => 'required|string|min:2|max:100'
  ]);

  $term = $validated['q'];

  return Contact::query()
    ->where('name', 'like', "%{$term}%")
    ->orWhere('identification', 'like', "%{$term}%")
    ->limit(20)
    ->get()
    ->map(fn($contact) => [
      'id' => $contact->id,
      'text' => "{$contact->name}"
    ]);
})->middleware(['auth', 'throttle:120,1']);  // Máximo 120 peticiones por minuto

// ✅ SEGURIDAD: Búsqueda de emisores con autenticación, validación y rate limiting
Route::get('/api/emisor/search', function (\Illuminate\Http\Request $request) {
  // Validar autenticación
  if (!auth()->check()) {
    return response()->json(['error' => 'No autenticado'], 401);
  }

  // Validar entrada
  $validated = $request->validate([
    'q' => 'required|string|min:2|max:100'
  ]);

  $term = $validated['q'];

  return Comprobante::query()
    ->where('emisor_nombre', 'like', "%{$term}%")
    ->select('emisor_nombre') // solo esta columna
    ->distinct()
    ->limit(20)
    ->pluck('emisor_nombre') // obtenemos solo los valores únicos
    ->map(fn($nombre) => [
      'id' => $nombre,
      'text' => $nombre
    ]);
})->middleware(['auth', 'throttle:120,1']);  // Máximo 120 peticiones por minuto

// routes/web.php
// 🚨 SEGURIDAD CRÍTICA: Ruta eliminada - exponía credenciales SMTP públicamente
// Esta ruta fue la CAUSA PROBABLE del incidente de spam
// NUNCA exponer configuración SMTP sin autenticación
/*
Route::get('/debug-mail', function () {
  // ELIMINADO POR SEGURIDAD - Exponía usuario SMTP públicamente
  abort(404);
});
*/

// 🚨 SEGURIDAD CRÍTICA: Ruta eliminada - exponía datos de sesión y usuario
// NUNCA exponer session()->all() públicamente
/*
Route::get('/check-session', function () {
  // ELIMINADO POR SEGURIDAD - Exponía tokens CSRF y datos sensibles
  abort(404);
});
*/

/*
// Rutas de autenticación
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  // Rutas de selección (sin contexto)
  Route::get('/select-role', [RoleSelectionController::class, 'show'])
    ->name('role-selection');

  Route::post('/select-role', [RoleSelectionController::class, 'select'])
    ->name('role-selection.select');

  Route::get('/select-department', [DepartmentSelectionController::class, 'show'])
    ->name('department-selection');

  Route::post('/select-department', [DepartmentSelectionController::class, 'select'])
    ->name('department-selection.select');
});

Route::get('/get-roles', function (Request $request) {
  $email = $request->query('email');

  if (!$email) {
    return response()->json(['error' => 'Email is required'], 400);
  }

  $user = User::where('email', $email)->first();

  if (!$user) {
    return response()->json(['roles' => []]);
  }

  // Cargar roles con sus relaciones
  $roles = $user->roles()->get();

  return response()->json([
    'roles' => $roles->map(function ($role) {
      return [
        'id' => $role->id,
        'name' => $role->name
      ];
    })
  ]);
});
*/

/*
// Ruta para mostrar el formulario de selección de contexto
Route::get('/select-context', [ContextController::class, 'showSelectionForm'])
  ->name('select-context')
  ->middleware('auth');

// Ruta para guardar el contexto seleccionado
Route::post('/set-context', [ContextController::class, 'setContext'])
  ->name('set-context')
  ->middleware('auth');
*/

/*
Route::get('/exportar-transactions/{key}', [ReportTransactionController::class, 'exportarTransactions'])
  ->name('transactions.exportar.descarga');
  */
/*
Route::get('/exportar-transactions/{key}', [ReportTransactionController::class, 'exportarTransactions'])
  ->name('transactions.exportar.descarga');

Route::get('/exportar-proforma-sencilla/{key}', [ReportProformaController::class, 'exportarProforma'])
  ->name('proforma-sencilla.exportar.descarga');

Route::get('/exportar-proforma-detallada/{key}', [ReportProformaController::class, 'exportarProforma'])
  ->name('proforma-detallada.exportar.descarga');

Route::get('/exportar-recibo-sencillo/{key}', [ReportProformaController::class, 'exportarRecibo'])
  ->name('recibo-sencillo.exportar.descarga');

Route::get('/exportar-recibo-detallado/{key}', [ReportProformaController::class, 'exportarRecibo'])
  ->name('recibo-detallado.exportar.descarga');
*/

/*
Route::get('/exportar-movimientos/{key}', function ($key) {

  Log::info("INTENTO DESCARGA CON CLAVE: $key");
  $params = Cache::pull($key);

  // 🔐 Validación estricta
  if (!is_array($params)) {
    Log::warning("Exportación fallida: clave '$key' inválida o expirada.");
    abort(404, 'Export key inválida o expirada');
  }

  $search = $params['search'] ?? '';
  $filters = $params['filters'] ?? [];
  $selectedIds = $params['selectedIds'] ?? [];
  $defaultStatus = $params['defaultStatus'] ?? null;

  $query = Movimiento::search($search, $filters, $defaultStatus);

  if (!empty($selectedIds)) {
    $query->whereIn('movimientos.id', $selectedIds);
  }

  return Excel::download(
    new MovimientoExportFromView($query),
    'movimientos-' . now()->format('Ymd_His') . '.xlsx'
  );
})->name('movimientos.exportar.descarga');
*/


// ✅ SEGURIDAD: Protección con autenticación y validación de ambiente
/*
Route::get('/check-assignments/{userId}', function ($userId) {
  // Solo permitir en ambiente local
  if (!app()->environment('local')) {
    abort(403, 'Esta acción no está permitida en producción.');
  }

  // Validar autenticación y rol
  if (!auth()->check() || !auth()->user()->hasRole('super-admin')) {
    abort(403, 'No autorizado. Se requiere rol de super-admin.');
  }

  $user = \App\Models\User::find($userId);

  if (!$user) {
    return response()->json(['error' => 'Usuario no encontrado'], 404);
  }

  return response()->json([
    'user' => $user->only('id', 'email'),
    'roles' => $user->roles()->get(),
    'assignments' => $user->roleAssignments()->get()
  ]);
})->middleware('auth');
*/
