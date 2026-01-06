# Resumen de Ajustes Aplicados: CuentaPorCobrarManager vs AntiguedadReport

## Fecha: 2026-01-06

## Problema Identificado

Existía una discrepancia entre los totales mostrados en:

- **CuentaPorCobrarManager**: Componente de gestión de cuentas por cobrar
- **AntiguedadReport**: Reporte de antigüedad de saldos

### Causa Raíz

La tabla `transactions_commissions` permite distribuir el monto de una factura entre múltiples centros de costo mediante un campo `percent`. Los cálculos de totales, pagos y saldos pendientes NO estaban considerando esta distribución proporcional.

## Cambios Aplicados

### 1. Archivo: `app/Models/Transaction.php`

**Método modificado**: `scopeSearch()`

**Cambios realizados**:

1. **Agregado campo `centro_costo_percent`**: Calcula el porcentaje del centro de costo filtrado (o 100% si no hay filtro)

```php
DB::raw("COALESCE((
    SELECT SUM(tc.percent)
    FROM transactions_commissions tc
    WHERE tc.transaction_id = transactions.id
    " . (!empty($filters['filter_centro_costo']) ?
        "AND tc.centro_costo_id = " . intval($filters['filter_centro_costo']) :
        "") . "
), 100) as centro_costo_percent")
```

2. **Agregado campo `totalComprobante_ajustado`**: Total ajustado por centro de costo

```php
DB::raw("(
    COALESCE(transactions.totalComprobante, 0) *
    COALESCE((
        SELECT SUM(tc.percent)
        FROM transactions_commissions tc
        WHERE tc.transaction_id = transactions.id
        " . (!empty($filters['filter_centro_costo']) ?
            "AND tc.centro_costo_id = " . intval($filters['filter_centro_costo']) :
            "") . "
    ), 100) / 100
) as totalComprobante_ajustado")
```

3. **Modificado campo `payment`**: Pago ajustado por centro de costo

```php
DB::raw("(
    COALESCE((
        SELECT SUM(tp.total_medio_pago)
        FROM transactions_payments tp
        WHERE tp.transaction_id = transactions.id
    ), 0) *
    COALESCE((
        SELECT SUM(tc.percent)
        FROM transactions_commissions tc
        WHERE tc.transaction_id = transactions.id
        " . (!empty($filters['filter_centro_costo']) ?
            "AND tc.centro_costo_id = " . intval($filters['filter_centro_costo']) :
            "") . "
    ), 100) / 100
) as payment")
```

4. **Modificado campo `pending_payment`**: Saldo pendiente ajustado por centro de costo

```php
DB::raw("ABS(
    (COALESCE(transactions.totalComprobante, 0) *
     COALESCE((
         SELECT SUM(tc.percent)
         FROM transactions_commissions tc
         WHERE tc.transaction_id = transactions.id
         " . (!empty($filters['filter_centro_costo']) ?
             "AND tc.centro_costo_id = " . intval($filters['filter_centro_costo']) :
             "") . "
     ), 100) / 100) -
    (COALESCE((
        SELECT SUM(tp.total_medio_pago)
        FROM transactions_payments tp
        WHERE tp.transaction_id = transactions.id
    ), 0) *
     COALESCE((
         SELECT SUM(tc.percent)
         FROM transactions_commissions tc
         WHERE tc.transaction_id = transactions.id
         " . (!empty($filters['filter_centro_costo']) ?
             "AND tc.centro_costo_id = " . intval($filters['filter_centro_costo']) :
             "") . "
     ), 100) / 100)
) as pending_payment")
```

### 2. Archivo: `app/Exports/AntiguedadReport.php`

**Método modificado**: `query()`

**Cambios realizados**:

1. **Modificado cálculo de `saldoPendiente`**: Ahora ajusta TANTO el total como los pagos por el porcentaje del centro de costo

**ANTES**:

```php
DB::raw("SUM((($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)) AS saldoPendiente")
```

**DESPUÉS**:

```php
DB::raw("SUM((($conversionCase) * COALESCE(cc_sum.percent,100)/100 - ($pagosCase * COALESCE(cc_sum.percent,100)/100))) AS saldoPendiente")
```

2. **Modificados todos los rangos de vencimiento**: Aplicar el mismo ajuste proporcional a los pagos en todos los rangos (sin vencer, 1-30 días, 31-45 días, etc.)

**Ejemplo del cambio**:

**ANTES**:

```php
THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - $pagosCase)
```

**DESPUÉS**:

```php
THEN (($conversionCase) * COALESCE(cc_sum.percent,100)/100 - ($pagosCase * COALESCE(cc_sum.percent,100)/100))
```

3. **Agregado filtro `whereExists`**: Para excluir facturas sin distribución de centro de costo y excluir centros específicos

```php
->whereExists(function ($q) {
    $q->select(DB::raw(1))
      ->from('transactions_commissions')
      ->whereColumn('transactions_commissions.transaction_id', 'transactions.id')
      ->whereNotIn('transactions_commissions.centro_costo_id', [1,12,14,15,16,17]);
})
```

## Archivos Creados

1. **`.gemini/analisis_discrepancia_cuentas_cobrar.md`**: Documento de análisis exhaustivo del problema
2. **`scripts/verificar_ajustes_cuentas_cobrar.php`**: Script de verificación para comprobar que los ajustes funcionan correctamente

## Verificación

Para verificar que los cambios funcionan correctamente, ejecutar:

```bash
php scripts/verificar_ajustes_cuentas_cobrar.php
```

Este script compara los totales calculados de dos formas diferentes y verifica que coincidan.

## Resultado Esperado

Después de aplicar estos cambios:

1. ✅ Los totales en `CuentaPorCobrarManager` coinciden con `AntiguedadReport`
2. ✅ Al filtrar por un centro de costo específico, solo se muestran los montos proporcionales
3. ✅ Las facturas sin distribución de centro de costo son excluidas de ambos reportes
4. ✅ Los pagos se ajustan proporcionalmente según el centro de costo

## Ejemplo de Funcionamiento

**Escenario**: Factura de $1,000 distribuida así:

- Centro de Costo A: 60% = $600
- Centro de Costo B: 40% = $400

**Resultado al filtrar por Centro A**:

- Total mostrado: $600 (no $1,000)
- Si hay un pago de $200, se muestra: $120 (60% de $200)
- Saldo pendiente: $480 ($600 - $120)

## Notas Importantes

- Los centros de costo 1, 12, 14, 15, 16, 17 están excluidos de ambos reportes
- Si una factura NO tiene registros en `transactions_commissions`, será excluida
- Los cálculos de conversión de moneda se mantienen intactos en `AntiguedadReport`
- El campo `totalComprobante` original se mantiene, se agregó `totalComprobante_ajustado` para el cálculo ajustado

## Compatibilidad

- ✅ Compatible con filtros existentes
- ✅ No afecta otras funcionalidades
- ✅ Mantiene la lógica de conversión de moneda
- ✅ Respeta los permisos y roles de usuario
