# Plan de pruebas — BAC San José

Carpeta de vistas: `resources/views/livewire/casos/partials/bac/panels/`

Antes de probar manualmente, correr la regresión automática (cubre la causa raíz del incidente: guardado con campos de dinero vacíos):

```
php artisan tinker docs/casos/scripts/regression_all_banks.php
```

## Checklist por panel

Para cada panel: abrir el caso, cambiar cada campo marcado 💰 dejándolo primero **vacío** y guardar (no debe dar error SQL), luego con un valor válido y confirmar que persiste al recargar. Para selects/fechas, confirmar que la opción elegida se guarda y no “arrastra” el valor del caso anterior al navegar entre registros (bug ya corregido en ede8998, pero es el patrón de regresión más común en este módulo).


### Aprobación (`aprobacion-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `afecha_aprobacion_remate` | Fecha Aprobación de Remate |  |
| `afecha_protocolizacion` | Fecha Protocolización |  |
| `afecha_senalamiento_puesta_posesion` | Fecha Señalamiento Puesta Posesión |  |
| `apuesta_posesion` | Puesta Posesión |  |
| `agastos_legales` | Gastos Legales | 💰 dinero/numérico |
| `pfecha_curso_demanda` | Fecha curso de la demanda |  |
| `afecha_informe_ultima_gestion` | Fecha Informe última Gestión |  |
| `ahonorarios_totales` | Honorarios Totales Colones | 💰 dinero/numérico |
| `anumero_placa1` | Número Placa |  |
| `acolisiones_embargos_anotaciones` | Colisiones Embargos Anotaciones |  |
| `anumero_marchamo` | Nùmero Marchamo |  |
| `afirma_legal` | Firma Legal |  |
| `afecha_registro` | Fecha de Registro |  |
| `afecha_presentacion_protocolizacion` | Fecha Presentación Protocolización |  |
| `afecha_inscripcion` | Fecha de Inscripción |  |
| `afecha_terminacion` | Fecha de Terminación |  |
| `afecha_suspencion_arreglo` | Fecha de Arreglo de Pago |  |
| `ajustificacion_casos_protocolizados_embargo` | Justificación Casos Protocolizados Embargo |  |
| `aestado_proceso_general_id` | Estado Proceso General |  |
| `atipo_expediente` | Tipo Expediente |  |
| `areasignaciones` | Reasignaciones |  |
| `fecha_activacion` | Fecha de activación |  |
| `codigo_activacion` | Código de activación |  |

### Información general (`info-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `pnumero` | Número |  |
| `fecha_creacion` | Fecha de creación |  |
| `contact_id` | Cliente |  |
| `bank_id` | Bank |  |
| `product_id` | Producto |  |
| `proceso_id` | Proceso |  |
| `currency_id` | Currency |  |
| `psaldo_dolarizado` | Saldo Dolarizado | 💰 dinero/numérico |
| `pfecha_asignacion_caso` | Fecha Asignación de Caso |  |
| `abogado_id` | Abogado |  |
| `asistente1_id` | Asistente #1 |  |
| `asistente2_id` | Asistente #2 |  |
| `pnumero_operacion1` | Número Operación #1 |  |
| `pnumero_operacion2` | Número Operación #2 |  |
| `pnumero_contrato` | Número de Contrato |  |
| `pnombre_demandado` | Nombre del Demandado |  |
| `pnumero_cedula` | Número de Cédula del demandado |  |
| `pdatos_codeudor1` | Datos Codeudor1 (Bullet Point) |  |
| `pdatos_codeudor2` | Datos Codeudor2 (Bullet Point) |  |
| `pdatos_anotantes` | Datos Anotantes (Bullet Point) |  |
| `pdetalle_garantia` | Detalle Garantia |  |
| `pubicacion_garantia` | Ubicación Garantia |  |
| `pfecha_presentacion_demanda` | Fecha Presentación Demanda |  |
| `pnumero_expediente_judicial` | Número Expediente Judicial |  |
| `pdespacho_judicial_juzgado` | Despacho Judicial Juzgado |  |
| `pmonto_estimacion_demanda` | Monto Estimación Demanda | 💰 dinero/numérico |
| `pexpectativa_recuperacion_id` | Expectativa Recuperación |  |
| `pestatus_operacion` | Estatus de Operaciòn |  |
| `pgastos_legales_caso` | Gastos Legales Caso | 💰 dinero/numérico |
| `pmonto_retencion_colones` | Monto retención ¢ | 💰 dinero/numérico |
| `pmonto_retencion_dolares` | Monto retención $ | 💰 dinero/numérico |
| `pinmueble` | Inmuebles |  |
| `pvehiculo` | Vehículo |  |
| `user_create` | Usuario que creó el caso |  |
| `user_update` | Usuario que actualizó el caso |  |
| `empresa` | Nombre de la Empresa |  |
| `pplazo_arreglo_pago` | Plazo arreglo de pago |  |
| `pavance_cronologico` | Avance Cronológico |  |

### Notificadores / Capturadores (`notifiadores-capturadores-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `f1fecha_asignacion_capturador` | Fecha de asignación al capturador |  |
| `capturador_id` | Capturador |  |
| `caso_servicio_capturador_id` | Servicio |  |
| `f1fecha_asignacion_notificador` | Fecha de asignación al notificador |  |
| `notificador_id` | Notificador |  |
| `caso_servicio_notificador_id` | Servicio |  |

### Notificación (`notificacion-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `nfecha_traslado_juzgado` | Fecha Traslado Juzgado |  |
| `nfecha_entrega_requerimiento_pago` | Fecha de entrega requerimiento pago |  |
| `nfecha_entrega_orden_captura` | Fecha de entrega orden de captura |  |
| `nfecha_notificacion_todas_partes` | Fecha Notificación Todas las Partes |  |
| `nfecha_ultima_liquidacion` | Fecha de última liquidación | 💰 dinero/numérico |
| `ncomentarios` | Comentarios |  |
| `npartes_notificadas` | Partes Notificadas |  |

### Sentencia (`sentencia-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `sfecha_captura` | Fecha Captura |  |
| `sfecha_sentencia` | Fecha Sentencia |  |
| `sfecha_remate` | Fecha Remate |  |
| `pfecha_primer_giro` | Fecha 1er Giro |  |
