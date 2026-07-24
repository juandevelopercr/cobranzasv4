# Plan de pruebas — Coocique

Carpeta de vistas: `resources/views/livewire/casos/partials/coocique/panels/`

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
| `afecha_aprobacion_remate` | Fecha Aprobación de Remate |  |
| `afecha_protocolizacion` | Fecha Protocolización |  |
| `afecha_senalamiento_puesta_posesion` | Fecha Señalamiento Puesta Posesión |  |
| `apuesta_posesion` | Puesta Posesión |  |
| `agastos_legales` | Gastos Legales | 💰 dinero/numérico |
| `ahonorarios_totales` | Honorarios Totales Colones | 💰 dinero/numérico |
| `anumero_placa1` | Número Placa |  |
| `anumero_placa2` | Número Placa |  |
| `acolisiones_embargos_anotaciones` | Colisiones Embargos Anotaciones |  |
| `anumero_marchamo` | Nùmero Marchamo |  |
| `afirma_legal` | Firma Legal |  |
| `afecha_registro` | Fecha de Registro |  |
| `afecha_presentacion_protocolizacion` | Fecha Presentación Protocolización |  |
| `afecha_inscripcion` | Fecha de Inscripción |  |
| `afecha_terminacion` | Fecha de Terminación |  |
| `afecha_suspencion_arreglo` | Fecha Suspención Arreglo |  |
| `ajustificacion_casos_protocolizados_embargo` | Justificación Casos Protocolizados Embargo |  |
| `aestado_proceso_general_id` | Estado Proceso General |  |
| `afecha_informe_ultima_gestion` | Fecha Informe última Gestión |  |
| `atipo_expediente` | Tipo Expediente |  |
| `areasignaciones` | Reasignaciones |  |
| `afecha_presentacion_embargo` | Fecha de presentación de embargo |  |
| `afecha_arreglo_pago` | Fecha de arreglo del pago |  |
| `afecha_pago` | Fecha de pago |  |
| `amonto_cancelar` | Monto que debe cancelar | 💰 dinero/numérico |
| `amonto_incobrable` | Monto incobrable | 💰 dinero/numérico |
| `acontacto_telefonico` | Contacto telefónico |  |
| `acorreo` | Correo |  |
| `fecha_activacion` | Fecha de activación |  |
| `codigo_activacion` | Código de activación |  |

### Arreglo de pago (`arreglo-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `afecha_aprobacion_arreglo` | Fecha de suspención por arreglo |  |
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

### Filtro 2 (`filtro2-caso.blade.php`)

| Campo (wire:model) | Etiqueta | Tipo probable |
|---|---|---|
| `f2causa_remate` | Causa de remate |  |
| `f2publicacion_edicto` | Publicaciòn de edicto |  |
| `f2fecha_publicacion_edicto` | Fecha de publicaciòn del edicto |  |
| `f2tiempo_concedido_edicto` | Tiempo concedido en el edicto |  |
| `f2preclusion_tiempo` | Preclusiòn del tiempo |  |
| `f2estado_remanente` | Estados remanente filtro2 |  |

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
| `psaldo_de_seguros` | Saldo De Seguros | 💰 dinero/numérico |
| `psaldo_de_multas` | Saldo De Multas | 💰 dinero/numérico |
| `pfecha_pago_multas_y_seguros` | Fecha Pago Multas Y Seguros | 💰 dinero/numérico |
| `pfecha_asignacion_caso` | Fecha Asignación de Caso |  |
| `abogado_id` | Abogado |  |
| `asistente1_id` | Asistente #1 |  |
| `asistente2_id` | Asistente #2 |  |
| `pdepartamento_solicitante` | Departamento Solicitante |  |
| `pfecha_e_instruccion_levantamiento` | Fecha e instrucciòn de levantamiento |  |
| `pnumero_operacion1` | Número Operación #1 |  |
| `pnumero_operacion2` | Número Operación #2 |  |
| `pnumero_contrato` | Número de Contrato |  |
| `pnombre_demandado` | Nombre del Demandado |  |
| `pnumero_cedula` | Número de Cédula del demandado |  |
| `pnombre_arrendatario` | Nombre del arrendatario |  |
| `pcedula_arrendatario` | Cèdula del arrendatario |  |
| `pcorreo_demandado_deudor_o_arrendatario` | Correo Demandado Deudor O Arrendatario |  |
| `ptelefono_demandado_deudor_o_arrendatario` | Teléfono Demandado Deudor O Arrendatario |  |
| `pnombre_contacto_o_arrendatario` | Nombre Contacto O Arrendatario |  |
| `pnombre_coarrendatario` | Nombre Coarrendatario |  |
| `pcedula_coarrendatario` | Cédula Coarrendatario |  |
| `pcorreo_coarrendatario` | Correo Coarrendatario |  |
| `ptelefono_coarrendatario` | Teléfono Coarrendatario |  |
| `pdatos_codeudor1` | Datos Codeudor #1 (Bullet Point) |  |
| `pdatos_codeudor2` | Datos Codeudor #2 (Bullet Point) |  |
| `pdatos_anotantes` | Datos Anotantes (Bullet Point) |  |
| `pdetalle_garantia` | Detalle Garantia |  |
| `pubicacion_garantia` | Ubicación Garantia |  |
| `pfecha_presentacion_demanda` | Fecha Presentación Demanda |  |
| `psubsidiaria` | Subsidiaria |  |
| `pmueble` | Muebles |  |
| `pinmueble` | Inmuebles |  |
| `pdespacho_judicial_juzgado` | Despacho Judicial Juzgado |  |
| `pnumero_expediente_judicial` | Número Expediente Judicial |  |
| `pmonto_estimacion_demanda` | Monto Estimación Demanda | 💰 dinero/numérico |
| `pexpectativa_recuperacion_id` | Expectativa Recuperación |  |
| `pgastos_legales_caso` | Gastos Legales Caso | 💰 dinero/numérico |
| `pcomentarios_bullet_point` | Comentarios (Bullet Point) |  |
| `pplaca1` | Placa #1 |  |
| `pplaca2` | Placa #2 |  |
| `pcontrato_leasing` | Contrato de Leasing |  |
| `ptitular_contrato` | Titular del contrato de Leasing |  |
| `pcedula_titular` | Cèdula del titular |  |
| `user_create` | Usuario que creó el caso |  |
| `user_update` | Usuario que actualizó el caso |  |
| `pavance_cronologico` | Avance Cronológico |  |
| `acomentarios` | Comentarios |  |

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
| `nfecha_traslado_juzgado` | Fecha Traslado Juzgado |  |
| `nfecha_notificacion_todas_partes` | Fecha Notificación Todas las Partes |  |
| `nmarchamo` | Marchamo |  |
| `nanotaciones` | Anotaciones |  |
| `nfecha_ultima_liquidacion` | Fecha de última liquidación | 💰 dinero/numérico |
| `npartes_notificadas` | Partes Notificadas |  |
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
| `sfecha_captura` | Fecha Captura |  |
| `sfecha_sentencia` | Fecha Sentencia |  |
| `sfecha_remate` | Fecha Remate |  |

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
| `ttraspaso_favor_tercero` | Traspaso a favor de terceros |  |
| `tfecha_envio_borrador_escritura` | Fecha de envio borrador de escritura |  |
| `tborrador_escritura` | Borrador de escritura |  |
| `tfecha_firma_escritura` | Fecha de firma de escritura |  |
| `tfecha_presentacion_escritura` | Fecha de presentaciòn de la escritura |  |
| `tfecha_comunicacion` | Fecha de comunicado para recolecciòn de tìtulo |  |
| `tautorizacion_tercero` | Autorizaciòn a tercero |  |
| `tfecha_entrega_titulo_propiedad` | Fecha de entrega de tìtulo de propiedad |  |
| `tfecha_exclusion` | Fecha de exclusiòn |  |
