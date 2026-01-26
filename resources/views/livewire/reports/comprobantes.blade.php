<div class="card">
  <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Reporte de Comprobantes') }}</h4>

  <div class="card-datatable text-nowrap">
    <div class="dataTables_wrapper dt-bootstrap5 no-footer">
      <form wire:submit.prevent="exportExcel">
        <div class="row g-3 align-items-end">

          <!-- Fecha de emisión -->
          <div class="col-md-3">
            <label class="form-label" for="filter_date">{{ __('Fecha de emisión') }}</label>
            <div class="input-group input-group-merge has-validation">
              <span class="input-group-text"><i class="bx bx-calendar"></i></span>
              <input type="text" id="filter_date"
                wire:model="filter_date"
                x-data="rangePickerLivewire({ wireEventName: 'dateRangeSelected' })"
                x-init="init($el)"
                wire:ignore
                class="form-control range-picker @error('filter_date') is-invalid @enderror"
                placeholder="dd-mm-aaaa to dd-mm-aaaa">
            </div>
            @error('filter_date')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <!-- Tipo Documento -->
          <div class="col-md-3 select2-primary">
            <label class="form-label" for="filter_tipo_documento">{{ __('Tipo Documento') }}</label>
            <div wire:ignore>
              <select wire:model="filter_tipo_documento" id="filter_tipo_documento"
                class="select2 form-select @error('filter_tipo_documento') is-invalid @enderror">
                <option value="">{{ __('Todos') }}</option>
                @foreach ($this->listTiposDocumento as $tipo)
                  <option value="{{ $tipo['id'] }}">{{ $tipo['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Estado Hacienda -->
          <div class="col-md-3 select2-primary">
            <label class="form-label" for="filter_estado_hacienda">{{ __('Estado Hacienda') }}</label>
            <div wire:ignore>
              <select wire:model="filter_estado_hacienda" id="filter_estado_hacienda"
                class="select2 form-select @error('filter_estado_hacienda') is-invalid @enderror">
                <option value="">{{ __('Todos') }}</option>
                @foreach ($this->listEstadosHacienda as $estado)
                  <option value="{{ $estado['id'] }}">{{ $estado['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Moneda -->
          <div class="col-md-3 select2-primary">
            <label class="form-label" for="filter_moneda">{{ __('Moneda') }}</label>
            <div wire:ignore>
              <select wire:model="filter_moneda" id="filter_moneda"
                class="select2 form-select">
                <option value="">{{ __('Todas') }}</option>
                <option value="CRC">CRC</option>
                <option value="USD">USD</option>
              </select>
            </div>
          </div>

          <!-- Emisor -->
          <div class="col-md-3">
            <label class="form-label" for="filter_emisor">{{ __('Emisor (Nombre o Identificación)') }}</label>
            <input type="text" id="filter_emisor" wire:model="filter_emisor" class="form-control">
          </div>

          <!-- Receptor -->
          <div class="col-md-3">
            <label class="form-label" for="filter_receptor">{{ __('Receptor (Nombre o Identificación)') }}</label>
            <input type="text" id="filter_receptor" wire:model="filter_receptor" class="form-control">
          </div>

        </div>

        <div class="row g-3 align-items-end">
          <!-- Botones de acción -->
          <div class="col-md-3 d-flex align-items-end">
            <button type="button"
                    class="btn btn-primary data-submit me-sm-4 me-1 mt-5"
                    wire:click="exportExcel"
                    wire:loading.attr="disabled"
                    wire:target="exportExcel">
                <span wire:loading.remove wire:target="exportExcel">
                    <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Exportar Excel') }}
                </span>
                <span wire:loading wire:target="exportExcel">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Generando...') }}
                </span>
            </button>
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
        'filter_tipo_documento',
        'filter_estado_hacienda',
        'filter_moneda'
      ];

      selects.forEach((id) => {
        const element = document.getElementById(id);
        if (element) {
          $(`#${id}`).select2();

          $(`#${id}`).on('change', function() {
            const newValue = $(this).val();
            //const livewireValue = @this.get(id); // access property directly if naming matches
             @this.set(id, newValue);
          });

          // Sync initial value (optional if simple wire:model bound but good for select2 reinit)
           const currentValue = @this.get(id);
           $(`#${id}`).val(currentValue).trigger('change');
        }
      });
    };

    // Re-ejecuta las inicializaciones después de actualizaciones de Livewire
    Livewire.on('reinitFormControls', () => {
      //console.log('Reinicializando controles...');
      setTimeout(() => {
        initializeSelect2();
      }, 200);
    });

    // Initial load
    initializeSelect2();

  })();
</script>
@endscript
