<?php

namespace App\Jobs;

use App\Models\Caso;
use App\Services\ApiBCCR;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalcularSaldoDolarizadoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200; // 20 minutos
    public int $tries   = 1;

    public function __construct(
        public readonly int $bankId,
        public readonly bool $sobreescribir = false
    ) {}

    public function handle(ApiBCCR $apiBCCR): void
    {
        // Caché de tasas por fecha para no llamar la API dos veces para la misma fecha
        $rateCache = [];
        $procesados = 0;
        $sinTasa    = 0;
        $sinSaldo   = 0;

        $query = Caso::with('currency')
            ->where('bank_id', $this->bankId)
            ->whereNotNull('fecha_creacion');

        // Si no se quiere sobreescribir, solo procesar los que no tengan tipo_de_cambio
        if (!$this->sobreescribir) {
            $query->whereNull('tipo_de_cambio');
        }

        $query->chunk(50, function ($casos) use ($apiBCCR, &$rateCache, &$procesados, &$sinTasa, &$sinSaldo) {
            foreach ($casos as $caso) {
                $saldo = $this->parseSaldo($caso->asaldo_capital_operacion);

                if ($saldo === null) {
                    $sinSaldo++;
                    continue;
                }

                $fecha = Carbon::parse($caso->fecha_creacion)->format('Y-m-d');

                // Obtener tasa del caché o llamar a la API
                if (!array_key_exists($fecha, $rateCache)) {
                    $tasa = $apiBCCR->obtenerTipoCambio(318, $fecha);
                    $rateCache[$fecha] = $tasa;

                    if ($tasa) {
                        Log::info("BCCR tasa obtenida", ['fecha' => $fecha, 'tasa' => $tasa]);
                    } else {
                        Log::warning("BCCR sin tasa para fecha", ['fecha' => $fecha]);
                    }
                }

                $tasa = $rateCache[$fecha];

                if (!$tasa) {
                    $sinTasa++;
                    continue;
                }

                $psaldoDolarizado = $this->calcularSaldoDolarizado($saldo, $tasa, $caso->currency);

                $caso->update([
                    'tipo_de_cambio'   => $tasa,
                    'psaldo_dolarizado' => $psaldoDolarizado,
                ]);

                $procesados++;
            }
        });

        Log::info('CalcularSaldoDolarizadoJob finalizado', [
            'bank_id'    => $this->bankId,
            'procesados' => $procesados,
            'sin_tasa'   => $sinTasa,
            'sin_saldo'  => $sinSaldo,
        ]);
    }

    private function parseSaldo(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') return null;
        $limpio = str_replace([',', ' '], ['', ''], (string) $valor);
        return is_numeric($limpio) ? (float) $limpio : null;
    }

    private function calcularSaldoDolarizado(float $saldo, float $tasa, $currency): ?float
    {
        if ($currency && strtoupper($currency->code) === 'USD') {
            return $saldo;
        }
        return $tasa > 0 ? round($saldo / $tasa, 2) : null;
    }
}
