<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\ApiBCCR;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rate:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the BCCR exchange rate in the database once a day';

    /**
     * Execute the console command.
     */
    public function handle(ApiBCCR $apiBCCR)
    {
        $business = Business::first();
        if (!$business) {
            $this->error('No business found.');
            return;
        }

        // Si ya se actualizó hoy, no hacemos nada
        if ($business->exchange_rate_updated_at && $business->exchange_rate_updated_at->isToday()) {
            $this->info('Exchange rate already updated today.');
            return;
        }

        $this->info('Fetching exchange rate from BCCR...');
        $rate = $apiBCCR->obtenerTipoCambio(318, now()->format('Y/m/d'));

        if ($rate) {
            $business->update([
                'exchange_rate' => $rate,
                'exchange_rate_updated_at' => now(),
            ]);
            $this->info("Exchange rate updated to: {$rate}");
            Log::info("BCCR: Exchange rate updated successfully via Scheduled Task: {$rate}");
        } else {
            $this->error('Failed to fetch exchange rate from BCCR.');
            Log::error('BCCR: Failed to update exchange rate via Scheduled Task.');
        }
    }
}
