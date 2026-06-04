<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tabla casos tiene columnas con default '0000-00-00' que MySQL strict
        // mode rechaza al hacer ALTER TABLE. Se desactiva temporalmente.
        DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

        Schema::table('casos', function (Blueprint $table) {
            $table->decimal('tipo_de_cambio', 10, 2)->nullable()->after('psaldo_dolarizado');
        });
    }

    public function down(): void
    {
        DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

        Schema::table('casos', function (Blueprint $table) {
            $table->dropColumn('tipo_de_cambio');
        });
    }
};
