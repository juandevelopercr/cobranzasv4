<div class="row g-6">
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_firmeza_aprobacion_remate">{{ __('Fecha Firmeza Aprobación Remate') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="afecha_firmeza_aprobacion_remate"
        wire:model="afecha_firmeza_aprobacion_remate"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('afecha_firmeza_aprobacion_remate') is-invalid @enderror">
    </div>
    @error('afecha_firmeza_aprobacion_remate')
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="asaldo_capital_operacion">{{ __('Saldo Capital Operación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="asaldo_capital_operacion" id="asaldo_capital_operacion" class="form-control @error('asaldo_capital_operacion') is-invalid @enderror">
    </div>
    @error('asaldo_capital_operacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="asaldo_capital_operacion_usd">{{ __('Saldo Capital de la Operación USD') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="asaldo_capital_operacion_usd" id="asaldo_capital_operacion_usd" class="form-control @error('asaldo_capital_operacion_usd') is-invalid @enderror">
    </div>
    @error('asaldo_capital_operacion_usd')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="aestimacion_demanda_en_presentacion">{{ __('Estimación Demanda Presentación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="aestimacion_demanda_en_presentacion" id="aestimacion_demanda_en_presentacion" class="form-control @error('aestimacion_demanda_en_presentacion') is-invalid @enderror">
    </div>
    @error('aestimacion_demanda_en_presentacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="aestimacion_demanda_en_presentacion_usd">{{ __('Estimación Demanda Presentación USD') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="aestimacion_demanda_en_presentacion_usd" id="aestimacion_demanda_en_presentacion_usd" class="form-control @error('aestimacion_demanda_en_presentacion_usd') is-invalid @enderror">
    </div>
    @error('aestimacion_demanda_en_presentacion_usd')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="liquidacion_intereses_aprobada_crc">{{ __('Liquidacion de intereses aprobada Colones') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="liquidacion_intereses_aprobada_crc" id="liquidacion_intereses_aprobada_crc" class="form-control @error('liquidacion_intereses_aprobada_crc') is-invalid @enderror">
    </div>
    @error('liquidacion_intereses_aprobada_crc')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="liquidacion_intereses_aprobada_usd">{{ __('Liquidacion de intereses aprobada Dólares') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="liquidacion_intereses_aprobada_usd" id="liquidacion_intereses_aprobada_usd" class="form-control @error('liquidacion_intereses_aprobada_usd') is-invalid @enderror">
    </div>
    @error('liquidacion_intereses_aprobada_usd')
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
    <label class="form-label" for="abufete">{{ __('Bufete') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="abufete" id="abufete" class="form-control @error('abufete') is-invalid @enderror">
    </div>
    @error('abufete')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pgastos_legales_caso">{{ __('Gastos Legales Caso') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $pgastos_legales_caso ?? '' }}',
        wireModelName: 'pgastos_legales_caso',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('pgastos_legales_caso', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="pgastos_legales_caso" x-ref="cleaveInput" wire:ignore class="form-control js-input-pgastos_legales_caso"
        >
      </div>
    </div>
    @error('pgastos_legales_caso')
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
    <label class="form-label" for="ahonorarios_totales_usd">{{ __('Honorarios Totales Dólares') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="ahonorarios_totales_usd" id="ahonorarios_totales_usd" class="form-control @error('ahonorarios_totales_usd') is-invalid @enderror">
    </div>
    @error('ahonorarios_totales_usd')
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
    <label class="form-label" for="tiempo_dias">{{ __('Tiempo en Días') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="tiempo_dias" id="tiempo_dias" class="form-control @error('tiempo_dias') is-invalid @enderror">
    </div>
    @error('tiempo_dias')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="tiempo_annos">{{ __('Tiempo en Años') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="tiempo_annos" id="tiempo_annos" class="form-control @error('tiempo_annos') is-invalid @enderror">
    </div>
    @error('tiempo_annos')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pretenciones">{{ __('Monto de retenciones') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $pretenciones ?? '' }}',
        wireModelName: 'pretenciones',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('pretenciones', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="pretenciones" x-ref="cleaveInput" wire:ignore class="form-control js-input-pretenciones"
        >
      </div>
    </div>
    @error('pretenciones')
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pmonto_retencion_colones">{{ __('Monto retención ¢') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $pmonto_retencion_colones ?? '' }}',
        wireModelName: 'pmonto_retencion_colones',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('pmonto_retencion_colones', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="pmonto_retencion_colones" x-ref="cleaveInput" wire:ignore class="form-control js-input-pmonto_retencion_colones"
        >
      </div>
    </div>
    @error('pmonto_retencion_colones')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pmonto_retencion_dolares">{{ __('Monto retención $') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $pmonto_retencion_dolares ?? '' }}',
        wireModelName: 'pmonto_retencion_dolares',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('pmonto_retencion_dolares', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="pmonto_retencion_dolares" x-ref="cleaveInput" wire:ignore class="form-control js-input-pmonto_retencion_dolares"
        >
      </div>
    </div>
    @error('pmonto_retencion_dolares')
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="acarga_gastos_legales">{{ __('Carga de Gastos Legales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $acarga_gastos_legales ?? '' }}',
        wireModelName: 'acarga_gastos_legales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('acarga_gastos_legales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="acarga_gastos_legales" x-ref="cleaveInput" wire:ignore class="form-control js-input-acarga_gastos_legales"
        >
      </div>
    </div>
    @error('acarga_gastos_legales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="agastos_mas_honorarios_acumulados">{{ __('Gastos + Honorarios acumulados') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $agastos_mas_honorarios_acumulados ?? '' }}',
        wireModelName: 'agastos_mas_honorarios_acumulados',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('agastos_mas_honorarios_acumulados', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="agastos_mas_honorarios_acumulados" x-ref="cleaveInput" wire:ignore class="form-control js-input-agastos_mas_honorarios_acumulados"
        >
      </div>
    </div>
    @error('agastos_mas_honorarios_acumulados')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="ahonorarios_iniciales">{{ __('Honorarios iniciales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $ahonorarios_iniciales ?? '' }}',
        wireModelName: 'ahonorarios_iniciales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('ahonorarios_iniciales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="ahonorarios_iniciales" x-ref="cleaveInput" wire:ignore class="form-control js-input-ahonorarios_iniciales"
        >
      </div>
    </div>
    @error('ahonorarios_iniciales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_demanda_presentada">{{ __('Diferencia P/ Demanda Presentada') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_demanda_presentada ?? '' }}',
        wireModelName: 'adiferencia_demanda_presentada',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_demanda_presentada', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_demanda_presentada" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_demanda_presentada"
        >
      </div>
    </div>
    @error('adiferencia_demanda_presentada')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_sentencia_afavor">{{ __('Diferencia P/ Sentencia a favor') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_sentencia_afavor ?? '' }}',
        wireModelName: 'adiferencia_sentencia_afavor',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_sentencia_afavor', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_sentencia_afavor" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_sentencia_afavor"
        >
      </div>
    </div>
    @error('adiferencia_sentencia_afavor')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_sentencia_enfirme">{{ __('Diferencia P/ Sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_segunda_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 2da Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_segunda_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_segunda_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_segunda_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_segunda_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_segunda_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_segunda_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_tercera_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 3ra Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_tercera_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_tercera_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_tercera_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_tercera_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_tercera_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_tercera_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_cuarta_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 4ta Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_cuarta_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_cuarta_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_cuarta_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_cuarta_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_cuarta_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_cuarta_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_quinta_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 5ta Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_quinta_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_quinta_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_quinta_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_quinta_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_quinta_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_quinta_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_sexta_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 6ta Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_sexta_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_sexta_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_sexta_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_sexta_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_sexta_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_sexta_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_septima_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 7ma Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_septima_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_septima_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_septima_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_septima_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_septima_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_septima_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_octava_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 8va Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_octava_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_octava_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_octava_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_octava_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_octava_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_octava_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_novena_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 9na Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_novena_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_novena_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_novena_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_novena_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_novena_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_novena_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 10ma Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_primera_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 11va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_primera_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_primera_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_primera_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_primera_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_primera_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_primera_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 12va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 13va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 14va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 15va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 16va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_septima_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 17va Liquidaciòn de sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_septima_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_septima_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_septima_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_septima_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_septima_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_septima_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_octava_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 18va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_octava_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_octava_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_octava_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_octava_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_octava_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_octava_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_decima_novena_liquidacion_de_sentencia_enfirme">{{ __('Diferencia P/ 19va Liquidaciòn sentencia en firme') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_decima_novena_liquidacion_de_sentencia_enfirme ?? '' }}',
        wireModelName: 'adiferencia_decima_novena_liquidacion_de_sentencia_enfirme',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_decima_novena_liquidacion_de_sentencia_enfirme', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_decima_novena_liquidacion_de_sentencia_enfirme" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_decima_novena_liquidacion_de_sentencia_enfirme"
        >
      </div>
    </div>
    @error('adiferencia_decima_novena_liquidacion_de_sentencia_enfirme')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="agastos_legales_iniciales">{{ __('Gastos Legales Iniciales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $agastos_legales_iniciales ?? '' }}',
        wireModelName: 'agastos_legales_iniciales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('agastos_legales_iniciales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="agastos_legales_iniciales" x-ref="cleaveInput" wire:ignore class="form-control js-input-agastos_legales_iniciales"
        >
      </div>
    </div>
    @error('agastos_legales_iniciales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="adiferencia_gastos_legales">{{ __('Diferencia de gastos legales') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $adiferencia_gastos_legales ?? '' }}',
        wireModelName: 'adiferencia_gastos_legales',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('adiferencia_gastos_legales', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="adiferencia_gastos_legales" x-ref="cleaveInput" wire:ignore class="form-control js-input-adiferencia_gastos_legales"
        >
      </div>
    </div>
    @error('adiferencia_gastos_legales')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="anumero_grupo">{{ __('No. Grupo') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $anumero_grupo ?? '' }}',
        wireModelName: 'anumero_grupo',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('anumero_grupo', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="anumero_grupo" x-ref="cleaveInput" wire:ignore class="form-control js-input-anumero_grupo"
        >
      </div>
    </div>
    @error('anumero_grupo')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
