# Estándares de Estabilidad Livewire v3 - Consortium V4

Este documento es el **ÚNICO DE CONSULTA OBLIGATORIA** para el desarrollo y mantenimiento de componentes Livewire. Siga estas reglas estrictamente para evitar errores de "Snapshot missing", "Component not found" y fallos en la persistencia del DOM.

---

## 1. Estabilidad del DOM y Morphing

### REGLA: Raíz con wire:key persistente
Todos los componentes Livewire deben tener un contenedor raíz único con una clave estática.
```blade
<div wire:key="mi-componente-root">
    <!-- contenido -->
</div>
```

### REGLA: No usar @if para ocultar formularios o componentes principales
Nunca use `@if($action === 'edit')` para renderizar o destruir bloques grandes del DOM que contengan componentes anidados o scripts. Livewire v3 pierde el rastro de los IDs de componentes hijos durante el "morphing".
**SOLUCIÓN:** Use clases de Alpine.js para ocultar/mostrar, manteniendo el elemento siempre en el DOM.
```blade
<div :class="{ 'd-none': action !== 'edit' }" wire:key="form-container-persistent">
    @include('mi-formulario-parcial')
</div>
```

### REGLA CRÍTICA: No envolver componentes Livewire hijos con @if
Nunca use `@if($this->recordId)` para mostrar/ocultar un componente `@livewire(...)` hijo.
Cuando `recordId` cambia, Livewire destruye el componente hijo y crea uno nuevo, lo que genera
"Snapshot missing" y pérdida de estado. Use Alpine para la visibilidad.
- **MAL**: `@if($this->recordId) @livewire('hijo', ...) @endif`
- **BIEN**: `<div :class="{ 'd-none': !$wire.recordId }"> @livewire('hijo', ...) </div>`

El componente hijo debe manejar internamente el caso de `transaction_id = null` en su mount().

### REGLA DE ORO: Identificadores Estables (No Dinámicos)
Nunca use IDs de registros en el `wire:key` de un contenedor que albergue componentes anidados.
- **MAL**: `<div wire:key="form-{{ $recordId }}"> ... <livewire:hijo /> ... </div>`
- **BIEN**: `<div wire:key="form-persistent-container"> ... <livewire:hijo /> ... </div>`
*Razón: Si `$recordId` cambia, Livewire destruye todo el bloque y los componentes hijos pierden su estado (Snapshot missing).*

---

## 2. Alpine.js — Inicialización de Variables

### REGLA CRÍTICA: Toda variable Alpine debe estar declarada en x-data
Nunca use una variable Alpine en `:class`, `x-show`, `x-on:` u otras directivas sin haberla
declarado previamente en el `x-data` del mismo elemento o de un ancestro.

- **MAL** (causa `ReferenceError: isDropping is not defined` y congela la pantalla):
```blade
<div :class="isDropping ? 'border-primary' : 'border-secondary'"
     x-on:dragover.prevent="isDropping = true">
```

- **BIEN** (siempre declarar en x-data del mismo elemento o ancestro):
```blade
<div x-data="{ isDropping: false }"
     :class="isDropping ? 'border-primary' : 'border-secondary'"
     x-on:dragover.prevent="isDropping = true">
```

*Razón: Un `ReferenceError` en Alpine no está contenido — rompe el árbol Alpine de toda la
página, dejando la interfaz congelada sin que el usuario pueda continuar trabajando.*

### REGLA: x-data debe estar en el elemento más externo del scope
Si múltiples directivas Alpine dependen de las mismas variables, declárelas en el padre común.

```blade
{{-- BIEN: el scope cubre todos los hijos --}}
<div x-data="{ open: false, loading: false }">
    <button @click="open = true; loading = true">...</button>
    <div x-show="open && !loading">...</div>
</div>
```

---

## 3. Inicialización de JavaScript (Select2, Cleave, etc.)

### REGLA: Escuchadores Scoped ($wire.on) — NUNCA Livewire.on()
**No use `Livewire.on(...)` dentro de los scripts de un componente.**
`Livewire.on()` es global: se acumula en cada re-render del componente creando múltiples
listeners para el mismo evento. Esto causa que Select2 se inicialice 2x, 3x, Nx veces.

Use siempre `$wire.on(...)` dentro de scripts del componente:
```javascript
// MAL — se duplica en cada re-render
Livewire.on('reinitSelect2Controls', () => { ... });

// BIEN — scoped al componente, no se acumula
$wire.on('reinitSelect2Controls', () => { ... });
```

*Excepción aceptable: `Livewire.on()` en scripts globales de app.js/custom.js que se cargan
una sola vez, siempre que estén protegidos contra duplicados con un flag.*

### REGLA: Namespacing de Eventos
Use namespaces en los eventos de jQuery para evitar bucles infinitos.
```javascript
$el.trigger('change.select2');
```

### REGLA: Uso de safeLivewireFind
Use el helper global `window.safeLivewireFind(id)` para interactuar con componentes desde JS global.
```javascript
const component = window.safeLivewireFind(id);
if (component) component.set('prop', value);
```

### REGLA: wire:ignore en contenedores de plugins JS
Todo elemento controlado por un plugin externo (Select2, Cleave, Flatpickr) debe estar
envuelto en `wire:ignore` para que Livewire no lo toque durante el morphing.
```blade
<div wire:ignore>
    <select id="contact_id" class="form-select select2">...</select>
</div>
```

---

## 4. Manejo de Datos y Fechas

- **Fechas**: Mostrar en `DD-MM-YYYY`. Convertir a `Y-m-d` en el Backend antes de persistir.
- **Select2 AJAX**: El backend debe despachar `setSelect2Value` con el ID y el Texto al editar para evitar campos vacíos.
  ```php
  $this->dispatch('setSelect2Value', id: 'contact_id', value: $record->contact_id, text: $record->contact->name);
  ```

---

## 5. Componentes Hijo — Comunicación y Ciclo de Vida

### REGLA: Los hijos deben tolerar props nulas en mount()
Un componente hijo que recibe `transaction_id` debe funcionar correctamente cuando ese valor
es `null` o vacío (caso de formulario nuevo antes de guardar).
```php
public function mount($transaction_id = null)
{
    $this->transaction_id = $transaction_id;
    // No hacer queries si transaction_id es null
    if ($this->transaction_id) {
        $this->loadRecords();
    }
}
```

### REGLA: Comunicación padre → hijo con dispatch + #[On]
El padre notifica al hijo mediante eventos. El hijo nunca lee props del padre directamente.
```php
// Padre despacha cuando cambia el contexto
$this->dispatch('transactionUpdated', transactionId: $this->recordId);

// Hijo escucha
#[On('transactionUpdated')]
public function onTransactionUpdated($transactionId): void
{
    $this->transaction_id = $transactionId;
    $this->loadRecords();
}
```

---

## 6. Solución a Problemas Comunes (Checklist)

1. **¿Error "Snapshot missing"?** Verifique que ningún contenedor padre tenga un `wire:key` que cambie dinámicamente.
2. **¿Formulario no carga?** Asegúrese de que no esté envuelto en un `@if` que se evalúe a falso en el mount inicial. Use Alpine para la visibilidad.
3. **¿Select2 no muestra el nombre al editar?** Asegúrese de enviar el evento `setSelect2Value` con el parámetro `text`.
4. **¿Cleave.js no funciona en campos nuevos?** Verifique que la inicialización sea idempotente y use `wire:ignore` en el contenedor del input.
5. **¿Pantalla congelada después de guardar?** Busque variables Alpine sin declarar en `x-data` (típicamente en zonas de drag-and-drop o modales). Un `ReferenceError` de Alpine congela la interfaz entera.
6. **¿Select2 se inicializa múltiples veces?** Reemplace `Livewire.on()` por `$wire.on()` en los scripts del componente.
7. **¿Componente hijo pierde estado al editar?** El padre probablemente usa `@if($this->recordId)` para mostrar el hijo. Cámbielo por Alpine `:class="{ 'd-none': !$wire.recordId }"`.
