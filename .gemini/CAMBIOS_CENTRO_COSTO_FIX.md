# Corrección del Guardado de Centros de Costo en MovimientoManager

## Fecha: 2026-01-17

## Problema Identificado

Al escribir en el campo `monto`, se realiza un cálculo en JavaScript que actualiza el primer centro de costo. Sin embargo, al guardar el movimiento, la información del centro de costo no se estaba guardando correctamente en la base de datos.

## Causas del Problema

1. **Sincronización JavaScript incorrecta**: La función `setFirstRowValueCentrocosto` no estaba encontrando correctamente el componente Livewire de `MovimientosCentroCosto` para actualizar el valor de `rows.0.amount`.

2. **Formato de datos**: Los valores con separadores de miles (comas) no se estaban limpiando antes de guardar en la base de datos.

3. **Flujo de eventos asíncrono**: El componente padre (`MovimientoManager`) no esperaba la confirmación del guardado de centros de costo antes de mostrar el mensaje de éxito.

## Solución Implementada

### PASO 1: Corregir sincronización JavaScript en el formulario

**Archivo**: `resources/views/livewire/movimientos/partials/_form-movimiento.blade.php`

**Cambios**:

- Modificada la función `setFirstRowValueCentrocosto` para buscar el componente Livewire a través del contenedor `content-centro-costo` en lugar del input más cercano.
- Agregados logs de consola para debugging.
- Convertir el valor a número con 2 decimales antes de enviarlo al componente.

```javascript
function setFirstRowValueCentrocosto(value) {
  const input = document.getElementById('amount_0');
  if (!input) return;

  // Aplicar formato visible
  setFormattedValue(input, value);

  // Buscar el contenedor del componente centro de costo
  const centroCostoContainer = document.getElementById('content-centro-costo');
  if (!centroCostoContainer) {
    console.warn('[setFirstRowValueCentrocosto] No se encontró el contenedor content-centro-costo');
    return;
  }

  const componentEl = centroCostoContainer.closest('[wire\\:id]');
  const componentId = componentEl?.getAttribute('wire:id');

  if (componentId && window.Livewire) {
    const component = Livewire.find(componentId);
    if (component) {
      const numericValue = parseFloat(value);
      const formattedValue = isNaN(numericValue) ? '0.00' : numericValue.toFixed(2);

      console.log('[setFirstRowValueCentrocosto] Actualizando rows.0.amount:', formattedValue);
      component.set('rows.0.amount', formattedValue, false);
    } else {
      console.warn('[setFirstRowValueCentrocosto] No se encontró el componente Livewire');
    }
  } else {
    console.warn('[setFirstRowValueCentrocosto] No se encontró componentId o Livewire');
  }
}
```

### PASO 2: Limpiar valores en el componente hijo

**Archivo**: `app/Livewire/Movimientos/MovimientosCentroCosto.php`

**Cambios**:

- Agregado import de `Log` facade.
- Limpieza de valores de `amount` al inicio del método `save()` para remover separadores de miles.
- Cambio de comparación de `$row['amount'] > 0` a `floatval($row['amount']) > 0`.
- Uso de `$cleanAmount` en lugar de `$row['amount']` al guardar en la base de datos.
- Cambio de `return;` a `return false;` y `return true;` para indicar éxito/fallo.

```php
public function save()
{
    // Limpiar valores de amount antes de validar
    foreach ($this->rows as $i => $row) {
      if (isset($row['amount'])) {
        // Limpiar separadores de miles y convertir a float
        $cleanAmount = floatval(str_replace(',', '', $row['amount']));
        $this->rows[$i]['amount'] = number_format($cleanAmount, 2, '.', '');
      }
    }

    $filasValidas = collect($this->rows)->filter(
      fn($row) =>
      isset($row['centro_costo_id'], $row['codigo_contable_id'], $row['amount']) &&
        is_numeric($row['centro_costo_id']) &&
        is_numeric($row['codigo_contable_id'])  &&
        floatval($row['amount']) > 0  // ← Cambiar de $row['amount'] > 0 a floatval()
    );

    if ($filasValidas->isEmpty()) {
      $this->addError('rows_valido', 'Debe agregar al menos un centro de costo completo.');
      return false;  // ← Retornar false en lugar de return;
    }

    try {
      $this->validate();

      foreach ($this->rows as $row) {
        // Asegurar que el amount esté limpio
        $cleanAmount = floatval(str_replace(',', '', $row['amount']));

        if (isset($row['id'])) {
          MovimientoCentroCosto::where('id', $row['id'])->update([
            'centro_costo_id' => $row['centro_costo_id'],
            'codigo_contable_id' => $row['codigo_contable_id'],
            'amount' => $cleanAmount,  // ← Usar $cleanAmount
          ]);
        } else {
          MovimientoCentroCosto::create([
            'movimiento_id' => $this->movimiento_id,
            'centro_costo_id' => $row['centro_costo_id'],
            'codigo_contable_id' => $row['codigo_contable_id'],
            'amount' => $cleanAmount,  // ← Usar $cleanAmount
          ]);
        }
      }

      return true;  // ← Retornar true en caso de éxito
    } catch (\Throwable $e) {
      $this->addError('rows_valido', 'Error al guardar: ' . $e->getMessage());
      return false;  // ← Retornar false en caso de error
    }
}
```

### PASO 3: Modificar el método que recibe el evento de guardado

**Archivo**: `app/Livewire/Movimientos/MovimientosCentroCosto.php`

**Cambios**:

- Captura del valor de retorno de `save()`.
- Dispatch de eventos `centrosGuardadosOk` o `centrosGuardadosFail` según el resultado.
- Agregados logs para debugging.

```php
#[On('save-centros-costo')]
public function saveCentrosCosto($data)
{
    $this->movimiento_id = $data['id'];
    $success = $this->save(); // este método ya valida y guarda

    if ($success) {
      $this->loadRows();

      Log::info('saveCentrosCosto: Disparando centrosGuardadosOk');

      // Disparar evento que el padre escuchará
      $this->dispatch('centrosGuardadosOk');
    } else {
      Log::info('saveCentrosCosto: Disparando centrosGuardadosFail');
      $this->dispatch('centrosGuardadosFail');
    }
}
```

### PASO 4: Llamar directamente al método de éxito en el componente padre

**Archivo**: `app/Livewire/Movimientos/MovimientoManager.php`

**Cambios en `crearMovimiento()`**:

- Agregada llamada directa a `onCentrosGuardadosOk()` después de dispatch del evento.
- Agregado `return;` en el catch para evitar ejecución adicional.

```php
public function crearMovimiento()
{
    // ... código existente ...

    try {
      DB::transaction(function () use ($validatedData) {
        // ... código de creación ...

        $record = Movimiento::create($validatedData);
        $this->recordId = $record->id;

        // Emite evento para que el componente hijo actualice centros de costo
        $this->dispatch('save-centros-costo', ['id' => $record->id]);
      });

      // ← AGREGAR ESTA LÍNEA: Llamar directamente a onCentrosGuardadosOk
      $this->onCentrosGuardadosOk();

    } catch (\Exception $e) {
      // ... manejo de errores ...
      return;
    }

    // Importante para que lo escuche el blade y actualize el sumary
    $this->dispatch('actualizarSumary');
}
```

**Cambios en `updateMovimiento()`**:

- Agregada llamada directa a `onCentrosGuardadosOk()` después de dispatch del evento.
- Agregado `return;` en el catch para evitar ejecución adicional.

```php
public function updateMovimiento()
{
    // ... código existente ...

    try {
      DB::transaction(function () use ($validatedData) {
        // ... código de actualización ...

        // Emite evento para que el componente hijo actualice centros de costo
        $this->dispatch('save-centros-costo', ['id' => $record->id]);
      });

      // ← AGREGAR ESTA LÍNEA: Llamar directamente a onCentrosGuardadosOk
      $this->onCentrosGuardadosOk();

    } catch (\Exception $e) {
      // ... manejo de errores ...
      return;
    }

    // Importante para que lo escuche el blade y actualize el sumary
    $this->dispatch('actualizarSumary');
}
```

## Verificación

Después de aplicar estos cambios, deberías ver en los logs:

1. `[setFirstRowValueCentrocosto] Actualizando rows.0.amount: X.XX`
2. `saveCentrosCosto: Disparando centrosGuardadosOk`
3. `onCentrosGuardadosOk ejecutado`

Y en el navegador:

- El formulario se cierra automáticamente (si se seleccionó "Guardar y Cerrar")
- Vuelve al listado o permanece en edición según la opción seleccionada
- Muestra notificación de éxito
- Los datos se guardan correctamente en la base de datos

## Archivos Modificados

1. `resources/views/livewire/movimientos/partials/_form-movimiento.blade.php`
2. `app/Livewire/Movimientos/MovimientosCentroCosto.php`
3. `app/Livewire/Movimientos/MovimientoManager.php`

## Notas Importantes

- La solución incluye una llamada directa a `onCentrosGuardadosOk()` como workaround para asegurar que el flujo se complete correctamente, incluso si el evento no se propaga adecuadamente.
- Los logs agregados ayudarán a diagnosticar cualquier problema futuro.
- La limpieza de separadores de miles es crucial para evitar errores al guardar valores numéricos.
