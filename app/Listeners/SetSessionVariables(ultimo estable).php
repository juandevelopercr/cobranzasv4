<?php

namespace App\Listeners;

use App\Models\Bank;
use App\Models\Department;
use App\Models\User;
use App\Models\UserRoleDepartmentBank;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SetSessionVariables
{
  // Bandera estática para evitar doble ejecución
  private static $executed = false;

  public function handle(Login $event)
  {
    // Evitar doble ejecución
    if (self::$executed) {
      return;
    }

    self::$executed = true;

    try {
      // Obtener ID de asignación
      $assignmentId = session('login_assignment_id');
      $user = $event->user;

      // Limpiar sesión temporal
      Session::forget('login_assignment_id');

      // Manejar acceso total (IDs que empiezan con 'full-')
      if (str_starts_with($assignmentId, 'full-')) {
        $roleId = str_replace('full-', '', $assignmentId);
        $role = $user->roles()->where('roles.id', (int)$roleId)->first();

        if (!$role) {
          throw new \Exception("Rol con ID {$roleId} no encontrado");
        }

        $this->handleFullAccessRole($user, $roleId, $role);
        return;
      }

      // Obtener la asignación completa
      $assignment = UserRoleDepartmentBank::with(['role', 'department'])
        ->where('id', $assignmentId)
        ->where('user_id', $user->id)
        ->first();


      if (!$assignment) {
        throw new \Exception("Asignación no encontrada");
      }

      // Obtener bancos para esta asignación específica
      $banks = UserRoleDepartmentBank::where('user_id', $user->id)
        ->where('role_id', $assignment->role_id)
        ->where('department_id', $assignment->department_id)
        ->pluck('bank_id')
        ->unique()
        ->toArray();

      // Establecer sesión
      Session::put([
        'current_role' => $assignment->role_id,
        'current_department' => $assignment->department_id,
        'current_banks' => $banks,
        'current_role_name' => $assignment->role->name,
        'current_department_name' => $assignment->department->name,
        'is_full_access' => false
      ]);
    } catch (\Exception $e) {
      // ... manejo de errores ...
    }
  }

  private function handleFullAccessRole(User $user, $roleId, $role)
  {
    // Obtener todos los bancos
    $banks = Bank::where('active', 1)->get()->pluck('id')->toArray();

    // Obtener el primer departamento
    $departments = Department::where('active', 1)->get()->pluck('id')->toArray();

    // Establecer sesión
    Session::put([
      'current_role' => $roleId,
      'current_department' => $departments,
      'current_banks' => $banks,
      'current_role_name' => $role->name,
      'current_department_name' => 'Todos los Departamentos',
      'is_full_access' => true
    ]);
  }
}
