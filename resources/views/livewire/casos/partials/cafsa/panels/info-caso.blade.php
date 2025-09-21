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
    <label class="form-label" for="psaldo_dolarizado">{{ __('Saldo Dolarizado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="psaldo_dolarizado" id="psaldo_dolarizado"
          class="form-control @error('psaldo_dolarizado') is-invalid @enderror">
    </div>
    @error('psaldo_dolarizado')
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
    <label class="form-label" for="pnumero_operacion1">{{ __('Número Operación #1') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_operacion1" id="pnumero_operacion1" class="form-control @error('pnumero_operacion1') is-invalid @enderror">
    </div>
    @error('pnumero_operacion1')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnombre_demandado">{{ __('Nombre del Demandado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnombre_demandado" id="pnombre_demandado" class="form-control @error('pnombre_demandado') is-invalid @enderror">
    </div>
    @error('pnombre_demandado')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_cedula">{{ __('Número de Cédula del demandado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_cedula" id="pnumero_cedula" class="form-control @error('pnumero_cedula') is-invalid @enderror">
    </div>
    @error('pnumero_cedula')
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
    <label class="form-label" for="pdatos_codeudor2">{{ __('Datos Codeudor #2 (Bullet Point)') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pdatos_codeudor2" id="pdatos_codeudor2" class="form-control @error('pdatos_codeudor2') is-invalid @enderror">
    </div>
    @error('pdatos_codeudor2')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pdatos_anotantes">{{ __('Datos Anotantes (Bullet Point)') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pdatos_anotantes" id="pdatos_anotantes" class="form-control @error('pdatos_anotantes') is-invalid @enderror">
    </div>
    @error('pdatos_anotantes')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pdetalle_garantia">{{ __('Detalle Garantia') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pdetalle_garantia" id="pdetalle_garantia" class="form-control @error('pdetalle_garantia') is-invalid @enderror">
    </div>
    @error('pdetalle_garantia')
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
    <label class="form-label" for="pdespacho_judicial_juzgado">{{ __('Despacho Judicial Juzgado') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model.live="pdespacho_judicial_juzgado" id="pdespacho_judicial_juzgado" class="form-control @error('pdespacho_judicial_juzgado') is-invalid @enderror">
    </div>
    @error('pdespacho_judicial_juzgado')
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
    <label class="form-label" for="pmonto_estimacion_demanda">{{ __('Monto Estimación Demanda') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pmonto_estimacion_demanda" id="pmonto_estimacion_demanda"
          class="form-control @error('pmonto_estimacion_demanda') is-invalid @enderror">
    </div>
    @error('pmonto_estimacion_demanda')
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

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="user_create">{{ __('Usuario que creó el caso') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="user_create" id="user_create" class="form-control @error('user_create') is-invalid @enderror" readonly>
    </div>
    @error('user_create')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="user_update">{{ __('Usuario que actualizó el caso') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="user_update" id="user_update" class="form-control @error('user_update') is-invalid @enderror" readonly>
    </div>
    @error('user_update')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-12 col-md-9 col-lg-9 fv-plugins-icon-container">
    <label class="form-label" for="pavance_cronologico">{{ __('Avance Cronológico') }}</label>
    <textarea class="form-control" wire:model="pavance_cronologico" name="pavance_cronologico" id="pavance_cronologico" rows="5"></textarea>
    @error('pavance_cronologico')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
