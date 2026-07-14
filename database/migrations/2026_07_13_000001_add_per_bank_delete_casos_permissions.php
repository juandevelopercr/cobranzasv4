<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * El permiso "delete-casos" era global: cualquier rol que lo tuviera (incluso
     * ABOGADOCARTERA) habilitaba el botón Eliminar en la tabla de casos de TODOS
     * los bancos, no solo del banco al que pertenecía el rol. Aquí se crea un
     * permiso "delete-{banco}-casos" por banco (igual que ya existe para "view")
     * y se preserva el acceso que ya tenían los roles administrativos.
     */
    private array $bankPermissions = [
        'delete-scotiabank-casos',
        'delete-scotiabank-bch-casos',
        'delete-bac-casos',
        'delete-banco-general-casos',
        'delete-terceros-casos',
        'delete-coocique-casos',
        'delete-coocique2-casos',
        'delete-davivienda-casos',
        'delete-lafise-casos',
        'delete-cafsa-casos',
        'delete-cartera-casos',
    ];

    public function up(): void
    {
        foreach ($this->bankPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Roles administrativos que ya tenían "delete-casos" global: conservan
        // la capacidad de eliminar en todos los bancos.
        $adminRoles = Role::whereIn('name', ['Administrador', 'JefeArea', 'Socio'])->get();
        foreach ($adminRoles as $role) {
            $role->givePermissionTo($this->bankPermissions);
        }

        // ABOGADOCARTERA solo debía poder eliminar casos de Cartera Comprada.
        $carteraRole = Role::where('name', 'ABOGADOCARTERA')->first();
        if ($carteraRole) {
            $carteraRole->givePermissionTo('delete-cartera-casos');
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', $this->bankPermissions)->delete();
    }
};
