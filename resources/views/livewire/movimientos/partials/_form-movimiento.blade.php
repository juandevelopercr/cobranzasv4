<!-- Formulario para productos -->
<div class="card mb-6">
    <form wire:submit.prevent="{{ $action == 'edit' ? 'update' : 'store' }}" class="card-body">
        <!-- ...existing code... -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><span class="badge bg-primary">{{ __('General Information') }}</span></h4>
            <div>
                <button type="button" class="btn btn-primary">
                    Fondos Disponibles:
                    <span class="badge bg-white text-primary ms-1">{{ $fondos ?? '0.00' }}</span>
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>{{ __('Please fix the following errors:') }}</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-6">

            <div class="col-md-3 select2-primary fv-plugins-icon-container" x-data="select2Livewire({
                wireModelName: 'cuenta_id',
                postUpdate: true
            })"
                x-init="init($refs.select)" wire:ignore>
                <label class="form-label" for="cuenta_id">{{ __('Cuenta') }}</label>
                <select x-ref="select" id="cuenta_id"
                    class="select2 form-select @error('cuenta_id') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->cuentas as $cuenta)
                        <option value="{{ $cuenta['id'] }}">{{ $cuenta['nombre_cuenta'] }}</option>
                    @endforeach
                </select>
                @error('cuenta_id')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 select2-primary fv-plugins-icon-container" x-data="select2Livewire({ wireModelName: 'moneda_id', postUpdate: false })"
                x-init="init($refs.select)" wire:ignore>
                <label class="form-label" for="moneda_id">{{ __('Currency') }}</label>
                <select x-ref="select" id="moneda_id"
                    class="select2 form-select @error('moneda_id') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->currencies as $currency)
                        <option value="{{ $currency['id'] }}">{{ $currency['code'] }}</option>
                    @endforeach
                </select>
                @error('moneda_id')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 select2-primary fv-plugins-icon-container" x-data="select2Livewire({
                wireModelName: 'tipo_movimiento',
                postUpdate: true
            })"
                x-init="init($refs.select)" wire:ignore>
                <label class="form-label" for="tipo_movimiento">{{ __('Type') }}</label>
                <select x-ref="select" id="tipo_movimiento" :disabled="{{ $this->recordId ? 'true' : 'false' }}"
                    class="select2 form-select @error('tipo_movimiento') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->types as $type)
                        <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                    @endforeach
                </select>
                @error('tipo_movimiento')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="numero">{{ __('Número') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-box"></i></span>
                    <input type="text" wire:model="numero" id="numero"
                        class="form-control @error('numero') is-invalid @enderror" placeholder="{{ __('Número') }}"
                        @if ($tipo_movimiento === 'CHEQUE') disabled @endif>
                </div>
                @error('numero')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="beneficiario">{{ __('Beneficiario') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                    <input type="text" wire:model="beneficiario" id="beneficiario"
                        class="form-control @error('beneficiario') is-invalid @enderror"
                        placeholder="{{ __('Beneficiario') }}">
                </div>
                @error('beneficiario')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="lugar">{{ __('Lugar') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                    <input type="text" wire:model="lugar" id="lugar"
                        class="form-control @error('lugar') is-invalid @enderror" placeholder="{{ __('Lugar') }}">
                </div>
                @error('lugar')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="fecha">{{ __('Date') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                    <input type="text" id="fecha" @if (!$recordId) readonly @endif
                        wire:key="date-picker-{{ $datePickerKey }}"
                        wire:model="fecha" x-data="datePickerLivewire({ wireEventName: 'dateSelected' })" x-init="init($el)" wire:ignore
                        class="form-control date-picke @error('fecha') is-invalid @enderror" placeholder="dd-mm-aaaa">
                </div>
                @error('fecha')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 select2-primary fv-plugins-icon-container" x-data="select2Livewire({
                wireModelName: 'status',
                postUpdate: true
            })"
                x-init="init($refs.select)" wire:ignore>
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select x-ref="select" id="status" disabled
                    class="select2 form-select @error('status') is-invalid @enderror">
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach ($this->liststatus as $status)
                        <option value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                    @endforeach
                </select>
                @error('status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="monto">{{ __('Monto') }}</label>
                <div x-data="cleaveLivewire({
                    initialValue: '{{ $monto ?? '' }}',
                    wireModelName: 'monto',
                    postUpdate: false,
                    decimalScale: 2,
                    allowNegative: true,
                    rawValueCallback: (val) => {
                        //console.log('Callback personalizado:', val);
                        // lógica extra aquí si deseas
                        const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
                        if (component) {
                            component.set('monto', val, false); // <- Esto envía el valor sin comas de forma diferida
                        }
                    }
                })" x-init="init($refs.cleaveInput)">
                    <div class="input-group input-group-merge has-validation">
                        <input type="text" id="monto" x-ref="cleaveInput" wire:ignore
                            class="form-control js-input-monto" />
                    </div>
                </div>
                @error('monto')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container" x-data="{
                ...cleaveLivewire({
                    initialValue: '{{ $saldo_cancelar ?? '' }}',
                    wireModelName: 'saldo_cancelar',
                    postUpdate: false,
                    decimalScale: 2,
                    allowNegative: true,
                    watchProperty: 'saldoVal',
                    rawValueCallback: (val) => {
                        //console.log('Callback personalizado:', val);
                        // lógica extra aquí si deseas
                    }
                }),
                tipoMovimiento: @entangle('tipo_movimiento'),
                saldoVal: @entangle('saldo_cancelar')
            }" x-init="init($refs.cleaveInput)"
                x-show="tipoMovimiento == 'DEPOSITO'" style="display: none;">
                <label class="form-label" for="saldo_cancelar">{{ __('Saldo a cancelar') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-transfer"></i></span>

                    <!-- oculto y sincronizado con Livewire -->
                    <input type="hidden" x-ref="inputHiddenSaldoCancelar" data-model="saldo_cancelar" />

                    <input id="saldo_cancelar" x-ref="cleaveInput"
                        class="form-control js-input-saldo-cancelar numeral-mask fw-bold" type="text"
                        disabled />
                </div>
                @error('saldo_cancelar')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            @if ($this->tipo_movimiento != 'DEPOSITO')
                <div class="col-md-3 fv-plugins-icon-container">
                    <label class="form-label" for="impuesto">{{ __('Tax') }}</label>
                    <div x-data="cleaveLivewire({
                        initialValue: '{{ $impuesto ?? '' }}',
                        wireModelName: 'impuesto',
                        postUpdate: false,
                        decimalScale: 2,
                        allowNegative: true,
                        rawValueCallback: (val) => {
                            //console.log('Callback personalizado:', val);
                            // lógica extra aquí si deseas
                            const component = Livewire.find($refs.cleaveInput.closest('[wire\\:id]').getAttribute('wire:id'));
                            if (component) {
                                component.set('impuesto', val, false); // <- Esto envía el valor sin comas de forma diferida
                            }
                        }
                    })" x-init="init($refs.cleaveInput)">
                        <div class="input-group input-group-merge has-validation">
                            {{--  <span class="input-group-text"><i class="bx bx-dollar"></i></span> --}}
                            <input type="text" id="impuesto" x-ref="cleaveInput" wire:ignore
                                class="form-control js-input-impuesto" />
                        </div>
                    </div>
                    @error('impuesto')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="col-md-3 fv-plugins-icon-container" x-data="{
                ...cleaveLivewire({
                    initialValue: '{{ $total_general ?? '' }}',
                    wireModelName: 'total_general',
                    postUpdate: false,
                    decimalScale: 2,
                    allowNegative: true,
                    watchProperty: 'totalVal',
                    rawValueCallback: (val) => {
                        //console.log('Callback personalizado:', val);
                        // lógica extra aquí si deseas
                    }
                }),
                tipoMovimiento: @entangle('tipo_movimiento'),
                totalVal: @entangle('total_general')
            }" x-init="init($refs.cleaveInput)"
                x-show="tipoMovimiento !== 'DEPOSITO'" style="display: none;">
                <label class="form-label" for="total_general">{{ __('Total General') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-transfer"></i></span>

                    <input type="hidden" x-ref="inputHiddenTotal" data-model="total_general" />

                    <input id="total_general" x-ref="cleaveInput"
                        class="form-control js-input-total-general numeral-mask fw-bold" type="text"
                        disabled />
                </div>
                @error('total_general')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <label class="form-label" for="diferencia">{{ __('Diferencia') }}</label>
                <div x-data="{
                    ...cleaveLivewire({
                        initialValue: '{{ $diferencia ?? '' }}',
                        wireModelName: 'diferencia',
                        postUpdate: false,
                        decimalScale: 2,
                        allowNegative: true,
                        watchProperty: 'diferenciaVal',
                        rawValueCallback: (val) => {
                            //console.log('Callback personalizado:', val);
                            // lógica extra aquí si deseas
                        }
                    }),
                    diferenciaVal: @entangle('diferencia')
                }" x-init="init($refs.cleaveInput)">
                    <div class="input-group input-group-merge has-validation">
                        <!-- oculto y sincronizado con Livewire -->
                        <input type="hidden" x-ref="inputHiddenDiferencia" data-model="diferencia" />

                        <input type="text" id="diferencia" x-ref="cleaveInput"
                            class="form-control js-input-diferencia fw-bold"
                            disabled />
                    </div>
                </div>
                @error('diferencia')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 fv-plugins-icon-container">
                <label class="form-label" for="monto_letras">{{ __('Monto en Letras') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                    <!-- Input único: visible, sincronizado con Livewire -->
                    <input type="text" id="monto_letras" class="form-control"
                        placeholder="{{ __('Monto en Letras') }}" readonly />
                </div>
                @error('monto_letras')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <div class="form-check form-switch ms-2 my-3">
                    <input type="checkbox" class="form-check-input" id="tiene_retencion"
                        wire:model.live="tiene_retencion" {{ $tiene_retencion == 1 ? 'checked' : '' }} />
                    <label for="tiene_retencion" class="switch-label">{{ __('Tiene Retención') }}</label>
                </div>
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <div class="form-check form-switch ms-2 my-3">
                    <input type="checkbox" class="form-check-input" id="bloqueo_fondos"
                        wire:model.live="bloqueo_fondos" {{ $bloqueo_fondos == 1 ? 'checked' : '' }} />
                    <label for="bloqueo_fondos" class="switch-label">{{ __('Bloqueo de Fondos') }}</label>
                </div>
            </div>

            <div class="col-md-3 fv-plugins-icon-container">
                <div class="form-check form-switch ms-2 my-3">
                    <input type="checkbox" class="form-check-input" id="comprobante_pendiente"
                        wire:model.live="comprobante_pendiente" {{ $comprobante_pendiente == 1 ? 'checked' : '' }} />
                    <label for="comprobante_pendiente" class="switch-label">{{ __('Comprobante Pendiente') }}</label>
                </div>
            </div>

            <div class="col-md-12 fv-plugins-icon-container">
                <label class="form-label" for="descripcion">{{ __('Description') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" wire:model="descripcion" name="descripcion"
                        id="descripcion" rows="4" placeholder="{{ __('Description') }}">
          </textarea>
                </div>
                @error('Description')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            @if ($this->recordId && $this->tipo_movimiento == \App\Models\Movimiento::TYPE_DEPOSITO)
                <h4 class="mb-0"><span class="badge bg-primary">{{ __('Invoices') }}</span></h4>
                @livewire('movimientos.movimientos-facturas', [
                    'movimientoId' => $this->recordId,
                ])
            @endif


            <h4 class="mb-0"><span class="badge bg-primary">{{ __('Centros de costo') }}</span></h4>
            @livewire('movimientos.movimientos-centro-costo', [
                'movimiento_id' => $this->recordId ?? null,
            ], key('movimientos-centro-costo-child'))

            <br>
            <h4 class="mb-0"><span class="badge bg-primary">{{ __('Solicitud de comprobante') }}</span></h4>

            <div class="col-md-4 fv-plugins-icon-container">
                <label class="form-label" for="concepto">{{ __('Concepto') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                    <input type="text" wire:model="concepto" id="concepto"
                        class="form-control @error('concepto') is-invalid @enderror"
                        placeholder="{{ __('Concepto') }}">
                </div>
                @error('concepto')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 fv-plugins-icon-container">
                <label class="form-label" for="email_destinatario">{{ __('Correo a cliente o abogado') }}</label>
                <div class="input-group input-group-merge has-validation">
                    <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                    <input type="text" wire:model="email_destinatario" id="email_destinatario"
                        class="form-control @error('email_destinatario') is-invalid @enderror"
                        placeholder="{{ __('Correo a cliente o abogado') }}">
                </div>
                @error('email_destinatario')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 fv-plugins-icon-container my-12">
                <button type="button" class="btn btn-primary" wire:click="sendComprobanteByEmail"
                    wire:loading.attr="disabled" wire:target="sendComprobanteByEmail">
                    <span wire:loading.remove wire:target="sendComprobanteByEmail">
                        {{ __('Enviar Correo') }}
                    </span>
                    <span wire:loading wire:target="sendComprobanteByEmail">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        {{ __('Enviando...') }}
                    </span>
                </button>
            </div>
        </div>

        <br>
        <div class="pt-6">
            {{-- Incluye botones de guardar y guardar y cerrar --}}
            @include('livewire.includes.button-saveAndSaveAndClose')

            <!-- Botón Cancel -->
            <button type="button" class="btn btn-outline-secondary me-sm-4 me-1 mt-5" wire:click="cancel"
                wire:loading.attr="disabled" wire:target="cancel">
                <span wire:loading.remove wire:target="cancel">
                    <span class="fa fa-remove bx-18px me-2"></span>{{ __('Cancel') }}
                </span>
                <span wire:loading wire:target="cancel">
                    <i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('Cancelling...') }}
                </span>
            </button>
        </div>
    </form>
</div>

@script()
    <script>
        (function() {
            // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
            Livewire.on('reinitConvertNumbertoWord', () => {
                jQuery(document).ready(function() {
                    //console.log('Reinicializando controles después de Livewire update reinitConvertNumbertoWord');
                    setTimeout(() => {
                        // También mostrar valor inicial
                        setConvertNumberToWord();
                        initMovimientoHandlers();
                    }, 200); // Retraso para permitir que el DOM se estabilice
                });
            });

            Livewire.on('refreshCalculaMontos', () => {
                jQuery(document).ready(function() {
                    //console.log('Reinicializando controles después de Livewire update reinitConvertNumbertoWord');
                    setTimeout(() => {
                        // También mostrar valor inicial
                        console.log("Entro al refresh");
                        calculaMontos(null);
                    }, 200); // Retraso para permitir que el DOM se estabilice
                });
            });

            function setConvertNumberToWord() {
                const inputMonto = document.getElementById('monto');
                const inputImpuesto = document.getElementById('impuesto');
                const inputLetras = document.getElementById('monto_letras');
                const tipo = document.getElementById('tipo_movimiento');

                if (!inputMonto || !inputLetras) return;

                inputMonto.addEventListener('input', actualizarLetras);
                if (inputImpuesto) inputImpuesto.addEventListener('input', actualizarLetras);

                function actualizarLetras() {
                    const amount = parseFloat(inputMonto.value.replace(/,/g, '') || 0);
                    const impuesto = parseFloat(inputImpuesto?.value.replace(/,/g, '') || 0);
                    const tipoValor = tipo?.value;

                    if (isNaN(amount)) return;

                    const total = tipoValor === 'DEPOSITO' ? amount : amount + impuesto;
                    const letras = numeroALetras(total);

                    inputLetras.value = letras;

                    if (window.Livewire) {
                        const componentEl = inputLetras.closest('[wire\\:id]');
                        if (componentEl) {
                            const component = Livewire.find(componentEl.getAttribute('wire:id'));
                            if (component) {
                                component.set('monto_letras', letras, false);
                            }
                        }
                    }
                }
            }

            function initMovimientoHandlers() {
                const inputMonto = document.getElementById('monto');
                const inputImpuesto = document.getElementById('impuesto');
                const inputMontoCentroCosto = document.querySelector('.inputMontoCentroCosto')

                if (inputMonto) {
                    inputMonto.addEventListener('input', (e) => calculaMontos(e));
                    inputMonto.addEventListener('keyup', (e) => calculaMontos(e));
                }

                if (inputImpuesto) {
                    inputImpuesto.addEventListener('input', (e) => calculaMontos(e));
                    inputImpuesto.addEventListener('keyup', (e) => calculaMontos(e));
                }

                if (inputMontoCentroCosto) {
                    inputMontoCentroCosto.addEventListener('input', (e) => calculaMontos(e));
                    inputMontoCentroCosto.addEventListener('keyup', (e) => calculaMontos(e));
                }
            }

            document.addEventListener('input', function(e) {
                if (e.target && e.target.id && e.target.id.startsWith('amount_')) {
                    //console.log("Se reasigna el evento en centros de costo");
                    calculaMontos(e);
                }
            });

            document.addEventListener('keyup', function(e) {
                if (e.target && e.target.id && e.target.id.startsWith('amount_')) {
                    //console.log("keyup el evento en centros de costo");
                    calculaMontos(e);
                }
            });

            function calculaMontos(event) {
                console.log("Entró al evento");
                const origin = event?.target;
                const inputEventId = origin?.id || '';
                //console.log('Evento disparado por:', origin?.id || origin?.name || origin?.className);
                //console.log('Evento disparado por:', origin?.id);

                const tipoMovimiento = document.getElementById('tipo_movimiento')?.value ?? '';
                const inputMonto = document.getElementById('monto');
                const inputImpuesto = document.getElementById('impuesto');

                const inputTotal = document.getElementById('total_general');
                const inputHiddenTotal = document.querySelector('[x-ref="inputHiddenTotal"]');

                const inputDiferencia = document.getElementById('diferencia');
                const inputHiddenDiferencia = document.querySelector('[x-ref="inputHiddenDiferencia"]');

                const inputSaldoCancelar = document.getElementById('saldo_cancelar');
                const inputHiddenSaldoCancelar = document.querySelector('[x-ref="inputHiddenSaldoCancelar"]');
                //const inputHiddenImpuesto = document.querySelector('[x-ref="inputHiddenImpuesto"]');
                const inputHiddenImpuesto = document.getElementById('impuesto');


                const monto = limpiarNumero(inputMonto?.value);
                const impuesto = limpiarNumero(inputImpuesto?.value);

                if (tipoMovimiento === 'DEPOSITO') {
                    if (['monto', 'impuesto'].includes(inputEventId))
                        setFirstRowValueCentrocosto(monto);
                    const saldo = limpiarNumero(inputSaldoCancelar?.value);
                    setFormattedValue(inputTotal, saldo, inputHiddenTotal);
                    calculateDifferences(monto, saldo);
                } else {
                    const total = monto + impuesto;
                    calculateTotalGeneral(inputTotal, monto, impuesto);
                    const montoDistribuido = getMontoDistribuido();
                    calculateDifferences(total, montoDistribuido);
                }

                /*
                function setFirstRowValueCentrocosto(value) {
                  const input = document.getElementById('amount_0');
                  if (input) setFormattedValue(input, value);
                }
                */
                function setFirstRowValueCentrocosto(value) {
                    const centroCostoContainer = document.getElementById('content-centro-costo');
                    if (!centroCostoContainer) return;

                    // Buscar el primer input que tenga la clase específica o empiece con amount_
                    const input = centroCostoContainer.querySelector('.inputMontoCentroCosto') ||
                                  centroCostoContainer.querySelector('input[id^="amount_"]');

                    if (!input) return;

                    // Asegurar que tengamos un valor numérico formateado a 2 decimales
                    const numericValue = parseFloat(value);
                    const formattedValue = isNaN(numericValue) ? '0.00' : numericValue.toFixed(2);

                    // 1. Actualizar el valor visualmente (Cleave.js maneja la máscara)
                    if (input.cleaveInstance) {
                        input.cleaveInstance.setRawValue(formattedValue);
                    } else {
                        input.value = formattedValue;
                    }

                    // 2. Disparar eventos para que Alpine/Livewire detecten el cambio manual
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));

                    // 3. Sincronizar directamente con el componente Livewire (hijo)
                    const componentEl = centroCostoContainer.closest('[wire\\:id]');
                    const componentId = componentEl?.getAttribute('wire:id');

                    if (componentId && window.Livewire) {
                        const component = Livewire.find(componentId);
                        if (component) {
                            // Extraer la llave (UUID) del ID del input (ej: amount_550e8400...)
                            const rowKey = input.id.replace('amount_', '');

                            //console.log(`[setFirstRowValueCentrocosto] Sincronizando rows.${rowKey}.amount = ${formattedValue}`);
                            component.set(`rows.${rowKey}.amount`, formattedValue, true);
                        }
                    }
                }

                function calculateDifferences(total, distributed) {
                    // Usar enteros para evitar errores de precisión decimal
                    const totalCents = Math.round(total * 100);
                    const distCents = Math.round(distributed * 100);
                    let diff = (totalCents - distCents) / 100;

                    if (Math.abs(diff) < 0.001) diff = 0;

                    setFormattedValue(inputDiferencia, diff, inputHiddenDiferencia);

                    // Poner en rojo si hay cualquier diferencia mayor a 0.5 centavos
                    inputDiferencia?.classList.toggle("text-danger", Math.abs(diff) >= 0.005);
                }

                function calculateTotalGeneral(input, amount, impuesto) {
                    const total = (Math.round(amount * 100) + Math.round(impuesto * 100)) / 100;
                    setFormattedValue(input, total, inputHiddenTotal);

                    const rows = parseInt(document.getElementById('content-centro-costo')?.dataset?.rowsCount || '0',
                        10);
                    if (rows === 1 && ['monto', 'impuesto'].includes(inputEventId))
                        setFirstRowValueCentrocosto(total);
                }

                function getMontoDistribuido() {
                    let total = 0;

                    // Captura todos los inputs con id que comienza en "amount_"
                    const inputs = document.querySelectorAll('input[id^="amount_"]');

                    inputs.forEach(input => {
                        if (input) {
                            const cleaned = input.value.replace(/[^0-9.-]/g, '').trim();
                            const value = parseFloat(cleaned);

                            if (!isNaN(value)) {
                                total += Math.round(value * 100);
                            }
                        }
                    });

                    return total / 100;
                }

                function limpiarNumero(valor) {
                    if (typeof valor === 'number') return valor;
                    if (!valor || typeof valor !== 'string') return 0;

                    const limpio = parseFloat(valor.replace(/,/g, '').trim());
                    return isNaN(limpio) ? 0 : limpio;
                }

                function isWireIgnored(el) {
                    while (el) {
                        if (el.hasAttribute?.('wire:ignore') || el.hasAttribute?.('wire:ignore.self')) {
                            return true;
                        }
                        el = el.parentElement;
                    }
                    return false;
                }

                function setFormattedValue(inputVisible, value, inputHidden) {
                    if (!inputVisible) {
                        console.log('[setFormattedValue] inputVisible es null', { value, inputHidden });
                        return 0;
                    }

                    console.log('[setFormattedValue] Input visible:', inputVisible);
                    console.log('[setFormattedValue] Input oculto:', inputHidden);
                    console.log('[setFormattedValue] Value recibido:', value);

                    let numericValue = parseFloat(value);
                    if (isNaN(numericValue) || !isFinite(numericValue)) numericValue = 0;

                    console.log('[setFormattedValue] Value numérico:', numericValue);

                    if (inputVisible.cleaveInstance) {
                        inputVisible.cleaveInstance.setRawValue(numericValue.toFixed(2));
                        console.log('[setFormattedValue] Usando cleaveInstance para setRawValue');
                    } else {
                        inputVisible.value = Number(numericValue).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        console.log('[setFormattedValue] Asignando value formateado manual');
                    }

                    if (inputHidden) {
                        const wireModel = inputHidden.getAttribute('data-model');
                        const numericValueStr = numericValue.toFixed(2);
                        inputHidden.value = numericValueStr;

                        if (wireModel && window.Livewire) {
                            const componentEl = inputHidden.closest('[wire\\:id]');
                            if (componentEl) {
                                const component = Livewire.find(componentEl.getAttribute('wire:id'));
                                if (component) {
                                    component.set(wireModel, numericValueStr, false);
                                }
                            }
                        }
                    }

                    return numericValue;
                }
            }
        })();
    </script>
@endscript
