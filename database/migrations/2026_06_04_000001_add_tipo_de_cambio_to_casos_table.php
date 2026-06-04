<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $table->decimal('tipo_de_cambio', 10, 2)->nullable()->after('psaldo_dolarizado');
        });
    }

    public function down(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $table->dropColumn('tipo_de_cambio');
        });
    }
};
