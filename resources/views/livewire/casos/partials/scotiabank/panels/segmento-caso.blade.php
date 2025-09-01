<div class="row g-6">

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="efecha_visita">{{ __('Fecha de la visita') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="efecha_visita"
        wire:model="efecha_visita"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('efecha_visita') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('efecha_visita')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="egestion_a_realizar">{{ __('Gestiòn a realizar') }}</label>
    <div wire:ignore>
      <select wire:model.live="egestion_a_realizar" id="egestion_a_realizar" class="select2 form-select @error('egestion_a_realizar') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Desinscribir">Desinscribir</option>
         <option value="Pendiente de definir">Pendiente de definir</option>
         <option value="Traspasar">Traspasar</option>
      </select>
    </div>
    @error('egestion_a_realizar')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="eestado_cliente_gran_tamano">{{ __('Gestiòn a realizar') }}</label>
    <div wire:ignore>
      <select wire:model.live="eestado_cliente_gran_tamano" id="eestado_cliente_gran_tamano" class="select2 form-select @error('eestado_cliente_gran_tamano') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Desinscrito">Desinscrito</option>
         <option value="Defectuoso">Defectuoso</option>
         <option value="Pendiente">Pendiente</option>
         <option value="Traspasado">Traspasado</option>
      </select>
    </div>
    @error('eestado_cliente_gran_tamano')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="savance_cronologico">{{ __('Avance Cronológico') }}</label>
    <textarea class="form-control" wire:model="savance_cronologico" name="savance_cronologico" id="savance_cronologico" rows="2"
              placeholder="{{ __('Avance Cronológico') }}"></textarea>
    @error('savance_cronologico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>


</div>
