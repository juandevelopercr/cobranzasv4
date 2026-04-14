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
    <label class="form-label" for="nfecha_entrega_requerimiento_pago">{{ __('Fecha de entrega requerimiento pago') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_entrega_requerimiento_pago"
        wire:model="nfecha_entrega_requerimiento_pago"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_entrega_requerimiento_pago') is-invalid @enderror">
    </div>
    @error('nfecha_entrega_requerimiento_pago')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_entrega_orden_captura">{{ __('Fecha de entrega orden de captura') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_entrega_orden_captura"
        wire:model="nfecha_entrega_orden_captura"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_entrega_orden_captura') is-invalid @enderror">
    </div>
    @error('nfecha_entrega_orden_captura')
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_ultima_liquidacion">{{ __('Fecha de última liquidación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_ultima_liquidacion"
        wire:model="nfecha_ultima_liquidacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_ultima_liquidacion') is-invalid @enderror">
    </div>
    @error('nfecha_ultima_liquidacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="ncomentarios">{{ __('Comentarios') }}</label>
    <textarea class="form-control" wire:model="ncomentarios" name="ncomentarios" id="ncomentarios" rows="2"></textarea>
    @error('ncomentarios')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="npartes_notificadas">{{ __('Partes Notificadas') }}</label>
    <textarea class="form-control" wire:model="npartes_notificadas" name="npartes_notificadas" id="npartes_notificadas" rows="2"></textarea>
    @error('npartes_notificadas')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
