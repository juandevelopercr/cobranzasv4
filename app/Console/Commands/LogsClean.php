<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Spatie\Activitylog\Models\Activity;

class LogsClean extends Command
{
    protected $signature = 'logs:clean {--days=30 : Días de retención de archivos de log}';
    protected $description = 'Elimina archivos de log con más de N días y registros de actividad con más de 1 año';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);
        $logPath = storage_path('logs');
        $deleted = 0;
        $errors = 0;

        // Eliminar archivos de log físicos más viejos que $days días
        foreach (File::files($logPath) as $file) {
            // Solo rotar archivos con fecha en el nombre (formato laravel-YYYY-MM-DD.log o scheduler-YYYY-MM-DD.log)
            if (!preg_match('/\d{4}-\d{2}-\d{2}/', $file->getFilename())) {
                continue;
            }

            $lastModified = Carbon::createFromTimestamp($file->getMTime());
            if ($lastModified->lt($cutoff)) {
                try {
                    File::delete($file->getPathname());
                    $deleted++;
                    $this->line("Eliminado: {$file->getFilename()}");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Error al eliminar {$file->getFilename()}: {$e->getMessage()}");
                }
            }
        }

        // Eliminar registros de actividad (Spatie) con más de 1 año
        $activityDeleted = Activity::where('created_at', '<', now()->subYear())->delete();

        $this->info("Archivos de log eliminados: {$deleted} (errores: {$errors})");
        $this->info("Registros de actividad eliminados: {$activityDeleted}");
    }
}
