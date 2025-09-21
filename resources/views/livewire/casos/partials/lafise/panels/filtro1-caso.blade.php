<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f1fecha_asignacion_capturador">{{ __('Fecha de asignaciòn a capturador') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="f1fecha_asignacion_capturador"
        wire:model="f1fecha_asignacion_capturador"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('f1fecha_asignacion_capturador') is-invalid @enderror">
    </div>
    @error('f1fecha_asignacion_capturador')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f1proveedor_servicio">{{ __('Proveedor del servicio') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="f1proveedor_servicio" id="f1proveedor_servicio" class="form-control @error('f1proveedor_servicio') is-invalid @enderror">
    </div>
    @error('f1proveedor_servicio')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="f1estado_captura">{{ __('Estado de captura filtro1') }}</label>
    <div wire:ignore>
      <select wire:model.live="f1estado_captura" id="f1estado_captura" class="select2 form-select @error('f1estado_captura') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Pendiente de captura">Pendiente de captura</option>
         <option value="Remanente filtro 2">Remanente filtro 2</option>
         <option value="Otras">Otras</option>
      </select>
    </div>
    @error('f1estado_captura')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f1honorarios_capturador">{{ __('Honorarios capturador') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $f1honorarios_capturador ?? '' }}',
        wireModelName: 'f1honorarios_capturador',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('f1honorarios_capturador', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="f1honorarios_capturador" x-ref="cleaveInput" wire:ignore class="form-control js-input-f1honorarios_capturador"
        >
      </div>
    </div>
    @error('f1honorarios_capturador')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f1honorarios_comision">{{ __('Honorarios comisiòn CCC') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $f1honorarios_comision ?? '' }}',
        wireModelName: 'f1honorarios_comision',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('f1honorarios_comision', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="f1honorarios_comision" x-ref="cleaveInput" wire:ignore class="form-control js-input-f1honorarios_comision"
        >
      </div>
    </div>
    @error('f1honorarios_comision')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="f1avance_cronologico">{{ __('Avance Cronológico') }}</label>
    <textarea class="form-control" wire:model="f1avance_cronologico" name="f1avance_cronologico" id="f1avance_cronologico" rows="5"></textarea>
    @error('f1avance_cronologico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

</div>
