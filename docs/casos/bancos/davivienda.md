# Plan de pruebas — Banco Davivienda

Carpeta de vistas: `resources/views/livewire/casos/partials/davivienda/panels/`

Antes de probar manualmente, correr la regresión automática (cubre la causa raíz del incidente: guardado con campos de dinero vacíos):

```
php artisan tinker docs/casos/scripts/regression_all_banks.php
```

## Checklist por panel

Para cada panel: abrir el caso, cambiar cada campo marcado 💰 dejándolo primero **vacío** y guardar (no debe dar error SQL), luego con un valor válido y confirmar que persiste al recargar. Para selects/fechas, confirmar que la opción elegida se guarda y no “arrastra” el valor del caso anterior al navegar entre registros (bug ya corregido en ede8998, pero es el patrón de regresión más común en este módulo).


### Anotaciones (`anotaciones-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `dnombre_notario` | Nombre del notario |  |
| `dnumero_carnet` | Nùmero de carnet |  |
| `dcorreo_electronico` | Correo electrònico |  |
| `dnumero_telefonico` | Nùmero telèfonico |  |
| `destado_casos_con_anotaciones` | Estado Casos con anotaciones |  |
| `dfecha_interposicion_denuncia` | Fecha de interposiciòn de la denuncia |  |
| `ddespacho_judicial_juzgado_id` | Despacho Judicial |  |
| `dnumero_expediente` | Nùmero de expediente |  |
| `dresultado_sentencia` | Resultado de la sentencia |  |
| `dgastos_microfilm` | Gastos Microfilm | 💰 dinero/numérico |
| `dhonorarios` | Honorarios | 💰 dinero/numérico |
| `aavance_cronologico` | Avance Cronológico |  |

### Aprobación (`aprobacion-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `afecha_firmeza_aprobacion_remate` | Fecha Firmeza Aprobación Remate |  |
| `abienes_adjudicados` | Bienes Adjudicados |  |
| `aestado_proceso_general_id` | Estado Proceso General |  |
| `afecha_senalamiento_puesta_posesion` | Fecha Señalamiento Puesta Posesión |  |
| `apuesta_posesion` | Puesta Posesión |  |
| `afecha_suspencion_arreglo` | Fecha Suspención Arreglo |  |
| `asaldo_capital_operacion` | Saldo Capital de la Operación Colones | 💰 dinero/numérico |
| `asaldo_capital_operacion_usd` | Saldo Capital de la Operación Dólares | 💰 dinero/numérico |
| `aestimacion_demanda_en_presentacion` | Estimación Demanda en la Presentación Colones | 💰 dinero/numérico |
| `aestimacion_demanda_en_presentacion_usd` | Estimación Demanda en la Presentación Dólares | 💰 dinero/numérico |
| `liquidacion_intereses_aprobada_crc` | Liquidacion de intereses aprobada Colones | 💰 dinero/numérico |
| `liquidacion_intereses_aprobada_usd` | Liquidacion de intereses aprobada Dólares | 💰 dinero/numérico |
| `agastos_legales` | Gastos Legales | 💰 dinero/numérico |
| `abufete` | Bufete |  |
| `pgastos_legales_caso` | Gastos Legales Caso | 💰 dinero/numérico |
| `ahonorarios_totales` | Honorarios Totales Colones | 💰 dinero/numérico |
| `ahonorarios_totales_usd` | Honorarios Totales Dólares | 💰 dinero/numérico |
| `afecha_terminacion` | Fecha de Terminación |  |
| `tiempo_dias` | Tiempo en Días |  |
| `tiempo_annos` | Tiempo en Años |  |
| `pretenciones` | Monto de retenciones | 💰 dinero/numérico |
| `nfecha_ultima_liquidacion` | Fecha de última liquidación | 💰 dinero/numérico |
| `pmonto_retencion_colones` | Monto retención ¢ | 💰 dinero/numérico |
| `pmonto_retencion_dolares` | Monto retención $ | 💰 dinero/numérico |
| `fecha_activacion` | Fecha de activación |  |
| `codigo_activacion` | Código de activación |  |
| `acarga_gastos_legales` | Carga de Gastos Legales | 💰 dinero/numérico |
| `agastos_mas_honorarios_acumulados` | Gastos + Honorarios acumulados | 💰 dinero/numérico |
| `ahonorarios_iniciales` | Honorarios iniciales | 💰 dinero/numérico |
| `adiferencia_demanda_presentada` | Diferencia P/ Demanda Presentada |  |
| `adiferencia_sentencia_afavor` | Diferencia P/ Sentencia a favor |  |
| `adiferencia_sentencia_enfirme` | Diferencia P/ Sentencia en firme |  |
| `adiferencia_liquidacion_de_sentencia_enfirme` | Diferencia P/ Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_segunda_liquidacion_de_sentencia_enfirme` | Diferencia P/ 2da Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_tercera_liquidacion_de_sentencia_enfirme` | Diferencia P/ 3ra Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_cuarta_liquidacion_de_sentencia_enfirme` | Diferencia P/ 4ta Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_quinta_liquidacion_de_sentencia_enfirme` | Diferencia P/ 5ta Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_sexta_liquidacion_de_sentencia_enfirme` | Diferencia P/ 6ta Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_septima_liquidacion_de_sentencia_enfirme` | Diferencia P/ 7ma Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_octava_liquidacion_de_sentencia_enfirme` | Diferencia P/ 8va Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_novena_liquidacion_de_sentencia_enfirme` | Diferencia P/ 9na Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_liquidacion_de_sentencia_enfirme` | Diferencia P/ 10ma Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_primera_liquidacion_de_sentencia_enfirme` | Diferencia P/ 11va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_segunda_liquidacion_de_sentencia_enfirme` | Diferencia P/ 12va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_tercera_liquidacion_de_sentencia_enfirme` | Diferencia P/ 13va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_cuarta_liquidacion_de_sentencia_enfirme` | Diferencia P/ 14va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_quinta_liquidacion_de_sentencia_enfirme` | Diferencia P/ 15va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_sexta_liquidacion_de_sentencia_enfirme` | Diferencia P/ 16va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_septima_liquidacion_de_sentencia_enfirme` | Diferencia P/ 17va Liquidaciòn de sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_octava_liquidacion_de_sentencia_enfirme` | Diferencia P/ 18va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `adiferencia_decima_novena_liquidacion_de_sentencia_enfirme` | Diferencia P/ 19va Liquidaciòn sentencia en firme | 💰 dinero/numérico |
| `agastos_legales_iniciales` | Gastos Legales Iniciales | 💰 dinero/numérico |
| `adiferencia_gastos_legales` | Diferencia de gastos legales | 💰 dinero/numérico |
| `anumero_grupo` | No. Grupo |  |

### Arreglo de pago (`arreglo-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `afecha_aprobacion_arreglo` | Fecha de suspención por arreglo |  |
| `acomentarios` | Comentarios |  |
| `aregistro_pago` | Registro de pago |  |
| `afecha_envio_cotizacion_gasto` | Fecha envio cotizaciòn gastos traspaso | 💰 dinero/numérico |
| `atraspaso_tercero` | Traspaso con tercero |  |

### Bienes (`bienes-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `bapersonamiento_formal` | Asesoramiento formal |  |
| `bfecha_entrega_poder` | Fecha de entrega del poder |  |
| `bsumaria` | Sumaria |  |
| `bcausa` | Causa |  |
| `bfecha_levantamiento_gravamen` | Fecha de levantamiento de gravamen |  |
| `bestado_levantamiento_id` | Estado de Marchamo |  |
| `bproveedores_servicios` | Proveedores de servicios |  |
| `bgastos_proceso` | Gastos del proceso | 💰 dinero/numérico |
| `bhonorarios_levantamiento` | Honorarios levantamiento | 💰 dinero/numérico |
| `bhonorarios_comision` | Honorarios comisiòn CCC | 💰 dinero/numérico |
| `bhonorarios_totales` | Honorarios totales | 💰 dinero/numérico |

### Denuncia (`denuncia-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `ranotacion` | Anotaciòn |  |
| `rmarchamo_al_dia` | Marchamo al dìa |  |
| `rpendiente` | Pendiente |  |
| `rcausa` | Causa |  |
| `rfecha_desinscripcion` | Fecha de desinscripciòn |  |
| `rhonorario_escritura_inscripcion` | Honorario escritura desinscripciòn | 💰 dinero/numérico |
| `rgastos_impuestos` | Gastos (Impuestos) | 💰 dinero/numérico |

### Facturación (`facturacion-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `fhonorarios_levantamiento` | Honorarios Levantamiento | 💰 dinero/numérico |
| `fcomision_ccc` | Comisión CCC | 💰 dinero/numérico |
| `fhonorarios_totales` | Honorarios Totales | 💰 dinero/numérico |

### Filtro 1 (`filtro1-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `f1fecha_asignacion_capturador` | Fecha de asignaciòn a capturador |  |
| `f1proveedor_servicio` | Proveedor del servicio |  |
| `f1estado_captura` | Estado de captura filtro1 |  |
| `f1honorarios_capturador` | Honorarios capturador | 💰 dinero/numérico |
| `f1honorarios_comision` | Honorarios comisiòn CCC | 💰 dinero/numérico |
| `f1avance_cronologico` | Avance Cronológico |  |

### Filtro 2 (`filtro2-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `f2causa_remate` | Causa de remate |  |
| `f2publicacion_edicto` | Publicaciòn de edicto |  |
| `f2fecha_publicacion_edicto` | Fecha de publicaciòn del edicto |  |
| `f2tiempo_concedido_edicto` | Tiempo concedido en el edicto |  |
| `f2preclusion_tiempo` | Preclusiòn del tiempo |  |
| `f2estado_remanente` | Estados remanente filtro2 |  |
| `f2avance_cronologico` | Avance Cronológico |  |

### Información general (`info-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `pnumero` | Número |  |
| `fecha_creacion` | Fecha de creación |  |
| `contact_id` | Cliente |  |
| `pnombre_apellidos_deudor` | Apellidos y Nombre del Deudor |  |
| `pcedula_deudor` | Cèdula deudor |  |
| `bank_id` | Bank |  |
| `product_id` | Tipo de Crédito |  |
| `proceso_id` | Proceso |  |
| `currency_id` | Currency |  |
| `pfecha_asignacion_caso` | Fecha Asignación de Caso |  |
| `pnumero_operacion1` | Número Operación #1 |  |
| `pnumero_operacion2` | Número Operación #2 |  |
| `pexpectativa_recuperacion_id` | Expectativa Recuperación |  |
| `pestatus_operacion` | Estatus de Operaciòn |  |
| `pnumero_expediente_judicial` | Número Expediente Judicial |  |
| `pdespacho_judicial_juzgado` | Despacho Judicial Juzgado |  |
| `pcomprador` | Comprador |  |
| `ppoderdante_id` | Poderdante |  |
| `pultima_gestion_cobro_administrativo` | Fecha última gestión cobro Administrativo |  |
| `pfecha_ingreso_cobro_judicial` | Fecha de ingreso a cobro judicial |  |
| `pfecha_devolucion_demanda_firma` | Fecha devolución de demanda para firma |  |
| `pfecha_escrito_demanda` | Fecha de escrito de demanda |  |
| `pfecha_presentacion_demanda` | Fecha Presentación Demanda |  |
| `pfecha_curso_demanda` | Fecha curso de la demanda |  |
| `abogado_id` | Abogado |  |
| `asistente1_id` | Asistente #1 |  |
| `asistente2_id` | Asistente #2 |  |
| `pente` | Ente |  |
| `pmonto_prima` | Monto Prima | 💰 dinero/numérico |
| `pplazo_arreglo_pago` | Plazo arreglo de pago |  |
| `pmonto_arreglo_pago` | Monto arreglo de pago | 💰 dinero/numérico |
| `pmonto_cuota` | Monto cuota | 💰 dinero/numérico |
| `pno_cuota` | No. cuota | 💰 dinero/numérico |
| `pestado_arreglo` | Estado arreglo |  |
| `pnombre_persona_juridica` | Nombre de la Persona Jurídica |  |
| `pdatos_codeudor1` | Datos Codeudor #1 (Bullet Point) |  |
| `pdatos_fiadores` | Datos de los Fiadores |  |
| `sfecha_captura` | Fecha Captura |  |
| `pnumero_cedula_juridica` | Número de cédula jurídica |  |

### Levantamiento (`levantamiento-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `lasesoramiento_formal` | Asesoramiento formal |  |
| `lfecha_entrega_poder` | Fecha de entrega del poder |  |
| `lsumaria` | Sumaria |  |
| `lcausa` | Causa |  |
| `ldespacho_judicial_juzgado_id` | Despacho Judicial |  |
| `lfecha_levantamiento_gravamen` | Fecha de levantamiento de gravamen |  |
| `lfecha_comunicado_banco` | Fecha de comunicado al banco |  |
| `lestado_levantamiento_id` | Estado de levantemiento del gravamen |  |
| `lproveedores_servicio` | Proveedores de servicios |  |
| `lavance_cronologico` | Avance Cronológico |  |

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
| `nestado_actual_primera_notificacion` | Estado actual primera notificación demandados |  |
| `noposicion_demanda` | Oposición de Demanda |  |
| `nfecha_audiencia` | Fecha de Audiencia |  |
| `ntipo_garantia` | Tipo de Garantia |  |
| `pdetalle_garantia` | Detalle Garantia |  |
| `nembargos_cuentas` | Embargos cuentas |  |
| `nembargos_salarios` | Embargos Salarios |  |
| `nembargos_muebles` | Embargos Muebles |  |
| `nembargos_inmuebles` | Embargos Inmuebles |  |
| `user_create` | Usuario que creó el caso |  |
| `user_update` | Usuario que actualizó el caso |  |
| `navance_cronologico` | Avance Cronológico |  |
| `nfecha_entrega_requerimiento_pago` | Fecha de entrega requerimiento pago |  |
| `nfecha_entrega_orden_captura` | Fecha de entrega orden de captura |  |
| `nfecha_notificacion_todas_partes` | Fecha Notificación Todas las Partes |  |
| `nfecha_ultima_liquidacion` | Fecha de última liquidación | 💰 dinero/numérico |
| `npartes_notificadas` | Partes Notificadas |  |
| `nmarchamo` | Marchamo |  |
| `nanotaciones` | Anotaciones |  |
| `nubicacion_garantia` | Ubicación de la garantía |  |
| `ntalleres_situaciones` | Talleres o situaciones especiales |  |
| `nfecha_notificacion` | Fecha de notificación |  |
| `ncomentarios` | Comentarios |  |
| `nhonorarios_notificacion` | Honorarios por notificación | 💰 dinero/numérico |
| `nhonorarios_cobro_administrativo` | Honorarios cobro administrativo | 💰 dinero/numérico |
| `nexonerado_cobro` | Exonerado de cobro |  |
| `nfecha_pago` | Fecha de pago |  |

### Segmento (`segmento-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `efecha_visita` | Fecha de la visita |  |
| `egestion_a_realizar` | Gestiòn a realizar |  |
| `eestado_cliente_gran_tamano` | Gestiòn a realizar |  |
| `savance_cronologico` | Avance Cronológico |  |

### Sentencia (`sentencia-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `sfecha_primer_remate` | Fecha de celebración de Primer remate |  |
| `sfecha_segundo_remate` | Fecha de celebración de Segundo remate |  |
| `sfecha_tercer_remate` | Fecha de celebración de Tercer remate |  |

### Terminación del proceso (`terminacion-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `afecha_terminacion` | Fecha de Terminación |  |
| `aestado_proceso_general_id` | Estado Proceso General |  |
| `tgastos_legales` | Gastos Legales | 💰 dinero/numérico |
| `thonorarios_totales` | Honorarios Totales | 💰 dinero/numérico |
| `fecha_activacion` | Fecha de activación |  |
| `codigo_activacion` | Código de Activación |  |

### Traspaso a tercero (`traspaso-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `tfecha_traspaso` | Fecha de pago de traspaso |  |
| `thonorarios_traspaso` | Honorarios de traspaso | 💰 dinero/numérico |
| `tgastos_traspaso` | Gastos de traspaso | 💰 dinero/numérico |
| `tfecha_envio_borrador_escritura` | Fecha de envio borrador de escritura |  |
| `tborrador_escritura` | Borrador de escritura |  |
| `tfecha_firma_escritura` | Fecha de firma de escritura |  |
| `tfecha_presentacion_escritura` | Fecha de presentaciòn de la escritura |  |
| `tfecha_comunicacion` | Fecha de comunicado para recolecciòn de tìtulo |  |
| `tautorizacion_tercero` | Autorizaciòn a tercero |  |
| `tfecha_entrega_titulo_propiedad` | Fecha de entrega de tìtulo de propiedad |  |
| `tfecha_exclusion` | Fecha de exclusiòn |  |
