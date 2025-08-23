@php
    use App\Models\User;
    use Spatie\Permission\Models\Role;
@endphp
<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <h5 class="card-header">{{ __('User Information') }}</h5>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('Please fix the following errors:') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body" enctype="multipart/form-data">
    <h6>1. {{ __('Personal Information') }}</h6>
    <div class="row">
      <div class="col-md-12">
        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">

            {{-- Mostrar la imagen temporal si se ha subido una nueva --}}
            @if ($profile_photo_path && method_exists($profile_photo_path, 'temporaryUrl'))
            <img class="d-block w-px-100 h-px-100 rounded" src="{{ $profile_photo_path->temporaryUrl() }}"
              alt="{{ __('Photo') }}" id="uploadedAvatar">
            @elseif ($oldProfile_photo_path)
            <img class="d-block w-px-100 h-px-100 rounded"
              src="{{ asset('storage/assets/img/avatars/' . $oldProfile_photo_path) }}" alt="{{ __('Photo') }}"
              id="uploadedAvatar">
            @else
            <img class="d-block w-px-100 h-px-100 rounded" src="{{ asset('storage/assets/default-image.png') }}"
              alt="{{ __('Photo') }}" id="uploadedAvatar">
            @endif

            <div class="button-wrapper">
              <label for="profile_photo_path" class="btn btn-primary me-3 mb-4" tabindex="0">
                <span class="d-none d-sm-block">{{ __('Upload Photo') }}</span>
                <i class="bx bx-upload d-block d-sm-none"></i>
                <input wire:model.live='profile_photo_path' id="profile_photo_path" hidden class="account-file-input"
                  accept="image/png, image/jpeg" type="file" />
                @error('profile_photo_path')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
              </label>

              <button type="button" class="btn btn-label-secondary account-image-reset mb-4" wire:click="resetPhoto">
                <i class="bx bx-reset d-block d-sm-none"></i>
                <span class="d-none d-sm-block">{{ __('Reset') }}</span>
              </button>
            </div>

            <div class="col" wire:loading.delay wire:target="profile_photo_path">
              <!-- Grid -->
              <div class="sk-grid sk-primary">
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
                <div class="sk-grid-cube"></div>
              </div>
              <span>{{ __('Loading, please wait...') }}</span>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="row g-6">
      <div class="col-md-6 fv-plugins-icon-container">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-user"></i></span>
          <input type="text" wire:model="name" name="name" id="name"
            class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="{{ __('Name') }}"
            aria-label="{{ __('Name') }}" aria-describedby="spanname">
        </div>
        @error('name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="email">{{ __('Email') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-envelope"></i></span>
          <input type="text" wire:model="email" id="email" name="email"
            class="form-control dt-email {{ $errors->has('email') ? 'is-invalid' : '' }}"
            placeholder="{{ __('Email') }}" aria-label="{{ __('Email') }}">
        </div>
        @error('email')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <div class="form-text">
          {{ __('You can use letters and numbers') }}
        </div>
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="initials">{{ __('Initials') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-pen"></i></span>
          <input type="text" wire:model="initials" id="initials" name="initials"
            class="form-control {{ $errors->has('initials') ? 'is-invalid' : '' }}" placeholder="{{ __('Initials') }}"
            aria-label="{{ __('Initials') }}">
        </div>
        @error('initials')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6 fv-plugins-icon-container">
        <div class="form-password-toggle">
          <label class="form-label" for="password">{{ __('Password') }}</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-key"></i></span>
            <input type="password" wire:model="password" name="password" id="password" autocomplete="new-password"
              class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
              aria-describedby="multicol-password2" />
            <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="bx bx-hide"></i></span>
          </div>
          @error('password')
          <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">{{ $message
            }}
          </div>
          @enderror
        </div>
      </div>
      <div class="col-md-6 fv-plugins-icon-container">
        <div class="form-password-toggle">
          <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-key"></i></span>
            <input type="password" wire:model="password_confirmation" name="password_confirmation" id="confirm-password"
              autocomplete="new-password"
              class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
              aria-describedby="multicol-confirm-password2" />
            <span class="input-group-text cursor-pointer" id="confirm-password2"><i class="bx bx-hide"></i></span>
          </div>
          @error('password_confirmation')
          <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">{{ $message
            }}
          </div>
          @enderror
        </div>
      </div>
    </div>
    <br>
    <div class="row g-6">
      <div class="col-md-6 fv-plugins-icon-container">
        <div class="form-check form-switch ms-2 my-2">
          <input type="checkbox" class="form-check-input" id="active" wire:model.defer="active" {{ $active==1
            ? 'checked' : '' }} />

          <label for="future-billing" class="switch-label">{{ __('Active') }}</label>
        </div>
      </div>
    </div>

    <br>
    <!-- Sección de Permisos -->
    <h6>3. {{ __('Permisos') }}</h6>
    @foreach ($roleAssignments as $index => $assignment)
    @php
    $roleId = $assignment['role_id'] ?? null;

    // Usar el helper de roles directamente
    $isFullAccess = false;
    if ($roleId) {
        $role = \Spatie\Permission\Models\Role::find($roleId);
        $isFullAccess = $role ? in_array($role->name, \App\Models\User::ROLES_ALL_DEPARTMENTS) : false;
    }

    // Obtener bancos para el departamento
    $departmentBanks = [];
    if (!$isFullAccess && $assignment['department_id']) {
        $department = \App\Models\Department::with('banks')->find($assignment['department_id']);
        $departmentBanks = $department->banks ?? [];
    }
    @endphp

    <div class="role-assignment border rounded p-3 mb-3" wire:key="assignment-{{ $index }}">
        <!-- Encabezado con botón de eliminar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Asignación #{{ $index + 1 }}</h6>
            <button type="button" class="btn btn-sm btn-danger"
                    wire:click="removeRoleAssignment({{ $index }})"
                    @if(count($roleAssignments) <= 1) disabled @endif>
                <i class="fas fa-trash me-1"></i> Eliminar
            </button>
        </div>

        <div class="row g-3">
            <!-- Selección de Rol -->
            <div class="col-md-3">
                <label class="form-label">{{ __('Role') }}</label>
                <div wire:ignore>
                  <select
                      class="form-select role-select"
                      wire:model="roleAssignments.{{ $index }}.role_id"
                      wire:change="updateRoleAssignment({{ $index }}, $event.target.value)"
                      data-index="{{ $index }}"
                      data-type="role">
                      <option value="">{{ __('Select Role') }}</option>
                      @foreach ($availableRoles as $id => $name)
                          <option value="{{ $id }}" {{ $assignment['role_id'] == $id ? 'selected' : '' }}>
                              {{ $name }}
                          </option>
                      @endforeach
                  </select>
                </div>
                @error("roleAssignments.{$index}.role_id")
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Departamento (solo para roles sin acceso total) -->
            @if (!$isFullAccess)
                <div class="col-md-3">
                    <label class="form-label">{{ __('Department') }}</label>
                    <div wire:ignore>
                    <select
                        class="form-select department-select"
                        wire:model="roleAssignments.{{ $index }}.department_id"
                        wire:change="updateDepartmentAssignment({{ $index }}, $event.target.value)"
                        data-index="{{ $index }}"
                        data-type="department">
                        <option value="">{{ __('Select Department') }}</option>
                        @foreach ($this->departments as $department)
                            <option value="{{ $department->id }}" {{ $assignment['department_id'] == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                    @error("roleAssignments.{$index}.department_id")
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Bancos (solo si hay departamento seleccionado) -->
                @if ($assignment['department_id'])
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Banks') }}</label>
                        <div wire:ignore>
                        <select
                            class="form-select bank-select"
                            wire:model="roleAssignments.{{ $index }}.banks"
                            multiple
                            data-index="{{ $index }}"
                            data-type="bank">
                            @foreach ($departmentBanks  as $bank)
                                <option value="{{ $bank->id }}" {{ in_array($bank->id, $assignment['banks'] ?? []) ? 'selected' : '' }}>
                                    {{ $bank->name }}
                                </option>
                            @endforeach
                        </select>
                        </div>
                        @error("roleAssignments.{$index}.banks")
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            @else
                <div class="col-md-8 d-flex align-items-center">
                    <div class="alert alert-primary mb-0 w-100">
                        <i class="fas fa-info-circle me-2"></i>
                        Este rol tiene acceso completo a todos los departamentos y bancos.
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endforeach

    <!-- Botón para agregar más asignaciones -->
    <button type="button" class="btn btn-primary mb-3" wire:click="addRoleAssignment">
        <i class="fas fa-plus me-2"></i> {{ __('Add Assignment') }}
    </button>


    <div class="pt-6">
      {{-- Incluye botones de guardar y guardar y cerrar --}}
      @include('livewire.includes.button-saveAndSaveAndClose')

      <!-- Botón Cancel -->
      <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="cancel"
        wire:loading.attr="disabled" wire:target="cancel">
        <span wire:loading.remove wire:target="cancel">
          <span class="fa fa-remove bx-18px me-2"></span>{{ __('Cancel') }}
        </span>
        <span wire:loading wire:target="cancel">
          <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Cancelling...') }}
        </span>
      </button>
    </div>
  </form>

  {{-- @livewire('users.user-activity-timeline') --}}
</div>

@script
<script>
$(document).ready(function() {
    console.log("Documento listo - Iniciando script");

    function initSelect2(element) {
        console.log("Inicializando Select2 para elemento:", element);
        
        const isMultiple = $(element).prop('multiple');
        console.log("¿Es múltiple?", isMultiple);
        
        const dropdownParent = $(element).closest('.role-assignment');
        console.log("Dropdown parent encontrado:", dropdownParent.length ? "Sí" : "No");
        
        const isBankSelect = $(element).data('type') === 'bank';
        console.log("¿Es select de bancos?", isBankSelect);

        const options = {
            width: '100%',
            dropdownParent: dropdownParent,
            placeholder: isMultiple ? 'Seleccione bancos' : 'Seleccione una opción',
            allowClear: !isMultiple,
            closeOnSelect: false
        };

        // Inicializar Select2
        $(element).select2(options);
        console.log("Select2 inicializado para elemento:", element);

        // Agregar funcionalidad de "Seleccionar todos" solo para bancos (multiselect)
        if (isMultiple) {
            console.log("Agregando funcionalidad de 'Seleccionar todos' para bancos");
            
            // Escuchar cuando se abre el dropdown
            $(element).on('select2:open', function(e) {
                console.log("Dropdown abierto para el select de bancos");
                
                // Obtener el contenedor del dropdown
                const dropdown = $('.select2-container--open .select2-dropdown');
                const results = dropdown.find('.select2-results');
                
                // Generar ID único para este toggle
                const toggleId = `s2-togall-${$(element).data('index')}`;
                
                // Si ya existe el botón, solo actualizamos su estado
                if ($(`#${toggleId}`).length) {
                    console.log("El botón ya existe, actualizando estado");
                    updateToggleButton(element, toggleId);
                    return;
                }
                
                console.log("Agregando botón toggle al dropdown");
                const toggleButton = $(`
                    <span id="${toggleId}" class="s2-togall-button" style="cursor:pointer; padding: 5px 10px; display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; border-bottom: 1px solid #eee;">
                        <span class="s2-select-label"><i class="fas fa-square me-2 text-secondary"></i> Seleccionar todo</span>
                        <span class="s2-unselect-label" style="display:none;"><i class="fas fa-check-square text-primary me-2"></i> Deseleccionar todo</span>
                    </span>
                `);
                
                results.before(toggleButton);
                updateToggleButton(element, toggleId);
                
                // Manejador de clic para el botón
                toggleButton.on('click', function(e) {
                    e.stopPropagation();
                    console.log("Click en el botón toggle");
                    
                    const $select = $(element);
                    const allOptions = $select.find('option').map(function() {
                        return $(this).val();
                    }).get();
                    
                    const selected = $select.val() || [];
                    const allSelected = selected.length === allOptions.length;
                    
                    if (allSelected) {
                        console.log("Deseleccionando todos");
                        $select.val([]);
                    } else {
                        console.log("Seleccionando todos");
                        $select.val(allOptions);
                    }
                    
                    $select.trigger('change');
                    updateToggleButton(element, toggleId);
                    
                    // Cerrar el dropdown después de la acción
                    $select.select2('close');
                });
            });
        }

        // Sincronizar con Livewire
        $(element).on('change', function(e) {
            console.log("Cambio detectado en select:", this);
            
            const index = $(this).data('index');
            const type = $(this).data('type');
            const value = $(this).val();
            
            console.log("Datos:", {index, type, value});
            
            const finalValue = isMultiple ? (Array.isArray(value) ? value : [value]) : value;
            console.log("Valor final a enviar a Livewire:", finalValue);
            
            @this.set(`roleAssignments.${index}.${type === 'bank' ? 'banks' : type + '_id'}`, finalValue);
            console.log("Valor enviado a Livewire");
            
            // Actualizar el botón si es un select de bancos
            if (type === 'bank') {
                const toggleId = `s2-togall-${index}`;
                updateToggleButton(this, toggleId);
            }
        });
    }

    // Función para actualizar el estado del botón toggle
    function updateToggleButton(selectElement, toggleId) {
        const $select = $(selectElement);
        const allOptions = $select.find('option').map(function() {
            return $(this).val();
        }).get();
        const selected = $select.val() || [];
        const allSelected = selected.length === allOptions.length;
        
        const toggle = $(`#${toggleId}`);
        if (toggle.length) {
            toggle.find('.s2-select-label').toggle(!allSelected);
            toggle.find('.s2-unselect-label').toggle(allSelected);
        }
    }

    function initializeAllSelects() {
        console.groupCollapsed("Inicializando todos los selects");
        console.log("Buscando selects...");
        
        const $selects = $('.role-select, .department-select, .bank-select');
        console.log("Encontrados", $selects.length, "selects");
        
        $selects.each(function(index) {
            console.log(`Procesando select #${index}:`, this);
            
            if (!$(this).hasClass('select2-hidden-accessible')) {
                console.log("Select2 no inicializado - Inicializando");
                initSelect2(this);
            } else {
                console.log("Select2 ya inicializado - Saltando");
            }
        });
        
        console.groupEnd();
    }

    // Inicializar al cargar
    console.log("Iniciando inicialización inicial");
    initializeAllSelects();

    // Re-inicializar con Livewire
    Livewire.on('reinitFormControls', () => {
        console.log("Livewire: reinitFormControls recibido");
        
        setTimeout(() => {
            console.log("Reinicializando controles después de 300ms");
            initializeAllSelects();
        }, 300);
    });
    
    console.log("Escuchadores configurados");
});
</script>
@endscript

@php
/*  
@script
<script>
$(document).ready(function() {
    function initSelect2(element) {
        const isMultiple = $(element).prop('multiple');
        const dropdownParent = $(element).closest('.role-assignment');

        $(element).select2({
            width: '100%',
            dropdownParent: dropdownParent,
            placeholder: isMultiple ? 'Seleccione bancos' : 'Seleccione una opción',
            allowClear: !isMultiple
        });

        // Manejar cambios para sincronizar con Livewire
        $(element).on('change', function(e) {
            const index = $(this).data('index');
            const type = $(this).data('type');
            const value = $(this).val();

            // Para selects múltiples, asegurarse de que siempre sea un array
            const finalValue = isMultiple ? (Array.isArray(value) ? value : [value]) : value;

            // Actualizar Livewire
            @this.set(`roleAssignments.${index}.${type === 'bank' ? 'banks' : type + '_id'}`, finalValue);
        });
    }

    // Inicializar todos los selects al cargar
    function initializeAllSelects() {
        console.log("Entró al initializeAllSelects");
        $('.role-select, .department-select, .bank-select').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                initSelect2(this);
            }
        });
    }

    // Inicializar al cargar
    initializeAllSelects();

    // Re-inicializar cuando Livewire emita el evento
    Livewire.on('reinitFormControls', () => {
      setTimeout(() => {
            initializeAllSelects();
        }, 300);

    });


});
</script>
@endscript
*/
@endphp
