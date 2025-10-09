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
    <th><b>#</b></th>
    <th style="text-align:center;"><b>No Factura</b></th>
    <th style="text-align:center;"><b>Emisor</b></th>
    <th style="text-align:center;"><b>Centro de costo</b></th>
    <th style="text-align:center;"><b>Fecha Factura</b></th>
    <th style="text-align:center;"><b>Fecha Vencimiento</b></th>
    <th style="text-align:center;"><b>Moneda</b></th>
    <th style="text-align:center;"><b>Tipo de Cambio</b></th>
    <th style="text-align:center;"><b>Total Venta</b></th>
    <th style="text-align:center;"><b>Total Descuento</b></th>
    <th style="text-align:center;"><b>Total Venta Neta</b></th>
    <th style="text-align:center;"><b>IVA</b></th>
    <th style="text-align:center;"><b>Otros Cargos</b></th>
    <th style="text-align:center;"><b>Total</b></th>
    <th style="text-align:center;"><b>Total Equivalente CRC</b></th>
    <th style="text-align:center;"><b>Número de Proforma</b></th>
    <th style="text-align:center;"><b>Deudor</b></th>
    <th style="text-align:center;"><b>O.C</b></th>
    <th style="text-align:center;"><b>MIGO</b></th>
</tr>

@php
    $totalesFacturaCRC = 0;
    $totalesAbonoCRC = 0;
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
    <td>{{ $factura->proforma_change_type }}</td>
    <td>{{ $factura->totalVenta }}</td>
    <td>{{ $factura->totalDiscount }}</td>
    <td>{{ $factura->totalVenta - $factura->totalDiscount }}</td>
    <td>{{ $factura->totalTax }}</td>
    <td>{{ $factura->totalOtrosCargos }}</td>
    <td>{{ $factura->totalComprobante }}</td>
    <td>{{ $factura->currency_id == 16 ? $factura->totalComprobante : ($factura->totalComprobante * ($factura->proforma_change_type ?: 1)) }}</td>
    <td>{{ $factura->proforma_no }}</td>
    <td>{{ $factura->caso ? $factura->caso->deudor : '' }}</td>
    <td>{{ $factura->oc }}</td>
    <td>{{ $factura->migo }}</td>
</tr>

@php
    if ($factura->currency_id == 1){
      $totalesFacturaCRC += $factura->totalComprobante * $factura->proforma_change_type;
      $totalesAbonoCRC += $factura->payments->sum('amount') * $factura->proforma_change_type;
    }
    else{
      $totalesFacturaCRC += $factura->totalComprobante;
      $totalesAbonoCRC += $factura->payments->sum('amount');
    }
@endphp
@endforeach

<tr>
    <td colspan="14" style="text-align:right; font-weight:bold;"><b>TOTAL FACTURA(S):</b></td>
    <td style="text-align:right; font-weight:bold;">{{ $totalesFacturaCRC }}</td>
</tr>

<tr>
    <td colspan="14" style="text-align:right; font-weight:bold;"><b>TOTAL ABONO(S):</b></td>
    <td style="text-align:right; font-weight:bold;">{{ $totalesAbonoCRC }}</td>
</tr>

<tr>
    <td colspan="14" style="text-align:right; font-weight:bold; color:red;"><b>TOTAL PENDIENTE:</b></td>
    <td style="text-align:right; font-weight:bold; color:red;">{{ $totalesFacturaCRC - $totalesAbonoCRC }}</td>
</tr>
@endforeach
