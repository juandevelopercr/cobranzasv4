<div class="row g-6">
  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_aprobacion_remate">{{ __('Fecha Aprobación de Remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_aprobacion_remate"
        wire:model="afecha_aprobacion_remate"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_aprobacion_remate') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_aprobacion_remate')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_protocolizacion">{{ __('Fecha Protocolización') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_protocolizacion"
        wire:model="afecha_protocolizacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_protocolizacion') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_protocolizacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_levantamiento">{{ __('Fecha aprobación levantamiento') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_levantamiento"
        wire:model="afecha_levantamiento"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_levantamiento') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_levantamiento')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_senalamiento_puesta_posesion">{{ __('Fecha Señalamiento Puesta Posesión') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_senalamiento_puesta_posesion"
        wire:model="afecha_senalamiento_puesta_posesion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_senalamiento_puesta_posesion') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_senalamiento_puesta_posesion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
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

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="agastos_legales">{{ __('Gastos Legales') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="agastos_legales" id="agastos_legales" class="form-control @error('agastos_legales') is-invalid @enderror"
        placeholder="{{ __('Gastos Legales') }}">
    </div>
    @error('agastos_legales')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="ahonorarios_totales">{{ __('Honorarios Totales Colones') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="ahonorarios_totales" id="ahonorarios_totales" class="form-control @error('ahonorarios_totales') is-invalid @enderror"
        placeholder="{{ __('Honorarios Totales Colones') }}">
    </div>
    @error('ahonorarios_totales')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="anumero_placa1">{{ __('Número Placa') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="anumero_placa1" id="anumero_placa1" class="form-control @error('anumero_placa1') is-invalid @enderror"
        placeholder="{{ __('Número Placa') }}">
    </div>
    @error('anumero_placa1')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="anumero_placa2">{{ __('Número Placa') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="anumero_placa2" id="anumero_placa2" class="form-control @error('anumero_placa2') is-invalid @enderror"
        placeholder="{{ __('Número Placa') }}">
    </div>
    @error('anumero_placa2')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="acolisiones_embargos_anotaciones">{{ __('Colisiones Embargos Anotaciones') }}</label>
    <textarea class="form-control" wire:model="acolisiones_embargos_anotaciones" name="acolisiones_embargos_anotaciones" id="acolisiones_embargos_anotaciones" rows="2"
              placeholder="{{ __('Colisiones Embargos Anotaciones') }}"></textarea>
    @error('acolisiones_embargos_anotaciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="anumero_marchamo">{{ __('Nùmero Marchamo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="anumero_marchamo" id="anumero_marchamo" class="form-control @error('anumero_marchamo') is-invalid @enderror"
        placeholder="{{ __('Nùmero Marchamo') }}">
    </div>
    @error('anumero_marchamo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afirma_legal">{{ __('Firma Legal') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="afirma_legal" id="afirma_legal" class="form-control @error('afirma_legal') is-invalid @enderror"
        placeholder="{{ __('Firma Legal') }}">
    </div>
    @error('afirma_legal')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_registro">{{ __('Fecha de Registro') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_registro"
        wire:model="afecha_registro"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_registro') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_registro')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_presentacion_protocolizacion">{{ __('Fecha Presentación Protocolización') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_presentacion_protocolizacion"
        wire:model="afecha_presentacion_protocolizacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_presentacion_protocolizacion') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_presentacion_protocolizacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_inscripcion">{{ __('Fecha de Inscripción') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_inscripcion"
        wire:model="afecha_inscripcion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_inscripcion') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_inscripcion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_suspencion_arreglo">{{ __('Fecha Suspención Arreglo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_suspencion_arreglo"
        wire:model="afecha_suspencion_arreglo"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_suspencion_arreglo') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('afecha_suspencion_arreglo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="ajustificacion_casos_protocolizados_embargo">{{ __('Justificación Casos Protocolizados Embargo') }}</label>
    <textarea class="form-control" wire:model="ajustificacion_casos_protocolizados_embargo" name="ajustificacion_casos_protocolizados_embargo" id="ajustificacion_casos_protocolizados_embargo" rows="5"
              placeholder="{{ __('Justificación Casos Protocolizados Embargo') }}"></textarea>
    @error('ajustificacion_casos_protocolizados_embargo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="atipo_expediente">{{ __('Tipo Expediente') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="atipo_expediente" id="atipo_expediente" class="form-control @error('atipo_expediente') is-invalid @enderror"
        placeholder="{{ __('Tipo Expediente') }}">
    </div>
    @error('atipo_expediente')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="areasignaciones">{{ __('Reasignaciones') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="areasignaciones" id="areasignaciones" class="form-control @error('areasignaciones') is-invalid @enderror"
        placeholder="{{ __('Reasignaciones') }}">
    </div>
    @error('areasignaciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="fecha_inicio_retenciones">{{ __('Fecha de inicio de retenciones') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="fecha_inicio_retenciones"
        wire:model="fecha_inicio_retenciones"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('fecha_inicio_retenciones') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('fecha_inicio_retenciones')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="fecha_prescripcion">{{ __('Fecha de Prescripción') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="fecha_prescripcion"
        wire:model="fecha_prescripcion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('fecha_prescripcion') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('fecha_prescripcion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="fecha_pruebas">{{ __('Fecha de Pruebas') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="fecha_pruebas"
        wire:model="fecha_pruebas"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('fecha_pruebas') is-invalid @enderror"
        placeholder="dd-mm-aaaa"
        >
    </div>
    @error('fecha_pruebas')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="motivo_terminacion">{{ __('Motivo de Terminación') }}</label>
    <div wire:ignore>
      <select wire:model.live="motivo_terminacion" id="motivo_terminacion" class="select2 form-select @error('motivo_terminacion') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Mutuo">Mutuo</option>
         <option value="Pago Cliente">Pago Cliente</option>
         <option value="Por decisión del Banco">Por decisión del Banco</option>
         <option value="Prescrito">Prescrito</option>
         <option value="Adjudicación">Adjudicación</option>
         <option value="Fallecido">Fallecido</option>
         <option value="Concursales">Concursales</option>
         <option value="Adjudicado por Tercero">Adjudicado por Tercero</option>
      </select>
    </div>
    @error('motivo_terminacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 fv-plugins-icon-container">
    <label class="form-label" for="honorarios_legales_dolares">{{ __('Honorarios Legales Dólares') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $honorarios_legales_dolares ?? '' }}',
        wireModelName: 'honorarios_legales_dolares',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('honorarios_legales_dolares', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="honorarios_legales_dolares" x-ref="cleaveInput" wire:ignore class="form-control js-input-honorarios_legales_dolares"
        >
      </div>
    </div>
    @error('honorarios_legales_dolares')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <!--
  insertar
  dynamic_form_detalles_ejecucion
  -->

  <!--
  insertar
  dynamic_form_avances_fecha_remate
  -->

</div>
