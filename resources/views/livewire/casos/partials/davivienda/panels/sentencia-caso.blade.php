<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="sfecha_primer_remate">{{ __('Fecha de celebración de Primer remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="sfecha_primer_remate"
        wire:model="sfecha_primer_remate"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('sfecha_primer_remate') is-invalid @enderror">
    </div>
    @error('sfecha_primer_remate')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="sfecha_segundo_remate">{{ __('Fecha de celebración de Segundo remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="sfecha_segundo_remate"
        wire:model="sfecha_segundo_remate"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('sfecha_segundo_remate') is-invalid @enderror">
    </div>
    @error('sfecha_segundo_remate')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="sfecha_tercer_remate">{{ __('Fecha de celebración de Tercer remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="sfecha_tercer_remate"
        wire:model="sfecha_tercer_remate"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('sfecha_tercer_remate') is-invalid @enderror">
    </div>
    @error('sfecha_tercer_remate')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
