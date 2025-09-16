<div class="row g-6">
  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_terminacion">{{ __('Fecha de Terminación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_terminacion"
        wire:model="afecha_terminacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_terminacion') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_terminacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="aestado_proceso_general_id">{{ __('Estado Proceso General') }}</label>
    <div wire:ignore>
      <select wire:model.live="aestado_proceso_general_id" id="aestado_proceso_general_id" class="select2 form-select @error('aestado_proceso_general_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->estados as $estado)
          <option value="{{ $estado->id }}">{{ $estado->name }}</option>
        @endforeach
      </select>
    </div>
    @error('aestado_proceso_general_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
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

  <div class="col-md-3 fv-plugins-icon-container">
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

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="fecha_activacion">{{ __('Fecha de activación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="fecha_activacion"
        wire:model="fecha_activacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('fecha_activacion') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('fecha_activacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="codigo_activacion">{{ __('Exonerado de cobro') }}</label>
    <div wire:ignore>
      <select wire:model.live="codigo_activacion" id="codigo_activacion" class="select2 form-select @error('codigo_activacion') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="TN">TN</option>
         <option value="SL">SL</option>
         <option value="AUD">AUD</option>
         <option value="AQ">AQ</option>
         <option value="SG">SG</option>
         <option value="GC">GC</option>
         <option value="MT">MT</option>
         <option value="ST">ST</option>
         <option value="DM">DM</option>
         <option value="OPOS">OPOS</option>
      </select>
    </div>
    @error('codigo_activacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
