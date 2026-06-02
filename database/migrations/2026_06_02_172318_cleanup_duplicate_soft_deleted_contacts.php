<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
            // Reasignar transacciones al contacto activo
            DB::table('transactions')
                ->where('contact_id', $pair->trashed_id)
                ->update(['contact_id' => $pair->active_id]);

            // Reasignar registros de cálculo al contacto activo
            DB::table('business_customer_calculo_registros')
                ->where('contact_id', $pair->trashed_id)
                ->update(['contact_id' => $pair->active_id]);

            // Eliminar actividades económicas del duplicado (el contacto activo ya tiene las suyas)
            DB::table('contacts_economic_activities')
                ->where('contact_id', $pair->trashed_id)
                ->delete();

            // Eliminar contactos asociados al duplicado (cascade FK)
            DB::table('contacts_contactos')
                ->where('contact_id', $pair->trashed_id)
                ->delete();

            // Eliminar permanentemente el contacto soft-deleted
            DB::table('contacts')->where('id', $pair->trashed_id)->delete();
        }
    }

    public function down(): void
    {
        // No se puede revertir una eliminación de datos sin un backup previo.
    }
};
