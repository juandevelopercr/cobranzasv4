<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// ============================================================================
// IMPORTANTE — evitar Schedule::command('...') en este archivo.
//
// Schedule::command() lanza el comando como un SUBPROCESO nuevo via
// proc_open()/posix_spawn(). En el servidor de tropicalv4 (mismo stack:
// CloudLinux/CWP) eso empezó a fallar bajo cron con
// "Error: proc_open(): posix_spawn() failed: Operation not permitted"
// (probable límite de procesos LVE de la cuenta, saturado por workers
// persistentes tipo Octane) — el cron corría, pero el comando real nunca
// llegaba a ejecutarse, en silencio. No hay evidencia confirmada de que este
// proyecto esté en un hosting con el mismo límite, pero se migra
// preventivamente porque no tiene downside: Artisan::call() ejecuta el
// comando DENTRO del mismo proceso de schedule:run, sin crear ningún
// proceso nuevo. Ver memoria "Cron posix_spawn Incident" en tropicalv4 para
// el diagnóstico completo si esto vuelve a investigarse.
// ============================================================================

Schedule::call(function () {
  Artisan::call('logs:clean');
})->name('logs-clean')->dailyAt('02:00');

Schedule::call(function () {
  Artisan::call('comprobantes:process-emails');
})->name('comprobantes-process-emails')->everyFiveMinutes()->withoutOverlapping();

Schedule::call(function () {
  Artisan::call('exchange-rate:update');
})->name('exchange-rate-update')->hourly();

Schedule::call(function () {
  Artisan::call('hacienda:poll-status');
})->name('hacienda-poll-status')->everyTenMinutes()->withoutOverlapping();
