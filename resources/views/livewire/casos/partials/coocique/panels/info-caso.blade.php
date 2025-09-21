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
    <label class="form-label" for="psaldo_de_seguros">{{ __('Saldo De Seguros') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $psaldo_de_seguros ?? '' }}',
        wireModelName: 'psaldo_de_seguros',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('psaldo_de_seguros', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="psaldo_de_seguros" x-ref="cleaveInput" wire:ignore class="form-control js-input-psaldo_de_seguros"
        >
      </div>
    </div>
    @error('psaldo_de_seguros')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="psaldo_de_multas">{{ __('Saldo De Multas') }}</label>
    <div
      x-data="cleaveLivewire({
        initialValue: '{{ $psaldo_de_multas ?? '' }}',
        wireModelName: 'psaldo_de_multas',
        postUpdate: false,
        decimalScale: 2,
        allowNegative: true,
        rawValueCallback: (val) => {
          //console.log('Callback personalizado:', val);
          // lógica extra aquí si deseas
          const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
          if (component) {
            component.set('psaldo_de_multas', val); // <- Esto envía el valor sin comas
          }
        }
      })"
      x-init="init($refs.cleaveInput)"
    >
      <div class="input-group input-group-merge has-validation">
        <input type="text" id="psaldo_de_multas" x-ref="cleaveInput" wire:ignore class="form-control js-input-psaldo_de_multas"
        >
      </div>
    </div>
    @error('psaldo_de_multas')
      <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_pago_multas_y_seguros">{{ __('Fecha Pago Multas Y Seguros') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_pago_multas_y_seguros"
        wire:model="pfecha_pago_multas_y_seguros"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_pago_multas_y_seguros') is-invalid @enderror">
    </div>
    @error('pfecha_pago_multas_y_seguros')
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
    <label class="form-label" for="pdepartamento_solicitante">{{ __('Departamento Solicitante') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pdepartamento_solicitante" id="pdepartamento_solicitante" class="form-control @error('pdepartamento_solicitante') is-invalid @enderror">
    </div>
    @error('pdepartamento_solicitante')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pfecha_e_instruccion_levantamiento">{{ __('Fecha e instrucciòn de levantamiento') }}</label>
    <div class="input-group input-group-merge has-validation">
      <span class="input-group-text"><i class="bx bx-calendar"></i></span>
      <input type="text" id="pfecha_e_instruccion_levantamiento"
        wire:model="pfecha_e_instruccion_levantamiento"
        x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
        x-init="init($el)"
        wire:ignore
        class="form-control date-picke @error('pfecha_e_instruccion_levantamiento') is-invalid @enderror">
    </div>
    @error('pfecha_e_instruccion_levantamiento')
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

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnumero_contrato">{{ __('Número de Contrato') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnumero_contrato" id="pnumero_contrato" class="form-control @error('pnumero_contrato') is-invalid @enderror">
    </div>
    @error('pnumero_contrato')
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
    <label class="form-label" for="pnombre_arrendatario">{{ __('Nombre del arrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnombre_arrendatario" id="pnombre_arrendatario" class="form-control @error('pnombre_arrendatario') is-invalid @enderror">
    </div>
    @error('pnombre_arrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcedula_arrendatario">{{ __('Cèdula del arrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcedula_arrendatario" id="pcedula_arrendatario" class="form-control @error('pcedula_arrendatario') is-invalid @enderror">
    </div>
    @error('pcedula_arrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcorreo_demandado_deudor_o_arrendatario">{{ __('Correo Demandado Deudor O Arrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcorreo_demandado_deudor_o_arrendatario" id="pcorreo_demandado_deudor_o_arrendatario" class="form-control @error('pcorreo_demandado_deudor_o_arrendatario') is-invalid @enderror">
    </div>
    @error('pcorreo_demandado_deudor_o_arrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="ptelefono_demandado_deudor_o_arrendatario">{{ __('Teléfono Demandado Deudor O Arrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="ptelefono_demandado_deudor_o_arrendatario" id="ptelefono_demandado_deudor_o_arrendatario" class="form-control @error('ptelefono_demandado_deudor_o_arrendatario') is-invalid @enderror">
    </div>
    @error('ptelefono_demandado_deudor_o_arrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnombre_contacto_o_arrendatario">{{ __('Nombre Contacto O Arrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnombre_contacto_o_arrendatario" id="pnombre_contacto_o_arrendatario" class="form-control @error('pnombre_contacto_o_arrendatario') is-invalid @enderror">
    </div>
    @error('pnombre_contacto_o_arrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pnombre_coarrendatario">{{ __('Nombre Coarrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pnombre_coarrendatario" id="pnombre_coarrendatario" class="form-control @error('pnombre_coarrendatario') is-invalid @enderror">
    </div>
    @error('pnombre_coarrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcedula_coarrendatario">{{ __('Cédula Coarrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcedula_coarrendatario" id="pcedula_coarrendatario" class="form-control @error('pcedula_coarrendatario') is-invalid @enderror">
    </div>
    @error('pcedula_coarrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcorreo_coarrendatario">{{ __('Correo Coarrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcorreo_coarrendatario" id="pcorreo_coarrendatario" class="form-control @error('pcorreo_coarrendatario') is-invalid @enderror">
    </div>
    @error('pcorreo_coarrendatario')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

 <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="ptelefono_coarrendatario">{{ __('Teléfono Coarrendatario') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="ptelefono_coarrendatario" id="ptelefono_coarrendatario" class="form-control @error('ptelefono_coarrendatario') is-invalid @enderror">
    </div>
    @error('ptelefono_coarrendatario')
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
    <label class="form-label" for="pubicacion_garantia">{{ __('Ubicación Garantia') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pubicacion_garantia" id="pubicacion_garantia" class="form-control @error('pubicacion_garantia') is-invalid @enderror">
    </div>
    @error('pubicacion_garantia')
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
    <label class="form-label" for="psubsidiaria">{{ __('Subsidiaria') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model.live="psubsidiaria" id="psubsidiaria" class="form-control @error('psubsidiaria') is-invalid @enderror">
    </div>
    @error('psubsidiaria')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pmueble">{{ __('Muebles') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model.live="pmueble" id="pmueble" class="form-control @error('pmueble') is-invalid @enderror">
    </div>
    @error('pmueble')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pinmueble">{{ __('Inmuebles') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model.live="pinmueble" id="pinmueble" class="form-control @error('pinmueble') is-invalid @enderror">
    </div>
    @error('pinmueble')
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
    <label class="form-label" for="pcomentarios_bullet_point">{{ __('Comentarios (Bullet Point)') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcomentarios_bullet_point" id="pcomentarios_bullet_point" class="form-control @error('pcomentarios_bullet_point') is-invalid @enderror">
    </div>
    @error('pcomentarios_bullet_point')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pplaca1">{{ __('Placa #1') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pplaca1" id="pplaca1" class="form-control @error('pplaca1') is-invalid @enderror">
    </div>
    @error('pplaca1')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pplaca2">{{ __('Placa #2') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pplaca2" id="pplaca2" class="form-control @error('pplaca2') is-invalid @enderror">
    </div>
    @error('pplaca2')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcontrato_leasing">{{ __('Contrato de Leasing') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcontrato_leasing" id="pcontrato_leasing" class="form-control @error('pcontrato_leasing') is-invalid @enderror">
    </div>
    @error('pcontrato_leasing')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="ptitular_contrato">{{ __('Titular del contrato de Leasing') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="ptitular_contrato" id="ptitular_contrato" class="form-control @error('ptitular_contrato') is-invalid @enderror">
    </div>
    @error('ptitular_contrato')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 col-sm-6 col-md-4 col-lg-3 fv-plugins-icon-container">
    <label class="form-label" for="pcedula_titular">{{ __('Cèdula del titular') }}</label>
    <div class="input-group input-group-merge has-validation">
      <input type="text" wire:model="pcedula_titular" id="pcedula_titular" class="form-control @error('pcedula_titular') is-invalid @enderror">
    </div>
    @error('pcedula_titular')
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

  <div class="col-12 col-md-9 fv-plugins-icon-container">
    <label class="form-label" for="acomentarios">{{ __('Comentarios') }}</label>
    <textarea class="form-control" wire:model="acomentarios" name="acomentarios" id="acomentarios" rows="2"></textarea>
    @error('acomentarios')
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
