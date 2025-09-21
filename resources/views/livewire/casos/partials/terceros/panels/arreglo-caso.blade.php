<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_aprobacion_arreglo">{{ __('Fecha de suspención por arreglo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_aprobacion_arreglo"
        wire:model="afecha_aprobacion_arreglo"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_aprobacion_arreglo') is-invalid @enderror">
    </div>
    @error('afecha_aprobacion_arreglo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="aregistro_pago">{{ __('Registro de pago') }}</label>
    <textarea class="form-control" wire:model="aregistro_pago" name="aregistro_pago" id="aregistro_pago" rows="2"></textarea>
    @error('aregistro_pago')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_envio_cotizacion_gasto">{{ __('Fecha envio cotizaciòn gastos traspaso') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_envio_cotizacion_gasto"
        wire:model="afecha_envio_cotizacion_gasto"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_envio_cotizacion_gasto') is-invalid @enderror">
    </div>
    @error('afecha_envio_cotizacion_gasto')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="atraspaso_tercero">{{ __('Traspaso con tercero') }}</label>
    <textarea class="form-control" wire:model="atraspaso_tercero" name="atraspaso_tercero" id="atraspaso_tercero" rows="2"></textarea>
    @error('atraspaso_tercero')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
