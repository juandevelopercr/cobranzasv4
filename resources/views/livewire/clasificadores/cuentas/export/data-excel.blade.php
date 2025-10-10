<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:x='urn:schemas-microsoft-com:office:excel'
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
  <meta http-equiv='Content-Type' content='text/html;charset=utf-8' />
</head>

<body class="kv-wrap">
  <table class="kv-grid-table table table-bordered kv-table-wrap">
    <thead>
      <tr>
        <th style="width: 30px;">#</th>
        <th style="width: 400px;">{{ __('Banco') }}</th>
        <th style="width: 200px;">{{ __('Número de Cuenta') }}</th>
        <th style="width: 250px;">{{ __('Persona o Sociedad') }}</th>
        <th style="width: 100px;">{{ __('Moneda') }}</th>
        <th style="width: 200px;">{{ __('Saldo Actual') }}</th>
        <th style="width: 100px;">{{ __('Último cheque') }}</th>
      </tr>
    </thead>
    <tbody>
      {{--@foreach ($query->cursor() as $key => $dato) --}}
      @foreach ($chunks as $chunk)
        @foreach ($chunk as $key => $dato)
          {{-- tu lógica actual --}}
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $dato->nombre_cuenta ?? '' }}</td>
            <td>{{ $dato->numero_cuenta }}</td>
            <td>{{ $dato->perosna_sociedad }}</td>
            <td>{{ $dato->moneda }}</td>
            <td style="text-align:right;">{{ (float) ($dato->getBalance() ?? 0) }}</td>
            <td>{{ $dato->ultimo_cheque }}</td>
          </tr>
        @endforeach
      @endforeach
    </tbody>
  </table>
</body>
</html>
