<?php

namespace App\Http\Controllers\reports;

use App\Http\Controllers\Controller;
use App\Livewire\Transactions\Export\TransactionExportFromView;
use App\Exports\CreditNoteExport;
use App\Exports\DebitNoteExport;
use App\Exports\ProformaExport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReportTransactionController extends Controller
{
  public function prepararExportacionTransacciones($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      $search = $params['search'] ?? '';
      $filters = $params['filters'] ?? [];
      $selectedIds = $params['selectedIds'] ?? [];
      $sortBy = $params['sortBy'] ?? 'transactions.transaction_date';
      $sortDir = $params['sortDir'] ?? 'DESC';
      $perPage = $params['perPage'] ?? 10;

      $query = Transaction::search($search, $filters)
        ->orderBy($sortBy, $sortDir)
        ->limit($perPage); // por ejemplo, limitar a 1000 registros
      //->paginate($perPage);

      if (!empty($selectedIds)) {
        $query->whereIn('transactions.id', $selectedIds);
      }

      $exportPath = storage_path('app/public/exports');
      if (!File::exists($exportPath)) {
        File::makeDirectory($exportPath, 0777, true);
      } else {
        foreach (File::files($exportPath) as $file) {
          $modified = Carbon::createFromTimestamp($file->getMTime());
          if ($modified->diffInMinutes(now()) >= 3) {
            File::delete($file->getPathname());
          }
        }
      }

      $exportType = $params['exportType'] ?? null;
      $filenamePrefix = 'transactions-';

      if ($exportType === 'CREDIT_NOTE') {
        $export = new CreditNoteExport($query);
        $filenamePrefix = 'credit-notes-';
      } elseif ($exportType === 'DEBIT_NOTE') {
        $export = new DebitNoteExport($query);
        $filenamePrefix = 'debit-notes-';
      } elseif ($exportType === 'PROFORMA') {
        $export = new ProformaExport($query);
        $filenamePrefix = 'proformas-';
      } elseif ($exportType === 'COTIZACION') {
        // Asegurar que solo exporte cotizaciones
        $query->where('document_type', \App\Models\Transaction::COTIZACION);
        $export = new \App\Exports\CotizacionExport($query);
        $filenamePrefix = 'cotizaciones-';
      } else {
        $export = new TransactionExportFromView($query);
      }

      $filename = $filenamePrefix . now()->format('Ymd_His') . '.xlsx';
      $relativePath = "exports/$filename";
      $storagePath = "public/$relativePath";

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      Excel::store($export, $storagePath);

      return response()->json(['filename' => $filename]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de transacciones: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function prepararExportacionTransaccionesVisible($key)
  {
    try {
      $params = Cache::pull($key);

      if (!is_array($params)) {
        abort(404, 'Clave de exportación inválida o expirada');
      }

      ini_set('memory_limit', '-1');
      ini_set('max_execution_time', '360');

      // Build query using the same filters as the Livewire component
      $managerClass = $params['managerClass'] ?? \App\Livewire\Transactions\CuentaPorCobrarManager::class;

      if (!class_exists($managerClass)) {
          $managerClass = \App\Livewire\Transactions\CuentaPorCobrarManager::class;
      }

      /** @var \App\Livewire\Transactions\TransactionManager $manager */
      $manager = new $managerClass();

      // hydrate manager with params where applicable
      $manager->search = $params['search'] ?? '';
      $manager->filters = $params['filters'] ?? [];

      // Aseguramos que document_type se establezca si es Cotización
      if (isset($params['exportType']) && $params['exportType'] === 'COTIZACION') {
          $manager->document_type = \App\Models\Transaction::COTIZACION;
      } elseif (isset($params['document_type'])) {
          $manager->document_type = $params['document_type'];
      }

      // Si el manager tiene método mount, idealmente deberíamos llamarlo o simular la carga de datos comunes si afecta al export (ej. bancos permitidos)
      // Pero loadCommonData() usa Session y Auth, lo cual funciona aquí si es la misma sesión.
      // Sin embargo, getQueryForExport (que llama a getFilteredQuery) usa auth()->user() directamente, así que debería funcionar.

      $sortBy = $params['sortBy'] ?? 'transactions.transaction_date';
      $sortDir = $params['sortDir'] ?? 'DESC';
      $perPage = $params['perPage'] ?? 10;
      $page = $params['page'] ?? 1;
      $selectedIds = $params['selectedIds'] ?? [];

      $baseQuery = $manager->getQueryForExport($params);

      // If the user selected specific ids, use them. Otherwise fetch specific IDs for current page.
      if (!empty($selectedIds)) {
        $externalQuery = $baseQuery->whereIn('transactions.id', $selectedIds)->orderBy($sortBy, $sortDir);
      } else {
        $currentPage = (int)($params['page'] ?? 1);
        $perPage = (int)($params['perPage'] ?? 10);
        $offset = max(($currentPage - 1) * $perPage, 0);

        // Get IDs for the current page
        $idsQuery = clone $baseQuery;
        $exportIds = $idsQuery->select('transactions.id')
            ->orderBy($sortBy, $sortDir)
            ->skip($offset)
            ->take($perPage)
            ->pluck('transactions.id')
            ->toArray();

        if (empty($exportIds)) {
            $externalQuery = \App\Models\Transaction::whereRaw('1 = 0');
        } else {
            // Re-fetch full records for these IDs
            $externalQuery = $manager->getQueryForExport($params)
                ->whereIn('transactions.id', $exportIds)
                ->orderBy($sortBy, $sortDir);
        }
      }

      if (isset($params['exportType']) && $params['exportType'] === 'COTIZACION') {
          $report = new \App\Exports\CotizacionExport($externalQuery);
          $filename = 'cotizaciones-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'PROFORMA') {
          $report = new \App\Exports\ProformaExport($externalQuery);
          $filename = 'proformas-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'BUSCADOR') {
          $report = new \App\Exports\BuscadorExport($externalQuery);
          $filename = 'buscador-proformas-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'CREDIT_NOTE') {
          $report = new \App\Exports\CreditNoteExport($externalQuery);
          $filename = 'credit-notes-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'CALCULO_REGISTRO') {
          $report = new \App\Exports\CalculoRegistroExport($externalQuery);
          $filename = 'calculo-registro-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'HISTORY') {
          $report = new \App\Exports\HistoryExport($externalQuery);
          $filename = 'history-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'INVOICE') {
          $report = new \App\Exports\InvoiceExport($externalQuery);
          $filename = 'electronic-invoices-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'SEGUIMIENTO') {
          $report = new \App\Exports\SeguimientoExport($externalQuery);
          $filename = 'seguimiento-facturas-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'ELECTRONIC_CREDIT_NOTE') {
          $report = new \App\Exports\ElectronicCreditNoteExport($externalQuery);
          $filename = 'electronic-credit-notes-' . now()->format('Ymd_His') . '.xlsx';
      } elseif (isset($params['exportType']) && $params['exportType'] === 'ELECTRONIC_DEBIT_NOTE') {
          $report = new \App\Exports\ElectronicDebitNoteExport($externalQuery);
          $filename = 'electronic-debit-notes-' . now()->format('Ymd_His') . '.xlsx';
      } else {
          $title = 'Cuentas por Cobrar';
          $report = new \App\Exports\CuentasPorCobrarReport($params, $title, $externalQuery);
          $filename = 'cuentas-por-cobrar-' . now()->format('Ymd_His') . '.xlsx';
      }

      $relativePath = "exports/$filename";
      $storagePath = "public/$relativePath";

      Excel::store($report, $storagePath);

      return response()->json(['filename' => $filename]);
    } catch (Throwable $e) {
      Log::error("Error al preparar exportación de transacciones visible: " . $e->getMessage());
      return response()->json(['error' => 'Error al generar el archivo'], 500);
    }
  }

  public function descargarExportacionTransacciones($filename)
  {
    $path = storage_path("app/public/exports/$filename");

    if (!file_exists($path)) {
      abort(404, 'Archivo no encontrado');
    }

    return response()->download($path, $filename);
  }
}
