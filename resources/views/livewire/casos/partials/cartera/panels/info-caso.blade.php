<div class="row g-6">
 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero">{{ __('Número') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero" id="pnumero"
          class="form-control @error('pnumero') is-invalid @enderror" disabled>
    </div>
    @error('pnumero')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
    <div class="form-text">
      {{ __('The system generates it') }}
    </div>
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="fecha_creacion">{{ __('Fecha de creación') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="fecha_creacion"
        wire:model="fecha_creacion"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('fecha_creacion') is-invalid @enderror">
    </div>
    @error('fecha_creacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="contact_id">{{ __('Cliente') }}</label>
    <div wire:ignore>
      <select wire:model.live="contact_id" id="contact_id" class="select2 form-select @error('contact_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->clientes as $cliente)
          <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
        @endforeach
      </select>
    </div>
    @error('contact_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="bank_id">{{ __('Bank') }}</label>
      <div wire:ignore>
      <select wire:model="bank_id" id="bank_id" class="select2 form-select @error('bank_id') is-invalid @enderror">
        @foreach ($this->banks as $bank)
          <option value="{{ $bank->id }}">{{ $bank->name }}</option>
        @endforeach
      </select>
      </div>
    @error('bank_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="product_id">{{ __('Producto') }}</label>
      <div wire:ignore>
      <select wire:model="product_id" id="product_id" class="select2 form-select @error('product_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->productos as $product)
          <option value="{{ $product->id }}">{{ $product->nombre }}</option>
        @endforeach
      </select>
      </div>
    @error('product_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="proceso_id">{{ __('Proceso') }}</label>
      <div wire:ignore>
      <select wire:model="proceso_id" id="proceso_id" class="select2 form-select @error('proceso_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->procesos as $proceso)
          <option value="{{ $proceso->id }}">{{ $proceso->nombre }}</option>
        @endforeach
      </select>
      </div>
    @error('proceso_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="currency_id">{{ __('Currency') }}</label>
    <div wire:ignore>
      <select wire:model="currency_id" id="currency_id" class="select2 form-select @error('currency_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->currencies as $currency)
          <option value="{{ $currency->id }}">{{ $currency->code }}</option>
        @endforeach
      </select>
    </div>
    @error('currency_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
 </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="origen_cartera">{{ __('Origen de Cartera') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="origen_cartera" id="origen_cartera" class="form-control @error('origen_cartera') is-invalid @enderror">
    </div>
    @error('origen_cartera')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_operacion1">{{ __('Número Operación #1') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_operacion1" id="pnumero_operacion1" class="form-control @error('pnumero_operacion1') is-invalid @enderror">
    </div>
    @error('pnumero_operacion1')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_cedula">{{ __('Número de Cédula') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_cedula" id="pnumero_cedula" class="form-control @error('pnumero_cedula') is-invalid @enderror">
    </div>
    @error('pnumero_cedula')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="psaldo_dolarizado">{{ __('Saldo Adeudado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="psaldo_dolarizado" id="psaldo_dolarizado"
          class="form-control @error('psaldo_dolarizado') is-invalid @enderror">
    </div>
    @error('psaldo_dolarizado')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_expediente_judicial">{{ __('Expediente') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_expediente_judicial" id="pnumero_expediente_judicial" class="form-control @error('pnumero_expediente_judicial') is-invalid @enderror">
    </div>
    @error('pnumero_expediente_judicial')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="estado_del_proceso">{{ __('Estado Procesal') }}</label>
    <div wire:ignore>
      <select wire:model.live="estado_del_proceso" id="estado_del_proceso" class="select2 form-select @error('estado_del_proceso') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Terminado">Terminado</option>
         <option value="Demanda">Demanda</option>
         <option value="Arreglo de Pago">Arreglo de Pago</option>
         <option value="Giros">Giros</option>
         <option value="Notificación">Notificación</option>
         <option value="Emplazamiento">Emplazamiento</option>
      </select>
    </div>
    @error('estado_del_proceso')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_asignacion_caso">{{ __('Fecha Asignación de Caso') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_asignacion_caso"
        wire:model="pfecha_asignacion_caso"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_asignacion_caso') is-invalid @enderror">
    </div>
    @error('pfecha_asignacion_caso')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_presentacion_demanda">{{ __('Fecha Demanda') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_presentacion_demanda"
        wire:model="pfecha_presentacion_demanda"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_presentacion_demanda') is-invalid @enderror">
    </div>
    @error('pfecha_presentacion_demanda')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_curso_demanda">{{ __('Fecha curso de la demanda') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_curso_demanda"
        wire:model="pfecha_curso_demanda"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_curso_demanda') is-invalid @enderror">
    </div>
    @error('pfecha_curso_demanda')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="nfecha_notificacion_todas_partes">{{ __('Fecha Notificación') }}</label>
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="afecha_suspencion_arreglo">{{ __('Fecha de Arreglo de Pago') }}</label>
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
        },
      watchProperty: '$wire.pmonto_retencion_colones'
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
        },
      watchProperty: '$wire.pmonto_retencion_dolares'
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
    <label class="form-label" for="afecha_terminacion">{{ __('Fecha de Terminado') }}</label>
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

  <div class="col-md-12 fv-plugins-icon-container">
    <label class="form-label" for="ncomentarios">{{ __('Comentarios') }}</label>
    <textarea class="form-control" wire:model="ncomentarios" name="ncomentarios" id="ncomentarios" rows="2"></textarea>
    @error('ncomentarios')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-4 fv-plugins-icon-container">
    <label class="form-label" for="f1fecha_asignacion_notificador">{{ __('Fecha de asignación al notificador') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="f1fecha_asignacion_notificador"
        wire:model="f1fecha_asignacion_notificador"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('f1fecha_asignacion_notificador') is-invalid @enderror">
    </div>
    @error('f1fecha_asignacion_notificador')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-4 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="notificador_id">{{ __('Notificador') }}</label>
    <div wire:ignore>
      <select wire:model.live="notificador_id" id="notificador_id" class="select2 form-select @error('notificador_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->notificadores as $notificador)
          <option value="{{ $notificador->id }}">{{ $notificador->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('notificador_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="monto_ap">{{ __('Monto AP') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $monto_ap ?? '' }}',
        wireModelName: 'monto_ap',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('monto_ap', val); // <- Esto envía el valor sin comas
          }
        },
      watchProperty: '$wire.monto_ap'
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="monto_ap" x-ref="cleaveInput" wire:ignore class="form-control js-input-monto_ap"
        >
      </div>
    </div>
    @error('monto_ap')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="cuota_ap">{{ __('Cuota AP') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $cuota_ap ?? '' }}',
        wireModelName: 'cuota_ap',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('cuota_ap', val); // <- Esto envía el valor sin comas
          }
        },
      watchProperty: '$wire.cuota_ap'
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="cuota_ap" x-ref="cleaveInput" wire:ignore class="form-control js-input-cuota_ap"
        >
      </div>
    </div>
    @error('cuota_ap')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>


  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="motivo_terminacion">{{ __('Motivo de Terminación') }}</label>
    <div wire:ignore>
      <select wire:model.live="motivo_terminacion" id="motivo_terminacion" class="select2 form-select @error('motivo_terminacion') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="Cancelación con descuento">Cancelación con descuento</option>
         <option value="Cancelación sin descuento">Cancelación sin descuento</option>
         <option value="AP">AP</option>
         <option value="Mutuo">Mutuo</option>
         <option value="Vía retenciones">Vía retenciones</option>
      </select>
    </div>
    @error('motivo_terminacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="cuota_ap">{{ __('Descuento aplicado') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $descuento_aplicado ?? '' }}',
        wireModelName: 'descuento_aplicado',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('descuento_aplicado', val); // <- Esto envía el valor sin comas
          }
        },
      watchProperty: '$wire.descuento_aplicado'
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="descuento_aplicado" x-ref="cleaveInput" wire:ignore class="form-control js-input-descuento_aplicado"
        >
      </div>
    </div>
    @error('descuento_aplicado')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="expectativa">{{ __('Expectativa') }}</label>
    <div wire:ignore>
      <select wire:model.live="expectativa" id="expectativa" class="select2 form-select @error('expectativa') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
         <option value="ALTA">ALTA</option>
         <option value="MEDIA">MEDIA</option>
         <option value="BAJA">BAJA</option>
      </select>
    </div>
    @error('expectativa')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
