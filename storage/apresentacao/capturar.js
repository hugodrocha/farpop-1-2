const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE = process.env.FARPOP_URL || 'http://127.0.0.1:8001';
const OUT = path.join(__dirname, 'screenshots');

const paginas = [
  { nome: '01-login', url: '/login', login: false },
  { nome: '02-dashboard', url: '/dashboard', login: true },
  { nome: '03-aqui-tem', url: '/aqui-tem', login: true },
  { nome: '04-estoque', url: '/estoque', login: true },
  { nome: '05-dispensa', url: '/saidas', login: true },
  { nome: '06-entradas', url: '/entradas', login: true },
  { nome: '07-transferencias', url: '/transferencias', login: true },
  { nome: '08-solicitacoes', url: '/solicitacoes', login: true },
  { nome: '09-alertas', url: '/alertas', login: true },
  { nome: '10-relatorios', url: '/relatorios', login: true },
  { nome: '11-medicamentos', url: '/medicamentos', login: true },
  { nome: '12-clinicas', url: '/clinicas', login: true },
  { nome: '13-usuarios', url: '/usuarios', login: true },
  { nome: '14-integracoes', url: '/integracoes', login: true },
  { nome: '15-configuracoes', url: '/configuracoes', login: true },
];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 1,
  });
  const page = await context.newPage();

  let logado = false;
  for (const p of paginas) {
    try {
      if (p.login && !logado) {
        await page.goto(`${BASE}/_apresentacao-login`, { waitUntil: 'networkidle', timeout: 60000 });
        logado = true;
        await page.waitForTimeout(800);
      }
      const target = p.login ? `${BASE}${p.url}` : `${BASE}${p.url}`;
      if (!p.login) {
        await page.goto(target, { waitUntil: 'networkidle', timeout: 60000 });
      } else {
        await page.goto(target, { waitUntil: 'networkidle', timeout: 60000 });
      }
      await page.waitForTimeout(900);
      // Expandir sidebar se existir
      await page.evaluate(() => {
        const s = document.getElementById('sidebarSistema');
        if (s) s.classList.add('sidebar-fixada');
      }).catch(() => {});
      await page.waitForTimeout(400);
      const file = path.join(OUT, `${p.nome}.png`);
      await page.screenshot({ path: file, fullPage: false });
      console.log('OK', p.nome);
    } catch (e) {
      console.error('FALHA', p.nome, e.message);
    }
  }

  await browser.close();
  console.log('Capturas concluídas em', OUT);
})();
