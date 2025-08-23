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
      Log::warning('SetSessionVariables ya ejecutado, evitando segunda ejecución');
      return;
    }

    self::$executed = true;

    try {
      // Obtener datos básicos
      $roleId = session('login_role_id');
      $user = $event->user;

      // Verificar si el rol es válido
      if (!$roleId) {
        Log::error('SetSessionVariables: roleId es nulo', [
          'user_id' => $user->id,
          'session' => session()->all()
        ]);

        Auth::logout();
        return redirect()->route('login')->withErrors([
          'role_id' => 'Debe seleccionar un rol para continuar'
        ]);
      }

      // Limpiar sesión temporal
      Session::forget('login_role_id');

      // Obtener el rol seleccionado
      $selectedRole = $user->roles()->where('roles.id', $roleId)->first();

      if (!$selectedRole) {
        throw new \Exception("Rol con ID {$roleId} no encontrado para el usuario");
      }

      // Verificar si el ROL SELECCIONADO es de acceso total
      $isFullAccessRole = in_array($selectedRole->name, User::ROLES_ALL_DEPARTMENTS);

      // Manejar según tipo de rol
      if ($isFullAccessRole) {
        $this->handleFullAccessRole($user, $roleId);
      } else {
        $this->handleNormalRole($user, $roleId, $selectedRole);
      }

      // Registrar éxito
      Log::info('Variables de sesión establecidas correctamente', [
        'user_id' => $user->id,
        'role_id' => $roleId
      ]);
    } catch (\Exception $e) {
      Log::error('Error en SetSessionVariables: ' . $e->getMessage(), [
        'exception' => $e,
        'user_id' => $user->id ?? null,
        'role_id' => $roleId ?? null
      ]);

      Auth::logout();
      return redirect()->route('login')->withErrors([
        'session' => 'Error crítico al configurar la sesión. Contacte al administrador.'
      ]);
    }
  }

  private function handleFullAccessRole(User $user, $roleId)
  {
    Log::info('Usuario con rol de acceso total', [
      'user_id' => $user->id,
      'role_id' => $roleId
    ]);

    // Obtener todos los bancos (sin ambigüedad)
    $banks = Bank::where('active', 1)->get()->pluck('id')->toArray();

    // Obtener el primer departamento
    $departments = Department::where('active', 1)->get()->pluck('id')->toArray();

    // Obtener nombre del rol con verificación de existencia
    $role = $user->roles()->where('roles.id', $roleId)->first();

    if (!$role) {
      throw new \Exception("Rol con ID {$roleId} no encontrado para el usuario");
    }

    Session::put([
      'current_role' => $roleId,
      'current_role_name' => $role->name,
      'current_department' => $departments,
      'current_banks' => $banks,
      'is_full_access' => true
    ]);

    return;
  }

  private function handleNormalRole(User $user, $roleId, $selectedRole)
  {
    Log::info('Usuario con rol normal', [
      'user_id' => $user->id,
      'role_id' => $roleId,
      'role_name' => $selectedRole->name
    ]);

    // Buscar asignación (primer registro para este rol y usuario)
    $assignment = UserRoleDepartmentBank::where('user_id', $user->id)
      ->where('role_id', $roleId)
      ->first();

    if (!$assignment) {
      Log::error('No se encontró asignación para el usuario', [
        'user_id' => $user->id,
        'role_id' => $roleId,
        'role_name' => $selectedRole->name
      ]);

      Auth::logout();
      return redirect()->route('login')->withErrors([
        'role_id' => 'No tiene asignaciones configuradas para este rol. Contacte al administrador.'
      ]);
    }

    // Obtener bancos específicos para este rol y departamento
    $banks = UserRoleDepartmentBank::where('user_id', $user->id)
      ->where('role_id', $roleId)
      ->where('department_id', $assignment->department_id)
      ->pluck('bank_id')
      ->toArray();

    // Establecer sesión
    Session::put([
      'current_role' => $roleId,
      'current_role_name' => $selectedRole->name,
      'current_department' => $assignment->department_id,
      'current_banks' => $banks,
      'is_full_access' => false
    ]);
  }
}
