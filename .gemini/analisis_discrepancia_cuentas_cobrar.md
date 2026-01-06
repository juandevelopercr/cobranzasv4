# Análisis de Discrepancia: CuentaPorCobrarManager vs AntiguedadReport

## Problema Identificado

Existe una discrepancia entre los totales mostrados en:

- **CuentaPorCobrarManager**: Componente de gestión de cuentas por cobrar
- **AntiguedadReport**: Reporte de antigüedad de saldos

### Causa Raíz

La tabla `transactions_commissions` permite distribuir el monto de una factura entre múltiples centros de costo mediante un campo `percent`. Por ejemplo:

```
Factura ID 100: Total $1,000
- Centro de Costo A: 60% = $600
- Centro de Costo B: 40% = $400
```

Cuando se filtra por "Centro de Costo A", solo se debe mostrar $600, no los $1,000 completos.

## Análisis de las Consultas

### 1. CuentaPorCobrarManager (INCORRECTO)

**Ubicación**: `app/Models/Transaction.php` - método `scopeSearch()`

**Problema**: Las líneas 434-446 calculan `payment` y `pending_payment` SIN considerar la distribución por centro de costo:

```php
DB::raw("(
    SELECT COALESCE(SUM(tp.total_medio_pago), 0)
    FROM transactions_payments tp
    WHERE tp.transaction_id = transactions.id
) as payment"),

DB::raw("ABS(
    COALESCE(transactions.totalComprobante, 0) -
    COALESCE((
        SELECT SUM(tp.total_medio_pago)
        FROM transactions_payments tp
        WHERE tp.transaction_id = transactions.id
    ), 0)
) as pending_payment"),
```

**Consecuencia**:

- Si una factura de $1,000 está distribuida 60% en Centro A y 40% en Centro B
- Al filtrar por Centro A, muestra $1,000 en lugar de $600
- Los totales NO coinciden con el reporte de antigüedad

### 2. AntiguedadReport (CORRECTO)

**Ubicación**: `app/Exports/AntiguedadReport.php` - método `query()`

**Implementación Correcta**: Usa un subquery para sumar solo los porcentajes de centros permitidos:

```php
$ccSubQuery = "(
    SELECT transaction_id, SUM(percent) as percent
    FROM transactions_commissions
    " . (!empty($centrosExcluyentes) ? "WHERE centro_costo_id NOT IN (".implode(',', $centrosExcluyentes).")" : "") . "
    GROUP BY transaction_id
) AS cc_sum";

// Luego ajusta los montos:
DB::raw("SUM(($conversionCase) * COALESCE(cc_sum.percent,100)/100) AS totalComprobanteAjustado"),
```

## Solución Propuesta

### Paso 1: Modificar `Transaction::scopeSearch()`

Agregar el ajuste por centro de costo en los cálculos de `payment` y `pending_payment`:

```php
// Si hay filtro de centro de costo, calcular el porcentaje correspondiente
$percentCalc = "COALESCE((
    SELECT SUM(tc.percent)
    FROM transactions_commissions tc
    WHERE tc.transaction_id = transactions.id
    " . (!empty($filters['filter_centro_costo']) ?
        "AND tc.centro_costo_id = " . $filters['filter_centro_costo'] :
        "") . "
), 100)";

// Ajustar payment
DB::raw("(
    SELECT COALESCE(SUM(tp.total_medio_pago), 0) * ($percentCalc) / 100
    FROM transactions_payments tp
    WHERE tp.transaction_id = transactions.id
) as payment"),

// Ajustar totalComprobante y pending_payment
DB::raw("(transactions.totalComprobante * ($percentCalc) / 100) as totalComprobante_ajustado"),

DB::raw("ABS(
    (COALESCE(transactions.totalComprobante, 0) * ($percentCalc) / 100) -
    COALESCE((
        SELECT SUM(tp.total_medio_pago) * ($percentCalc) / 100
        FROM transactions_payments tp
        WHERE tp.transaction_id = transactions.id
    ), 0)
) as pending_payment"),
```

### Paso 2: Excluir facturas sin distribución

Ambos componentes deben excluir facturas que no tienen registros en `transactions_commissions`:

```php
->whereExists(function ($q) {
    $q->select(DB::raw(1))
      ->from('transactions_commissions')
      ->whereColumn('transactions_commissions.transaction_id', 'transactions.id');
})
```

### Paso 3: Actualizar las columnas en CuentaPorCobrarManager

Modificar `getDefaultColumns()` para usar `totalComprobante_ajustado` en lugar de `totalComprobante`.

## Verificación

Después de aplicar los cambios, verificar que:

1. ✅ Los totales en CuentaPorCobrarManager coincidan con AntiguedadReport
2. ✅ Al filtrar por un centro de costo específico, solo se muestren los montos proporcionales
3. ✅ Las facturas sin distribución de centro de costo sean excluidas
4. ✅ Los pagos se ajusten proporcionalmente según el centro de costo

## Archivos a Modificar

1. `app/Models/Transaction.php` - método `scopeSearch()`
2. `app/Livewire/Transactions/CuentaPorCobrarManager.php` - método `getFilteredQuery()` (si es necesario)
3. `app/Exports/AntiguedadReport.php` - verificar que esté correcto (ya lo está)
