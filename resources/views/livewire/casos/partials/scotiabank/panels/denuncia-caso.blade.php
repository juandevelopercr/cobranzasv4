<div class="row g-6">
  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="ranotacion">{{ __('Anotaciòn') }}</label>
    <div wire:ignore>
      <select wire:model.live="ranotacion" id="ranotacion" class="select2 form-select @error('ranotacion') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Si">Si</option>
         <option value="No">No</option>
      </select>
    </div>
    @error('ranotacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="rmarchamo_al_dia">{{ __('Marchamo al dìa') }}</label>
    <div wire:ignore>
      <select wire:model.live="rmarchamo_al_dia" id="rmarchamo_al_dia" class="select2 form-select @error('rmarchamo_al_dia') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Si">Si</option>
         <option value="No">No</option>
      </select>
    </div>
    @error('rmarchamo_al_dia')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="rpendiente">{{ __('Pendiente') }}</label>
    <div wire:ignore>
      <select wire:model.live="rpendiente" id="rpendiente" class="select2 form-select @error('rpendiente') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Si">Si</option>
         <option value="No">No</option>
      </select>
    </div>
    @error('rpendiente')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="rcausa">{{ __('Causa') }}</label>
    <textarea class="form-control" wire:model="rcausa" name="rcausa" id="rcausa" rows="5"></textarea>
    @error('rcausa')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="rfecha_desinscripcion">{{ __('Fecha de desinscripciòn') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="rfecha_desinscripcion"
        wire:model="rfecha_desinscripcion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('rfecha_desinscripcion') is-invalid @enderror">
    </div>
    @error('rfecha_desinscripcion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="rhonorario_escritura_inscripcion">{{ __('Honorario escritura desinscripciòn') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $rhonorario_escritura_inscripcion ?? '' }}',
        wireModelName: 'rhonorario_escritura_inscripcion',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('rhonorario_escritura_inscripcion', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="rhonorario_escritura_inscripcion" x-ref="cleaveInput" wire:ignore class="form-control js-input-rhonorario_escritura_inscripcion"
        >
      </div>
    </div>
    @error('rhonorario_escritura_inscripcion')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>


  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="rgastos_impuestos">{{ __('Gastos (Impuestos)') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $rgastos_impuestos ?? '' }}',
        wireModelName: 'rgastos_impuestos',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('rgastos_impuestos', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="rgastos_impuestos" x-ref="cleaveInput" wire:ignore class="form-control js-input-rgastos_impuestos"
        >
      </div>
    </div>
    @error('rgastos_impuestos')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

</div>
