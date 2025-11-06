<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_traslado_juzgado">{{ __('Fecha Traslado Juzgado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_traslado_juzgado"
        wire:model="nfecha_traslado_juzgado"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_traslado_juzgado') is-invalid @enderror">
    </div>
    @error('nfecha_traslado_juzgado')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_notificacion_todas_partes">{{ __('Fecha Notificación Todas las Partes') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_notificacion_todas_partes"
        wire:model="nfecha_notificacion_todas_partes"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_notificacion_todas_partes') is-invalid @enderror">
    </div>
    @error('nfecha_notificacion_todas_partes')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="nmarchamo">{{ __('Marchamo') }}</label>
    <div wire:ignore>
      <select wire:model.live="nmarchamo" id="nmarchamo" class="select2 form-select @error('nmarchamo') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Al día">Al día</option>
         <option value="Pendiente">Pendiente</option>
      </select>
    </div>
    @error('nmarchamo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="nanotaciones">{{ __('Anotaciones') }}</label>
    <textarea class="form-control" wire:model="nanotaciones" name="nanotaciones" id="nanotaciones" rows="2"></textarea>
    @error('nanotaciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_ultima_liquidacion">{{ __('Fecha de última liquidación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_ultima_liquidacion"
        wire:model="nfecha_ultima_liquidacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_ultima_liquidacion') is-invalid @enderror">
    </div>
    @error('nfecha_ultima_liquidacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="npartes_notificadas">{{ __('Partes Notificadas') }}</label>
    <textarea class="form-control" wire:model="npartes_notificadas" name="npartes_notificadas" id="npartes_notificadas" rows="2"></textarea>
    @error('npartes_notificadas')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 fv-plugins-icon-container">
    <label class="form-label" for="nubicacion_garantia">{{ __('Ubicación de la garantía') }}</label>
    <textarea class="form-control" wire:model="nubicacion_garantia" name="nubicacion_garantia" id="nubicacion_garantia" rows="2"></textarea>
    @error('nubicacion_garantia')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 fv-plugins-icon-container">
    <label class="form-label" for="ntalleres_situaciones">{{ __('Talleres o situaciones especiales') }}</label>
    <textarea class="form-control" wire:model="ntalleres_situaciones" name="ntalleres_situaciones" id="ntalleres_situaciones" rows="2"></textarea>
    @error('ntalleres_situaciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_notificacion">{{ __('Fecha de notificación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_notificacion"
        wire:model="nfecha_notificacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_notificacion') is-invalid @enderror">
    </div>
    @error('nfecha_notificacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  @php
  /*
  <div class="col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="ncomentarios">{{ __('Comentarios') }}</label>
    <textarea class="form-control" wire:model="ncomentarios" name="ncomentarios" id="ncomentarios" rows="2"></textarea>
    @error('ncomentarios')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  */
  @endphp

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nhonorarios_notificacion">{{ __('Honorarios por notificación') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $nhonorarios_notificacion ?? '' }}',
        wireModelName: 'nhonorarios_notificacion',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('nhonorarios_notificacion', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="nhonorarios_notificacion" x-ref="cleaveInput" wire:ignore class="form-control js-input-nhonorarios_notificacion"
        >
      </div>
    </div>
    @error('nhonorarios_notificacion')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nhonorarios_cobro_administrativo">{{ __('Honorarios cobro administrativo') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $nhonorarios_cobro_administrativo ?? '' }}',
        wireModelName: 'nhonorarios_cobro_administrativo',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('nhonorarios_cobro_administrativo', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="nhonorarios_cobro_administrativo" x-ref="cleaveInput" wire:ignore class="form-control js-input-nhonorarios_cobro_administrativo"
        >
      </div>
    </div>
    @error('nhonorarios_cobro_administrativo')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="nexonerado_cobro">{{ __('Exonerado de cobro') }}</label>
    <div wire:ignore>
      <select wire:model.live="nexonerado_cobro" id="nexonerado_cobro" class="select2 form-select @error('nexonerado_cobro') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Si">Si</option>
         <option value="No">No</option>
      </select>
    </div>
    @error('nexonerado_cobro')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_pago">{{ __('Fecha de pago') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="nfecha_pago"
        wire:model="nfecha_pago"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('nfecha_pago') is-invalid @enderror">
    </div>
    @error('nfecha_pago')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
