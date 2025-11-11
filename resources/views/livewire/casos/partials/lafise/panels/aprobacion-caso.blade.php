<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_aprobacion_remate">{{ __('Fecha Aprobación de Remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_aprobacion_remate"
        wire:model="afecha_aprobacion_remate"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_aprobacion_remate') is-invalid @enderror">
    </div>
    @error('afecha_aprobacion_remate')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_protocolizacion">{{ __('Fecha Protocolización') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_protocolizacion"
        wire:model="afecha_protocolizacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_protocolizacion') is-invalid @enderror">
    </div>
    @error('afecha_protocolizacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="agastos_legales">{{ __('Gastos Legales') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="agastos_legales" id="agastos_legales" class="form-control @error('agastos_legales') is-invalid @enderror">
    </div>
    @error('agastos_legales')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="ahonorarios_totales">{{ __('Honorarios Totales Colones') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="ahonorarios_totales" id="ahonorarios_totales" class="form-control @error('ahonorarios_totales') is-invalid @enderror">
    </div>
    @error('ahonorarios_totales')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="anumero_placa1">{{ __('Número Placa') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="anumero_placa1" id="anumero_placa1" class="form-control @error('anumero_placa1') is-invalid @enderror">
    </div>
    @error('anumero_placa1')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="acolisiones_embargos_anotaciones">{{ __('Colisiones Embargos Anotaciones') }}</label>
    <textarea class="form-control" wire:model="acolisiones_embargos_anotaciones" name="acolisiones_embargos_anotaciones" id="acolisiones_embargos_anotaciones" rows="2"></textarea>
    @error('acolisiones_embargos_anotaciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="anumero_marchamo">{{ __('Nùmero Marchamo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="anumero_marchamo" id="anumero_marchamo" class="form-control @error('anumero_marchamo') is-invalid @enderror">
    </div>
    @error('anumero_marchamo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afirma_legal">{{ __('Firma Legal') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="afirma_legal" id="afirma_legal" class="form-control @error('afirma_legal') is-invalid @enderror">
    </div>
    @error('afirma_legal')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_registro">{{ __('Fecha de Registro') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_registro"
        wire:model="afecha_registro"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_registro') is-invalid @enderror">
    </div>
    @error('afecha_registro')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_inscripcion">{{ __('Fecha de Inscripción') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_inscripcion"
        wire:model="afecha_inscripcion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_inscripcion') is-invalid @enderror">
    </div>
    @error('afecha_inscripcion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_terminacion">{{ __('Fecha de Terminación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_terminacion"
        wire:model="afecha_terminacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_terminacion') is-invalid @enderror">
    </div>
    @error('afecha_terminacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_suspencion_arreglo">{{ __('Fecha Suspención Arreglo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_suspencion_arreglo"
        wire:model="afecha_suspencion_arreglo"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_suspencion_arreglo') is-invalid @enderror">
    </div>
    @error('afecha_suspencion_arreglo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="ajustificacion_casos_protocolizados_embargo">{{ __('Justificación Casos Protocolizados Embargo') }}</label>
    <textarea class="form-control" wire:model="ajustificacion_casos_protocolizados_embargo" name="ajustificacion_casos_protocolizados_embargo" id="ajustificacion_casos_protocolizados_embargo" rows="5"></textarea>
              </textarea>
    @error('ajustificacion_casos_protocolizados_embargo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="aestado_proceso_general_id">{{ __('Estado Proceso General') }}</label>
    <div wire:ignore>
      <select wire:model.live="aestado_proceso_general_id" id="aestado_proceso_general_id" class="select2 form-select @error('aestado_proceso_general_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->estados as $estado)
          <option value="{{ $estado->id }}">{{ $estado->name }}</option>
        @endforeach
      </select>
    </div>
    @error('aestado_proceso_general_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_informe_ultima_gestion">{{ __('Fecha Informe última Gestión') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_informe_ultima_gestion"
        wire:model="afecha_informe_ultima_gestion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_informe_ultima_gestion') is-invalid @enderror">
    </div>
    @error('afecha_informe_ultima_gestion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="atipo_expediente">{{ __('Tipo Expediente') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="atipo_expediente" id="atipo_expediente" class="form-control @error('atipo_expediente') is-invalid @enderror">
    </div>
    @error('atipo_expediente')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="areasignaciones">{{ __('Reasignaciones') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="areasignaciones" id="areasignaciones" class="form-control @error('areasignaciones') is-invalid @enderror">
    </div>
    @error('areasignaciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="amonto_avaluo">{{ __('Monto avaluo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="amonto_avaluo" id="amonto_avaluo" class="form-control @error('amonto_avaluo') is-invalid @enderror">
    </div>
    @error('amonto_avaluo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_avaluo">{{ __('Fecha avaluo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_avaluo"
        wire:model="afecha_avaluo"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_avaluo') is-invalid @enderror">
    </div>
    @error('afecha_avaluo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="aembargo_cuentas">{{ __('Embargos cuentas') }}</label>
    <div wire:ignore>
      <select wire:model.live="aembargo_cuentas" id="aembargo_cuentas" class="select2 form-select @error('aembargo_cuentas') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
      </select>
    </div>
    @error('aembargo_cuentas')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="aembargo_salarios">{{ __('Embargos salarios') }}</label>
    <div wire:ignore>
      <select wire:model.live="aembargo_salarios" id="aembargo_salarios" class="select2 form-select @error('aembargo_salarios') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
      </select>
    </div>
    @error('aembargo_salarios')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="aembargo_muebles">{{ __('Embargos Muebles') }}</label>
    <div wire:ignore>
      <select wire:model.live="aembargo_muebles" id="aembargo_muebles" class="select2 form-select @error('aembargo_muebles') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="SI">SI</option>
         <option value="NO">NO</option>
      </select>
    </div>
    @error('aembargo_muebles')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="aembargo_inmuebles">{{ __('Embargos Inmuebles') }}</label>
    <div wire:ignore>
      <select wire:model.live="aembargo_inmuebles" id="aembargo_inmuebles" class="select2 form-select @error('aembargo_inmuebles') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="SI">SI</option>
         <option value="NO">NO</option>
      </select>
    </div>
    @error('aembargo_inmuebles')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="aretenciones_con_giro">{{ __('Retenciones con giro') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="aretenciones_con_giro" id="aretenciones_con_giro" class="form-control @error('aretenciones_con_giro') is-invalid @enderror">
    </div>
    @error('aretenciones_con_giro')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_ultimo_giro">{{ __('Fecha de último giro') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_ultimo_giro"
        wire:model="afecha_ultimo_giro"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_ultimo_giro') is-invalid @enderror">
    </div>
    @error('afecha_ultimo_giro')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="abienes_adjudicados">{{ __('Bienes Adjudicados') }}</label>
    <div wire:ignore>
      <select wire:model.live="abienes_adjudicados" id="abienes_adjudicados" class="select2 form-select @error('abienes_adjudicados') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="SI">SI</option>
         <option value="NO">NO</option>
      </select>
    </div>
    @error('abienes_adjudicados')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_senalamiento_puesta_posesion">{{ __('Fecha Señalamiento Puesta Posesión') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_senalamiento_puesta_posesion"
        wire:model="afecha_senalamiento_puesta_posesion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_senalamiento_puesta_posesion') is-invalid @enderror">
    </div>
    @error('afecha_senalamiento_puesta_posesion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="apuesta_posesion">{{ __('Puesta Posesión') }}</label>
    <div wire:ignore>
      <select wire:model.live="apuesta_posesion" id="apuesta_posesion" class="select2 form-select @error('apuesta_posesion') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="SI">SI</option>
         <option value="NO">NO</option>
      </select>
    </div>
    @error('apuesta_posesion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="abufete">{{ __('Bufete') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="abufete" id="abufete" class="form-control @error('abufete') is-invalid @enderror">
    </div>
    @error('abufete')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
  <br>
  <h4 class="mb-0"><span class="badge bg-primary text-white">{{ __('Fecha de Remate') }}</span></h4>
  @include('livewire.casos.partials.scotiabank.panels.repeater-fecha-remate')
</div>
