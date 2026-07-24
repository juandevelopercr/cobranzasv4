<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Ver docs/casos/04-plan-migracion-campos-numericos.md
// Las 7 columnas del plan. psaldo_dolarizado y asaldo_capital_operacion
// requirieron una limpieza previa de miles de filas (formato coma-decimal
// costarricense, símbolos de moneda, y casos con patrones irregulares
// revisados y aprobados uno por uno) antes de poder incluirlas aquí — ver
// docs/casos/scripts/clean_all_final.php.
//
// No se usa Schema::table()->decimal(...)->change(): la tabla `casos` tiene
// 307 columnas, y un MODIFY que cambia el tipo de varchar a decimal
// (requiere reescribir la fila completa) dispara un bug/límite interno de
// MySQL 8 en tablas tan anchas:
//   SQLSTATE[HY000]: 1366 Incorrect DECIMAL value: '0' for column '' at row -1
// Confirmado que MODIFY sin cambiar el tipo (varchar a varchar) SÍ funciona,
// y que ADD COLUMN / DROP COLUMN / RENAME COLUMN funcionan bien. Por eso el
// approach es: columna nueva -> backfill con CAST -> verificar -> drop la
// vieja -> renombrar la nueva. Cada paso es una operación que ya se probó
// que funciona en esta tabla.
return new class extends Migration
{
    private array $columns = [
        'amonto_avaluo',
        'asaldo_capital_operacion',
        'asaldo_capital_operacion_usd',
        'pmonto_retencion_colones',
        'pmonto_retencion_dolares',
        'psaldo_dolarizado',
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
                $tmp = "{$column}_reverting";
                DB::statement("alter table casos add column `$tmp` varchar(190) null");
                DB::statement("update casos set `$tmp` = CAST(`$column` AS CHAR) where `$column` is not null");
                DB::statement("alter table casos drop column `$column`");
                DB::statement("alter table casos rename column `$tmp` to `$column`");
            }
        });
    }

    private function migrateColumn(string $column): void
    {
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
