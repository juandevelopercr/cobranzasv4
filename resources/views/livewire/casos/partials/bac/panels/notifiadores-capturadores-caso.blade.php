<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-4 fv-plugins-icon-container">
    <label class="form-label" for="f1fecha_asignacion_capturador">{{ __('Fecha de asignación al capturador') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="f1fecha_asignacion_capturador"
        wire:model="f1fecha_asignacion_capturador"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('f1fecha_asignacion_capturador') is-invalid @enderror">
    </div>
    @error('f1fecha_asignacion_capturador')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-4 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="capturador_id">{{ __('Capturador') }}</label>
    <div wire:ignore>
      <select wire:model.live="capturador_id" id="capturador_id" class="select2 form-select @error('capturador_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->capturadores as $capturador)
          <option value="{{ $capturador->id }}">{{ $capturador->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('capturador_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-4 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="caso_servicio_capturador_id">{{ __('Servicio') }}</label>
    <div wire:ignore>
      <select wire:model.live="caso_servicio_capturador_id" id="caso_servicio_capturador_id" class="select2 form-select @error('caso_servicio_capturador_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->servicios as $servicio)
          <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('caso_servicio_capturador_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-4 fv-plugins-icon-container">
    <label class="form-label" for="f1fecha_asignacion_notificador">{{ __('Fecha de asignación al notificador') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="f1fecha_asignacion_notificador"
        wire:model="f1fecha_asignacion_notificador"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('f1fecha_asignacion_notificador') is-invalid @enderror">
    </div>
    @error('f1fecha_asignacion_notificador')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-4 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="notificador_id">{{ __('Notificador') }}</label>
    <div wire:ignore>
      <select wire:model.live="notificador_id" id="notificador_id" class="select2 form-select @error('notificador_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->notificadores as $notificador)
          <option value="{{ $notificador->id }}">{{ $notificador->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('notificador_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-4 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="caso_servicio_notificador_id">{{ __('Servicio') }}</label>
    <div wire:ignore>
      <select wire:model.live="caso_servicio_notificador_id" id="caso_servicio_notificador_id" class="select2 form-select @error('caso_servicio_notificador_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->servicios as $servicio)
          <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('caso_servicio_notificador_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

</div>
