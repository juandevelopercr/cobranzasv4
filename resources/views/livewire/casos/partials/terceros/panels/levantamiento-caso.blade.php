<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="lasesoramiento_formal">{{ __('Asesoramiento formal') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="lasesoramiento_formal" id="lasesoramiento_formal" class="form-control @error('lasesoramiento_formal') is-invalid @enderror"
        placeholder="{{ __('Asesoramiento formal') }}">
    </div>
    @error('lasesoramiento_formal')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="lsumaria">{{ __('Sumaria') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="lsumaria" id="lsumaria" class="form-control @error('lsumaria') is-invalid @enderror"
        placeholder="{{ __('Sumaria') }}">
    </div>
    @error('lsumaria')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="lcausa">{{ __('Causa') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="lcausa" id="lcausa" class="form-control @error('lcausa') is-invalid @enderror"
        placeholder="{{ __('Causa') }}">
    </div>
    @error('lcausa')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="ldespacho_judicial_juzgado_id">{{ __('Despacho Judicial') }}</label>
    <div wire:ignore>
      <select wire:model.live="ldespacho_judicial_juzgado_id" id="ldespacho_judicial_juzgado_id" class="select2 form-select @error('ldespacho_judicial_juzgado_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->juzgados as $juzgado)
          <option value="{{ $juzgado->id }}">{{ $juzgado->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('ldespacho_judicial_juzgado_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="lfecha_levantamiento_gravamen">{{ __('Fecha de levantamiento de gravamen') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="lfecha_levantamiento_gravamen"
        wire:model="lfecha_levantamiento_gravamen"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('lfecha_levantamiento_gravamen') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('lfecha_levantamiento_gravamen')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="lfecha_comunicado_banco">{{ __('Fecha de comunicado al banco') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="lfecha_comunicado_banco"
        wire:model="lfecha_comunicado_banco"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('lfecha_comunicado_banco') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('lfecha_comunicado_banco')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="lestado_levantamiento_id">{{ __('Estado de levantemiento del gravamen') }}</label>
    <div wire:ignore>
      <select wire:model.live="lestado_levantamiento_id" id="lestado_levantamiento_id" class="select2 form-select @error('lestado_levantamiento_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->estados as $estado)
          <option value="{{ $estado->id }}">{{ $estado->estado }}</option>
        @endforeach
      </select>
    </div>
    @error('lestado_levantamiento_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="lproveedores_servicio">{{ __('Proveedores de servicios') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="lproveedores_servicio" id="lproveedores_servicio" class="form-control @error('lproveedores_servicio') is-invalid @enderror">
    </div>
    @error('lproveedores_servicio')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="lavance_cronologico">{{ __('Avance Cronológico') }}</label>
    <textarea class="form-control" wire:model="lavance_cronologico" name="lavance_cronologico" id="lavance_cronologico" rows="5"></textarea>
    @error('lavance_cronologico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

</div>
