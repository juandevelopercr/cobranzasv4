<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_traspaso">{{ __('Fecha de pago de traspaso') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_traspaso"
        wire:model="tfecha_traspaso"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_traspaso') is-invalid @enderror">
    </div>
    @error('tfecha_traspaso')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="thonorarios_traspaso">{{ __('Honorarios de traspaso') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $thonorarios_traspaso ?? '' }}',
        wireModelName: 'thonorarios_traspaso',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('thonorarios_traspaso', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="thonorarios_traspaso" x-ref="cleaveInput" wire:ignore class="form-control js-input-thonorarios_traspaso"
        >
      </div>
    </div>
    @error('thonorarios_traspaso')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tgastos_traspaso">{{ __('Gastos de traspaso') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $tgastos_traspaso ?? '' }}',
        wireModelName: 'tgastos_traspaso',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('tgastos_traspaso', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="tgastos_traspaso" x-ref="cleaveInput" wire:ignore class="form-control js-input-tgastos_traspaso"
        >
      </div>
    </div>
    @error('tgastos_traspaso')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_envio_borrador_escritura">{{ __('Fecha de envio borrador de escritura') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_envio_borrador_escritura"
        wire:model="tfecha_envio_borrador_escritura"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_envio_borrador_escritura') is-invalid @enderror">
    </div>
    @error('tfecha_envio_borrador_escritura')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="tborrador_escritura">{{ __('Borrador de escritura') }}</label>
    <textarea class="form-control" wire:model="tborrador_escritura" name="tborrador_escritura" id="tborrador_escritura" rows="2"></textarea>
    @error('tborrador_escritura')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_firma_escritura">{{ __('Fecha de firma de escritura') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_firma_escritura"
        wire:model="tfecha_firma_escritura"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_firma_escritura') is-invalid @enderror">
    </div>
    @error('tfecha_firma_escritura')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_presentacion_escritura">{{ __('Fecha de presentaciòn de la escritura') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_presentacion_escritura"
        wire:model="tfecha_presentacion_escritura"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_presentacion_escritura') is-invalid @enderror">
    </div>
    @error('tfecha_presentacion_escritura')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_comunicacion">{{ __('Fecha de comunicado para recolecciòn de tìtulo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_comunicacion"
        wire:model="tfecha_comunicacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_comunicacion') is-invalid @enderror">
    </div>
    @error('tfecha_comunicacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="tautorizacion_tercero">{{ __('Autorizaciòn a tercero') }}</label>
    <textarea class="form-control" wire:model="tautorizacion_tercero" name="tautorizacion_tercero" id="tautorizacion_tercero" rows="2"></textarea>
    @error('tautorizacion_tercero')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_entrega_titulo_propiedad">{{ __('Fecha de entrega de tìtulo de propiedad') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_entrega_titulo_propiedad"
        wire:model="tfecha_entrega_titulo_propiedad"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_entrega_titulo_propiedad') is-invalid @enderror">
    </div>
    @error('tfecha_entrega_titulo_propiedad')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tfecha_exclusion">{{ __('Fecha de exclusiòn') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="tfecha_exclusion"
        wire:model="tfecha_exclusion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('tfecha_exclusion') is-invalid @enderror">
    </div>
    @error('tfecha_exclusion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
