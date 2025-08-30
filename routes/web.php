<?php

use \App\Models\Contact;
use App\Http\Controllers\Auth\DepartmentSelectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RoleSelectionController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\billing\CalculoRegistroController;
use App\Http\Controllers\billing\InvoiceController;
use App\Http\Controllers\billing\ProformaController;
use App\Http\Controllers\casos\CasoController;
use App\Http\Controllers\classifiers\AreaPracticaController;
use App\Http\Controllers\classifiers\BankController;
use App\Http\Controllers\classifiers\CaratulaController;
use App\Http\Controllers\classifiers\CasoEstadoController;
use App\Http\Controllers\classifiers\CasoExpectativaController;
use App\Http\Controllers\classifiers\CasoJuzgadoController;
use App\Http\Controllers\classifiers\CasoPoderdanteController;
use App\Http\Controllers\classifiers\CasoProcesoController;
use App\Http\Controllers\classifiers\CasoProductController;
use App\Http\Controllers\classifiers\CatalogoCuentaController;
use App\Http\Controllers\classifiers\CentroCostoController;
use App\Http\Controllers\classifiers\ComisionistaController;
use App\Http\Controllers\classifiers\CuentaController;
use App\Http\Controllers\classifiers\DepartmentController;
use App\Http\Controllers\classifiers\GarantiaController;
use App\Http\Controllers\classifiers\GrupoEmpresarialController;
use App\Http\Controllers\classifiers\HonorarioController;
use App\Http\Controllers\classifiers\MovimientoNotificationController;
use App\Http\Controllers\classifiers\ProductoCasoController;
use App\Http\Controllers\classifiers\SectorController;
use App\Http\Controllers\classifiers\TimbreController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\customers\CustomerController;
use App\Http\Controllers\dashboard\GraficoController;
use App\Http\Controllers\hacienda\ApiHaciendaController;
use App\Http\Controllers\Home;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\movimientos\MovimientoController;
use App\Http\Controllers\movimientos\RevisionController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\products\ProductController;
use App\Http\Controllers\reports\ReportCasoController;
use App\Http\Controllers\reports\ReportInvoiceController;
use App\Http\Controllers\reports\ReportMovimientoController;
use App\Http\Controllers\reports\ReportProformaController;
use App\Http\Controllers\reports\ReportTransactionController;
use App\Http\Controllers\rolesPersmissions\AccessPermission;
use App\Http\Controllers\rolesPersmissions\AccessRoles;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\UserController;
use App\Livewire\Movimientos\Export\MovimientoExportFromView;
use App\Mail\TestMail;
use App\Models\Department;
use App\Models\Movimiento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;






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
  Route::get('suppliers', [CustomerController::class, 'supplier'])->name('suppliers.index');         // Listar usuarios

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
  Route::get('billing/comprobantes', [InvoiceController::class, 'comprobante'])->name('billing-comprobantes-electronicos');

  // CRUD PRODUCTS
  Route::get('casos', [CasoController::class, 'index'])->name('casos.index');         // Listar usuarios

  // CRUD PROFORMAS
  Route::get('settings/business', [SettingController::class, 'index'])->name('settings-business');

  Route::get('classifiers/honorarios', [HonorarioController::class, 'index'])->name('classifiers-honorarios');
  Route::get('classifiers/timbres', [TimbreController::class, 'index'])->name('classifiers-timbres');
  Route::get('classifiers/banks', [BankController::class, 'index'])->name('classifiers-banks');
  Route::get('classifiers/cuentas', [CuentaController::class, 'index'])->name('classifiers-cuentas');
  Route::get('classifiers/catalogo-cuentas', [CatalogoCuentaController::class, 'index'])->name('classifiers-catalogo-cuentas');
  Route::get('classifiers/centro-costos', [CentroCostoController::class, 'index'])->name('classifiers-centro-costos');
  Route::get('classifiers/departments', [DepartmentController::class, 'index'])->name('classifiers-departments');
  Route::get('classifiers/caratulas', [CaratulaController::class, 'index'])->name('classifiers-casos-caratulas');
  Route::get('classifiers/garantias', [GarantiaController::class, 'index'])->name('classifiers-casos-garantias');
  Route::get('classifiers/casos-estados', [CasoEstadoController::class, 'index'])->name('classifiers-casos-estados');
  Route::get('classifiers/casos-products', [CasoProductController::class, 'index'])->name('classifiers-casos-products');
  Route::get('classifiers/casos-procesos', [CasoProcesoController::class, 'index'])->name('classifiers-casos-procesos');
  Route::get('classifiers/casos-juzgados', [CasoJuzgadoController::class, 'index'])->name('classifiers-casos-juzgados');
  Route::get('classifiers/casos-poderdantes', [CasoPoderdanteController::class, 'index'])->name('classifiers-casos-poderdantes');
  Route::get('classifiers/casos-expectativas', [CasoExpectativaController::class, 'index'])->name('classifiers-casos-expectativas');
  Route::get('classifiers/grupos-empresariales', [GrupoEmpresarialController::class, 'index'])->name('classifiers-grupos-empresariales');
  Route::get('classifiers/areas-practicas', [AreaPracticaController::class, 'index'])->name('classifiers-areas-practicas');
  Route::get('classifiers/sectores', [SectorController::class, 'index'])->name('classifiers-sectores');
  Route::get('classifiers/comisionistas', [ComisionistaController::class, 'index'])->name('classifiers-comisionistas');

  // CRUD Casos
  Route::get('casos/scotiabank', [CasoController::class, 'scotiabank'])->name('casos-scotiabank');

  // CRUD MODULO BANCOS
  Route::get('banks/movements', [MovimientoController::class, 'index'])->name('banks-movements.index');
  Route::get('banks/revisions', [RevisionController::class, 'index'])->name('banks-revisions.index');
  Route::get('banks/movements-notifications', [MovimientoNotificationController::class, 'index'])->name('banks-movements-notifications');

  // DASHBOARD
  Route::get('dashboard/firmas', [GraficoController::class, 'firmas'])->name('dashboard-firmas.index');
  Route::get('dashboard/honorarios-anno', [GraficoController::class, 'honorariosAnno'])->name('dashboard-honorarios-anno.index');
  Route::get('dashboard/honorarios-mes', [GraficoController::class, 'honorariosMes'])->name('dashboard-honorarios-mes.index');
  Route::get('dashboard/control-mensual', [GraficoController::class, 'controlMensual'])->name('dashboard-control-mensual.index');
  Route::get('dashboard/carga-trabajo', [GraficoController::class, 'cargaTrabajo'])->name('dashboard-carga-trabajo.index');
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
});

//Route::get('/usuarios', [UserCrud::class, 'index'])->name('usuarios.index');

Route::get('/test-mail', function () {
  Mail::to('caceresvega@gmail.com')->send(new TestMail());
  return 'Correo enviado con MFA';
});

Route::get('/temporary-file', function (Request $request) {
  $path = $request->get('path');
  return response()->file(storage_path('app/livewire-tmp/' . $path));
})->name('temporary.file');

Route::get('/clear-cache', function () {
  // Verifica si el entorno es 'local' para limitar su uso en producción
  //if (app()->environment('local')) {
  Artisan::call('cache:clear');
  Artisan::call('config:clear');
  Artisan::call('route:clear');
  Artisan::call('view:clear');
  return "Caché de la aplicación, configuración, rutas y vistas ha sido limpiada.";
  //}

  //abort(403, 'Esta acción no está permitida en producción.');
});

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
Route::get('/api/casos/search', function (\Illuminate\Http\Request $request) {
  $term = $request->get('q');
  $bank_id = $request->get('bank_id');
  if ($bank_id) {
    return \App\Models\Caso::query()
      ->where('numero', 'like', "%{$term}%")
      ->orWhere('deudor', 'like', "%{$term}%")
      ->limit(20)
      ->get()
      ->map(fn($caso) => [
        'id' => $caso->id,
        'text' => "{$caso->numero} - {$caso->deudor}"
      ]);
  } else
    return [];
});

// routes/web.php o routes/api.php
Route::get('/api/customers/search', function (\Illuminate\Http\Request $request) {
  $term = $request->get('q');
  return Contact::query()
    ->where('name', 'like', "%{$term}%")
    ->orWhere('identification', 'like', "%{$term}%")
    ->limit(20)
    ->get()
    ->map(fn($contact) => [
      'id' => $contact->id,
      'text' => "{$contact->name}"
    ]);
});

// routes/web.php
Route::get('/debug-mail', function () {
  return [
    'host' => config('mail.mailers.smtp.host'),
    'port' => config('mail.mailers.smtp.port'),
    'username' => config('mail.mailers.smtp.username'),
    'from_address' => config('mail.from.address'),
    'encryption' => config('mail.mailers.smtp.encryption'),
  ];
});

Route::get('/check-session', function () {
  return response()->json([
    'session_data' => session()->all(),
    'user' => auth()->user()
  ]);
});

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


Route::get('/check-assignments/{userId}', function ($userId) {
  $user = \App\Models\User::find($userId);

  if (!$user) {
    return "Usuario no encontrado";
  }

  return response()->json([
    'user' => $user->only('id', 'email'),
    'roles' => $user->roles()->get(),
    'assignments' => $user->roleAssignments()->get()
  ]);
});
