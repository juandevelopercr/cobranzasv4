<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="sfecha_captura">{{ __('Fecha Captura') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="sfecha_captura"
        wire:model="sfecha_captura"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('sfecha_captura') is-invalid @enderror">
    </div>
    @error('sfecha_captura')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="sfecha_sentencia">{{ __('Fecha Sentencia') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="sfecha_sentencia"
        wire:model="sfecha_sentencia"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('sfecha_sentencia') is-invalid @enderror">
    </div>
    @error('sfecha_sentencia')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="sfecha_remate">{{ __('Fecha Remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="sfecha_remate"
        wire:model="sfecha_remate"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('sfecha_remate') is-invalid @enderror">
    </div>
    @error('sfecha_remate')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="noposicion_demanda">{{ __('Estado actual primera notificación demandados') }}</label>
    <div wire:ignore>
      <select wire:model.live="noposicion_demanda" id="noposicion_demanda" class="select2 form-select @error('noposicion_demanda') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="SI">SI</option>
         <option value="NO">NO</option>
      </select>
    </div>
    @error('noposicion_demanda')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_audiencia">{{ __('Fecha de Audiencia') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_audiencia"
        wire:model="nfecha_audiencia"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_audiencia') is-invalid @enderror">
    </div>
    @error('nfecha_audiencia')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
