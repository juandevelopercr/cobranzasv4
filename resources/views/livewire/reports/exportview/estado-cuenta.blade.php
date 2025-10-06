<tr>
  <th colspan="20" style="font-size:16px; text-align:left">
        <strong>{{ $title }}</strong>
    </th>
</tr>
@foreach($clientes as $cliente)
<tr>
    <th colspan="20" style="font-size:12px; text-align:left">
        <strong>Cliente: {{ $cliente->name }} Teléfono: {{ $cliente->phone }} Correo Electrónico: {{ $cliente->email }}</strong>
    </th>
</tr>

<tr>
    <th>#</th>
    <th>No Factura</th>
    <th>Emisor</th>
    <th>Centro de costo</th>
    <th>Fecha Factura</th>
    <th>Fecha Vencimiento</th>
    <th>Moneda</th>
    <th>Tipo de Cambio</th>
    <th>Total Venta</th>
    <th>Total Descuento</th>
    <th>Total Venta Neta</th>
    <th>IVA</th>
    <th>Otros Cargos</th>
    <th>Total</th>
    <th>Total Equivalente</th>
    <th>Número de Proforma</th>
    <th>Deudor</th>
    <th>O.C</th>
    <th>MIGO</th>
</tr>

@php
    $totalesFactura = 0;
    $totalesAbono = 0;
@endphp

@foreach($cliente->transactionsEstadoCuenta as $factura)
<tr style="background-color:#D9F2D9">
    <td>{{ $factura->id }}</td>
    <td>{{ $factura->consecutivo }}</td>
    <td>{{ $factura->location->name }}</td>
    <td>
        @php
            $str = '';
            foreach ($factura->commisions as $cc) {
                $codigoContable = '-';

                if (!empty($cc->centroCosto->codigo) && !empty($factura->codigoContable->codigo)) {
                    $codigoContable = str_replace('XX', $cc->centroCosto->codigo, $factura->codigoContable->codigo);
                    $codigoContable = str_replace('YYY', $factura->location->code ?? '', $codigoContable);
                }

                $str .= !empty($str) ? ', ' . $codigoContable : $codigoContable;
            }
        @endphp
        {{ $str }}
    </td>
    <td>{{ $factura->transaction_date ? \Carbon\Carbon::parse($factura->transaction_date)->format('d-m-Y') : '' }}</td>
    <td>
      @php
          if ($factura->pay_term_number > 0) {
              $fechaVencimiento = \Carbon\Carbon::parse($factura->fecha_vencimiento)
                                  ->addDays($factura->pay_term_number)
                                  ->format('d/m/Y');
          } else {
              $fechaVencimiento = \Carbon\Carbon::parse($factura->fecha_vencimiento)
                                  ->format('d/m/Y');
          }
      @endphp
      {{ $fechaVencimiento }}

    </td>
    <td>{{ $factura->currency->code }}</td>
    <td>{{ number_format($factura->proforma_change_type, 2, '.', ',') }}</td>
    <td>{{ number_format($factura->totalVenta, 2, '.', ',') }}</td>
    <td>{{ number_format($factura->totalDiscount, 2, '.', ',') }}</td>
    <td>{{ number_format($factura->totalVenta - $factura->totalDiscount, 2, '.', ',') }}</td>
    <td>{{ number_format($factura->totalTax, 2, '.', ',') }}</td>
    <td>{{ number_format($factura->totalOtrosCargos, 2, '.', ',') }}</td>
    <td>{{ number_format($factura->totalComprobante, 2, '.', ',') }}</td>
    <td>{{ number_format($factura->currency_id == 1 ? $factura->totalComprobante : ($factura->totalComprobante / ($factura->proforma_change_type ?: 1)), 2, '.', ',') }}</td>
    <td>{{ $factura->proforma_no }}</td>
    <td>{{ $factura->caso ? $factura->caso->deudor : '' }}</td>
    <td>{{ $factura->oc }}</td>
    <td>{{ $factura->migo }}</td>
</tr>

@php
    $totalesFactura += $factura->totalComprobante;
    $totalesAbono += $factura->payments->sum('amount');
@endphp
@endforeach

<tr style="font-weight:bold; background-color:#f0f0f0">
    <td colspan="12" style="text-align:right"><b>TOTAL FACTURA(S):</b></td>
    <td>{{ number_format($totalesFactura, 2, '.', ',') }}</td>
</tr>

<tr style="font-weight:bold; background-color:#f0f0f0">
    <td colspan="12" style="text-align:right"><b>TOTAL ABONO(S):</b></td>
    <td>{{ number_format($totalesAbono, 2, '.', ',') }}</td>
</tr>

<tr style="font-weight:bold; background-color:#f0f0f0">
    <td colspan="12" style="text-align:right"><b>TOTAL PENDIENTE:</b></td>
    <td>{{ number_format($totalesFactura - $totalesAbono, 2, '.', ',') }}</td>
</tr>
@endforeach
