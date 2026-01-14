<div class="card">
  <div class="card-body">
    <h5 class="mb-3">Saldo de Cuentas</h5>
    <div x-data="{
          tipoCambioLocal: parseFloat('{{ $tipo_cambio }}') || 1,

          cuentas: [
              @foreach($cuentas301 as $cuenta)
              {
                  saldo: parseFloat('{{ $cuenta['saldo_sistema'] ?? 0 }}'),
                  pendiente: parseFloat('{{ $cuenta['pendiente_registro'] ?? 0 }}'),
                  gastos: parseFloat('{{ $cuenta['traslados_gastos']['total_timbres'] ?? 0 }}'),
                  honorarios: parseFloat('{{ $cuenta['traslados_honorarios']['total_honorarios'] ?? 0 }}'),
                  karla: parseFloat('{{ $cuenta['traslados_karla'] ?? 0 }}'),
                  certifondo: parseFloat('{{ $cuenta['certifondo_bnfa'] ?? 0 }}'),
                  colchon: parseFloat('{{ $cuenta['colchon'] ?? 0 }}'),
                  nombre: '{{ $cuenta['nombre_cuenta'] }}',
                  moneda_id: {{ $cuenta['moneda_id'] }}
              },
              @endforeach
          ],

          otrasCuentasColones: parseFloat('{{ $otrasCuentasColones }}'),
          otrasCuentasDolares: parseFloat('{{ $otrasCuentasDolares }}'),

          formatNumber(value) {
              let number = parseFloat(value) || 0;
              return number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          },

          parseInput(value) {
              // permite solo números y un punto decimal
              let clean = value.replace(/[^0-9.]/g, '');
              const parts = clean.split('.');
              if(parts.length > 2) clean = parts[0] + '.' + parts.slice(1).join('');
              return parseFloat(clean) || 0;
          },

          totalFila(c) {
              return c.saldo - c.pendiente - c.gastos - c.honorarios - c.karla - c.certifondo - c.colchon;
          },

          totalColones() {
              let total = 0;
              this.cuentas.forEach(c => { if(c.moneda_id == 16) total += this.totalFila(c); });
              return total + this.otrasCuentasColones;
          },

          totalDolares() {
              let total = 0;
              this.cuentas.forEach(c => { if(c.moneda_id == 1) total += this.totalFila(c); });
              return total + this.otrasCuentasDolares;
          },

          totalDolarizado() {
              return this.totalDolares() + (this.totalColones() / this.tipoCambioLocal);
          },

          updateField(index, field, event) {
              this.cuentas[index][field] = this.parseInput(event.target.value);
          }
      }">

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
              <template x-for="(cuenta, index) in cuentas" :key="index">
                  <tr>
                      <td x-text="index + 1"></td>
                      <td x-text="cuenta.nombre"></td>

                      <td>
                        <input type="text" class="form-control text-end" style="width:140px"
                          x-model="cuenta.saldo"
                          x-on:input="cuenta.saldo = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(cuenta.saldo)"
                        >
                      </td>
                      <td>
                        <input type="text" class="form-control text-end" style="width:140px"
                          x-model="cuenta.pendiente"
                          x-on:input="cuenta.pendiente = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(cuenta.pendiente)"
                        >
                      </td>
                      <td>
                        <input type="text" class="form-control text-end" style="width:140px"
                          x-model="cuenta.gastos"
                          x-on:input="cuenta.gastos = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(cuenta.gastos)"
                        >
                      </td>
                      <td>
                        <input type="text" class="form-control text-end" style="width:140px"
                          x-model="cuenta.honorarios"
                          x-on:input="cuenta.honorarios = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(cuenta.honorarios)"
                        >
                      </td>
                      <td>
                        <input type="text" class="form-control text-end" style="width:140px"
                          x-model="cuenta.karla"
                          x-on:input="cuenta.karla = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(cuenta.karla)"
                        >
                      </td>
                      <td>
                        <input type="text" class="form-control text-end" style="width:140px"
                          x-model="cuenta.certifondo"
                          x-on:input="cuenta.certifondo = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(cuenta.certifondo)"
                        >
                      </td>
                      <td>
                        <input type="text" class="form-control text-end" style="width:140px"
                          x-model="cuenta.colchon"
                          x-on:input="cuenta.colchon = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(cuenta.colchon)"
                        >
                      </td>

                      <td :class="totalFila(cuenta) < 0 ? 'text-danger text-end' : 'text-end'">
                          <b x-text="formatNumber(totalFila(cuenta))"></b>
                      </td>
                  </tr>
              </template>
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
                  <td class="text-end"><b x-text="formatNumber(totalColones())"></b></td>
                  <td class="text-end"><b x-text="formatNumber(totalDolares())"></b></td>
              </tr>
              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>SALDO DE LAS DEMAS CUENTAS</b></td>
                  <td class="text-end"><b x-text="formatNumber(otrasCuentasColones)"></b></td>
                  <td class="text-end"><b x-text="formatNumber(otrasCuentasDolares)"></b></td>
              </tr>
              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>TOTAL DISPONIBLE</b></td>
                  <td class="text-end"><b x-text="formatNumber(totalColones() + otrasCuentasColones)"></b></td>
                  <td class="text-end"><b x-text="formatNumber(totalDolares() + otrasCuentasDolares)"></b></td>
              </tr>
              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>TIPO DE CAMBIO</b></td>
                  <td class="text-end">
                      <input type="text" class="form-control text-end fw-bold"
                          x-model="tipoCambioLocal"
                          x-on:input="tipoCambioLocal = parseInput($el.value)"
                          x-on:blur="$el.value = formatNumber(tipoCambioLocal)"
                          style="width:160px;"
                      >
                  </td>
                  <td></td>
              </tr>
              <tr>
                  <td colspan="6"></td>
                  <td class="text-end" colspan="2"><b>TOTAL GENERAL DOLARIZADO</b></td>
                  <td class="text-end"><b x-text="formatNumber(totalDolarizado())"></b></td>
                  <td></td>
              </tr>
          </tfoot>
      </table>
</div>
