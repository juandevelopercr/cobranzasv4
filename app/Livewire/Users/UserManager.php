<?php

namespace App\Livewire\Users;

use App\Exports\UsersExport;
use App\Helpers\Helpers;
use App\Livewire\BaseComponent;
use App\Models\Bank;
use App\Models\DataTableConfig;
use App\Models\Department;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserManager extends BaseComponent
{
  use WithFileUploads;
  use WithPagination;

  #[Url(history: true)]
  public $search = '';

  #[Url(as: 'userActive', history: true)]
  public $active = '';

  #[Url(history: true)]
  public $sortBy = 'users.name';

  #[Url(as: 'userSort', history: true)]
  public $sortDir = 'ASC';

  #[Url()]
  public $perPage = 10;

  public $action = 'list';
  public $recordId = '';

  public $name;
  public $email;
  public $initials;
  public $password;
  public $password_confirmation;
  public $department_id;
  public $profile_photo_path;

  public $oldProfile_photo_path = NULL; // Imagen existente en la BD

  public $closeForm = false;

  public $columns;
  public $defaultColumns;
  public $listActives;

  #[Computed()]
  public function listroles()
  {
    return Role::all();
  }

  #[Computed()]
  public function departments()
  {
    return Department::orderBy('name', 'asc')->get();
  }

  #[Computed()]
  public function listbancos()
  {
    return Bank::where('active', 1)->orderBy('name', 'asc')->get();
  }

  #[Computed]
  public function departmentsWithBanks()
  {
    return Department::with('banks')->get();
  }

  #[Computed]
  public function allBanks()
  {
    return Bank::all();
  }

  /*
  public function getBanksForDepartment($departmentId)
  {
    if (!$departmentId) return collect();

    return $this->departmentsWithBanks->firstWhere('id', $departmentId)?->banks ?? collect();
  }
  */

  public function getBanksForDepartment($departmentId)
  {
    if (!$departmentId) return collect();

    // Cachear resultados para mejor performance
    return once(function () use ($departmentId) {
      return Department::with('banks')->find($departmentId)?->banks ?? collect();
    });
  }

  //#[Url()]
  public $roles = [];
  public $bancos = [];
  public $departamentos = [];

  public $roleAssignments = [];
  public $availableRoles = [];
  public $allDepartments = [];

  // Escuha el evento del componente customerModal
  protected $listeners = [
    'datatableSettingChange' => 'refresDatatable',
    'dateRangeSelected' => 'dateRangeSelected',
    'department-changed' => 'updateBanksForDepartment'
  ];

  protected function getModelClass(): string
  {
    return User::class;
  }

  public function mount()
  {
    $this->refresDatatable();

    if (session('current_role_name') == User::SUPERADMIN) {
      $this->availableRoles = Role::orderBy('id', 'DESC')->pluck('name', 'id')->toArray();
    } else {
      $this->availableRoles = Role::where('name', '<>', User::SUPERADMIN)->orderBy('id', 'DESC')->pluck('name', 'id')->toArray();
    }

    //$this->availableRoles = Role::all()->pluck('name', 'id')->toArray();
    $this->allDepartments = Department::all()->keyBy('id');

    if ($this->action == 'create') {
      $this->addRoleAssignment(); // Iniciar con un campo vacío
    }

    $this->listActives = [['id' => 1, 'name' => 'Activo'], ['id' => 0, 'name' => 'Inactivo']];
  }

  public function render()
  {
    $users = User::search($this->search, $this->filters) // Utiliza el scopeSearch para la búsqueda
      //->when($this->active !== '', function ($query) {
      //$query->where('users.active', $this->active);
      //})
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    $userCount = User::get()->count();
    $userActive = User::where('active', 1)->get()->count();
    $notActive = User::where('active', 0)->get()->count();
    $usersUnique = $users->unique(['email']);
    $userDuplicates = $users->diff($usersUnique)->count();

    $percentActive = $userCount > 0 ? $userActive / $userCount * 100 : 0;
    $percentActive = Helpers::formatDecimal($percentActive);

    $percentNoActive = $userCount > 0 ? $notActive / $userCount * 100 : 0;
    $percentNoActive = Helpers::formatDecimal($percentNoActive);

    $percentDuplicate = $userCount > 0 ? $userDuplicates / $userCount * 100 : 0;
    $percentDuplicate = Helpers::formatDecimal($percentDuplicate);

    return view('livewire.user-manager.user-manager', [
      'users' => $users,
      'totalUser' => $userCount,
      'userActive' => $userActive,
      'notActive' => $notActive,
      'userDuplicates' => $userDuplicates,
      'percentActive' => $percentActive,
      'percentNoActive' => $percentNoActive,
      'percentDuplicate' => $percentDuplicate
    ]);
  }

  public function updatedActive($value)
  {
    $this->active = (int) $value;
  }

  public function create()
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    // Resetear todas las asignaciones de roles
    $this->roleAssignments = [];

    // Agregar una asignación vacía
    $this->addRoleAssignment();

    $this->active = 1;
    $this->action = 'create';
    $this->dispatch('scroll-to-top');
  }

  public function rules()
  {
    $rules = [
      'name'          => 'required|string|max:255',
      'email'         => 'required|string|email|max:255|unique:users,email,' . $this->recordId,
      'initials'      => 'required|string|max:30',
      'password'      => 'nullable|string|min:8|confirmed',
      'roleAssignments' => [
        'required',
        'array',
        'min:1',
        function ($attribute, $value, $fail) {
          $hasValidRole = collect($value)->contains(function ($assignment) {
            return !empty($assignment['role_id']);
          });

          if (!$hasValidRole) {
            $fail('Debe asignar al menos un rol al usuario');
          }
        }
      ],
      'active'        => 'required|integer|in:0,1',
    ];

    // Precargar roles
    $roleIds = collect($this->roleAssignments)
      ->pluck('role_id')
      ->filter()
      ->unique()
      ->toArray();

    $roles = !empty($roleIds)
      ? Role::whereIn('id', $roleIds)->get()->keyBy('id')
      : collect();

    $uniqueCombinations = [];

    foreach ($this->roleAssignments as $index => $assignment) {
      $roleId = $assignment['role_id'] ?? null;
      $deptId = $assignment['department_id'] ?? null;

      if ($roleId) {
        $rules["roleAssignments.$index.role_id"] = 'required|exists:roles,id';

        // Obtener el modelo del rol
        $role = $roles->get($roleId);

        // Solo aplicar reglas de departamento/bancos si el rol NO es de acceso completo
        if ($role && !in_array($role->name, User::ROLES_ALL_DEPARTMENTS)) {
          $comboKey = $roleId . '-' . $deptId;

          if ($deptId) {
            if (in_array($comboKey, $uniqueCombinations)) {
              $rules["roleAssignments.$index.combo"] = 'unique_combo';
            } else {
              $uniqueCombinations[] = $comboKey;
            }
          }

          $rules["roleAssignments.$index.department_id"] = 'required|exists:departments,id';
          $rules["roleAssignments.$index.banks"] = 'required|array|min:1';
          $rules["roleAssignments.$index.banks.*"] = 'exists:banks,id';
        }
      }
    }

    if (empty($this->recordId)) {
      $rules['password'] = 'required|string|min:8|confirmed';
    }

    $this->dispatch('reinitFormControls');

    return $rules;
  }

  public function messages()
  {
    return [
      'required' => 'El campo :attribute es obligatorio.',
      'email.unique' => 'El :attribute ya está registrado.',
      'password.confirmed' => 'Las contraseñas no coinciden.',
      'password.min' => 'La clave debe tener cómo mínimo 8 caracteres',
      'bancos.required' => 'Debe seleccionar al menos un banco.',
      'bancos.min' => 'Debe seleccionar al menos un banco.',
      'departamentos.required' => 'Debe seleccionar al menos un departamento.',
      'departamentos.min' => 'Debe seleccionar al menos un departamento.',

      'unique_combo' => 'La combinación de rol y departamento ya existe en otra asignación.',
      'roleAssignments.*.department_id.required' => 'Debe seleccionar un departamento para el rol.',
      'roleAssignments.*.banks.required' => 'Debe seleccionar al menos un banco para el rol.',
    ];
  }

  public function validationAttributes()
  {
    return [
      'name' => 'nombre',
      'email' => 'correo electrónico',
      'initials' => 'iniciales',
      'password' => 'contraseña',
      'password_confirmation' => 'confirmación de contraseña',
      'department_id' => 'departamento',
      'roles' => 'roles',
      'bancos' => 'banco',
      'active' => 'estado',
    ];
  }

  public function store()
  {
    // Validación de los datos de entrada
    $validatedData = $this->validate();

    // Validar la imagen solo si existe una nueva imagen
    if ($this->profile_photo_path) {
      $this->validate([
        'profile_photo_path' => 'image|mimes:jpg,jpeg,png,gif|max:2048',
      ]);
    }

    try {
      if ($this->profile_photo_path) {
        $imageName = uniqid() . '.' . $this->profile_photo_path->extension();
        $this->profile_photo_path->storeAs('assets/img/avatars', $imageName, 'public');
        $validatedData['profile_photo_path'] = $imageName;
      }

      $password = $validatedData['password'];

      // Crear el usuario con la contraseña encriptada
      $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'initials' => $validatedData['initials'],
        'password' => Hash::make($validatedData['password']),
        'active' => $validatedData['active'],
        'profile_photo_path' => $validatedData['profile_photo_path'] ?? null,
      ]);

      $closeForm = $this->closeForm;

      // Obtener IDs de roles únicos
      $roleIds = collect($this->roleAssignments)
        ->pluck('role_id')
        ->filter()
        ->unique()
        ->toArray();

      // Pre-cargar todos los roles involucrados
      $roles = Role::whereIn('id', $roleIds)->get()->keyBy('id');

      // Preparar listas para diferentes tipos de asignaciones
      $globalRoles = [];
      $departmentAssignments = [];

      foreach ($this->roleAssignments as $assignment) {
        $role = $roles[$assignment['role_id']] ?? null;
        if (!$role) continue;

        if (in_array($role->name, User::ROLES_ALL_DEPARTMENTS)) {
          $globalRoles[] = $role->name;
        } elseif ($assignment['department_id']) {
          $departmentAssignments[] = $assignment;
        }
      }

      // Construir lista completa de roles (nombres) para sincronización
      $allRoles = $globalRoles;
      foreach ($departmentAssignments as $assignment) {
        $role = $roles[$assignment['role_id']] ?? null;
        if ($role) {
          $allRoles[] = $role->name;
        }
      }

      // Eliminar duplicados (por si un rol aparece en ambos tipos)
      $allRoles = array_unique($allRoles);

      // Sincronizar TODOS los roles
      $user->syncRoles($allRoles);

      // Eliminar asignaciones departamentales previas
      DB::table('user_role_department')->where('user_id', $user->id)->delete();
      DB::table('user_role_department_banks')->where('user_id', $user->id)->delete();

      // Procesar asignaciones específicas
      $departmentPivots = [];
      $bankRecords = [];

      foreach ($departmentAssignments as $assignment) {
        $role = $roles[$assignment['role_id']] ?? null;
        $departmentId = $assignment['department_id'];

        if (!$role || !$departmentId) continue;

        $departmentPivots[] = [
          'user_id' => $user->id,
          'role_id' => $role->id,
          'department_id' => $departmentId
        ];

        foreach ($assignment['banks'] ?? [] as $bankId) {
          $bankRecords[] = [
            'user_id' => $user->id,
            'role_id' => $role->id,
            'department_id' => $departmentId,
            'bank_id' => $bankId
          ];
        }
      }

      // Inserciones masivas
      if (!empty($departmentPivots)) {
        DB::table('user_role_department')->insert($departmentPivots);
      }

      if (!empty($bankRecords)) {
        DB::table('user_role_department_banks')->insert($bankRecords);
      }


      $this->resetControls();
      if ($closeForm) {
        $this->action = 'list';
      } else {
        $this->action = 'edit';
        $this->edit($user->id);
      }

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been created')]);

      // Enviar email al usuario con las credenciales
      $this->afterCreateUser($user->name, $user->email, $password);
    } catch (\Exception $e) {
      // Manejo de errores
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while creating the registro') . ' ' . $e->getMessage()]);
    }
  }

  /*
  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $this->recordId = $recordId;
    $user = User::with([
      'roles',
      'departments' => function ($query) {
        $query->withPivot('role_id');
      },
      'banks'
    ])->findOrFail($recordId);

    $this->name = $user->name;
    $this->email = $user->email;
    $this->initials = $user->initials;
    $this->active = $user->active;
    $this->profile_photo_path = $user->profile_photo_path;
    $this->oldProfile_photo_path = $user->profile_photo_path;

    $this->roleAssignments = $this->formatRoleAssignments($user);

    $this->action = 'edit';

    $this->roles = $user->getRoleNames()->toArray();

    $this->roleAssignments = [];

    // Agrupar asignaciones por combinación única rol-departamento
    $groupedAssignments = [];

    foreach ($user->roleAssignments as $assignment) {
      $key = $assignment->role_id . '-' . $assignment->department_id;

      if (!isset($groupedAssignments[$key])) {
        $groupedAssignments[$key] = [
          'role_id' => $assignment->role_id,
          'department_id' => $assignment->department_id,
          'banks' => []
        ];
      }

      if ($assignment->bank_id) {
        $groupedAssignments[$key]['banks'][] = $assignment->bank_id;
      }
    }

    $this->roleAssignments = array_values($groupedAssignments);

    // Si no hay asignaciones, agregar una vacía
    if (empty($this->roleAssignments)) {
      $this->addRoleAssignment();
    }

    $this->bancos = $user->banks->pluck('id')->toArray();
    $this->departamentos = $user->departments->pluck('id')->toArray();

    // Cargar la imagen actual guardada en la base de datos
    $this->oldProfile_photo_path = $user->profile_photo_path;

    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val

    //$this->dispatch('reinit-form-controls-delayed');
    $this->dispatch('reinitFormControls');
  }
  */
  public function edit($recordId)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    $this->recordId = $recordId;
    $user = User::with([
      'roles',
      'departments' => function ($query) {
        $query->withPivot('role_id');
      },
      'banks'
    ])->findOrFail($recordId);

    $this->name = $user->name;
    $this->email = $user->email;
    $this->initials = $user->initials;
    $this->active = $user->active;
    $this->profile_photo_path = $user->profile_photo_path;
    $this->oldProfile_photo_path = $user->profile_photo_path;

    // RESETEAR LAS ASIGNACIONES DE ROLES
    $this->roleAssignments = [];

    // 1. PRIMERO CARGAR ROLES DE ACCESO COMPLETO (SPATIE)
    foreach ($user->roles as $role) {
      if (in_array($role->name, User::ROLES_ALL_DEPARTMENTS)) {
        $this->roleAssignments[] = [
          'role_id' => $role->id,
          'department_id' => null,
          'banks' => []
        ];
      }
    }

    // 2. LUEGO CARGAR ROLES CON DEPARTAMENTOS (TABLAS PERSONALIZADAS)
    $departmentAssignments = DB::table('user_role_department')
      ->where('user_id', $user->id)
      ->get();

    foreach ($departmentAssignments as $assignment) {
      $banks = DB::table('user_role_department_banks')
        ->where('user_id', $user->id)
        ->where('role_id', $assignment->role_id)
        ->where('department_id', $assignment->department_id)
        ->pluck('bank_id')
        ->toArray();

      $this->roleAssignments[] = [
        'role_id' => $assignment->role_id,
        'department_id' => $assignment->department_id,
        'banks' => $banks
      ];
    }

    // Si no hay asignaciones, agregar una vacía
    if (empty($this->roleAssignments)) {
      $this->addRoleAssignment();
    }

    $this->action = 'edit';

    // Eliminar estas líneas redundantes
    // $this->roles = $user->getRoleNames()->toArray();
    // $this->bancos = $user->banks->pluck('id')->toArray();
    // $this->departamentos = $user->departments->pluck('id')->toArray();

    $this->resetErrorBag();
    $this->resetValidation();
    $this->dispatch('reinitFormControls');
  }

  public function update()
  {
    $recordId = $this->recordId;
    $validatedData = $this->validate();

    // Validar la imagen solo si existe una nueva imagen
    if ($this->profile_photo_path instanceof \Illuminate\Http\UploadedFile) {
      $this->validate([
        'profile_photo_path' => 'image|mimes:jpg,jpeg,png,gif|max:2048',
      ]);
    }

    try {
      $user = User::findOrFail($recordId);

      // Procesa la nueva imagen si se subió
      if ($this->profile_photo_path instanceof \Illuminate\Http\UploadedFile) {
        // Crear la carpeta si no existe
        $directory = 'assets/img/avatars';
        if (!Storage::disk('public')->exists($directory)) {
          Storage::disk('public')->makeDirectory($directory);
        }

        // Eliminar la imagen anterior si existe
        if ($this->oldProfile_photo_path) {
          Storage::disk('public')->delete($directory . '/' . $this->oldProfile_photo_path);
        }

        // Guardar la nueva imagen
        $imageName = uniqid() . '.' . $this->profile_photo_path->extension();
        $this->profile_photo_path->storeAs($directory, $imageName, 'public');
        $validatedData['profile_photo_path'] = $imageName;
      } else {
        // Mantener la imagen anterior
        $validatedData['profile_photo_path'] = $this->oldProfile_photo_path;
      }

      // Actualizar datos básicos del usuario
      $updateData = [
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'initials' => $validatedData['initials'],
        'active' => $validatedData['active'],
        'profile_photo_path' => $validatedData['profile_photo_path']
      ];

      if (!empty($validatedData['password'])) {
        $updateData['password'] = Hash::make($validatedData['password']);
      }

      $user->update($updateData);

      // Procesar asignaciones de roles
      $this->processRoleAssignments($user);

      // Actualizar el estado del componente
      $this->refreshAfterUpdate($user);

      $this->selectedIds = [];
      $this->dispatch('updateSelectedIds', $this->selectedIds);

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The record has been updated')
      ]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while updating the record') . ': ' . $e->getMessage()
      ]);
    }
  }

  protected function processRoleAssignments(User $user)
  {
    $roleIds = collect($this->roleAssignments)
      ->pluck('role_id')
      ->filter()
      ->unique()
      ->toArray();

    $roles = Role::whereIn('id', $roleIds)->get()->keyBy('id');

    // Preparar listas
    $rolesToSync = [];
    $departmentAssignments = [];

    foreach ($this->roleAssignments as $assignment) {
      $roleId = $assignment['role_id'] ?? null;
      if (!$roleId) continue;

      $role = $roles[$roleId] ?? Role::find($roleId);
      if (!$role) continue;

      $rolesToSync[] = $role->name;

      // Solo procesar departamentos si NO es rol de acceso completo
      if (!in_array($role->name, User::ROLES_ALL_DEPARTMENTS)) {
        $departmentAssignments[] = [
          'role' => $role,
          'department_id' => $assignment['department_id'] ?? null,
          'banks' => $assignment['banks'] ?? []
        ];
      }
    }

    // Sincronizar roles (elimina todos y agrega los nuevos)
    $user->syncRoles(array_unique($rolesToSync));

    // Procesar asignaciones departamentales
    $this->processDepartmentAssignments($user, $departmentAssignments);
  }

  protected function processDepartmentAssignments(User $user, $assignments)
  {
    // Eliminar todas las asignaciones existentes
    DB::table('user_role_department')->where('user_id', $user->id)->delete();
    DB::table('user_role_department_banks')->where('user_id', $user->id)->delete();

    $departmentPivots = [];
    $bankRecords = [];

    foreach ($assignments as $assignment) {
      if (!$assignment['department_id']) continue;

      $departmentPivots[] = [
        'user_id' => $user->id,
        'role_id' => $assignment['role']->id,
        'department_id' => $assignment['department_id']
      ];

      foreach ($assignment['banks'] as $bankId) {
        $bankRecords[] = [
          'user_id' => $user->id,
          'role_id' => $assignment['role']->id,
          'department_id' => $assignment['department_id'],
          'bank_id' => $bankId
        ];
      }
    }

    // Inserciones masivas
    if (!empty($departmentPivots)) {
      DB::table('user_role_department')->insert($departmentPivots);
    }

    if (!empty($bankRecords)) {
      DB::table('user_role_department_banks')->insert($bankRecords);
    }
  }

  protected function refreshAfterUpdate(User $user)
  {
    $this->roleAssignments = $this->formatRoleAssignments($user);
    if ($this->closeForm) {
      //dd("Closed");
      $this->action = 'list';
    } else {
      $this->action = 'edit';
      $this->edit($user->id);
      /*
      $this->recordId = $user->id;
      $this->name = $user->name;
      $this->email = $user->email;
      $this->initials = $user->initials;
      $this->active = $user->active;
      $this->profile_photo_path = $user->profile_photo_path;
      $this->oldProfile_photo_path = $user->profile_photo_path;
      */
    }

    //$this->dispatch('reinitFormControls');
  }

  // AÑADIR ESTE MÉTODO PARA FORMATAR LAS ASIGNACIONES
  protected function formatRoleAssignments(User $user)
  {
    $assignments = [];

    // 1. Roles de acceso completo (Spatie)
    foreach ($user->roles as $role) {
      if (in_array($role->name, User::ROLES_ALL_DEPARTMENTS)) {
        $assignments[] = [
          'role_id' => $role->id,
          'department_id' => null,
          'banks' => []
        ];
      }
    }

    // 2. Roles con departamentos
    $departmentAssignments = DB::table('user_role_department')
      ->where('user_id', $user->id)
      ->get();

    foreach ($departmentAssignments as $assignment) {
      $banks = DB::table('user_role_department_banks')
        ->where('user_id', $user->id)
        ->where('role_id', $assignment->role_id)
        ->where('department_id', $assignment->department_id)
        ->pluck('bank_id')
        ->toArray();

      $assignments[] = [
        'role_id' => $assignment->role_id,
        'department_id' => $assignment->department_id,
        'banks' => $banks
      ];
    }

    return $assignments;
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    $recordId = $this->getRecordAction($recordId);

    if (!$recordId) {
      return; // Ya se lanzó la notificación desde getRecordAction
    }

    // static::getName() devuelve automáticamente el nombre del componente Livewire actual, útil para dispatchTo.
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforedelete()
  {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $user = User::findOrFail($recordId);

      if ($user->id == 1) {
        $this->dispatch('show-notification', ['type' => 'error', 'message' => __('The user superadmin cannot be deleted')]);
      } else {
        // Verifica si el usuario tiene una foto de perfil y si el archivo realmente existe en el disco
        if ($user->profile_photo_path && Storage::disk('public')->exists('assets/img/avatars/' . $user->profile_photo_path)) {
          // Elimina la imagen solo si existe
          Storage::disk('public')->delete('assets/img/avatars/' . $user->profile_photo_path);
        }

        if ($user->delete()) {

          $this->selectedIds = array_filter(
            $this->selectedIds,
            fn($selectedId) => $selectedId != $recordId
          );

          // Opcional: limpiar "seleccionar todo" si ya no aplica
          if (empty($this->selectedIds)) {
            $this->selectAll = false;
          }

          // Emitir actualización
          $this->dispatch('updateSelectedIds', $this->selectedIds);

          // Puedes emitir un evento para redibujar el datatable o actualizar la lista
          $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
        }
      }
    } catch (\Exception $e) {
      // Registrar el error y mostrar un mensaje de error al usuario
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while deleting the registro') . ' ' . $e->getMessage()]);
    }
  }

  public function updated($propertyName)
  {
    // Elimina el error de validación del campo actualizado
    $this->resetErrorBag($propertyName);
  }

  public function updatedPerPage($value)
  {
    $this->resetPage(); // Resetea la página a la primera cada vez que se actualiza $perPage
  }

  public function cancel()
  {
    $this->action = 'list';
    $this->resetControls();
    $this->dispatch('scroll-to-top');
  }

  public function resetControls()
  {
    $this->reset(
      'name',
      'email',
      'initials',
      'password',
      'password_confirmation',
      'department_id',
      'active',
      'roles',
      'profile_photo_path',
      'closeForm',
    );

    $this->selectedIds = [];
    $this->dispatch('updateSelectedIds', $this->selectedIds);

    $this->recordId = '';
    $this->oldProfile_photo_path = '';

    // Añadir una asignación vacía después del reset
    $this->addRoleAssignment();
  }

  public function setSortBy($sortByField)
  {
    if ($this->sortBy === $sortByField) {
      $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
      return;
    }

    $this->sortBy = $sortByField;
    $this->sortDir = 'DESC';
  }

  public function updatedSearch()
  {
    $this->resetPage();
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'user-datatable')
      ->first();

    if ($config) {
      // Verifica si ya es un array o si necesita decodificarse
      $columns = is_array($config->columns) ? $config->columns : json_decode($config->columns, true);
      $this->columns = array_values($columns); // Asegura que los índices se mantengan correctamente
      $this->perPage = $config->perPage  ?? 10; // Valor por defecto si viene null
    } else {
      $this->columns = $this->getDefaultColumns();
      $this->perPage = 10;
    }
  }

  public $filters = [
    'filter_name' => NULL,
    'filter_departments' => NULL,
    'filter_role' => NULL,
    'filter_email' => NULL,
    'filter_initials' => NULL,
    'filter_created_at' => NULL,
    'filter_active' => NULL,
  ];

  public function getDefaultColumns()
  {
    $this->defaultColumns = [
      [
        'field' => 'name',
        'orderName' => 'users.name',
        'label' => __('Name'),
        'filter' => 'filter_name',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnName',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'departments',
        'orderName' => '',
        'label' => __('Department'),
        'filter' => 'filter_departments',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlcolumnDepartment',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'roles',
        'orderName' => '',
        'label' => __('Roles'),
        'filter' => 'filter_role',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlcolumnRoles',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'email',
        'orderName' => 'email',
        'label' => __('Email'),
        'filter' => 'filter_email',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'initials',
        'orderName' => 'initials',
        'label' => __('Initials'),
        'filter' => 'filter_initials',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'created_at',
        'orderName' => 'users.created_at',
        'label' => __('Created at'),
        'filter' => 'filter_created_at',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'active',
        'orderName' => 'users.active',
        'label' => __('Active'),
        'filter' => 'filter_active',
        'filter_type' => 'select',
        'filter_sources' => 'listActives',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => 'center',
        'columnClass' => '',
        'function' => 'getHtmlColumnActive',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'action',
        'orderName' => '',
        'label' => __('Actions'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'action',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ]
    ];

    return $this->defaultColumns;
  }

  public function storeAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de almacenamiento
    $this->store();
  }

  public function updateAndClose()
  {
    // para mantenerse en el formulario
    $this->closeForm = true;

    // Llama al método de actualización
    $this->update();
  }

  public function resetFilters()
  {
    $this->reset('filters');
    //$this->filters['filter_active'] = 1;
    $this->selectedIds = [];
  }

  public function dateRangeSelected($id, $range)
  {
    $this->filters[$id] = $range;
  }

  private function afterCreateUser($name, $email, $password)
  {
    $sent = Helpers::sendUserCredentialEmail($name, $email, $password);

    if ($sent) {
      $menssage = __('An email has been sent to the following address:') . ' ' . $email;

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __("The user's credentials have been sent successfully") . '. ' . $menssage
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }

  #[On('credentialSend')]
  public function credentialSend($recordId)
  {
    // 1. Obtener el usuario
    $user = User::find($recordId);

    if (!$user) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('User not found')
      ]);
      return;
    }

    // 2. Generar nueva contraseña segura
    $newPassword = Str::random(10); // Ej: aB8zXy9LmN

    // 3. Guardarla hasheada en BD
    $user->password = Hash::make($newPassword);
    $user->save();

    // 4. Enviar email con la clave en texto plano
    $sent = Helpers::sendUserCredentialEmail($user->name, $user->email, $newPassword);

    // 5. Notificación según éxito
    if ($sent) {
      $message = __('An email has been sent to the following address:') . ' ' . $user->email;

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __("The user's credentials have been sent successfully") . '. ' . $message
      ]);
    } else {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred, the email could not be sent')
      ]);
    }
  }

  // En el componente
  public function loadBanks($role)
  {
    $this->dispatch('banksUpdated');
  }

  public function updateBanksForDepartment($role, $departmentId)
  {
    if (!$departmentId) {
      return;
    }

    // Actualizar bancos disponibles
    $department = Department::find($departmentId);
    $this->banks[$role] = $department->banks->pluck('id')->toArray();

    // Forzar actualización de la vista
    $this->dispatch('refresh-selects');
  }

  public function updateBanksForRole($index, $departmentId)
  {
    if (!$departmentId) {
      return [];
    }

    $department = Department::with('banks')->find($departmentId);
    return $department ? $department->banks->toArray() : [];
  }

  // Modificar el método addRoleAssignment
  public function addRoleAssignment()
  {
    $this->roleAssignments[] = [
      'role_id' => null,
      'department_id' => null,
      'banks' => []
    ];

    $this->dispatch('assignmentAdded');
  }

  // Método para eliminar asignaciones
  public function removeRoleAssignment($index)
  {
    unset($this->roleAssignments[$index]);
    $this->roleAssignments = array_values($this->roleAssignments);
    $this->dispatch('reinitFormControls');
  }

  // Actualizar bancos cuando cambia un departamento
  public function updatedRoleAssignments($value, $path)
  {
    $pathParts = explode('.', $path);

    Log::debug("updatedRoleAssignments = ", [$pathParts]);

    if (count($pathParts) === 3 && $pathParts[2] === 'department_id') {
      $index = $pathParts[1];
      $this->roleAssignments[$index]['banks'] = [];

      // Disparar evento para actualizar bancos
      $this->dispatch('update-banks', index: $index);
    }

    $this->dispatch('reinitFormControls');
  }

  // Nuevos métodos para manejar cambios en los selects
  public function updateRoleAssignment($index, $roleId)
  {
    $this->roleAssignments[$index]['role_id'] = $roleId;

    Log::debug("roleAssignments = ", [$roleId]);
    // Si el rol es de acceso completo, limpiar departamento y bancos
    $role = Role::find($roleId);
    if ($role && in_array($role->name, User::ROLES_ALL_DEPARTMENTS)) {
      $this->roleAssignments[$index]['department_id'] = null;
      $this->roleAssignments[$index]['banks'] = [];
    }

    $this->dispatch('reinitFormControls');
  }

  public function updateDepartmentAssignment($index, $departmentId)
  {
    Log::debug("updateDepartmentAssignment = ", [$index]);
    $this->roleAssignments[$index]['department_id'] = $departmentId;
    $this->roleAssignments[$index]['banks'] = [];

    // Disparar evento para actualizar el select de bancos
    $this->dispatch('updateBanks', index: $index);
    $this->dispatch('reinitFormControls');
  }

  // Resetear página cuando cambien los filtros
  public function updatedFilters($value, $key)
  {
    $this->resetPage();
  }
}
