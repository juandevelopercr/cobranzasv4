<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Ver docs/casos/04-plan-migracion-campos-numericos.md y el incidente del
// 2026-07-23: `agastos_legales` se migró a mano (por SQL directo) mientras
// se investigaba el bug de MySQL en la tabla `casos` (307 columnas), y por
// error se excluyó de la migración
// 2026_07_23_003055_change_money_columns_to_decimal_in_casos_table.php
// pensando que ya estaba resuelta — pero eso solo aplicaba a la copia local
// donde se hizo la prueba manual, nunca quedó en una migración versionada.
// En el servidor real la columna se quedó en varchar mientras el modelo
// Caso ya la trataba como decimal (cast 'agastos_legales' => 'decimal:2'),
// y cualquier fila con '' (válido en varchar) tronaba al leerla
// (Brick\Math\Exception\NumberFormatException).
//
// Esta migración completa lo que debió quedar en la anterior. Usa el mismo
// método seguro (columna nueva + backfill + verificar + drop + rename) en
// vez de MODIFY directo, por el mismo bug de MySQL en tablas anchas ya
// documentado.
return new class extends Migration
{
    private string $column = 'agastos_legales';

    public function up(): void
    {
        $type = DB::selectOne("SHOW COLUMNS FROM casos WHERE Field = '{$this->column}'")->Type;
        if (str_starts_with($type, 'decimal')) {
            // Ya está migrada (pasó por la prueba manual mientras se
            // investigaba el bug de MySQL en esta tabla) — no hay nada que
            // hacer, y comparar una columna decimal contra '' en un UPDATE
            // truena bajo sql_mode estricto (a diferencia de un SELECT).
            return;
        }

        $this->assertColumnIsClean();

        $this->withRelaxedSqlMode(function () {
            $this->migrateColumn();
        });
    }

    public function down(): void
    {
        $this->withRelaxedSqlMode(function () {
            $tmp = "{$this->column}_reverting";
            DB::statement("alter table casos add column `$tmp` varchar(190) null");
            DB::statement("update casos set `$tmp` = CAST(`{$this->column}` AS CHAR) where `{$this->column}` is not null");
            DB::statement("alter table casos drop column `{$this->column}`");
            DB::statement("alter table casos rename column `$tmp` to `{$this->column}`");
        });
    }

    private function migrateColumn(): void
    {
        $column = $this->column;
        $tmp = "{$column}_migrating";

        DB::statement("alter table casos add column `$tmp` decimal(18, 2) null");
        DB::statement("update casos set `$tmp` = CAST(`$column` AS DECIMAL(18,2)) where `$column` is not null and `$column` != ''");

        $before = DB::table('casos')->whereNotNull($column)->where($column, '!=', '')->count();
        $after = DB::table('casos')->whereNotNull($tmp)->count();

        if ($before !== $after) {
            DB::statement("alter table casos drop column `$tmp`");
            throw new \RuntimeException(
                "Migración abortada en columna $column: antes había $before filas con valor, ".
                "después del backfill quedaron $after."
            );
        }

        DB::statement("alter table casos drop column `$column`");
        DB::statement("alter table casos rename column `$tmp` to `$column`");
    }

    private function assertColumnIsClean(): void
    {
        // Si la columna YA es decimal (como en el entorno donde se hizo la
        // prueba manual), no hay nada que hacer.
        $type = DB::selectOne("SHOW COLUMNS FROM casos WHERE Field = '{$this->column}'")->Type;
        if (str_starts_with($type, 'decimal')) {
            return;
        }

        $dirty = DB::table('casos')
            ->whereNotNull($this->column)
            ->where($this->column, '!=', '')
            ->where($this->column, 'NOT REGEXP', '^-?[0-9]+(\\.[0-9]+)?$')
            ->limit(1)
            ->value('id');

        if ($dirty !== null) {
            throw new \RuntimeException(
                "Migración abortada: la columna casos.{$this->column} tiene un valor no numérico ".
                "(id=$dirty). Revisar antes de continuar — no se modificó el esquema."
            );
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
