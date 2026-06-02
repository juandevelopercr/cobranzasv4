<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar permanentemente los registros soft-deleted cuya identificación
        // ya existe en un registro activo (deleted_at IS NULL).
        DB::statement("
            DELETE FROM contacts
            WHERE deleted_at IS NOT NULL
            AND identification IN (
                SELECT identification FROM (
                    SELECT identification
                    FROM contacts
                    WHERE deleted_at IS NULL
                ) AS active_identifications
            )
        ");
    }

    public function down(): void
    {
        // No se puede revertir una eliminación de datos sin un backup previo.
    }
};
