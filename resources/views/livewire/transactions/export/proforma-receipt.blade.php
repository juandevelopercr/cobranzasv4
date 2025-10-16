<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
  <!-- Meta Tags -->
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="SoftwareSolutions">
  <!-- Site Title -->
  <title>{{ $titleFrom }}</title>
  <link rel="stylesheet" href="{{ public_path('css/grey-invoice.css') }}" type="text/css">
</head>

<body>
  @if(!empty($watermark)) <!-- Variable que debes pasar desde Laravel -->
  <div class="watermark">{{ $watermark }}</div>
  @endif
  <div class="tm_container">
    <div class="tm_invoice_wrap">
      <div class="tm_invoice tm_style1 tm_type1" id="tm_download_section">
        <div class="tm_invoice_in">
          <div class="tm_invoice_head tm_top_head tm_mb15 tm_align_center">
            <div class="tm_invoice_left">
              <div class="tm_logo">
                <img src="{{ $logo }}" alt="Logo">
              </div>
            </div>
            <div class="tm_invoice_right tm_text_right tm_mobile_hide">
              <div class="tm_f24 tm_text_uppercase tm_white_color mt-5">
                {{ $title }}<br>
                <span class="tm_f12 tm_invoice_number">
                  <b>No: {{ $consecutivo }}</b>
                </span>
              </div>
            </div>
            <div class="tm_shape_bg tm_accent_bg tm_mobile_hide"></div>
          </div>
          <div class="tm_invoice_info tm_mb25">
            <div class="tm_card_note tm_mobile_hide"><b class="tm_primary_color"></b></div>
            <div class="tm_invoice_info_list tm_white_color">
              <p class="tm_invoice_date tm_m0">
                <b>
                  Fecha:
                  {{ \Carbon\Carbon::parse($transaction->transaction_date)
                  ->locale('es')
                  ->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </b>
              </p>
            </div>
            <div class="tm_invoice_seperator tm_accent_bg"></div>
          </div>
          <div class="tm_invoice_head tm_mb10">
            <div class="tm_invoice_left">
              <p class="tm_mb2"><b class="tm_primary_color">{{ $titleFrom }} Para:</b></p>
              <p>
                <b class="tm_primary_color">Nombre:</b> {{ $transaction->customer_name }} <br>
                <b class="tm_primary_color">Email:</b> {{ $transaction->customer_email }} <br>
                @if (!empty($email_cc))
                <b class="tm_primary_color">Email CC:</b> {!! nl2br($email_cc) !!}<br>
                @endif
              </p>
            </div>
            <div class="tm_invoice_right tm_text_right">
              <p class="tm_mb2"><b class="tm_primary_color">Pagar a:</b></p>
              <p>
                {{-- @if(!is_null($transaction->location) && $type == 'HONORARIO' && $transaction->bank_id == $bankSanJose) --}}
                @if(!is_null($transaction->location) && $type == 'HONORARIO')
                <b class="tm_primary_color">{{ strtoupper($transaction->location->name) }}</b><br>
                <b class="tm_primary_color">{{ $transaction->location->identification }}</b>
                <br>
                @endif
                Paseo Colón, Torre Mercedes  Piso 7<br>
                San José,San José,Merced.<br>
                <a href="/cdn-cgi/l/email-protection" class="__cf_email__"
                  data-cfemail="3054555d5f70575d51595c1e535f5d">cccfacturas@cobranzas.cr</a>
              </p>
            </div>
          </div>
          @if ($transaction->tipo_facturacion == \App\Models\Transaction::INDIVIDUAL && $transaction->caso)
          @php
            $demendado = ($transaction->bank_id == \App\Models\Bank::DAVIVIENDA) ? $transaction->caso->pnombre_apellidos_deudor : $transaction->caso->pnombre_demandado;
            $numero_operacion = $transaction->bank_id == \App\Models\Bank::DAVIVIENDA ? $transaction->caso->pnumero_operacion2: $transaction->caso->pnumero_operacion1;

            $tipo_proceso = $transaction->caso->proceso->nombre;
            $numero_expediente = $transaction->caso->pnumero_expediente_judicial;

            $producto = $transaction->caso->producto->nombre;
          @endphp
          <div class="tm_table tm_style1">
              <div class="tm_table_responsive">
                <table>
                  <thead>
                    <tr>
                      <th class="tm_mb2 tm_text_center" colspan="2">Datos del Proceso</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($caso)
                    <tr>
                      <td class="tm_width_1" valign="top">
                        <b class="tm_primary_color">Nombre:</b> {{ $demendado }}<br>
                        <b class="tm_primary_color">O.P:</b> {{ $numero_operacion }}<br>
                        <b class="tm_primary_color">EXP.:</b> {{ $numero_expediente }}<br>
                      </td>
                      <td class="tm_width_1" valign="top">
                        <b class="tm_primary_color">Tipo de Proces:</b> {{ $tipo_proceso }}<br>
                        <b class="tm_primary_color">Producto:</b> {{ $producto }}<br>
                      </td>
                    </tr>
                    @endif
                  </tbody>
                </table>
              </div>
          </div>
          @endif

          @if ($transaction->department)
          <div class="tm_table tm_style1">
            <div class="tm_table_responsive">
              <table>
                <tbody>
                  <tr>
                    <td class="tm_width_1" valign="top">
                      <b class="tm_primary_color">Dirigido a Departamento:</b> {{ $transaction->department ? $transaction->department->name: '-' }}<br>
                    </td>
                    <td class="tm_width_1" valign="top">
                      <b class="tm_primary_color">Contacto:</b> {{ $transaction->contacto_banco ? $transaction->contacto_banco : '-' }}<br>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <br>
          @endif


          <div class="tm_table tm_style1">
            <div class="">
              <div class="tm_table_responsive">
                <table>
                  <thead>
                    <tr class="tm_accent_bg">
                      <th class="tm_width_4 tm_semi_bold tm_white_color">Asunto / Descripción</th>
                      @if ($transaction->tipo_facturacion == \App\Models\Transaction::MASIVA)
                        <th class="tm_width_3 tm_semi_bold tm_white_color">Detalle del Caso</th>
                      @endif
                      <th class="tm_width_2 tm_semi_bold tm_white_color">Precio</th>
                      <th class="tm_width_1 tm_semi_bold tm_white_color">Cantidad</th>
                      <th class="tm_width_3 tm_semi_bold tm_white_color tm_text_right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                    $montoTotal = 0;
                    $totalTimbres = 0;
                    $totalHonorarios = 0;
                    $cargoAdicional = 0;

                    $str = $transaction->nombre_caso.'&nbsp;';
                    if ($transaction->oc)
                    $str .= 'OC: '.$transaction->oc.'&nbsp;';
                    if ($transaction->migo)
                    $str .= 'MIGO: '.$transaction->migo.'&nbsp;';
                    if ($transaction->or)
                    $str .= 'OR: '.$transaction->or.'&nbsp;';
                    if ($transaction->gln)
                    $str .= 'GLN: '.$transaction->gln.'&nbsp;';
                    if ($transaction->prebill)
                    $str .= $transaction->prebill.'&nbsp;';
                    if ($transaction->detalle_adicional)
                    $str .= $transaction->detalle_adicional.'&nbsp;';
                    @endphp

                    @if ($transaction->nombre_caso || $transaction->oc || $transaction->migo || $transaction->or ||
                    $transaction->gln || $transaction->prebill || $transaction->detalle_adicional)
                    <tr>
                      <td class="tm_width_4">{!! html_entity_decode($str) !!}</td>
                      @if ($transaction->tipo_facturacion == \App\Models\Transaction::MASIVA)
                        <td class="tm_width_3"></td>
                      @endif
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right"></td>
                    </tr>
                    @endif

                    @foreach ($transaction_lines as $index => $line)
                    @php
                    $monto_sin_descuento = 0;
                    // Desglose Tabla Abogados
                    $desglose_formula_timbres = $line->desglose_timbre_formula;

                    // Desglose Tabla Abogados
                    $desglose_tabla_abogados_timbres = $line->desglose_tabla_abogados;

                    // Desglose Calculo Fijo
                    $desglose_calculos_fijos_timbres = $line->desglose_calculos_fijos;

                    // Desglose Timbre Monto Manual
                    $desglose_calculos_monto_manual_timbres = $line->desglose_calculo_monto_timbre_manual;

                    // Calcular honorarios
                    $desglose_honorarios = $line->desglose_honorarios;

                    // Desglose Honoario Monto Manual
                    $desglose_calculo_monto_manual_honorarios = $line->desglose_calculo_monto_honorario_manual;

                    $description = $line->detail .' '. $transaction->currency->code;

                    $totalTimbres_temp = $desglose_formula_timbres['monto_sin_descuento'] +
                    $desglose_tabla_abogados_timbres['monto_sin_descuento'] +
                    $desglose_calculos_fijos_timbres['monto_sin_descuento'] +
                    $desglose_calculos_monto_manual_timbres['monto_sin_descuento'];
                    $totalHonorarios_temp = $desglose_honorarios['monto_sin_descuento'] +
                    $desglose_calculo_monto_manual_honorarios['monto_sin_descuento'];

                    $total_temp_sin_descuento = $totalTimbres_temp + $totalHonorarios_temp;
                    $total_temp_con_descuento = $total_temp_sin_descuento;

                    $descuento = 0;
                    if ($line->discount > 0) {
                      $descuento = $total_temp_con_descuento * $line->discount / 100;
                      $total_temp_con_descuento = $total_temp_con_descuento - $descuento;
                    }
                    @endphp
                    <tr>
                      <td class="tm_width_4">
                        {!! html_entity_decode($description) . ' ' . Helper::formatDecimal($line->getPrice()) !!}
                        @if($descuento > 0)
                          <br />
                          <div style="width:100%; text-align:right">
                            <span style="font-size: 10px; font-weight: bold;">DESCUENTO APLICADO {{ Helper::formatDecimal($descuento) }} % SOBRE {{ Helper::formatDecimal($total_temp_sin_descuento) }}</span>
                          </div>
                        @endif
                      </td>

                      @if ($transaction->tipo_facturacion == \App\Models\Transaction::MASIVA)
                      <td class="tm_width_3">
                        @if ($line->caso)
                          @php
                            $demendado = ($transaction->bank_id == \App\Models\Bank::DAVIVIENDA) ? $line->caso->pnombre_apellidos_deudor : $line->caso->pnombre_demandado;
                            $numero_operacion = $transaction->bank_id == \App\Models\Bank::DAVIVIENDA ? $line->caso->pnumero_operacion2: $line->caso->pnumero_operacion1;

                            $tipo_proceso = $line->caso->proceso->nombre;
                            $numero_expediente = $line->caso->pnumero_expediente_judicial;

                            $producto = $line->caso->producto->nombre;

                            $numero = $line->caso->pnumero;
                          @endphp
                          {{ $tipo_proceso . ', ' . $numero_operacion . ', ' . $numero . '- ' . $demendado . ', ' . $producto }}
                        @endif
                      </td>
                      @endif
                      <td class="tm_width_2">
                        @php
                        $value = 0;
                        foreach ($desglose_formula_timbres['datos'] as $d)
                        $value += $d['monto_con_descuento'];

                        foreach ($desglose_tabla_abogados_timbres['datos'] as $d)
                        $value += $d['monto_con_descuento'];

                        foreach ($desglose_calculos_fijos_timbres['datos'] as $d)
                        $value += $d['monto_con_descuento'];

                        foreach ($desglose_calculos_monto_manual_timbres['datos'] as $d)
                        $value += $d['monto_con_descuento'];

                        foreach ($desglose_honorarios['datos'] as $d)
                        $value += $d['monto_con_descuento'];

                        foreach ($desglose_calculo_monto_manual_honorarios['datos'] as $d)
                        $value += $d['monto_con_descuento'];

                        $value = $value + $line->monto_cargo_adicional/ $line->quantity;

                        @endphp
                        {{ $transaction->currency->symbol }} {{ Helper::formatDecimal($value) }}
                      </td>
                      <td class="tm_width_1 tm_text_center">{{ (int)$line->quantity }}</td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol }}
                        {{ Helper::formatDecimal($value + $line->monto_cargo_adicional) }}
                      </td>
                    </tr>

                    <!-- formula timbre -->
                    @if ($receipt_type == 'detallado')
                    @foreach ($desglose_formula_timbres['datos'] as $data)
                    <tr>
                      <td class="tm_width_4">{!! $data['titulo'] ?? '' !!}</td>
                      <td class="tm_width_3"></td>
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($data['monto_con_descuento'] +
                        $line->monto_cargo_adicional) }}
                      </td>
                    </tr>
                    @php
                      $totalTimbres += $data['monto_con_descuento'];
                    @endphp
                    @endforeach
                    @else
                    @php
                      $totalTimbres += $desglose_formula_timbres['monto_con_descuento'];
                    @endphp
                    @endif

                    <!--  Tabla de abogados -->
                    @if ($receipt_type == 'detallado')
                    @foreach ($desglose_tabla_abogados_timbres['datos'] as $data)
                    <tr>
                      <td class="tm_width_4">{!! $data['titulo'] ?? '' !!}</td>
                      <td class="tm_width_3"></td>
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($data['monto_con_descuento'] +
                        $line->monto_cargo_adicional) }}
                      </td>
                    </tr>
                    @php
                    $totalTimbres += $data['monto_con_descuento'];
                    @endphp
                    @endforeach
                    @else
                    @php
                    $totalTimbres += $desglose_tabla_abogados_timbres['monto_con_descuento'];
                    @endphp
                    @endif

                    <!--  Calculos fijos -->
                    @if ($receipt_type == 'detallado')
                    @foreach ($desglose_calculos_fijos_timbres['datos'] as $data)
                    <tr>
                      <td class="tm_width_4">{!! $data['titulo'] ?? '' !!}</td>
                      <td class="tm_width_3"></td>
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($data['monto_con_descuento'] +
                        $line->monto_cargo_adicional) }}
                      </td>
                    </tr>
                    @php
                    $totalTimbres += $data['monto_con_descuento'];
                    @endphp
                    @endforeach
                    @else
                    @php
                    $totalTimbres += $desglose_calculos_fijos_timbres['monto_con_descuento'];
                    @endphp
                    @endif

                    <!--  Calculos monto manual -->
                    @if ($receipt_type == 'detallado')
                    @foreach ($desglose_calculos_monto_manual_timbres['datos'] as $data)
                    <tr>
                      <td class="tm_width_4">{!! $data['titulo'] ?? ''!!}</td>
                      <td class="tm_width_3"></td>
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($data['monto_con_descuento'] +
                        $line->monto_cargo_adicional) }}
                      </td>
                    </tr>
                    @php
                    $totalTimbres += $data['monto_con_descuento'];
                    @endphp
                    @endforeach
                    @else
                    @php
                    $totalTimbres += $desglose_calculos_monto_manual_timbres['monto_con_descuento'];
                    @endphp
                    @endif

                    <!--  Calculos honorarios -->
                    @if ($receipt_type == 'detallado')
                    @foreach ($desglose_honorarios['datos'] as $data)
                    <tr>
                      <td class="tm_width_4">{!! $data['titulo'] ?? '' !!}</td>
                      <td class="tm_width_3"></td>
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($data['monto_con_descuento'] +
                        $line->monto_cargo_adicional) }}
                      </td>
                    </tr>
                      @php
                        $totalHonorarios += $data['monto_con_descuento'];
                      @endphp
                    @endforeach
                    @else
                      @php
                        $totalHonorarios += $desglose_honorarios['monto_con_descuento'];
                      @endphp
                    @endif

                    <!--  Calculos honorarios monto manual -->
                    @if ($receipt_type == 'detallado')
                    @foreach ($desglose_calculo_monto_manual_honorarios['datos'] as $data)
                    <tr>
                      <td class="tm_width_4">{!! $data['titulo'] ?? '' !!}</td>
                      <td class="tm_width_3"></td>
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($data['monto_con_descuento'] +
                        $line->monto_cargo_adicional) }}
                      </td>
                    </tr>
                      @php
                        $totalHonorarios += $data['monto_con_descuento'];
                      @endphp
                    @endforeach
                    @else
                      @php
                        $totalHonorarios += $desglose_calculo_monto_manual_honorarios['monto_con_descuento'];
                      @endphp
                    @endif

                    @php
                    $cargoAdicional += $line->monto_cargo_adicional;
                    @endphp
                    @endforeach

                    @php
                    if ($totalHonorarios > 0)
                      $totalHonorarios += $cargoAdicional;
                    else
                    if ($totalTimbres > 0)
                      $totalTimbres += $cargoAdicional;
                    @endphp
                    <!--  Otros cargos -->
                    @if ($transaction_other_charges->isNotEmpty())
                    <tr>
                      <td class="tm_width_4"><strong>Otros Cargos</strong></td>
                      @if ($transaction->tipo_facturacion == \App\Models\Transaction::MASIVA)
                        <td class="tm_width_3"></td>
                      @endif
                      <td class="tm_width_2"></td>
                      <td class="tm_width_1 tm_text_center"></td>
                      <td class="tm_width_3 tm_text_right">
                      </td>
                    </tr>

                    @foreach ($transaction_other_charges as $charge)
                    <tr>
                      <td class="tm_width_4">{!! $charge->detail !!}</td>
                      @if ($transaction->tipo_facturacion == \App\Models\Transaction::MASIVA)
                        <td class="tm_width_3">
                          @if ($charge->caso)
                            @php
                              $demendado = ($transaction->bank_id == \App\Models\Bank::DAVIVIENDA) ? $charge->caso->pnombre_apellidos_deudor : $charge->caso->pnombre_demandado;
                              $numero_operacion = $transaction->bank_id == \App\Models\Bank::DAVIVIENDA ? $charge->caso->pnumero_operacion2: $charge->caso->pnumero_operacion1;

                              $tipo_proceso = $charge->caso->proceso->nombre;
                              $numero_expediente = $charge->caso->pnumero_expediente_judicial;

                              $producto = $charge->caso->producto->nombre;

                              $numero = $charge->caso->pnumero;
                            @endphp
                            {{ $tipo_proceso . ', ' . $numero_operacion . ', ' . $numero . '- ' . $demendado . ', ' . $producto }}
                          @endif
                        </td>
                      @endif
                      <td class="tm_width_2">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($charge->amount) }}
                      </td>
                      <td class="tm_width_1 tm_text_center">
                        {{ $charge->quantity }}
                      </td>
                      <td class="tm_width_3 tm_text_right">
                        {{ $transaction->currency->symbol.' '. Helper::formatDecimal($charge->amount *
                        $charge->quantity) }}
                      </td>
                    </tr>
                    @endforeach
                    @endif

                    @php
                    $montoTotal = $totalTimbres + $totalHonorarios;
                    @endphp

                  </tbody>
                </table>
              </div>
            </div>
            <div class="tm_invoice_footer tm_border_top tm_mb15 tm_m0_md">
              <div class="tm_left_footer">
                @if(($cuenta && in_array($transaction->showInstruccionesPago, ['NACIONAL', 'AMBAS'])) && $cuenta->intruccionesPagoNacional)
                  <p class="tm_f12 tm_m0 tm_text_left">
                    {!! $cuenta->intruccionesPagoNacional !!}
                  </p>
                @endif

                @if (!empty($transaction->notes))
                <p class="tm_m0 tm_f12">
                  <b class="tm_primary_color">Observaciones:</b> {{ $transaction->notes }}
                </p>
                @endif

                @if ($transaction->bank_id == $bankDavivienda)
                <p class="tm_m0">
                  <b class="tm_primary_color tm_f12">
                    Esta cotización será actualizada un día antes o el mismo día de la firma
                    por efecto de tipo de cambio
                  </b>
                </p>
                @endif

                <p class="tm_m0 tm_f12">
                  <b class="tm_primary_color">Fecha de Actualización:</b>
                  {{ \Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d F Y') }}
                </p>
              </div>
              <div class="tm_right_footer" valign="top">
                <table class="tm_mb15">
                  <tbody>
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color tm_bold">Monto Honorarios</td>
                      <td class="tm_width_3 tm_primary_color tm_bold tm_text_right">
                        {{ $transaction->currency->symbol }} {{ Helper::formatDecimal($totalHonorarios) }}
                      </td>
                    </tr>
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color tm_bold">Monto de Gastos</td>
                      <td class="tm_width_3 tm_primary_color tm_bold tm_text_right">
                        {{ $transaction->currency->symbol }}
                        {{ Helper::formatDecimal($totalTimbres + $transaction->totalOtrosCargos) }}
                      </td>
                    </tr>
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color">IVA</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right">
                        {{ $transaction->currency->symbol }}
                        {{ Helper::formatDecimal($transaction->totalTax) }}
                      </td>
                    </tr>
                    <tr class="tm_accent_bg">
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f19 tm_white_color">Total </td>
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f19 tm_white_color tm_text_right">
                        {{ $transaction->currency->symbol }}
                        {{ Helper::formatDecimal($transaction->totalComprobante) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          @if(($cuenta && in_array($transaction->showInstruccionesPago, ['INTERNACIONAL', 'AMBAS'])) &&
              ($cuenta->intruccionesPagoNacional || $cuenta->intruccionesPagoInternacional))
            <div class="tm_note tm_font_style_normal">
              <hr class="tm_mb15">
              <p class="tm_mb2 tm_text_center"><b class="tm_primary_color">Instrucciones de pago internacional:</b></p>
              <table>
                <tbody>
                  <tr>
                    <td width="50%" valign="top">
                      @if (in_array($transaction->showInstruccionesPago, ['INTERNACIONAL', 'AMBAS']))
                        <p class="tm_f12 tm_m0 tm_text_left">
                          {!! $cuenta->intruccionesPagoInternacional !!}
                        </p>
                      @endif
                    </td>
                  </tr>
                </tbody>
              </table>
            </div><!-- .tm_note -->
          @endif
        </div>
      </div>
    </div>
  </div>
</body>


</html>
