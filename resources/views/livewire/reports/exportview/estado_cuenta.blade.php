<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            margin-bottom: 20px;
        }
        .logo {
            float: left;
            margin-right: 20px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .cliente-row {
            background-color: #e6f7ff !important;
            font-weight: bold;
        }
        .factura-row {
            background-color: #ffffff;
        }
        .totals-row {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($logoPath && file_exists($logoPath))
            <div class="logo">
                <img src="{{ $logoPath }}" alt="Logo" style="height: 50px;">
            </div>
        @endif
        <div class="title">{{ $title }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">No Factura</th>
                <th width="40%">Emisor</th>
                <th width="25%">Centro de costo</th>
                <th width="15%">Fecha Factura</th>
                <th width="15%">Fecha Vencimiento</th>
                <th width="10%">Moneda</th>
                <th width="15%">Tipo de Cambio</th>
                <th width="15%">Total Venta</th>
                <th width="15%">Total Descuento</th>
                <th width="15%">Total Venta Neta</th>
                <th width="15%">IVA</th>
                <th width="15%">Otros Cargos</th>
                <th width="15%">Total</th>
                <th width="15%">Total Equivalente USD</th>
                <th width="25%">Número de Proforma</th>
                <th width="35%">Deudor</th>
                <th width="25%">O.C</th>
                <th width="25%">MIGO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totales = [
                    'totalVenta' => 0,
                    'totalDescuento' => 0,
                    'totalVentaNeta' => 0,
                    'iva' => 0,
                    'otrosCargos' => 0,
                    'total' => 0,
                    'totalUSD' => 0
                ];
            @endphp

            @foreach($data as $index => $row)
                @if($row['type'] === 'CLIENTE')
                    <tr class="cliente-row">
                        <td colspan="2"><strong>CLIENTE: {{ $row['customer_name'] }}</strong></td>
                        <td colspan="3"><strong>IDENTIFICACIÓN: {{ $row['identification'] }}</strong></td>
                        <td colspan="14"></td>
                    </tr>
                @else
                    <tr class="factura-row">
                        <td class="text-left">{{ $row['id'] }}</td>
                        <td class="text-left">{{ $row['consecutivo'] }}</td>
                        <td class="text-left">{{ $row['nombreEmisor'] }}</td>
                        <td class="text-center">{{ $row['centro_costo'] }}</td>
                        <td class="text-center">{{ $row['transaction_date'] }}</td>
                        <td class="text-center">{{ $row['fecha_vencimiento'] }}</td>
                        <td class="text-center">{{ $row['moneda'] }}</td>
                        <td class="text-right">{{ number_format($row['proforma_change_type'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['totalVenta'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['totalDescuento'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['totalVentaNeta'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['iva'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['otrosCargos'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['total'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['totalUSD'], 2) }}</td>
                        <td class="text-left">{{ $row['proforma_no'] }}</td>
                        <td class="text-left">{{ $row['deudor'] }}</td>
                        <td class="text-left">{{ $row['ordenCompra'] }}</td>
                        <td class="text-left">{{ $row['migo'] }}</td>
                    </tr>
                    @php
                        $totales['totalVenta'] += $row['totalVenta'];
                        $totales['totalDescuento'] += $row['totalDescuento'];
                        $totales['totalVentaNeta'] += $row['totalVentaNeta'];
                        $totales['iva'] += $row['iva'];
                        $totales['otrosCargos'] += $row['otrosCargos'];
                        $totales['total'] += $row['total'];
                        $totales['totalUSD'] += $row['totalUSD'];
                    @endphp
                @endif
            @endforeach

            @if(count($data) > 0)
                <tr class="totals-row">
                    <td colspan="8"><strong>TOTALES GENERALES</strong></td>
                    <td class="text-right"><strong>{{ number_format($totales['totalVenta'], 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totales['totalDescuento'], 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totales['totalVentaNeta'], 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totales['iva'], 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totales['otrosCargos'], 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totales['total'], 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totales['totalUSD'], 2) }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
