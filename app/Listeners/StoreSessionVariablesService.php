<?php

namespace App\Listeners;

use App\Models\Bank;
use App\Models\Business;
use App\Models\Department;
use App\Models\User;
use App\Services\ApiBCCR;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class StoreSessionVariablesService
{
  protected $apiBCCR;

  /**
   * Constructor para inyectar dependencias.
   */
  public function __construct(ApiBCCR $apiBCCR)
  {
    $this->apiBCCR = $apiBCCR;
  }

  /**
   * Gestiona y guarda las variables de sesión.
   */
  public function storeVariables($user)
  {
    try {
      // Esto lo pongo fijo de momento luego hay que ver la lógica a seguir
      $user->business_id = 1;

      $bussines = Business::findOrFail($user->business_id);

      // Otras variables de sesión
      Session::put('user.business_id', $user->business_id);
      Session::put('user.name', $user->name);
      Session::put('user.business', $bussines);

      // Llama al método para obtener el tipo de cambio
      $response = $this->apiBCCR->obtenerIndicadorEconomico(
        318, // Indicador del tipo de cambio
        now()->format('d/m/Y'), // Fecha de inicio
        now()->format('d/m/Y')  // Fecha de fin
      );

      $exchange_rate = '';

      if ($response) {
        // Procesar el XML devuelto
        $exchange_rate = $response;
      }

      // Guarda el tipo de cambio en la sesión
      Session::put('exchange_rate', $exchange_rate);
      Log::info('Tipo de cambio y variables de usuario guardadas en la sesión.', [
        'exchange_rate' => $exchange_rate,
        'user' => $user->name,
      ]);

      // Nuevo proceso para las variables del rol del usuario 
      // Obtener ID de asignación      
      $assignmentId = session('login_assignment_id');

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

      // Obtener la asignación específica
      if (str_contains($assignmentId, '-')) {
        [$roleId, $departmentId] = explode('-', $assignmentId);

        // Verificar si existe la asignación en la tabla de departamentos
        $assignmentExists = DB::table('user_role_department')
          ->where('user_id', $user->id)
          ->where('role_id', $roleId)
          ->where('department_id', $departmentId)
          ->exists();

        if (!$assignmentExists) {
          throw new \Exception("Asignación no encontrada");
        }

        // Obtener bancos para esta asignación específica
        $banks = DB::table('user_role_department_banks')
          ->where('user_id', $user->id)
          ->where('role_id', $roleId)
          ->where('department_id', $departmentId)
          ->pluck('bank_id')
          ->toArray();

        // Obtener nombres
        $role = Role::find($roleId);
        $department = Department::find($departmentId);

        // Establecer sesión
        Session::put([
          'current_role' => $roleId,
          'current_department' => [$departmentId],
          'current_banks' => $banks,
          'current_role_name' => $role ? $role->name : 'Rol no encontrado',
          'current_department_name' => $department ? $department->name : 'Departamento no encontrado',
          'is_full_access' => false
        ]);
      } else {
        throw new \Exception("Formato de ID de asignación no reconocido");
      }

      // Guardar configuración de correo en sesión
      /*
      Session::put('mail_config', [
          'host_smtp' => $business->host_smtp,
          'port_smtp' => $business->puerto_smtp,
          'encryption_smtp' => $business->smtp_encryptation,
          'username_smpt' => $business->user_smtp,
          'password_smtp' => $business->pass_smtp,
          'from_address_smtp' => $business->user_smtp,
          'from_name_smtp' => $business->name,

          'host_imap' => $business->host_imap,
          'port_imap' => $business->puerto_imap,
          'encryption_imap' => $business->imap_encryptation,
          'username_imap' => $business->user_imap,
          'password_imap' => $business->pass_imap,
          'from_address_imap' => $business->user_imap,
          'from_name_imap' => $business->name,
      ]);
      */
    } catch (\Exception $e) {
      Log::error('Error al procesar variables de sesión.', ['error' => $e->getMessage()]);
      //throw $e;
    }
  }

  private function handleFullAccessRole(User $user, $roleId, $role)
  {
    // Para roles de acceso completo, no necesitamos bancos específicos
    // Obtener todos los departamentos activos
    $departments = Department::where('active', 1)->pluck('id')->toArray();

    // Obtener todos los bancos activos
    $banks = Bank::where('active', 1)->pluck('id')->toArray();

    // Establecer sesión
    Session::put([
      'current_role' => $roleId,
      'current_department' => $departments, // Todos los departamentos
      'current_banks' => $banks, // Todos los bancos activos
      'current_role_name' => $role->name,
      'current_department_name' => 'Todos los Departamentos',
      'is_full_access' => true
    ]);
  }

  /**
   * Procesar la respuesta XML para obtener el tipo de cambio.
   */
  /*
    protected function procesarRespuesta($xmlResponse)
    {
        $xml = simplexml_load_string($xmlResponse);
        if ($xml === false) {
            //throw new \Exception('No se pudo interpretar el XML');
            Log::error('No se pudo interpretar el XML');
        }

        // Navega por los nodos para encontrar el tipo de cambio
        $tipoCambio = (string) $xml->INGC011_CAT_INDICADORECONOMIC->INGC011_CAT_INDICADORECONOMIC->NUM_VALOR;

        return $tipoCambio;
    }
    */
}
