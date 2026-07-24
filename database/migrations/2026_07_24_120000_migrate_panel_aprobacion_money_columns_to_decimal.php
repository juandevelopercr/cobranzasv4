<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Segunda tanda de campos de dinero detectados como varchar en el panel
// "Aprobación" (a raíz del reporte de ahonorarios_totales aceptando texto
// libre) — ver docs/casos/01-correcciones-aplicadas.md y
// docs/casos/scripts/clean_panel_aprobacion_campos.php (limpieza de datos,
// ya ejecutada y verificada antes de esta migración).
//
// Mismo método seguro usado en
// 2026_07_23_003055_change_money_columns_to_decimal_in_casos_table.php y
// 2026_07_23_211036_migrate_agastos_legales_column_to_decimal.php: la tabla
// `casos` (307 columnas) dispara un bug/límite de MySQL 8 en un MODIFY que
// cambia varchar -> decimal directo
// (SQLSTATE[HY000]: 1366 Incorrect DECIMAL value: '0' for column '' at row -1),
// así que se usa columna nueva -> backfill con CAST -> verificar conteo ->
// drop la vieja -> renombrar la nueva.
return new class extends Migration
{
    private array $columns = [
        'aestimacion_demanda_en_presentacion',
        'aestimacion_demanda_en_presentacion_usd',
        'ahonorarios_totales',
        'ahonorarios_totales_usd',
        'amonto_cancelar',
        'amonto_incobrable',
        'bgastos_proceso',
        'liquidacion_intereses_aprobada_crc',
        'liquidacion_intereses_aprobada_usd',
        'pmonto_estimacion_demanda_colones',
        'pmonto_estimacion_demanda_dolares',
    ];

    public function up(): void
    {
        $this->assertColumnsAreClean();

        $this->withRelaxedSqlMode(function () {
            foreach ($this->columns as $column) {
                $this->migrateColumn($column);
            }
        });
    }

    public function down(): void
    {
        $this->withRelaxedSqlMode(function () {
            foreach ($this->columns as $column) {
                if (!$this->isDecimal($column)) {
                    continue;
                }
                $tmp = "{$column}_reverting";
                DB::statement("alter table casos add column `$tmp` varchar(200) null");
                DB::statement("update casos set `$tmp` = CAST(`$column` AS CHAR) where `$column` is not null");
                DB::statement("alter table casos drop column `$column`");
                DB::statement("alter table casos rename column `$tmp` to `$column`");
            }
        });
    }

    private function migrateColumn(string $column): void
    {
        if ($this->isDecimal($column)) {
            // Ya migrada en este entorno (por ejemplo, si ya se aplicó a
            // mano durante una prueba) — no hay nada que hacer.
            return;
        }

        $tmp = "{$column}_migrating";

        DB::statement("alter table casos add column `$tmp` decimal(18, 2) null");
        DB::statement("update casos set `$tmp` = CAST(`$column` AS DECIMAL(18,2)) where `$column` is not null and `$column` != ''");

        $before = DB::table('casos')->whereNotNull($column)->where($column, '!=', '')->count();
        $after = DB::table('casos')->whereNotNull($tmp)->count();

        if ($before !== $after) {
            DB::statement("alter table casos drop column `$tmp`");
            throw new \RuntimeException(
                "Migración abortada en columna $column: antes había $before filas con valor, ".
                "después del backfill quedaron $after. No se completó el reemplazo de esta columna."
            );
        }

        DB::statement("alter table casos drop column `$column`");
        DB::statement("alter table casos rename column `$tmp` to `$column`");

        Log::info("Migración casos.$column completada", ['filas_migradas' => $after]);
    }

    private function assertColumnsAreClean(): void
    {
        foreach ($this->columns as $column) {
            if ($this->isDecimal($column)) {
                continue;
            }

            $dirty = DB::table('casos')
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->where($column, 'NOT REGEXP', '^-?[0-9]+(\\.[0-9]+)?$')
                ->limit(1)
                ->value('id');

            if ($dirty !== null) {
                throw new \RuntimeException(
                    "Migración abortada: la columna casos.$column tiene un valor no numérico ".
                    "(id=$dirty). Revisar antes de continuar — no se modificó el esquema."
                );
            }
        }
    }

    private function isDecimal(string $column): bool
    {
        $type = DB::selectOne("SHOW COLUMNS FROM casos WHERE Field = '{$column}'")->Type;

        return str_starts_with($type, 'decimal');
    }

    private function withRelaxedSqlMode(\Closure $callback): void
    {
        $original = DB::selectOne('SELECT @@SESSION.sql_mode as m')->m;
        $relaxed = implode(',', array_diff(explode(',', $original), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']));

        DB::statement("SET SESSION sql_mode = '{$relaxed}'");
        try {
            $callback();
        } finally {
            DB::statement("SET SESSION sql_mode = '{$original}'");
        }
    }
};
