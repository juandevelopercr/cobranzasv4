<div class="row g-6">
  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="fhonorarios_levantamiento">{{ __('Honorarios Levantamiento') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $fhonorarios_levantamiento ?? '' }}',
        wireModelName: 'fhonorarios_levantamiento',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('fhonorarios_levantamiento', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="fhonorarios_levantamiento" x-ref="cleaveInput" wire:ignore class="form-control js-input-fhonorarios_levantamiento"
        >
      </div>
    </div>
    @error('fhonorarios_levantamiento')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="fcomision_ccc">{{ __('Comisión CCC') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $fcomision_ccc ?? '' }}',
        wireModelName: 'fcomision_ccc',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('fcomision_ccc', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="fcomision_ccc" x-ref="cleaveInput" wire:ignore class="form-control js-input-fcomision_ccc"
        >
      </div>
    </div>
    @error('fcomision_ccc')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="fhonorarios_totales">{{ __('Honorarios Totales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $fhonorarios_totales ?? '' }}',
        wireModelName: 'fhonorarios_totales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('fhonorarios_totales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="fhonorarios_totales" x-ref="cleaveInput" wire:ignore class="form-control js-input-fhonorarios_totales"
        >
      </div>
    </div>
    @error('fhonorarios_totales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

</div>
