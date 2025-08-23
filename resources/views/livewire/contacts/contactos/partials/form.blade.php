<!-- Form to add new record -->
<!-- Multi Column with Form Separator -->
<div class="card mb-6">
  <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
    <h6>1. {{ __('General Information') }} Cliente: {{ $this->contactName }}</h6>

    <div class="row g-6">
      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="grupo_empresarial_id">{{ __('Grupo empresarial') }}</label>
        <div wire:ignore>
          <select wire:model="grupo_empresarial_id" id="grupo_empresarial_id" class="select2 form-select @error('grupo_empresarial_id') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->grupos as $grupo)
              <option value="{{ $grupo->id }}">{{ $grupo->name }}</option>
            @endforeach
          </select>
        </div>
        @error('grupo_empresarial_id')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="tipo_cliente">{{ __('Tipo de cliente') }}</label>
        <div wire:ignore>
          <select wire:model="tipo_cliente" id="tipo_cliente" class="select2 form-select @error('tipo_cliente') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->tipos as $tipo)
              <option value="{{ $tipo['id'] }}">{{ $tipo['name'] }}</option>
            @endforeach
          </select>
        </div>
        @error('tipo_cliente')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 select2-primary fv-plugins-icon-container">
        <label class="form-label" for="clasificacion">{{ __('Clasificación') }}</label>
        <div wire:ignore>
          <select wire:model="clasificacion" id="clasificacion" class="select2 form-select @error('clasificacion') is-invalid @enderror">
            <option value="">{{ __('Seleccione...') }}</option>
            @foreach ($this->clasificaciones as $c)
              <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
            @endforeach
          </select>
        </div>
        @error('clasificacion')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-detail"></i></span>
          <input type="text" wire:model="name" name="name" id="name"
            class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('name') }}">
        </div>
        @error('name')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="fecha_nacimiento">{{ __('Fecha de nacimiento') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-calendar"></i></span>
          <input type="text" id="fecha_nacimiento" @if (!$recordId) readonly @endif
            wire:model="fecha_nacimiento"
            x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
            x-init="init($el)"
            wire:ignore
            class="form-control date-picke @error('fecha_nacimiento') is-invalid @enderror"
            placeholder="dd-mm-aaaa">
        </div>
        @error('fecha_nacimiento')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="email">{{ __('Email') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-envelope"></i></span>
          <input type="text" wire:model="email" id="email" name="email"
            class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email') }}"
            aria-label="{{ __('Email') }}">
        </div>
        @error('email')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="telefono">{{ __('Phone') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-phone"></i></span>
          <input type="text" wire:model="telefono" class="form-control @error('telefono') is-invalid @enderror">
        </div>
        @error('telefono')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="ext">{{ __('Ext') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-phone"></i></span>
          <input type="text" wire:model="ext" class="form-control @error('ext') is-invalid @enderror">
        </div>
        @error('ext')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="celular">{{ __('Celular') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-phone"></i></span>
          <input type="text" wire:model="celular" class="form-control @error('celular') is-invalid @enderror">
        </div>
        @error('celular')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3 fv-plugins-icon-container">
        <label class="form-label" for="anno_ingreso">{{ __('Año ingreso') }}</label>
        <div class="input-group input-group-merge has-validation">
          <span class="input-group-text"><i class="bx bx-phone"></i></span>
          <input type="text" wire:model="anno_ingreso" class="form-control @error('anno_ingreso') is-invalid @enderror">
        </div>
        @error('anno_ingreso')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
          <label class="form-label">{{ __('Área práctica') }}</label>
          <div wire:ignore>
              <select class="form-select bank-select select2" multiple wrire:model="areasPracticas" id="areasPracticas">
                  @foreach ($areas as $area)
                      <option value="{{ $area->id }}">{{ $area->name }}
                      </option>
                  @endforeach
              </select>
          </div>
          @error("areasPracticas")
              <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>

      <div class="col-md-6">
          <label class="form-label">{{ __('Sectores industriales') }}</label>
          <div wire:ignore>
              <select class="form-select bank-select select2" multiple wrire:model="sectoresIndustriales" id="sectoresIndustriales">
                  @foreach ($sectores as $sector)
                      <option value="{{ $sector->id }}">{{ $sector->name }}
                      </option>
                  @endforeach
              </select>
          </div>
          @error("sectoresIndustriales")
              <div class="text-danger mt-1">{{ $message }}</div>
          @enderror
      </div>
    </div>
    <br>

    <div class="row g-6">
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
    </div>
  </form>
</div>
