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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnombre_apellidos_deudor">{{ __('Apellidos y Nombre del Deudor') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnombre_apellidos_deudor" id="pnombre_apellidos_deudor" class="form-control @error('pnombre_apellidos_deudor') is-invalid @enderror">
    </div>
    @error('pnombre_apellidos_deudor')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcedula_deudor">{{ __('Cèdula deudor') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcedula_deudor" id="pcedula_deudor" class="form-control @error('pcedula_deudor') is-invalid @enderror">
    </div>
    @error('pcedula_deudor')
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
    <label class="form-label" for="product_id">{{ __('Tipo de Crédito') }}</label>
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
    <label class="form-label" for="pnumero_operacion1">{{ __('Número Operación #1') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_operacion1" id="pnumero_operacion1" class="form-control @error('pnumero_operacion1') is-invalid @enderror">
    </div>
    @error('pnumero_operacion1')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_operacion2">{{ __('Número Operación #2') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_operacion2" id="pnumero_operacion2" class="form-control @error('pnumero_operacion2') is-invalid @enderror">
    </div>
    @error('pnumero_operacion2')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="pexpectativa_recuperacion_id">{{ __('Expectativa Recuperación') }}</label>
    <div wire:ignore>
      <select wire:model.live="pexpectativa_recuperacion_id" id="pexpectativa_recuperacion_id" class="select2 form-select @error('pexpectativa_recuperacion_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->expectativas as $expectativa)
          <option value="{{ $expectativa->id }}">{{ $expectativa->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('pexpectativa_recuperacion_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="pestatus_operacion">{{ __('Estatus de Operaciòn') }}</label>
    <div wire:ignore>
      <select wire:model="pestatus_operacion" id="pestatus_operacion" class="select2 form-select @error('pestatus_operacion') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        <option value="ACTIVO">ACTIVO</option>
        <option value="INCOBRABLE">INCOBRABLE</option>
        <option value="TERMINADO">TERMINADO</option>
        <option value="CASTIGADO">CASTIGADO</option>
        <option value="COBRO JUDICIAL">COBRO JUDICIAL</option>
      </select>
    </div>
    @error('pestatus_operacion')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_expediente_judicial">{{ __('Número Expediente Judicial') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model.live="pnumero_expediente_judicial" id="pnumero_expediente_judicial" class="form-control @error('pnumero_expediente_judicial') is-invalid @enderror">
    </div>
    @error('pnumero_expediente_judicial')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pdespacho_judicial_juzgado">{{ __('Despacho Judicial Juzgado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model.live="pdespacho_judicial_juzgado" id="pdespacho_judicial_juzgado" class="form-control @error('pdespacho_judicial_juzgado') is-invalid @enderror">
    </div>
    @error('pdespacho_judicial_juzgado')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcomprador">{{ __('Comprador') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model.live="pcomprador" id="pcomprador" class="form-control @error('pcomprador') is-invalid @enderror">
    </div>
    @error('pcomprador')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="ppoderdante_id">{{ __('Poderdante') }}</label>
    <div wire:ignore>
      <select wire:model="ppoderdante_id" id="ppoderdante_id" class="select2 form-select @error('ppoderdante_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->poderdantes as $poderdante)
          <option value="{{ $poderdante->id }}">{{ $poderdante->nombre }}</option>
        @endforeach
      </select>
    </div>
    @error('ppoderdante_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pultima_gestion_cobro_administrativo">{{ __('Fecha última gestión cobro Administrativo') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pultima_gestion_cobro_administrativo"
        wire:model="pultima_gestion_cobro_administrativo"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pultima_gestion_cobro_administrativo') is-invalid @enderror">
    </div>
    @error('pultima_gestion_cobro_administrativo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_ingreso_cobro_judicial">{{ __('Fecha de ingreso a cobro judicial') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_ingreso_cobro_judicial"
        wire:model="pfecha_ingreso_cobro_judicial"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_ingreso_cobro_judicial') is-invalid @enderror">
    </div>
    @error('pfecha_ingreso_cobro_judicial')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_devolucion_demanda_firma">{{ __('Fecha devolución de demanda para firma') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_devolucion_demanda_firma"
        wire:model="pfecha_devolucion_demanda_firma"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_devolucion_demanda_firma') is-invalid @enderror">
    </div>
    @error('pfecha_devolucion_demanda_firma')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_escrito_demanda">{{ __('Fecha de escrito de demanda') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_escrito_demanda"
        wire:model="pfecha_escrito_demanda"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_escrito_demanda') is-invalid @enderror">
    </div>
    @error('pfecha_escrito_demanda')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_presentacion_demanda">{{ __('Fecha Presentación Demanda') }}</label>
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="abogado_id">{{ __('Abogado') }}</label>
    <div wire:ignore>
      <select wire:model="abogado_id" id="abogado_id" class="select2 form-select @error('abogado_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->abogados as $abogado)
          <option value="{{ $abogado->id }}">{{ $abogado->name }}</option>
        @endforeach
      </select>
    </div>
    @error('abogado_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="asistente1_id">{{ __('Asistente #1') }}</label>
    <div wire:ignore>
      <select wire:model="asistente1_id" id="asistente1_id" class="select2 form-select @error('asistente1_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->asistentes as $asistente)
          <option value="{{ $asistente->id }}">{{ $asistente->name }}</option>
        @endforeach
      </select>
    </div>
    @error('asistente1_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="asistente2_id">{{ __('Asistente #2') }}</label>
    <div wire:ignore>
      <select wire:model="asistente2_id" id="asistente2_id" class="select2 form-select @error('asistente2_id') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        @foreach ($this->asistentes as $asistente)
          <option value="{{ $asistente->id }}">{{ $asistente->name }}</option>
        @endforeach
      </select>
    </div>
    @error('asistente2_id')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pente">{{ __('Ente') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pente" id="pente"
          class="form-control @error('pente') is-invalid @enderror">
    </div>
    @error('pente')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pmonto_prima">{{ __('Monto Prima') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $pmonto_prima ?? '' }}',
        wireModelName: 'pmonto_prima',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('pmonto_prima', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="pmonto_prima" x-ref="cleaveInput" wire:ignore class="form-control js-input-pmonto_prima"
        >
      </div>
    </div>
    @error('pmonto_prima')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pplazo_arreglo_pago">{{ __('Plazo arreglo de pago') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pplazo_arreglo_pago" id="pplazo_arreglo_pago"
          class="form-control @error('pplazo_arreglo_pago') is-invalid @enderror">
    </div>
    @error('pplazo_arreglo_pago')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pmonto_arreglo_pago">{{ __('Monto arreglo de pago') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $pmonto_arreglo_pago ?? '' }}',
        wireModelName: 'pmonto_arreglo_pago',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('pmonto_arreglo_pago', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="pmonto_arreglo_pago" x-ref="cleaveInput" wire:ignore class="form-control js-input-pmonto_arreglo_pago"
        >
      </div>
    </div>
    @error('pmonto_arreglo_pago')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pmonto_cuota">{{ __('Monto cuota') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $pmonto_cuota ?? '' }}',
        wireModelName: 'pmonto_cuota',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('pmonto_cuota', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="pmonto_cuota" x-ref="cleaveInput" wire:ignore class="form-control js-input-pmonto_cuota"
        >
      </div>
    </div>
    @error('pmonto_cuota')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pno_cuota">{{ __('No. cuota') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pno_cuota" id="pno_cuota"
          class="form-control @error('pno_cuota') is-invalid @enderror">
    </div>
    @error('pno_cuota')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 select2-primary fv-plugins-icon-container">
    <label class="form-label" for="pestado_arreglo">{{ __('Estado arreglo') }}</label>
    <div wire:ignore>
      <select wire:model="pestado_arreglo" id="pestado_arreglo" class="select2 form-select @error('pestado_arreglo') is-invalid @enderror">
        <option value="">{{ __('Seleccione...') }}</option>
        <option value="Activo">Activo</option>
        <option value="Inactivo">Inactivo</option>
      </select>
    </div>
    @error('pestado_arreglo')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnombre_persona_juridica">{{ __('Nombre de la Persona Jurídica') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnombre_persona_juridica" id="pnombre_persona_juridica"
          class="form-control @error('pnombre_persona_juridica') is-invalid @enderror">
    </div>
    @error('pnombre_persona_juridica')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pdatos_codeudor1">{{ __('Datos Codeudor #1 (Bullet Point)') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pdatos_codeudor1" id="pdatos_codeudor1" class="form-control @error('pdatos_codeudor1') is-invalid @enderror">
    </div>
    @error('pdatos_codeudor1')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pdatos_fiadores">{{ __('Datos de los Fiadores') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pdatos_fiadores" id="pdatos_fiadores" class="form-control @error('pdatos_fiadores') is-invalid @enderror">
    </div>
    @error('pdatos_fiadores')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="sfecha_captura">{{ __('Fecha Captura') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="sfecha_captura"
        wire:model="sfecha_captura"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('sfecha_captura') is-invalid @enderror">
    </div>
    @error('sfecha_captura')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_cedula_juridica">{{ __('Número de cédula jurídica') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_cedula_juridica" id="pnumero_cedula_juridica"
          class="form-control @error('pnumero_cedula_juridica') is-invalid @enderror">
    </div>
    @error('pnumero_cedula_juridica')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
