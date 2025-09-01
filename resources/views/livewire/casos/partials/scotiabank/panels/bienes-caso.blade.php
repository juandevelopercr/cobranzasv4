<div class="row g-6">
  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bapersonamiento_formal">{{ __('Asesoramiento formal') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="bapersonamiento_formal" id="bapersonamiento_formal" class="form-control @error('bapersonamiento_formal') is-invalid @enderror"
        placeholder="{{ __('Asesoramiento formal') }}">
    </div>
    @error('bapersonamiento_formal')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bfecha_entrega_poder">{{ __('Fecha de entrega del poder') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="bfecha_entrega_poder"
        wire:model="bfecha_entrega_poder"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('bfecha_entrega_poder') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('bfecha_entrega_poder')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bsumaria">{{ __('Sumaria') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="bsumaria" id="bsumaria" class="form-control @error('bsumaria') is-invalid @enderror"
        placeholder="{{ __('Sumaria') }}">
    </div>
    @error('bsumaria')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="bcausa">{{ __('Causa') }}</label>
    <div wire:ignore>
      <select wire:model.live="bcausa" id="bcausa" class="select2 form-select @error('bcausa') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Civil">Civil</option>
         <option value="Embargos">Embargos</option>
         <option value="Penal">Penal</option>
         <option value="Pérdida total">Pérdida total</option>
         <option value="Transito">Transito</option>
         <option value="Traspaso defectuoso">Traspaso defectuoso</option>
         <option value="Otro">Otro</option>
      </select>
    </div>
    @error('bcausa')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bfecha_levantamiento_gravamen">{{ __('Fecha de levantamiento de gravamen') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="bfecha_levantamiento_gravamen"
        wire:model="bfecha_levantamiento_gravamen"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('bfecha_levantamiento_gravamen') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('bfecha_levantamiento_gravamen')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="bestado_levantamiento_id">{{ __('Estado de Marchamo') }}</label>
    <div wire:ignore>
      <select wire:model.live="bestado_levantamiento_id" id="bestado_levantamiento_id" class="select2 form-select @error('bestado_levantamiento_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->estados as $estado)
          <option value="{{ $estado->id }}">{{ $estado->estado }}</option>
        @endforeach
      </select>
    </div>
    @error('bestado_levantamiento_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bproveedores_servicios">{{ __('Proveedores de servicios') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="bproveedores_servicios" id="bproveedores_servicios" class="form-control @error('bproveedores_servicios') is-invalid @enderror"
        placeholder="{{ __('Proveedores de servicios') }}">
    </div>
    @error('bproveedores_servicios')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bgastos_proceso">{{ __('Gastos del proceso') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $bgastos_proceso ?? '' }}',
        wireModelName: 'bgastos_proceso',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('bgastos_proceso', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="bgastos_proceso" x-ref="cleaveInput" wire:ignore class="form-control js-input-bgastos_proceso"
        >
      </div>
    </div>
    @error('bgastos_proceso')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bhonorarios_levantamiento">{{ __('Honorarios levantamiento') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $bhonorarios_levantamiento ?? '' }}',
        wireModelName: 'bhonorarios_levantamiento',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('bhonorarios_levantamiento', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="bhonorarios_levantamiento" x-ref="cleaveInput" wire:ignore class="form-control js-input-bhonorarios_levantamiento"
        >
      </div>
    </div>
    @error('bhonorarios_levantamiento')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bhonorarios_comision">{{ __('Honorarios comisiòn CCC') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $bhonorarios_comision ?? '' }}',
        wireModelName: 'bhonorarios_comision',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('bhonorarios_comision', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="bhonorarios_comision" x-ref="cleaveInput" wire:ignore class="form-control js-input-bhonorarios_comision"
        >
      </div>
    </div>
    @error('bhonorarios_comision')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="bhonorarios_totales">{{ __('Honorarios totales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $bhonorarios_totales ?? '' }}',
        wireModelName: 'bhonorarios_totales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('bhonorarios_totales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="bhonorarios_totales" x-ref="cleaveInput" wire:ignore class="form-control js-input-bhonorarios_totales"
        >
      </div>
    </div>
    @error('bhonorarios_totales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>


</div>
