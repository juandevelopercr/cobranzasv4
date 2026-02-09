# SOLUCIÓN DEFINITIVA: Problema de Líneas Vacías en Proformas

## PROBLEMA REPORTADO POR EL CLIENTE

"Hoy le devolví unas solicitudes de proformas al abogado, pero le llegaron vacías (lo mismo de la vez pasada), cuando él la abrió tenía el monto pero no las líneas de detalle que ya estaban agregadas y que incluso ya habíamos mandado al banco. Lo resolvimos metiendo otra vez las líneas para poder enviar la corrección que el banco nos había pedido, pero cuando le dio guardar, le salió un aviso de que la página no existía."

## CAUSA RAÍZ IDENTIFICADA

El problema era causado por **contaminación cruzada de sesiones** entre múltiples usuarios o múltiples pestañas del mismo usuario.

### Flujo del Problema:

1. **Usuario A** abre Proforma ID=100 en su navegador
   - `ProformaManager` guarda en sesión: `transaction_context = {transaction_id: 100}`
2. **Usuario B** abre Proforma ID=200 en su navegador (o Usuario A abre otra pestaña)
   - `ProformaManager` **SOBRESCRIBE** la sesión: `transaction_context = {transaction_id: 200}`
3. **Usuario A** intenta guardar líneas en su Proforma ID=100

   - `TransactionLineManager` lee de sesión y obtiene `transaction_id: 200`
   - Las líneas se guardan en la Proforma incorrecta (ID=200)
   - Usuario A ve su proforma vacía
   - Usuario B ve líneas que no agregó

4. **Error "página no existía"**
   - Ocurría cuando el `transaction_id` de sesión apuntaba a una transacción inexistente o eliminada

## SOLUCIÓN IMPLEMENTADA

### 1. Eliminado uso de Session en TransactionLineManager

**ANTES:**

```php
public function mount(..., $transaction_id = null) {
    $this->transaction_id = $transaction_id;

    // ❌ PROBLEMA: Fallback a sesión causaba contaminación
    if ($this->transaction_id) {
        $this->loadTransactionDetails();
    } elseif (session()->has('transaction_context')) {
        $this->handleUpdateContext(session()->get('transaction_context'));
    }
}
```

**DESPUÉS:**

```php
public function mount(..., $transaction_id = null) {
    $this->transaction_id = $transaction_id;

    // ✅ SOLUCIÓN: transaction_id es obligatorio, no hay fallback a sesión
    if ($this->transaction_id) {
        $this->loadTransactionDetails();
    } else {
        Log::error('TransactionLineManager mounted without transaction_id');
        $this->dispatch('show-notification', [
            'type' => 'error',
            'message' => __('Transaction ID is required')
        ]);
    }
}
```

### 2. Eliminado session()->put() en ProformaManager

**ANTES:**

```php
public function edit($recordId) {
    // ... código ...

    // ❌ PROBLEMA: Guardaba en sesión compartida
    $contextData = [
        'transaction_id' => $record->id,
        'bank_id' => $record->bank_id,
        // ...
    ];
    session()->forget('transaction_context');
    session()->put('transaction_context', $contextData);

    // Evento Livewire (correcto)
    $this->dispatch('updateTransactionContext', $contextData);
}
```

**DESPUÉS:**

```php
public function edit($recordId) {
    // ... código ...

    // ✅ SOLUCIÓN: Solo eventos Livewire, NO sesión
    $this->dispatch('updateTransactionContext', [
        'transaction_id' => $record->id,
        'bank_id' => $record->bank_id,
        'type_notarial_act' => $record->proforma_type,
        'tipo_facturacion' => $record->tipo_facturacion,
    ]);
}
```

### 3. Mismo cambio en NotaDebitoElectronicaManager

Se aplicó la misma corrección para mantener consistencia en todos los managers de transacciones.

## POR QUÉ ESTA SOLUCIÓN ES ROBUSTA Y DEFINITIVA

### 1. **Aislamiento por Componente**

- Cada instancia de `TransactionLineManager` tiene su propio `$transaction_id`
- No hay estado compartido entre usuarios o pestañas

### 2. **Comunicación Correcta**

- Los componentes se comunican solo mediante eventos Livewire
- Los eventos son específicos de la instancia del componente

### 3. **Validación Temprana**

- Si falta `transaction_id`, se muestra error inmediatamente
- No hay comportamiento silencioso incorrecto

### 4. **Sin Efectos Secundarios**

- La sesión ya no se usa para este propósito
- No hay riesgo de sobrescritura

## ARCHIVOS MODIFICADOS

1. `app/Livewire/TransactionsLines/TransactionLineManager.php`

   - Eliminado fallback a `session()->get('transaction_context')`
   - Agregada validación de `transaction_id` obligatorio

2. `app/Livewire/Transactions/ProformaManager.php`

   - Eliminado `session()->put('transaction_context', ...)`
   - Mantenido solo `dispatch('updateTransactionContext', ...)`

3. `app/Livewire/Transactions/NotaDebitoElectronicaManager.php`
   - Mismos cambios que ProformaManager

## PRUEBAS RECOMENDADAS

1. **Prueba de Usuario Único - Múltiples Pestañas:**

   - Abrir Proforma A en pestaña 1
   - Abrir Proforma B en pestaña 2
   - Agregar líneas en ambas
   - Verificar que cada proforma mantiene sus propias líneas

2. **Prueba de Múltiples Usuarios:**

   - Usuario 1 edita Proforma A
   - Usuario 2 edita Proforma B simultáneamente
   - Ambos agregan líneas
   - Verificar que no hay mezcla de datos

3. **Prueba de Devolución:**
   - Crear proforma con líneas
   - Cambiar estado a "SOLICITADA"
   - Devolverla a "PROCESO"
   - Verificar que las líneas siguen ahí

## BENEFICIOS ADICIONALES

- **Mejor Rendimiento:** No hay lecturas/escrituras innecesarias de sesión
- **Más Mantenible:** Comunicación explícita entre componentes
- **Debugging Más Fácil:** Los logs muestran claramente qué `transaction_id` usa cada componente
- **Escalable:** Funciona correctamente con cualquier número de usuarios concurrentes

## CONCLUSIÓN

Esta solución elimina completamente la causa raíz del problema reportado por el cliente. El uso de sesión para compartir `transaction_id` era fundamentalmente incompatible con la arquitectura multi-usuario y multi-pestaña de Livewire.

La nueva implementación usa solo mecanismos nativos de Livewire (parámetros de componente y eventos), que están diseñados específicamente para este tipo de comunicación entre componentes.
