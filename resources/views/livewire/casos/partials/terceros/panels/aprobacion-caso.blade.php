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
         <option value="Si">Si</option>
         <option value="No">No</option>
      </select>
    </div>
    @error('apuesta_posesion')
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="anumero_placa2">{{ __('Número Placa') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="anumero_placa2" id="anumero_placa2" class="form-control @error('anumero_placa2') is-invalid @enderror">
    </div>
    @error('anumero_placa2')
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
    <label class="form-label" for="afecha_presentacion_protocolizacion">{{ __('Fecha Presentación Protocolización') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_presentacion_protocolizacion"
        wire:model="afecha_presentacion_protocolizacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_presentacion_protocolizacion') is-invalid @enderror">
    </div>
    @error('afecha_presentacion_protocolizacion')
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
    <label class="form-label" for="afecha_presentacion_embargo">{{ __('Fecha de presentación de embargo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_presentacion_embargo"
        wire:model="afecha_presentacion_embargo"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_presentacion_embargo') is-invalid @enderror">
    </div>
    @error('afecha_presentacion_embargo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_arreglo_pago">{{ __('Fecha de arreglo del pago') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_arreglo_pago"
        wire:model="afecha_arreglo_pago"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_arreglo_pago') is-invalid @enderror">
    </div>
    @error('afecha_arreglo_pago')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_pago">{{ __('Fecha de pago') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_pago"
        wire:model="afecha_pago"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_pago') is-invalid @enderror">
    </div>
    @error('afecha_pago')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="amonto_cancelar">{{ __('Monto que debe cancelar') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="amonto_cancelar" id="amonto_cancelar" class="form-control @error('amonto_cancelar') is-invalid @enderror">
    </div>
    @error('amonto_cancelar')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="amonto_incobrable">{{ __('Monto incobrable') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="amonto_incobrable" id="amonto_incobrable" class="form-control @error('amonto_incobrable') is-invalid @enderror">
    </div>
    @error('amonto_incobrable')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="acontacto_telefonico">{{ __('Contacto telefónico') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="acontacto_telefonico" id="acontacto_telefonico" class="form-control @error('acontacto_telefonico') is-invalid @enderror">
    </div>
    @error('acontacto_telefonico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="acorreo">{{ __('Correo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="acorreo" id="acorreo" class="form-control @error('acorreo') is-invalid @enderror">
    </div>
    @error('acorreo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="fecha_activacion">{{ __('Fecha de activación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="fecha_activacion"
        wire:model="fecha_activacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('fecha_activacion') is-invalid @enderror">
    </div>
    @error('fecha_activacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="codigo_activacion">{{ __('Código de activación') }}</label>
    <div wire:ignore>
      <select wire:model.live="codigo_activacion" id="codigo_activacion" class="select2 form-select @error('codigo_activacion') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="TN">TN</option>
         <option value="SL">SL</option>
         <option value="AUD">AUD</option>
         <option value="AQ">AQ</option>
         <option value="SG">SG</option>
         <option value="GC">GC</option>
         <option value="MT">MT</option>
         <option value="ST">ST</option>
         <option value="DM">DM</option>
         <option value="OPOS">OPOS</option>
      </select>
    </div>
    @error('codigo_activacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <br>
  <h4 class="mb-0"><span class="badge bg-primary text-white">{{ __('Fecha de Remate') }}</span></h4>
  @include('livewire.casos.partials.scotiabank.panels.repeater-fecha-remate')

</div>
