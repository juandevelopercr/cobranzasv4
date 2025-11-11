<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f2causa_remate">{{ __('Causa de remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="f2causa_remate" id="f2causa_remate" class="form-control @error('f2causa_remate') is-invalid @enderror">
    </div>
    @error('f2causa_remate')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="f2publicacion_edicto">{{ __('Publicaciòn de edicto') }}</label>
    <div wire:ignore>
      <select wire:model.live="f2publicacion_edicto" id="f2publicacion_edicto" class="select2 form-select @error('f2publicacion_edicto') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
      </select>
    </div>
    @error('f2publicacion_edicto')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f2fecha_publicacion_edicto">{{ __('Fecha de publicaciòn del edicto') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="f2fecha_publicacion_edicto"
        wire:model="f2fecha_publicacion_edicto"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('f2fecha_publicacion_edicto') is-invalid @enderror">
    </div>
    @error('f2fecha_publicacion_edicto')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f2tiempo_concedido_edicto">{{ __('Tiempo concedido en el edicto') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="f2tiempo_concedido_edicto" id="f2tiempo_concedido_edicto" class="form-control @error('f2tiempo_concedido_edicto') is-invalid @enderror">
    </div>
    @error('f2tiempo_concedido_edicto')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="f2preclusion_tiempo">{{ __('Preclusiòn del tiempo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="f2preclusion_tiempo" id="f2preclusion_tiempo" class="form-control @error('f2preclusion_tiempo') is-invalid @enderror">
    </div>
    @error('f2preclusion_tiempo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="f2estado_remanente">{{ __('Estados remanente filtro2') }}</label>
    <div wire:ignore>
      <select wire:model.live="f2estado_remanente" id="f2estado_remanente" class="select2 form-select @error('f2estado_remanente') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Desinscrito">Desinscrito</option>
         <option value="Defectuoso">Defectuoso</option>
         <option value="Pendiente">Pendiente</option>
      </select>
    </div>
    @error('f2estado_remanente')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="f2avance_cronologico">{{ __('Avance Cronológico') }}</label>
    <textarea class="form-control" wire:model="f2avance_cronologico" name="f2avance_cronologico" id="f2avance_cronologico" rows="5"></textarea>
    @error('f2avance_cronologico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

</div>
