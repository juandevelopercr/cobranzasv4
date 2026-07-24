// Arnes generico de certificacion: abre un caso, prueba TODOS los campos
// visibles del formulario (texto, fecha, select2, dinero), guarda, verifica,
// restaura, guarda de nuevo. Uso:
//   node certify.js <bank-slug> <numero-filtro> <nombre-para-reporte>
//
// IMPORTANTE: cada interaccion vuelve a buscar el campo por su #id justo
// antes de tocarlo (no reutiliza un handle capturado al inicio), porque
// Livewire puede re-renderizar partes del DOM a mitad del lote y un handle
// viejo puede terminar apuntando al campo equivocado.
const { chromium } = require('playwright');

const BASE = 'http://127.0.0.1:8030';
const SS = '/tmp/claude-1000/-home-caceres-dev-cobranzasv4/73faeb29-e48d-4b23-8053-09116480e03d/scratchpad/screenshots';

const bankSlug = process.argv[2];
const numeroFiltro = process.argv[3];
const label = process.argv[4] || bankSlug;

if (!bankSlug || !numeroFiltro) {
  console.error('Uso: node certify.js <bank-slug> <numero-filtro> [label]');
  process.exit(1);
}

function byId(page, id) {
  return page.locator(`[id="${id}"]`).first();
}

(async () => {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1200 } });
  page.setDefaultTimeout(5000);

  const consoleErrors = [];
  page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });
  page.on('pageerror', err => consoleErrors.push('pageerror: ' + err.message));

  await page.goto(`${BASE}/__qa-login/1`, { waitUntil: 'domcontentloaded' });
  await page.goto(`${BASE}/casos/${bankSlug}`, { waitUntil: 'networkidle', timeout: 30000 });

  const numeroFilter = page.locator('table thead tr').nth(1).locator('input').first();
  await numeroFilter.fill(numeroFiltro);
  await page.waitForTimeout(1200);

  const rowCount = await page.locator('tbody tr').count();
  if (rowCount === 0) {
    console.log(JSON.stringify({ label, error: `No se encontro ningun caso con numero=${numeroFiltro}` }));
    await browser.close();
    process.exit(2);
  }

  const checkbox = page.locator('tbody tr').first().locator('input[type="checkbox"]');
  await checkbox.check();
  await page.waitForTimeout(300);
  await page.locator('button:has-text("Editar")').first().click();
  await page.waitForTimeout(2500);

  const scope = '#navs-justified-home form';
  await page.waitForSelector(scope, { timeout: 15000 });

  // --- enumerar campos (solo para tomar id/clase/tipo, sin guardar el handle) ---
  const rawHandles = await page.locator(`${scope} input:visible, ${scope} textarea:visible, ${scope} select:visible`).all();

  const fields = [];
  const seenIds = new Set();
  for (const h of rawHandles) {
    const tag = await h.evaluate(el => el.tagName.toLowerCase());
    const type = await h.getAttribute('type');
    const id = await h.getAttribute('id');
    const cls = (await h.getAttribute('class')) || '';
    const wireModel = (await h.getAttribute('wire:model')) || (await h.getAttribute('wire:model.live'));
    if (type === 'checkbox' || type === 'radio' || type === 'file' || type === 'hidden') continue;
    if (!id || id === 'perPage') continue;
    if (seenIds.has(id)) continue; // evitar duplicados si el mismo id aparece 2 veces
    const disabled = await h.isDisabled().catch(() => false);
    const readonly = await h.getAttribute('readonly').catch(() => null);
    if (disabled || readonly !== null) continue;
    seenIds.add(id);
    fields.push({ tag, type, id, cls, wireModel });
  }

  console.log(`[${label}] Campos detectados: ${fields.length}`);

  // --- clasificar y capturar valor original (por id, fresco) ---
  for (const f of fields) {
    try {
      f.kind = f.tag === 'select' ? 'select2'
        : f.cls.includes('date-picke') ? 'fecha'
        : f.cls.includes('js-input-') ? 'dinero'
        : f.type === 'number' ? 'numero'
        : f.type === 'email' || /correo|email/i.test(f.id || '') ? 'correo'
        : f.tag === 'textarea' ? 'texto-largo'
        : 'texto';
      f.original = await byId(page, f.id).inputValue();
    } catch (e) {
      f.kind = 'desconocido';
      f.original = null;
      f.captureError = e.message.split('\n')[0];
    }
  }

  // --- fase 1: cambiar todos los campos a un valor de prueba ---
  const T = 3000;
  let changed = 0;
  let i = 0;
  for (const f of fields) {
    i++;
    try {
      const loc = byId(page, f.id);
      await loc.scrollIntoViewIfNeeded({ timeout: T }).catch(() => {});
      if (f.kind === 'select2') {
        const options = await loc.locator('option').count();
        if (options > 1) {
          const currentIdx = await loc.evaluate(el => el.selectedIndex);
          const targetIdx = currentIdx === 1 && options > 2 ? 2 : 1;
          await loc.selectOption({ index: targetIdx }, { timeout: T });
          await page.waitForLoadState('networkidle', { timeout: 3000 }).catch(() => {});
          changed++;
        }
      } else if (f.kind === 'fecha') {
        await loc.fill('15-03-2024', { timeout: T });
        await page.keyboard.press('Escape');
        changed++;
      } else if (f.kind === 'dinero') {
        try {
          await loc.click({ clickCount: 3, timeout: T });
        } catch (e) {
          await loc.click({ clickCount: 3, timeout: T, force: true });
        }
        await page.keyboard.press('Control+A');
        await page.keyboard.press('Delete');
        await page.waitForLoadState('networkidle', { timeout: 3000 }).catch(() => {});
        changed++;
      } else if (f.kind === 'numero') {
        await loc.fill('123', { timeout: T }).catch(() => loc.fill('123', { timeout: T, force: true }));
        changed++;
      } else if (f.kind === 'correo') {
        await loc.fill('qa-test@example.com', { timeout: T }).catch(() => loc.fill('qa-test@example.com', { timeout: T, force: true }));
        changed++;
      } else if (f.kind === 'texto' || f.kind === 'texto-largo') {
        await loc.fill('TEST-QA', { timeout: T }).catch(() => loc.fill('TEST-QA', { timeout: T, force: true }));
        changed++;
      }
    } catch (e) {
      f.setError = e.message.split('\n')[0];
    }
    if (i % 10 === 0) console.log(`  ... progreso modificar: ${i}/${fields.length}`);
  }
  console.log(`[${label}] Campos modificados: ${changed}`);
  await page.screenshot({ path: `${SS}/cert-${label}-01-modificado.png`, fullPage: true });

  // --- guardar (prueba real) ---
  const saveBtnGlobal = page.locator('button[wire\\:click="update"]').first();
  await saveBtnGlobal.scrollIntoViewIfNeeded();
  await saveBtnGlobal.click({ force: true });
  await page.waitForTimeout(2500);
  await page.screenshot({ path: `${SS}/cert-${label}-02-guardado.png`, fullPage: true });

  const errorBoxCount = await page.locator('text=/SQLSTATE|Error al actualizar|Whoops/i').count();
  const validationErrorCount = await page.locator('text=/corrija los siguientes errores/i').count();
  const successToast = await page.locator('text=/actualizado correctamente|creado correctamente/i').count();
  let validationErrorTexts = [];
  if (validationErrorCount > 0) {
    validationErrorTexts = await page.locator('.alert li, .text-danger').allTextContents().catch(() => []);
    validationErrorTexts = validationErrorTexts.map(t => t.trim()).filter(Boolean);
  }

  // --- fase 2: restaurar valores originales (por id, fresco) ---
  let restored = 0;
  i = 0;
  for (const f of fields) {
    i++;
    try {
      if (f.original === null) continue;
      const loc = byId(page, f.id);
      await loc.scrollIntoViewIfNeeded({ timeout: T }).catch(() => {});
      if (f.kind === 'select2') {
        await loc.selectOption(f.original, { timeout: T });
      } else if (f.kind === 'fecha') {
        await loc.fill(f.original, { timeout: T });
        await page.keyboard.press('Escape');
      } else if (f.kind === 'dinero') {
        const cleanVal = f.original.replace(/[^0-9.\-]/g, '');
        await loc.fill(cleanVal, { timeout: T }).catch(() => loc.fill(cleanVal, { timeout: T, force: true }));
        await page.waitForTimeout(150);
      } else if (f.kind === 'numero' || f.kind === 'correo' || f.kind === 'texto' || f.kind === 'texto-largo') {
        await loc.fill(f.original, { timeout: T }).catch(() => loc.fill(f.original, { timeout: T, force: true }));
      }
      restored++;
    } catch (e) {
      f.restoreError = e.message.split('\n')[0];
    }
    if (i % 10 === 0) console.log(`  ... progreso restaurar: ${i}/${fields.length}`);
  }
  console.log(`[${label}] Campos restaurados: ${restored}`);

  await saveBtnGlobal.scrollIntoViewIfNeeded();
  await saveBtnGlobal.click({ force: true });
  await page.waitForTimeout(2500);
  const errorBoxCountRestore = await page.locator('text=/SQLSTATE|Error al actualizar|Whoops/i').count();
  const successToastRestore = await page.locator('text=/actualizado correctamente|creado correctamente/i').count();

  const summary = {
    label,
    bankSlug,
    numeroFiltro,
    totalCampos: fields.length,
    porTipo: {
      texto: fields.filter(f => f.kind === 'texto').length,
      textoLargo: fields.filter(f => f.kind === 'texto-largo').length,
      fecha: fields.filter(f => f.kind === 'fecha').length,
      select2: fields.filter(f => f.kind === 'select2').length,
      dinero: fields.filter(f => f.kind === 'dinero').length,
      numero: fields.filter(f => f.kind === 'numero').length,
      correo: fields.filter(f => f.kind === 'correo').length,
      desconocido: fields.filter(f => f.kind === 'desconocido').length,
    },
    guardadoPrueba: { errorBox: errorBoxCount > 0, validationError: validationErrorCount > 0, successToast: successToast > 0, validationErrorTexts },
    guardadoRestauracion: { errorBox: errorBoxCountRestore > 0, successToast: successToastRestore > 0 },
    camposConErrorAlSetear: fields.filter(f => f.setError).map(f => ({ id: f.id, wireModel: f.wireModel, error: f.setError })),
    camposConErrorAlRestaurar: fields.filter(f => f.restoreError).map(f => ({ id: f.id, wireModel: f.wireModel, error: f.restoreError })),
    erroresConsola: consoleErrors.slice(0, 20),
  };

  console.log('RESULTADO_JSON:' + JSON.stringify(summary));

  await browser.close();
})().catch(e => {
  console.error('FALLO:', e.message);
  process.exit(1);
});
