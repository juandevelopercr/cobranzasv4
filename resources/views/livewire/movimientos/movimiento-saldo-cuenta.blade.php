<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Saldo de Cuentas</h5>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th width="26%">Cuenta</th>
                    <th width="9%">Saldo Sistema</th>
                    <th width="9%">Pendiente</th>
                    <th width="9%">Gastos</th>
                    <th width="9%">Honorarios</th>
                    <th width="9%">Karla</th>
                    <th width="9%">Certifondo</th>
                    <th width="9%">Colchón</th>
                    <th width="9%">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($cuentas301 as $key => $cuenta)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $cuenta['nombre_cuenta'] }}</td>

                    {{-- Columnas solo lectura --}}
                    @php
                        $lectura = [
                            'saldo_sistema',
                            'pendiente_registro',
                            'traslados_gastos.total_timbres',
                            'traslados_honorarios.total_honorarios',
                        ];
                    @endphp

                    @foreach($lectura as $field)
                        @php
                            $value = data_get($cuenta, $field, 0);
                        @endphp
                        <td class="{{ $value < 0 ? 'text-danger text-end' : 'text-end' }}">
                            {{ number_format($value, 2) }}
                        </td>
                    @endforeach

                    {{-- Columnas editables con Cleave.js --}}
                    @php
                        $editable = [
                            ['field' => 'traslados_karla', 'wire' => "cuentas301.{$key}.traslados_karla"],
                            ['field' => 'certifondo_bnfa', 'wire' => "cuentas301.{$key}.certifondo_bnfa"],
                            ['field' => 'colchon', 'wire' => "cuentas301.{$key}.colchon"],
                        ];
                    @endphp

                    @foreach($editable as $col)
                        @php
                            $value = data_get($cuenta, $col['field'], 0);
                        @endphp
                        <td class="{{ $value < 0 ? 'text-danger text-end' : 'text-end' }}">
                            <div
                                x-data="cleaveLivewire({
                                    initialValue: '{{ $value }}',
                                    wireModelName: '{{ $col['wire'] }}',
                                    postUpdate: true,
                                    decimalScale: 2
                                })"
                                x-init="init($refs.cleaveInput)"
                            >
                                <div class="input-group input-group-merge has-validation">
                                    <input x-ref="cleaveInput" class="form-control integer-mask" type="text" wire:ignore />
                                </div>
                            </div>
                        </td>
                    @endforeach

                    {{-- Total calculado --}}
                    @php
                        $total = ($cuenta['saldo_sistema'] ?? 0)
                                - ($cuenta['pendiente_registro'] ?? 0)
                                - ($cuenta['traslados_gastos']['total_timbres'] ?? 0)
                                - ($cuenta['traslados_honorarios']['total_honorarios'] ?? 0)
                                - ($cuenta['traslados_karla'] ?? 0)
                                - ($cuenta['certifondo_bnfa'] ?? 0)
                                - ($cuenta['colchon'] ?? 0);
                    @endphp
                    <td class="{{ $total < 0 ? 'text-danger text-end' : 'text-end' }}">
                        <b>{{ number_format($total, 2) }}</b>
                    </td>
                </tr>
            @endforeach
            </tbody>
          <tfoot>
              <tr>
                  <td colspan="8"></td>
                  <td><b>TOTAL COLONES</b></td>
                  <td><b>TOTAL DOLARES</b></td>
              </tr>

              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>SALDO DE CUENTAS 3-101 Y BAC CDF</b></td>
                  <td class="text-end">
                      <input type="text"
                            class="form-control text-end fw-bold {{ $totalColones301 < 0 ? 'text-danger' : '' }}"
                            readonly
                            wire:model="totalColones301"
                            style="width: 160px;">
                  </td>
                  <td class="text-end">
                      <input type="text"
                            class="form-control text-end fw-bold {{ $totalDolares301 < 0 ? 'text-danger' : '' }}"
                            readonly
                            wire:model="totalDolares301"
                            style="width: 160px;">
                  </td>
              </tr>

              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>SALDO DE LAS DEMAS CUENTAS</b></td>
                  <td class="text-end">
                      <input type="text"
                            class="form-control text-end fw-bold {{ $otrasCuentasColones < 0 ? 'text-danger' : '' }}"
                            readonly
                            wire:model="otrasCuentasColones"
                            style="width: 160px;">
                  </td>
                  <td class="text-end">
                      <input type="text"
                            class="form-control text-end fw-bold {{ $otrasCuentasDolares < 0 ? 'text-danger' : '' }}"
                            readonly
                            wire:model="otrasCuentasDolares"
                            style="width: 160px;">
                  </td>
              </tr>

              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>TOTAL DISPONIBLE</b></td>
                  <td class="text-end">
                      <input type="text"
                            class="form-control text-end fw-bold {{ $totalDisponibleColones < 0 ? 'text-danger' : '' }}"
                            readonly
                            wire:model="totalDisponibleColones"
                            style="width: 160px;">
                  </td>
                  <td class="text-end">
                      <input type="text"
                            class="form-control text-end fw-bold {{ $totalDisponibleDolares < 0 ? 'text-danger' : '' }}"
                            readonly
                            wire:model="totalDisponibleDolares"
                            style="width: 160px;">
                  </td>
              </tr>

              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>TIPO DE CAMBIO</b></td>
                  <td class="text-end">
                      <div
                          x-data="cleaveLivewire({
                              initialValue: '{{ $tipo_cambio ?? '' }}',
                              wireModelName: 'tipo_cambio',
                              postUpdate: true,
                              decimalScale: 2
                          })"
                          x-init="init($refs.cleaveInput)"
                      >
                          <div class="input-group input-group-merge has-validation">
                              <input
                                  x-ref="cleaveInput"
                                  class="form-control text-end fw-bold"
                                  type="text"
                                  style="width: 160px;"
                                  wire:ignore
                              />
                          </div>
                      </div>
                  </td>
                  <td></td>
              </tr>

              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>TOTAL GENERAL DOLARIZADO</b></td>
                  <td class="text-end">
                      <input type="text"
                            class="form-control text-end fw-bold {{ $totalDolarizado < 0 ? 'text-danger' : '' }}"
                            readonly
                            wire:model="totalDolarizado"
                            style="width: 160px;">
                  </td>
                  <td></td>
              </tr>
          </tfoot>

        </table>

        <div class="mt-3">
            <button wire:click="guardarDatos" class="btn btn-primary">Guardar</button>
            <button wire:click="exportarExcel" class="btn btn-success">Exportar a Excel</button>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success mt-2">{{ session('message') }}</div>
        @endif
    </div>
</div>

@script()
<script>
  $(document).ready(function() {
    function initCleave() {
        console.log("initCleave");
        document.querySelectorAll('.cleave-number').forEach(function(el) {
            if (!el.cleave) { // evitar reinicializar
                el.cleave = new Cleave(el, {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: '.',
                    delimiter: ',',
                    numeralDecimalScale: 2
                });
            }
        });
    }

    // Re-inicializar cada vez que Livewire actualice el DOM
    Livewire.hook('message.processed', (message, component) => {
        console.log("message.processed");
        initCleave();
    });

    Livewire.on('reinitCleaveControls', () => {
      console.log('Reinicializando controles reinitCleaveControls');
      setTimeout(() => {
        initCleave();
      }, 300); // Retraso para permitir que el DOM se estabilice
    });

    setTimeout(() => {
      initCleave();
    }, 300); // Retraso para permitir que el DOM se estabilice

});

</script>
@endscript
