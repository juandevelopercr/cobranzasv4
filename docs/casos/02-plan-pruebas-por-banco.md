# Plan de pruebas por banco — módulo Casos

Objetivo: verificar, banco por banco, que (a) el guardado no truena con
ningún campo de dinero vacío, (b) las etiquetas corregidas se ven bien, y
(c) no aparecen regresiones nuevas al navegar entre paneles/registros.

Este módulo no tiene navegador headless disponible en el entorno donde se
hizo esta revisión (no hay `chromium-cli` ni Playwright instalado), así que
la verificación automática se hizo con el harness oficial de pruebas de
Livewire (`Livewire::test()`), que ejecuta el mismo código PHP que dispara un
clic real en el navegador (`validate()`, `update()`, render de la vista),
dentro de una transacción que siempre revierte. **Falta la verificación
visual real en navegador** — este documento sirve como checklist para
hacerla a mano, o para instalar herramienta de automatización y correr los
scripts como guía.

## 1. Regresión automática (cubre el bug del incidente en los 11 bancos)

```bash
php artisan tinker docs/casos/scripts/regression_all_banks.php
```

Por cada banco: toma el último caso existente, abre el formulario
(`edit`), deja vacío el primer campo `numeric` de sus reglas de validación,
llama a `update()` dentro de una transacción que revierte, y reporta si hubo
error SQL o de validación. No modifica datos reales.

Para auditar además qué columnas de dinero están guardadas como texto libre
en la base (input para el plan de migración):

```bash
php artisan tinker docs/casos/scripts/auditar_campos_numericos_como_string.php
```

## 2. Checklist manual por banco

Cada archivo lista, panel por panel, los campos reales de esa vista
(extraídos directo de los `.blade.php`, no a mano) con los de dinero
marcados 💰 — son los de mayor riesgo por el bug de guardado corregido hoy.

| Banco | Detalle |
|---|---|
- [BAC San José](bancos/bac.md) — 78 campos en 5 paneles
- [Banco General](bancos/banco-general.md) — 60 campos en 5 paneles
- [Financiera CAFSA](bancos/cafsa.md) — 145 campos en 16 paneles
- [Cartera Comprada](bancos/cartera.md) — 69 campos en 5 paneles
- [Coocique](bancos/coocique.md) — 188 campos en 16 paneles
- [Coocique2](bancos/coocique2.md) — 188 campos en 16 paneles
- [Banco Davivienda](bancos/davivienda.md) — 211 campos en 16 paneles
- [Banco Lafise](bancos/lafise.md) — 164 campos en 16 paneles
- [DAVIBANK (código interno "Scotiabank")](bancos/scotiabank.md) — 188 campos en 16 paneles — **prioridad: es el banco del incidente**
- [DAVIBANK-BCH (código interno "Scotiabank BCH")](bancos/scotiabank-bch.md) — 186 campos en 16 paneles
- [Terceros](bancos/terceros.md) — 185 campos en 16 paneles

Nota: BAC, Banco General y Cartera Comprada usan un formulario "simple" (5
paneles: info, aprobación, notificadores/capturadores, notificación,
sentencia) — no tienen panel de Terminación, así que no cargaban el bug de
la etiqueta ni tienen tantos campos de dinero expuestos.

## 3. Prioridad sugerida

1. **DAVIBANK / scotiabank** — es el banco que reportó el incidente. Probar
   primero el caso real, expediente 12-015907-1170-CJ (id 482), y el panel
   de Terminación completo.
2. Los otros 6 bancos con el mismo copy-paste de etiqueta
   (davivienda, cafsa, coocique, coocique2, lafise, scotiabank-bch) —
   confirmar que la corrección de label no rompió nada visualmente.
3. El resto (bac, banco-general, cartera, terceros) — cobertura general del
   fix de `cleanEmptyNumericFields()`, menor urgencia porque no fueron
   reportados.

## 4. Qué reportar como bug real (no ruido)

- Guardar y que tire un error 500 / SQL.
- Un campo que muestre el valor del caso anterior después de cambiar de
  registro sin recargar la página (patrón de regresión ya visto antes,
  commits `ede8998` y `f8b7b7a`).
- Una etiqueta que no corresponda al campo (comparar contra el nombre de
  columna en `casos` si hay duda).
- Un valor de dinero que se vea con más o menos decimales de los que el
  usuario ingresó.
