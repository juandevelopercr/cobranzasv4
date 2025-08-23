<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Nombre Corto') }}</th>
            <th>{{ __('Activo') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
            <td>{{ $record->crr }}</td>
            <td>{{ $record->nombre }}</td>
            <td>{{ $record->impresion }}</td>
            <td>{{ $record->active }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
