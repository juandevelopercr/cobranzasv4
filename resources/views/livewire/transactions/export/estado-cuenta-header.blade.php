<table width="100%" cellpading="10">
	<tr>
		<td width="33%" valign="middle">
        <img src="{{ $logo }}" alt="Logo" style="max-height: 80px; width: auto;">
        </td>
        <td width="33%" align="center" valign="top">
          <br />
          <span style="font-size: 13px;"><strong>{{ $transaction->contact->name ?? $transaction->customer_name }}</strong></span><br />
        </td>
        <td align="right" style="padding-right:5;" width="33%">
          <br />
			    <span style="font-weight: normal; font-size: 12px;">Fecha:</span><span style="font-size: 12px;"> <?= date('d-m-Y h:i a'); ?></span><br /><br />
        </td>
    </tr>
    <tr>
        <td align="right" colspan="3">&nbsp;

        </td>
    </tr>
</table>
<table width="100%" cellpadding="2" cellspacing="2">
<tr>
    <td align="center">
        <span style="font-size: 10px; font-weight: bold;">ESTADO DE CUENTA</span>
    </td>
</tr>
</table>
<br />
<table class="table table-bordered" border="1" cellspacing="0" cellpadding="5" width="100%">
	<thead>
		<tr>
        <th align="center" width="18%" colspan="2" style="font-size: 10px; font-weight: bold; text-align:center">
            No Factura
        </th>
        <th align="center" width="18%" style="font-size: 10px; font-weight: bold; text-align:center">
            Emisor
        </th>
        <th align="center" width="8%" style="font-size: 10px; font-weight: bold; text-align:center">
            Fecha Vencimiento
        </th>
        <th align="center" width="8%" style="font-size: 10px; font-weight: bold; text-align:center">
            Moneda
        </th>
        <th align="center" width="8%" style="font-size: 10px; font-weight: bold; text-align:center">
            Tipo de Cambio
        </th>
        <th align="center" width="20%" style="font-size: 10px; font-weight: bold; text-align:center">
            Monto CRC
        </th>
        <th align="center" width="20%" style="font-size: 10px; font-weight: bold; text-align:center">
            Monto USD
        </th>
    </tr>
	</thead>
	<tbody>
