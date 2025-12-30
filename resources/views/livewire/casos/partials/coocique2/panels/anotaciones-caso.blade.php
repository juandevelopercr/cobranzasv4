<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dnombre_notario">{{ __('Nombre del notario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="dnombre_notario" id="dnombre_notario" class="form-control @error('dnombre_notario') is-invalid @enderror">
    </div>
    @error('dnombre_notario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dnumero_carnet">{{ __('Nùmero de carnet') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="dnumero_carnet" id="dnumero_carnet" class="form-control @error('dnumero_carnet') is-invalid @enderror">
    </div>
    @error('dnumero_carnet')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dcorreo_electronico">{{ __('Correo electrònico') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="dcorreo_electronico" id="dcorreo_electronico" class="form-control @error('dcorreo_electronico') is-invalid @enderror">
    </div>
    @error('dcorreo_electronico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dnumero_telefonico">{{ __('Nùmero telèfonico') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="dnumero_telefonico" id="dnumero_telefonico" class="form-control @error('dnumero_telefonico') is-invalid @enderror">
    </div>
    @error('dnumero_telefonico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="destado_casos_con_anotaciones">{{ __('Estado Casos con anotaciones') }}</label>
    <div wire:ignore>
      <select wire:model.live="destado_casos_con_anotaciones" id="destado_casos_con_anotaciones" class="select2 form-select @error('destado_casos_con_anotaciones') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Interposición de proceso judicial">Interposición de proceso judicial</option>
         <option value="Pendiente de subsanación">Pendiente de subsanación</option>
         <option value="Subsanado/Desinscrito o traspasado">Subsanado/Desinscrito o traspasado</option>
      </select>
    </div>
    @error('destado_casos_con_anotaciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dfecha_interposicion_denuncia">{{ __('Fecha de interposiciòn de la denuncia') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="dfecha_interposicion_denuncia"
        wire:model="dfecha_interposicion_denuncia"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('dfecha_interposicion_denuncia') is-invalid @enderror">
    </div>
    @error('dfecha_interposicion_denuncia')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="ddespacho_judicial_juzgado_id">{{ __('Despacho Judicial') }}</label>
    <div wire:ignore>
      <select wire:model.live="ddespacho_judicial_juzgado_id" id="ddespacho_judicial_juzgado_id" class="select2 form-select @error('ddespacho_judicial_juzgado_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->juzgados as $juzgado)
          <option value="{{ $juzgado->id }}">{{ $juzgado->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('ddespacho_judicial_juzgado_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dnumero_expediente">{{ __('Nùmero de expediente') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="dnumero_expediente" id="dnumero_expediente" class="form-control @error('dnumero_expediente') is-invalid @enderror">
    </div>
    @error('dnumero_expediente')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="dresultado_sentencia">{{ __('Resultado de la sentencia') }}</label>
    <div wire:ignore>
      <select wire:model.live="dresultado_sentencia" id="dresultado_sentencia" class="select2 form-select @error('dresultado_sentencia') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Desestimatoria">Desestimatoria</option>
         <option value="Estimatoria">Estimatoria</option>
      </select>
    </div>
    @error('dresultado_sentencia')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dgastos_microfilm">{{ __('Gastos Microfilm') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $dgastos_microfilm ?? '' }}',
        wireModelName: 'dgastos_microfilm',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('dgastos_microfilm', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="dgastos_microfilm" x-ref="cleaveInput" wire:ignore class="form-control js-input-dgastos_microfilm"
        >
      </div>
    </div>
    @error('dgastos_microfilm')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="dhonorarios">{{ __('Honorarios') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $dhonorarios ?? '' }}',
        wireModelName: 'dhonorarios',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('dhonorarios', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="dhonorarios" x-ref="cleaveInput" wire:ignore class="form-control js-input-dhonorarios"
        >
      </div>
    </div>
    @error('dhonorarios')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="aavance_cronologico">{{ __('Avance Cronológico') }}</label>
    <textarea class="form-control" wire:model="aavance_cronologico" name="aavance_cronologico" id="aavance_cronologico" rows="5"></textarea>
    @error('aavance_cronologico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
