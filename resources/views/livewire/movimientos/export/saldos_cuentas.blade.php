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
            @php
                // Función para limpiar comas y convertir a float
                $toFloat = fn($val) => (float) str_replace(',', '', $val);

                $saldo = $toFloat($cuenta['saldo_sistema'] ?? 0);
                $pendiente = $toFloat($cuenta['pendiente_registro'] ?? 0);
                $gastos = $toFloat($cuenta['traslados_gastos']['total_timbres'] ?? 0);
                $honorarios = $toFloat($cuenta['traslados_honorarios']['total_honorarios'] ?? 0);
                $karla = $toFloat($cuenta['traslados_karla'] ?? 0);
                $certifondo = $toFloat($cuenta['certifondo_bnfa'] ?? 0);
                $colchon = $toFloat($cuenta['colchon'] ?? 0);

                $total = $saldo - $pendiente - $gastos - $honorarios - $karla - $certifondo - $colchon;
            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $cuenta['nombre_cuenta'] }}</td>
                <td class="{{ $saldo < 0 ? 'text-danger text-end' : 'text-end' }}">{{ number_format($saldo, 2) }}</td>
                <td class="{{ $pendiente < 0 ? 'text-danger text-end' : 'text-end' }}">{{ number_format($pendiente, 2) }}</td>
                <td class="{{ $gastos < 0 ? 'text-danger text-end' : 'text-end' }}">{{ number_format($gastos, 2) }}</td>
                <td class="{{ $honorarios < 0 ? 'text-danger text-end' : 'text-end' }}">{{ number_format($honorarios, 2) }}</td>
                <td class="{{ $karla < 0 ? 'text-danger text-end' : 'text-end' }}">{{ number_format($karla, 2) }}</td>
                <td class="{{ $certifondo < 0 ? 'text-danger text-end' : 'text-end' }}">{{ number_format($certifondo, 2) }}</td>
                <td class="{{ $colchon < 0 ? 'text-danger text-end' : 'text-end' }}">{{ number_format($colchon, 2) }}</td>
                <td class="{{ $total < 0 ? 'text-danger text-end' : 'text-end' }}"><b>{{ number_format($total, 2) }}</b></td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        @php
            $clean = fn($val) => number_format((float) str_replace(',', '', $val), 2);
        @endphp
        <tr>
            <td colspan="8"></td>
            <td><b>TOTAL COLONES</b></td>
            <td><b>TOTAL DOLARES</b></td>
        </tr>

        <tr>
            <td colspan="6"></td>
            <td class="text-end" colspan="2"><b>SALDO DE CUENTAS 3-101 Y BAC CDF</b></td>
            <td class="{{ $totalColones301 < 0 ? 'text-danger text-end' : 'text-end' }}">{{ $clean($totalColones301) }}</td>
            <td class="{{ $totalDolares301 < 0 ? 'text-danger text-end' : 'text-end' }}">{{ $clean($totalDolares301) }}</td>
        </tr>

        <tr>
            <td colspan="6"></td>
            <td class="text-end" colspan="2"><b>SALDO DE LAS DEMAS CUENTAS</b></td>
            <td class="{{ $otrasCuentasColones < 0 ? 'text-danger text-end' : 'text-end' }}">{{ $clean($otrasCuentasColones) }}</td>
            <td class="{{ $otrasCuentasDolares < 0 ? 'text-danger text-end' : 'text-end' }}">{{ $clean($otrasCuentasDolares) }}</td>
        </tr>

        <tr>
            <td colspan="6"></td>
            <td class="text-end" colspan="2"><b>TOTAL DISPONIBLE</b></td>
            <td class="{{ $totalDisponibleColones < 0 ? 'text-danger text-end' : 'text-end' }}">{{ $clean($totalDisponibleColones) }}</td>
            <td class="{{ $totalDisponibleDolares < 0 ? 'text-danger text-end' : 'text-end' }}">{{ $clean($totalDisponibleDolares) }}</td>
        </tr>

        <tr>
            <td colspan="6"></td>
            <td class="text-end" colspan="2"><b>TIPO DE CAMBIO</b></td>
            <td class="text-end">{{ $clean($tipo_cambio) }}</td>
            <td></td>
        </tr>

        <tr>
            <td colspan="6"></td>
            <td class="text-end" colspan="2"><b>TOTAL GENERAL DOLARIZADO</b></td>
            <td class="{{ $totalDolarizado < 0 ? 'text-danger text-end' : 'text-end' }}">{{ $clean($totalDolarizado) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
