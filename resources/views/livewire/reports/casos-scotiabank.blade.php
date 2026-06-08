<div class="card">
    <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Reporte de Casos de Davibank') }}</h4>
    <div class="card-datatable text-nowrap">
        <div class="dataTables_wrapper dt-bootstrap5 no-footer">
            <form wire:submit.prevent="exportExcel">
                <div class="row g-6">
                    <div class="col-md-3 fv-plugins-icon-container">
                        <label class="form-label" for="filter_date">{{ __('Fecha de asignación de caso') }}</label>
                        <div class="input-group input-group-merge has-validation">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="text" id="filter_date" wire:model="filter_date" x-data="rangePickerLivewire({ wireEventName: 'dateRangeSelected' })"
                                x-init="init($el)" wire:ignore
                                class="form-control range-picker @error('filter_date') is-invalid @enderror"
                                placeholder="dd-mm-aaaa">
                        </div>
                        @error('filter_date')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 fv-plugins-icon-container">
                        <label class="form-label" for="filter_numero_caso">{{ __('Número de Caso') }}</label>
                        <div class="input-group input-group-merge has-validation">
                            <span class="input-group-text"><i class="bx bx-box"></i></span>
                            <input type="text" wire:model="filter_numero_caso" id="filter_numero_caso"
                                class="form-control @error('filter_numero_caso') is-invalid @enderror"
                                placeholder="{{ __('Número de Caso') }}">
                        </div>
                        @error('filter_numero_caso')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 select2-primary fv-plugins-icon-container">
                        <label class="form-label" for="filter_abogado">{{ __('Abogado') }}</label>
                        <div wire:ignore>
                            <select wire:model="filter_abogado" id="filter_abogado"
                                class="select2 form-select @error('filter_abogado') is-invalid @enderror">
                                <option value="">{{ __('Seleccione...') }}</option>
                                @foreach ($this->abogados as $abogado)
                                    <option value="{{ $abogado->id }}">{{ $abogado->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('filter_abogado')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 select2-primary fv-plugins-icon-container">
                        <label class="form-label" for="filter_asistente">{{ __('Asistente') }}</label>
                        <div wire:ignore>
                            <select wire:model="filter_asistente" id="filter_asistente"
                                class="select2 form-select @error('filter_asistente') is-invalid @enderror">
                                <option value="">{{ __('Seleccione...') }}</option>
                                @foreach ($this->abogados as $abogado)
                                    <option value="{{ $abogado->id }}">{{ $abogado->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('filter_asistente')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 select2-primary fv-plugins-icon-container">
                        <label class="form-label" for="filter_currency">{{ __('Currency') }}</label>
                        <div wire:ignore>
                            <select wire:model="filter_currency" id="filter_currency"
                                class="select2 form-select @error('filter_currency') is-invalid @enderror">
                                <option value="">{{ __('Seleccione...') }}</option>
                                @foreach ($this->currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('filter_currency')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 select2-primary fv-plugins-icon-container">
                        <label class="form-label" for="filter_exclude_products">{{ __('Excluir Productos') }}</label>
                        <div wire:ignore>
                            <select wire:model="filter_exclude_products" id="filter_exclude_products"
                                multiple
                                class="select2 form-select @error('filter_exclude_products') is-invalid @enderror">
                                @foreach ($this->productos as $producto)
                                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">Los productos seleccionados NO aparecerán en el reporte.</small>
                    </div>

                    <!-- Botones de acción -->
                    <div class="col-md-3 d-flex align-items-end"
                         x-data="{ loading: false }"
                         @download-ready.window="loading = false">
                        <button type="button" class="btn btn-primary data-submit me-sm-4 me-1 mt-5"
                            :disabled="loading"
                            @click="loading = true; $wire.exportExcel(document.getElementById('filter_date')?.value?.trim() ?? '')">
                            <span x-show="!loading">
                                <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Exportar') }}
                            </span>
                            <span x-show="loading" x-cloak>
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Generando reporte...') }}
                            </span>
                        </button>
                    </div>
                </div>
                <div class="row g-6">
                    <div class="col-md-12 mt-4">
                        <div class="p-3 border rounded bg-light">
                            <p class="text-dark mb-2" style="font-size: 0.9rem;">
                                <strong>Leyenda:</strong> Para que se desplieguen los datos debe cumplir con:
                            </p>

                            <ol class="text-dark" style="font-size: 0.9rem; padding-left: 20px;">
                                <li>Si selecciona un rango de fechas, se filtrarán los casos por fecha de asignación.</li>
                                <li>Si no selecciona fechas, se exportarán <strong>todos</strong> los casos de Davibank.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@script()
    <script>
        (function() {
            // Función para inicializar Select2
            const initializeSelect2 = () => {
                const selects = [
                    'filter_abogado',
                    'filter_asistente',
                    'filter_banco',
                    'filter_currency',
                    'filter_exclude_products'
                ];

                selects.forEach((id) => {
                    const element = document.getElementById(id);
                    if (element) {
                        //console.log(`Inicializando Select2 para: ${id}`);

                        $(`#${id}`).select2();

                        $(`#${id}`).on('change', function() {
                            const newValue = $(this).val();
                            const livewireValue = @this.get(id);

                            if (newValue !== livewireValue) {
                                // Actualiza Livewire solo si es el select2 de `condition_sale`
                                // Hay que poner wire:ignore en el select2 para que todo vaya bien
                                const specificIds = ['filter_abogado', 'filter_asistente',
                                    'filter_banco', 'filter_currency', 'filter_exclude_products'
                                ]; // Lista de IDs específicos

                                if (specificIds.includes(id)) {
                                    @this.set(id, newValue);
                                } else {
                                    // Para los demás select2, actualiza localmente sin llamar al `updated`
                                    @this.set(id, newValue, false);
                                }
                            }
                        });
                    }

                    // Sincroniza el valor actual desde Livewire al Select2
                    const currentValue = @this.get(id);
                    $(`#${id}`).val(currentValue).trigger('change');
                });
            };

            // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
            Livewire.on('reinitFormControls', () => {
                console.log('Reinicializando controles después de Livewire update reinitFormControls');
                setTimeout(() => {
                    initializeSelect2();
                }, 200);
            });

            Livewire.on('start-download', ({ url }) => {
                const a = document.createElement('a');
                a.href = url;
                a.setAttribute('download', '');
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.dispatchEvent(new CustomEvent('download-ready'));
            });

        })();
    </script>
@endscript
