<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_traslado_juzgado">{{ __('Fecha Traslado Juzgado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_traslado_juzgado"
        wire:model="nfecha_traslado_juzgado"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_traslado_juzgado') is-invalid @enderror">
    </div>
    @error('nfecha_traslado_juzgado')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_notificacion_todas_partes">{{ __('Fecha Notificación Todas las Partes') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_notificacion_todas_partes"
        wire:model="nfecha_notificacion_todas_partes"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_notificacion_todas_partes') is-invalid @enderror">
    </div>
    @error('nfecha_notificacion_todas_partes')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="nestado_id">{{ __('Estado') }}</label>
    <div wire:ignore>
      <select wire:model.live="nestado_id" id="nestado_id" class="select2 form-select @error('nestado_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->estados as $estado)
          <option value="{{ $estado->id }}">{{ $estado->name }}</option>
        @endforeach
      </select>
    </div>
    @error('nestado_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="npartes_notificadas">{{ __('Partes Notificadas') }}</label>
    <textarea class="form-control" wire:model="npartes_notificadas" name="npartes_notificadas" id="npartes_notificadas" rows="2"></textarea>
    @error('npartes_notificadas')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
