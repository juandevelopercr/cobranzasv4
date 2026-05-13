<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\Hacienda\ApiHacienda;
use App\Services\Hacienda\Login\AuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollHaciendaStatus extends Command
{
    /*
    php artisan hacienda:poll-status
    */

    protected $signature = 'hacienda:poll-status {--limit=50 : Máximo de facturas a consultar por ejecución}';
    protected $description = 'Consulta en Hacienda el estado de facturas electrónicas en estado RECIBIDA o PENDIENTE';

    // Tipos de documento electrónico que pueden estar pendientes en Hacienda
    private const ELECTRONIC_TYPES = ['FE', 'TE', 'NCE', 'NDE', 'FEC', 'FEE', 'REP'];

    public function handle(): void
    {
        $limit = (int) $this->option('limit');

        Log::channel('scheduler')->info('hacienda:poll-status iniciado', ['limit' => $limit]);

        $pending = Transaction::whereIn('status', [Transaction::RECIBIDA, Transaction::PENDIENTE])
            ->whereIn('document_type', self::ELECTRONIC_TYPES)
            ->whereNotNull('key')
            ->where('key', '!=', '')
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No hay facturas pendientes de respuesta en Hacienda.');
            Log::channel('scheduler')->info('hacienda:poll-status: sin facturas pendientes');
            return;
        }

        $this->info("Consultando {$pending->count()} facturas en Hacienda...");

        $api = new ApiHacienda();
        $procesadas = 0;
        $errores = 0;

        // Agrupar por location para reutilizar token por emisor
        $byLocation = $pending->groupBy('location_id');

        foreach ($byLocation as $locationId => $transactions) {
            $location = $transactions->first()->location;

            if (!$location) {
                Log::channel('scheduler')->warning('hacienda:poll-status: location no encontrada', ['location_id' => $locationId]);
                $errores += $transactions->count();
                continue;
            }

            try {
                $authService = new AuthService();
                $token = $authService->getToken($location->api_user_hacienda, $location->api_password);
            } catch (\Exception $e) {
                Log::channel('scheduler')->error('hacienda:poll-status: error de autenticación', [
                    'location_id' => $locationId,
                    'error' => $e->getMessage(),
                ]);
                $errores += $transactions->count();
                continue;
            }

            foreach ($transactions as $transaction) {
                try {
                    $tipoDocumento = $transaction->getComprobanteCode();
                    $result = $api->getStatusComprobante($token, $transaction, $location, $tipoDocumento);

                    if (isset($result['error']) && $result['error'] == 1) {
                        Log::channel('scheduler')->warning('hacienda:poll-status: respuesta con error', [
                            'key' => $transaction->key,
                            'mensaje' => $result['mensaje'] ?? '',
                        ]);
                        $errores++;
                    } else {
                        $procesadas++;
                        Log::channel('scheduler')->info('hacienda:poll-status: estado actualizado', [
                            'key' => $transaction->key,
                            'estado' => $result['estado'] ?? 'desconocido',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::channel('scheduler')->error('hacienda:poll-status: excepción al consultar', [
                        'key' => $transaction->key,
                        'error' => $e->getMessage(),
                    ]);
                    $errores++;
                }
            }
        }

        $this->info("Procesadas: {$procesadas} | Errores: {$errores}");
        Log::channel('scheduler')->info('hacienda:poll-status finalizado', [
            'procesadas' => $procesadas,
            'errores' => $errores,
        ]);
    }
}
