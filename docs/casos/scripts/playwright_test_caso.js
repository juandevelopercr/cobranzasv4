const { chromium } = require('playwright');

const BASE = 'http://127.0.0.1:8020';
const SS = '/tmp/claude-1000/-home-caceres-dev-cobranzasv4/73faeb29-e48d-4b23-8053-09116480e03d/scratchpad/screenshots';

(async () => {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1200 } });

  const errors = [];
  page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  page.on('pageerror', err => errors.push('pageerror: ' + err.message));

  await page.goto(`${BASE}/__qa-login/1`, { waitUntil: 'domcontentloaded' });
  await page.goto(`${BASE}/casos/scotiabank`, { waitUntil: 'networkidle', timeout: 30000 });

  const numeroFilter = page.locator('table thead tr').nth(1).locator('input').first();
  await numeroFilter.fill('48221');
  await page.waitForTimeout(1200);

  const checkbox = page.locator('tbody tr').first().locator('input[type="checkbox"]');
  await checkbox.check();
  await page.waitForTimeout(300);
  await page.locator('button:has-text("Editar")').first().click();
  await page.waitForTimeout(2000);

  // Capturar el input de Honorarios Totales, limpiarlo
  const honorariosInput = page.locator('.js-input-thonorarios_totales').first();
  await honorariosInput.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);
  await page.screenshot({ path: `${SS}/06-terminacion-antes.png` });

  const honorariosVisible = await honorariosInput.count();
  console.log('Input honorarios totales encontrado:', honorariosVisible);

  if (honorariosVisible > 0) {
    const before = await honorariosInput.inputValue();
    console.log('Valor antes:', before);
    await honorariosInput.click({ clickCount: 3 });
    await honorariosInput.press('Control+A');
    await honorariosInput.press('Delete');
    await page.waitForTimeout(300);
    await page.screenshot({ path: `${SS}/07-campo-vacio.png` });

    // Guardar
    const saveBtn = page.locator('button[wire\\:click="update"]').first();
    await saveBtn.scrollIntoViewIfNeeded();
    await saveBtn.click({ force: true });
    await page.waitForTimeout(2500);
    await page.screenshot({ path: `${SS}/08-despues-guardar.png`, fullPage: true });

    // Revisar si aparecio un cuadro de error SQL (rojo)
    const errorBox = await page.locator('text=/SQLSTATE|Error al actualizar/i').count();
    console.log('Cuadro de error SQL visible:', errorBox > 0 ? 'SI (FALLO)' : 'NO (correcto)');

    // Restaurar el valor original para no dejar el caso real modificado
    await honorariosInput.click({ clickCount: 3 });
    await honorariosInput.press('Control+A');
    await honorariosInput.type(before.replace(/[^0-9.]/g, ''));
    await page.waitForTimeout(300);
    await saveBtn.scrollIntoViewIfNeeded();
    await saveBtn.click({ force: true });
    await page.waitForTimeout(2000);
    console.log('Valor restaurado a:', before);
  }

  if (errors.length) console.log('Errores JS:', errors.slice(0, 10));

  await browser.close();
})().catch(e => {
  console.error('FALLO:', e.message);
  process.exit(1);
});
