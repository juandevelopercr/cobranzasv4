<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_terminacion">{{ __('Fecha de Terminación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_terminacion"
        wire:model="tfecha_terminacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_terminacion') is-invalid @enderror">
    </div>
    @error('tfecha_terminacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="testado_proceso_id">{{ __('Estado Proceso General') }}</label>
    <div wire:ignore>
      <select wire:model.live="testado_proceso_id" id="testado_proceso_id" class="select2 form-select @error('testado_proceso_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->estados as $estado)
          <option value="{{ $estado->id }}">{{ $estado->name }}</option>
        @endforeach
      </select>
    </div>
    @error('testado_proceso_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tgastos_legales">{{ __('Gastos Legales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $tgastos_legales ?? '' }}',
        wireModelName: 'tgastos_legales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('tgastos_legales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="tgastos_legales" x-ref="cleaveInput" wire:ignore class="form-control js-input-tgastos_legales"
        >
      </div>
    </div>
    @error('tgastos_legales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="thonorarios_totales">{{ __('Honorarios Totales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $thonorarios_totales ?? '' }}',
        wireModelName: 'thonorarios_totales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('thonorarios_totales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="thonorarios_totales" x-ref="cleaveInput" wire:ignore class="form-control js-input-thonorarios_totales"
        >
      </div>
    </div>
    @error('thonorarios_totales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
