@php
  use App\Models\Transaction;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Session;
  use App\Models\User;
@endphp
<div>
  @if($action == 'list')
    <!-- DataTable with Buttons -->
    <div class="card">
      <h4 class="card-header pb-0 text-md-start text-center ms-n2">{{ __('Seguimientofacturas') }}</h4>
      <div class="card-datatable text-nowrap">
        <div class="dataTables_wrapper dt-bootstrap5 no-footer">
          <div class="row">
            <div class="col-md-2">
              <div class="ms-n2">
                @include('livewire.includes.table-paginate')
              </div>
            </div>
            <div class="col-md-10">
              <div
                class="dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0">
                <div class="dt-buttons btn-sm btn-group flex-wrap mt-5">
                  @can("edit-proformas")
                    @include('livewire.includes.button-edit')
                  @endcan

                  <!-- Dropdown with icon -->
                  <div x-data="{ action: @entangle('action') }">
                    <div x-show="action === 'list'" x-cloak>
                      @can("export-seguimientofacturas")
                        <livewire:transactions.transaction-datatable-export />
                      @endcan
                    </div>
                  </div>

                  <!-- Dropdown with icon -->
                  <div class="btn-group" role="group" aria-label="DataTable Actions">
                    <!-- Botón para Reiniciar Filtros -->
                      @include('livewire.includes.button-reset-filters')

                      <!-- Botón para Configurar Columnas -->
                      @include('livewire.includes.button-config-columns')
                  </div>

                  <!-- Renderizar el componente Livewire -->
                  @livewire('components.datatable-settings', [
                    'datatableName' => 'seguimiento-datatable',
                    'availableColumns' => $this->columns,
                    'perPage' => $this->perPage,
                  ],
                  key('seguimiento-datatable-config'))

                </div>
              </div>
            </div>
          </div>

          @can("view-seguimientofacturas")
            <div class="card-datatable table-responsive">
              <table class="table table-sm mb-0 border-top table-hover dataTable no-footer" id="transaction-table" style="width: 100%;">
                <thead>
                  <tr>
                    <th class="control sorting_disabled dtr-hidden" rowspan="1" colspan="1"
                      style="width: 0px; display: none;" aria-label="">
                    </th>
                    <th class="sorting_disabled dt-checkboxes-cell dt-checkboxes-select-all" rowspan="1" colspan="1"
                      style="width: 18px;" data-col="1" aria-label="">
                      <input type="checkbox" class="form-check-input" id="select-all" wire:click="toggleSelectAll">
                    </th>

                    @include('livewire.includes.headers', ['columns' => $this->columns])

                    @php
                    /*
                    <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 120px;" aria-label="Actions">{{
                      __('Actions') }}
                    </th>
                    */
                    @endphp
                  </tr>
                  <!-- Fila de filtros -->
                  <tr>
                    @include('livewire.includes.filters', ['columns' => $this->columns])
                  </tr>
                </thead>
                <tbody>
                  @php
                  $tComprobante = 0;
                  $tComprobanteUsd = 0;
                  $tComprobanteCrc = 0;
                  $allowedRoles = User::ROLES_ALL_BANKS;
                  @endphp

                  @foreach ($records as $record)
                  @php
                  $totalComprobante = $record->totalComprobante;
                  $totalComprobanteUsd = $record->getTotalComprobante('USD');
                  $totalComprobanteCrc = $record->getTotalComprobante('CRC');

                  $tComprobante += $totalComprobante;
                  $tComprobanteUsd += $totalComprobanteUsd;
                  $tComprobanteCrc += $totalComprobanteCrc;
                  @endphp

                  <tr wire:key='{{ $record->id }}' class="{{ $loop->odd ? 'odd' : 'even' }} {{ $record->department_id != 1 ? 'table-info' : '' }}">
                    <td class="control" style="display: none;" tabindex="0"></td>
                    <td class="dt-checkboxes-cell">
                      <input type="checkbox" class="form-check-input" wire:model.live="selectedIds"
                        value="{{ $record->id }}">
                    </td>

                    @include('livewire.includes.columns', [
                        'columns' => $this->columns,
                        'record' => $record,
                        'canedit' => auth()->user()->can('edit-proformas') &&
                            ($record->proforma_status == Transaction::PROCESO ||
                            ($record->proforma_status == Transaction::SOLICITADA &&
                            auth()->user()->hasAnyRole(User::ROLES_ALL_BANKS)))
                    ])

                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <td></td>
                    @foreach ($this->columns as $index => $column)
                      @if ($column['visible'])
                        @php
                        $value = !empty($column['sumary']) ? (${$column['sumary']} ?? '') : '';
                        @endphp
                      <td>
                        <strong>{{ Helper::formatDecimal($value) }}</strong>
                      </td>
                      @endif
                    @endforeach
                  </tr>
                </tfoot>
              </table>
              <div class="row overflow-y-scroll" wire:scroll>
                {{ $records->links(data: ['scrollTo' => false]) }}
              </div>
            </div>
          @endcan
        </div>
        <div style="width: 1%;"></div>
      </div>
    </div>
  @endif

  <livewire:modals.caby-modal />

  @livewire('transactions.send-email-modal', [
            'documentType' => 'PROFORMA',
            'canview'   => auth()->user()->can('view-documento-proformas'),
            'cancreate' => auth()->user()->can('create-documento-proformas'),
            'canedit'   => auth()->user()->can('edit-documento-proformas'),
            'candelete' => auth()->user()->can('delete-documento-proformas'),
            'canexport' => auth()->user()->can('export-documento-proformas'),
          ], key('transaction-send-email'))


  <div x-data="{ action: @entangle('action') }">
    <div x-show="action === 'create' || action === 'edit'" x-cloak>
      @include('livewire.transactions.partials.form_seguimiento')
    </div>
  </div>

  @if($showDepositoModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar datos de depósito</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDepositoModal', false)"></button>
                </div>
                <div class="modal-body">
                  <div class="row g-6">
                    <div class="col-md-6 fv-plugins-icon-container">
                      <label class="form-label" for="fechaDepositoModal">{{ __('Fecha de depósito') }}</label>
                      <div class="input-group input-group-merge has-validation">
                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                        <input type="text" id="fechaDepositoModal"
                          wire:model="fechaDepositoModal"
                          x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
                          x-init="init($el)"
                          wire:ignore
                          class="form-control date-picke @error('fechaDepositoModal') is-invalid @enderror"
                          placeholder="dd-mm-aaaa">
                      </div>
                      @error('fechaDepositoModal')
                      <div class="text-danger mt-1">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="col-md-6 fv-plugins-icon-container">
                      <label class="form-label" for="numeroDepositoPagoModal">{{ __('No. depósito') }}</label>
                      <div class="input-group input-group-merge has-validation">
                        <span class="input-group-text"><i class="bx bx-receipt"></i></span>
                        <input type="text" wire:model="numeroDepositoPagoModal" name="numeroDepositoPagoModal" id="numeroDepositoPagoModal"
                          class="form-control @error('numeroDepositoPagoModal') is-invalid @enderror" placeholder="{{ __('No. depósito') }}"
                          aria-label="{{ __('No. depósito') }}">
                      </div>
                      @error('numeroDepositoPagoModal')
                      <div class="text-danger mt-1">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                    <!-- Botón Guardar -->
                    <button type="button"
                        class="btn btn-success data-submit me-sm-4 me-1 mt-5"
                        wire:click="saveDepositoModal"
                        wire:loading.attr="disabled"
                        wire:target="saveDepositoModal">

                        <!-- Icono + texto normal -->
                        <span wire:loading.remove wire:target="saveDepositoModal">
                            <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Save') }}
                        </span>

                        <!-- Spinner mientras carga -->
                        <span wire:loading wire:target="saveDepositoModal">
                            <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                            {{ __('Updating...') }}
                        </span>
                    </button>

                    <!-- Botón Cancelar -->
                    <button type="button"
                        class="btn btn-outline-secondary me-sm-4 me-1 mt-5"
                        wire:click="$set('showDepositoModal', false)"
                        wire:loading.attr="disabled">
                        <i class="bx bx-x bx-18px me-2"></i>{{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
  @endif

  @if($showHonorarioModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar datos de depósito</h5>
                    <button type="button" class="btn-close" wire:click="$set('showHonorarioModal', false)"></button>
                </div>
                <div class="modal-body">
                  <div class="row g-6">
                    <div class="col-md-6 fv-plugins-icon-container">
                      <label class="form-label" for="fechaTrasladoHonorarioModal">{{ __('Fecha de traslado de honorario') }}</label>
                      <div class="input-group input-group-merge has-validation">
                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                        <input type="text" id="fechaTrasladoHonorarioModal"
                          wire:model="fechaTrasladoHonorarioModal"
                          x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
                          x-init="init($el)"
                          wire:ignore
                          class="form-control date-picke @error('fechaTrasladoHonorarioModal') is-invalid @enderror"
                          placeholder="dd-mm-aaaa">
                      </div>
                      @error('fechaTrasladoHonorarioModal')
                      <div class="text-danger mt-1">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="col-md-6 fv-plugins-icon-container">
                      <label class="form-label" for="numeroTrasladoHonorarioModal">{{ __('No. de honorario') }}</label>
                      <div class="input-group input-group-merge has-validation">
                        <span class="input-group-text"><i class="bx bx-receipt"></i></span>
                        <input type="text" wire:model="numeroTrasladoHonorarioModal" name="numeroTrasladoHonorarioModal" id="numeroTrasladoHonorarioModal"
                          class="form-control @error('numeroTrasladoHonorarioModal') is-invalid @enderror" placeholder="{{ __('No. de honorario') }}"
                          aria-label="{{ __('No. de honorario') }}">
                      </div>
                      @error('numeroTrasladoHonorarioModal')
                      <div class="text-danger mt-1">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <!-- Botón Guardar -->
                  <button type="button"
                      class="btn btn-success data-submit me-sm-4 me-1 mt-5"
                      wire:click="saveHonorarioModal"
                      wire:loading.attr="disabled"
                      wire:target="saveHonorarioModal">

                      <!-- Icono + texto normal -->
                      <span wire:loading.remove wire:target="saveHonorarioModal">
                          <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Save') }}
                      </span>

                      <!-- Spinner mientras carga -->
                      <span wire:loading wire:target="saveHonorarioModal">
                          <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                          {{ __('Updating...') }}
                      </span>
                  </button>

                  <!-- Botón Cancelar -->
                  <button type="button"
                      class="btn btn-outline-secondary me-sm-4 me-1 mt-5"
                      wire:click="$set('showHonorarioModal', false)"
                      wire:loading.attr="disabled">
                      <i class="bx bx-x bx-18px me-2"></i>{{ __('Cancel') }}
                  </button>
                </div>
            </div>
        </div>
    </div>
  @endif

  @if($showGastoModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar datos de depósito</h5>
                    <button type="button" class="btn-close" wire:click="$set('showGastoModal', false)"></button>
                </div>
                <div class="modal-body">
                  <div class="row g-6">
                    <div class="col-md-6 fv-plugins-icon-container">
                      <label class="form-label" for="fechaTrasladoGastoModal">{{ __('Fecha de traslado de gasto') }}</label>
                      <div class="input-group input-group-merge has-validation">
                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                        <input type="text" id="fechaTrasladoGastoModal"
                          wire:model="fechaTrasladoGastoModal"
                          x-data="datePickerLivewire({ wireEventName: 'dateSelected' })"
                          x-init="init($el)"
                          wire:ignore
                          class="form-control date-picke @error('fechaTrasladoGastoModal') is-invalid @enderror"
                          placeholder="dd-mm-aaaa">
                      </div>
                      @error('fechaTrasladoGastoModal')
                      <div class="text-danger mt-1">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="col-md-6 fv-plugins-icon-container">
                      <label class="form-label" for="numeroTrasladoGastoModal">{{ __('No. de gasto') }}</label>
                      <div class="input-group input-group-merge has-validation">
                        <span class="input-group-text"><i class="bx bx-receipt"></i></span>
                        <input type="text" wire:model="numeroTrasladoGastoModal" name="numeroTrasladoGastoModal" id="numeroTrasladoGastoModal"
                          class="form-control @error('numeroTrasladoGastoModal') is-invalid @enderror" placeholder="{{ __('No. de gasto') }}"
                          aria-label="{{ __('No. de gasto') }}">
                      </div>
                      @error('numeroTrasladoGastoModal')
                      <div class="text-danger mt-1">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                    <!-- Botón Guardar -->
                    <button type="button"
                        class="btn btn-success data-submit me-sm-4 me-1 mt-5"
                        wire:click="saveGastoModal"
                        wire:loading.attr="disabled"
                        wire:target="saveGastoModal">

                        <!-- Icono + texto normal -->
                        <span wire:loading.remove wire:target="saveGastoModal">
                            <i class="tf-icons bx bx-save bx-18px me-2"></i>{{ __('Save') }}
                        </span>

                        <!-- Spinner mientras carga -->
                        <span wire:loading wire:target="saveGastoModal">
                            <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                            {{ __('Updating...') }}
                        </span>
                    </button>

                    <!-- Botón Cancelar -->
                    <button type="button"
                        class="btn btn-outline-secondary me-sm-4 me-1 mt-5"
                        wire:click="$set('showGastoModal', false)"
                        wire:loading.attr="disabled">
                        <i class="bx bx-x bx-18px me-2"></i>{{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
  @endif


</div>

@push('scripts')
<script>
  function printTable() {
      // Selecciona el contenido que deseas imprimir. Ajusta `#table-content` según el ID de tu tabla.
      const printContent = document.getElementById('transaction-table').innerHTML;
      const originalContent = document.body.innerHTML;

      document.body.innerHTML = printContent;
      window.print();
      document.body.innerHTML = originalContent;
  }
</script>
@endpush


@script()
<script>
  (function () {
        Livewire.on('exportReady', (dataArray) => {

          const data = Array.isArray(dataArray) ? dataArray[0] : dataArray;
          const prepareUrl = data.prepareUrl;
          const downloadBase = data.downloadBase;

          Livewire.dispatch('showLoading', [{ message: 'Generando reporte. Por favor espere...' }]);

          setTimeout(() => {
            fetch(prepareUrl)
              .then(res => {
                if (!res.ok) throw new Error('Respuesta inválida');
                return res.json();
              })
              .then(response => {
                const downloadUrl = `${downloadBase}/${response.filename}`;
                window.location.assign(downloadUrl);
                setTimeout(() => Livewire.dispatch('hideLoading'), 1000);
              })
              .catch(err => {
                console.error(err);
                Livewire.dispatch('hideLoading');
                //alert('Error al generar el archivo');
              });
          }, 100);
        });
    })();
</script>
@endscript
