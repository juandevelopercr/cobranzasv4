<div>
  <div x-data="{
        tipoCambioLocal: '{{ $tipo_cambio }}',
        totalDolarizadoLocal: '{{ number_format($totalDolarizado, 2) }}',

        // Totales footer que se van a actualizar
        totalColones301Local: '{{ number_format($totalColones301, 2) }}',
        totalDolares301Local: '{{ number_format($totalDolares301, 2) }}',
        otrasCuentasColonesLocal: '{{ number_format($otrasCuentasColones, 2) }}',
        otrasCuentasDolaresLocal: '{{ number_format($otrasCuentasDolares, 2) }}',
        totalDisponibleColonesLocal: '{{ number_format($totalDisponibleColones, 2) }}',
        totalDisponibleDolaresLocal: '{{ number_format($totalDisponibleDolares, 2) }}',

        formatNumber(value) {
            let number = parseFloat(value.toString().replace(/,/g, ''));
            if (isNaN(number)) number = 0;
            return number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        parseInput(value) {
            return value.toString().replace(/,/g, '');
        },

        updateWire(localVar, fieldPath, index) {
            $wire.set(`cuentas301.${index}.${fieldPath}`, parseFloat(this[localVar]) || 0);
        },

        getTextClass(value) {
            return parseFloat(value) < 0 ? 'text-danger text-end' : 'text-end';
        },

        calcularTotalDolarizado() {
            let totalColones = 0;
            @foreach($cuentas301 as $key => $cuenta)
                totalColones += parseFloat('{{ $cuenta['saldo_sistema'] ?? 0 }}')
                                - parseFloat('{{ $cuenta['pendiente_registro'] ?? 0 }}')
                                - parseFloat('{{ $cuenta['traslados_gastos']['total_timbres'] ?? 0 }}')
                                - parseFloat('{{ $cuenta['traslados_honorarios']['total_honorarios'] ?? 0 }}')
                                - parseFloat('{{ $cuenta['traslados_karla'] ?? 0 }}')
                                - parseFloat('{{ $cuenta['certifondo_bnfa'] ?? 0 }}')
                                - parseFloat('{{ $cuenta['colchon'] ?? 0 }}');
            @endforeach

            totalColones += parseFloat('{{ $otrasCuentasColones }}');

            return totalColones / (parseFloat(this.tipoCambioLocal.replace(/,/g,'')) || 1);
        },

        init() {
            // Inicializar total dolarizado
            this.totalDolarizadoLocal = this.formatNumber(this.calcularTotalDolarizado());
        }
    }" x-init="init()">
    <h3 class="mb-3">Saldo de Cuentas</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th width="20%">Cuenta</th>
                <th width="10%">Saldo Sistema</th>
                <th width="10%">Pendiente</th>
                <th width="10%">Gastos</th>
                <th width="10%">Honorarios</th>
                <th width="10%">Karla</th>
                <th width="10%">Certifondo</th>
                <th width="10%">Colchón</th>
                <th width="10%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuentas301 as $key => $cuenta)
                <tr x-data="{
                    localSaldo: '{{ number_format($cuenta['saldo_sistema'] ?? 0, 2) }}',
                    localPendiente: '{{ number_format($cuenta['pendiente_registro'] ?? 0, 2) }}',
                    localGastos: '{{ number_format($cuenta['traslados_gastos']['total_timbres'] ?? 0, 2) }}',
                    localHonorarios: '{{ number_format($cuenta['traslados_honorarios']['total_honorarios'] ?? 0, 2) }}',
                    localKarla: '{{ number_format($cuenta['traslados_karla'] ?? 0, 2) }}',
                    localCertifondo: '{{ number_format($cuenta['certifondo_bnfa'] ?? 0, 2) }}',
                    localColchon: '{{ number_format($cuenta['colchon'] ?? 0, 2) }}'
                }">
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $cuenta['nombre_cuenta'] }}</td>

                    @foreach([
                        ['field' => 'saldo_sistema', 'local' => 'localSaldo'],
                        ['field' => 'pendiente_registro', 'local' => 'localPendiente'],
                        ['field' => 'traslados_gastos.total_timbres', 'local' => 'localGastos'],
                        ['field' => 'traslados_honorarios.total_honorarios', 'local' => 'localHonorarios'],
                        ['field' => 'traslados_karla', 'local' => 'localKarla'],
                        ['field' => 'certifondo_bnfa', 'local' => 'localCertifondo'],
                        ['field' => 'colchon', 'local' => 'localColchon'],
                    ] as $col)
                      <td :class="getTextClass({{ $col['local'] }})">
                          <input type="text" class="form-control text-end" style="width: 150px;"
                              x-model="{{ $col['local'] }}"
                              x-on:input="
                                  {{ $col['local'] }} = $el.value.replace(/,/g,'');
                                  updateWire('{{ $col['local'] }}','{{ $col['field'] }}',{{ $key }});
                                  $el.value = formatNumber({{ $col['local'] }});
                                  totalDolarizadoLocal = formatNumber(calcularTotalDolarizado());
                              "
                              x-on:blur="
                                  {{ $col['local'] }} = $el.value.replace(/,/g,'');
                                  updateWire('{{ $col['local'] }}','{{ $col['field'] }}',{{ $key }});
                                  $el.value = formatNumber({{ $col['local'] }});
                                  totalDolarizadoLocal = formatNumber(calcularTotalDolarizado());
                              "
                          >
                      </td>
                    @endforeach

                    <td :class="getTextClass({{ $cuenta['saldo_sistema'] - $cuenta['pendiente_registro'] - $cuenta['traslados_gastos']['total_timbres'] - $cuenta['traslados_honorarios']['total_honorarios'] - $cuenta['traslados_karla'] - $cuenta['certifondo_bnfa'] - $cuenta['colchon'] }})">
                        <b x-text="formatNumber({{ $cuenta['saldo_sistema'] - $cuenta['pendiente_registro'] - $cuenta['traslados_gastos']['total_timbres'] - $cuenta['traslados_honorarios']['total_honorarios'] - $cuenta['traslados_karla'] - $cuenta['certifondo_bnfa'] - $cuenta['colchon'] }})"></b>
                    </td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <!-- TOTAL COLONES Y DÓLARES -->
            <tr>
                <td colspan="7"></td>
                <td class="text-end"><b>TOTAL COLONES</b></td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        readonly
                        value="{{ number_format($totalColones301, 2) }}"
                        :class="parseFloat('{{ $totalColones301 }}') < 0 ? 'text-danger' : ''"
                        style="width: 160px;">
                </td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        readonly
                        value="{{ number_format($totalDolares301, 2) }}"
                        :class="parseFloat('{{ $totalDolares301 }}') < 0 ? 'text-danger' : ''"
                        style="width: 160px;">
                </td>
            </tr>

            <!-- SALDO DE LAS DEMÁS CUENTAS -->
            <tr>
                <td colspan="7"></td>
                <td class="text-end"><b>SALDO DE LAS DEMAS CUENTAS</b></td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        readonly
                        value="{{ number_format($otrasCuentasColones, 2) }}"
                        :class="parseFloat('{{ $otrasCuentasColones }}') < 0 ? 'text-danger' : ''"
                        style="width: 160px;">
                </td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        readonly
                        value="{{ number_format($otrasCuentasDolares, 2) }}"
                        :class="parseFloat('{{ $otrasCuentasDolares }}') < 0 ? 'text-danger' : ''"
                        style="width: 160px;">
                </td>
            </tr>

            <!-- TOTAL DISPONIBLE -->
            <tr>
                <td colspan="7"></td>
                <td class="text-end"><b>TOTAL DISPONIBLE</b></td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        readonly
                        value="{{ number_format($totalDisponibleColones, 2) }}"
                        :class="parseFloat('{{ $totalDisponibleColones }}') < 0 ? 'text-danger' : ''"
                        style="width: 160px;">
                </td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        readonly
                        value="{{ number_format($totalDisponibleDolares, 2) }}"
                        :class="parseFloat('{{ $totalDisponibleDolares }}') < 0 ? 'text-danger' : ''"
                        style="width: 160px;">
                </td>
            </tr>

            <!-- TIPO DE CAMBIO -->
            <tr>
                <td colspan="7"></td>
                <td class="text-end"><b>TIPO DE CAMBIO</b></td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        x-model="tipoCambioLocal"
                        x-on:input="
                            $el.value = $el.value.replace(/[^0-9.-]/g, '');
                            tipoCambioLocal = $el.value;
                            $wire.set('tipo_cambio', parseFloat(tipoCambioLocal) || 0);
                            totalDolarizadoLocal = formatNumber(calcularTotalDolarizado());
                        "
                        x-on:blur="
                            tipoCambioLocal = formatNumber(tipoCambioLocal);
                            $wire.set('tipo_cambio', parseFloat(tipoCambioLocal.replace(/,/g,'')) || 0);
                            totalDolarizadoLocal = formatNumber(calcularTotalDolarizado());
                            $el.value = tipoCambioLocal;
                        "
                        style="width: 160px;"
                    >
                </td>
                <td></td>
            </tr>

            <!-- TOTAL GENERAL DOLARIZADO -->
            <tr>
                <td colspan="7"></td>
                <td class="text-end"><b>TOTAL GENERAL DOLARIZADO</b></td>
                <td class="text-end">
                    <input type="text" class="form-control text-end fw-bold"
                        readonly
                        x-model="totalDolarizadoLocal"
                        :class="parseFloat(totalDolarizadoLocal.replace(/,/g,'')) < 0 ? 'text-danger' : ''"
                        style="width: 160px;"
                    >
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
