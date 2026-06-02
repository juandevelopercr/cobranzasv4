<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $database = DB::connection()->getDatabaseName();

        // Obtener dinámicamente todas las tablas y columnas con FK a contacts
        $foreignKeys = DB::select("
            SELECT TABLE_NAME, COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = ?
              AND REFERENCED_TABLE_NAME = 'contacts'
              AND REFERENCED_COLUMN_NAME = 'id'
        ", [$database]);

        // Buscar pares: contacto soft-deleted con la misma identificación que uno activo
        $duplicates = DB::select("
            SELECT trashed.id as trashed_id, active.id as active_id
            FROM contacts trashed
            JOIN contacts active
                ON active.identification = trashed.identification
               AND active.deleted_at IS NULL
            WHERE trashed.deleted_at IS NOT NULL
        ");

        foreach ($duplicates as $pair) {
            // Reasignar todas las FK al contacto activo
            foreach ($foreignKeys as $fk) {
                // La tabla pivot de actividades económicas se elimina, no se reasigna
                if ($fk->TABLE_NAME === 'contacts_economic_activities') {
                    DB::table($fk->TABLE_NAME)
                        ->where($fk->COLUMN_NAME, $pair->trashed_id)
                        ->delete();
                } else {
                    DB::table($fk->TABLE_NAME)
                        ->where($fk->COLUMN_NAME, $pair->trashed_id)
                        ->update([$fk->COLUMN_NAME => $pair->active_id]);
                }
            }

            // Eliminar permanentemente el contacto soft-deleted
            DB::table('contacts')->where('id', $pair->trashed_id)->delete();
        }
    }

    public function down(): void
    {
        // No se puede revertir una eliminación de datos sin un backup previo.
    }
};
