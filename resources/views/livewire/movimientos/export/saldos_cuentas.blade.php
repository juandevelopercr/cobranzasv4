<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cuenta</th>
            <th>Saldo Sistema</th>
            <th>Pendiente</th>
            <th>Gastos</th>
            <th>Honorarios</th>
            <th>Karla</th>
            <th>Certifondo</th>
            <th>Colchón</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cuentas301 as $key => $cuenta)
            @php
                $totalFila = $cuenta['saldo_sistema']
                    - $cuenta['pendiente_registro']
                    - $cuenta['traslados_gastos']['total_timbres']
                    - $cuenta['traslados_honorarios']['total_honorarios']
                    - $cuenta['traslados_karla']
                    - $cuenta['certifondo_bnfa']
                    - $cuenta['colchon'];
            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $cuenta['nombre_cuenta'] }}</td>
                <td>{{ number_format($cuenta['saldo_sistema'], 2) }}</td>
                <td>{{ number_format($cuenta['pendiente_registro'], 2) }}</td>
                <td>{{ number_format($cuenta['traslados_gastos']['total_timbres'], 2) }}</td>
                <td>{{ number_format($cuenta['traslados_honorarios']['total_honorarios'], 2) }}</td>
                <td>{{ number_format($cuenta['traslados_karla'], 2) }}</td>
                <td>{{ number_format($cuenta['certifondo_bnfa'], 2) }}</td>
                <td>{{ number_format($cuenta['colchon'], 2) }}</td>
                <td>{{ number_format($totalFila, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="9"><b>Total Colones 301</b></td>
            <td>{{ number_format($totalColones301, 2) }}</td>
        </tr>
        <tr>
            <td colspan="9"><b>Total Dólares 301</b></td>
            <td>{{ number_format($totalDolares301, 2) }}</td>
        </tr>
        <tr>
            <td colspan="9"><b>Saldo de las demás cuentas Colones</b></td>
            <td>{{ number_format($otrasCuentasColones, 2) }}</td>
        </tr>
        <tr>
            <td colspan="9"><b>Saldo de las demás cuentas Dólares</b></td>
            <td>{{ number_format($otrasCuentasDolares, 2) }}</td>
        </tr>
        <tr>
            <td colspan="9"><b>Total Disponible Colones</b></td>
            <td>{{ number_format($totalDisponibleColones, 2) }}</td>
        </tr>
        <tr>
            <td colspan="9"><b>Total Disponible Dólares</b></td>
            <td>{{ number_format($totalDisponibleDolares, 2) }}</td>
        </tr>
        <tr>
            <td colspan="9"><b>Total General Dolarizado</b></td>
            <td>{{ number_format($totalDolarizado, 2) }}</td>
        </tr>
        <tr>
            <td colspan="9"><b>Tipo de Cambio</b></td>
            <td>{{ number_format($tipo_cambio, 2) }}</td>
        </tr>
    </tfoot>
</table>
