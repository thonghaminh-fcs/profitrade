#!/usr/bin/env node
// Full-page screenshots + a small overflow report, with Playwright installed once OUTSIDE the repo
// (~/.cache/wpa-shot). Usage:
//   node scripts/wp-shot.mjs <url|file> <out.png> [--width 1440] [--click "text=Shop"]... [--wait 800] [--auth]
// --click may repeat (SPA mockups: click through nav before capturing). --auth logs into wp-admin using .env.
// Prints JSON: { out, width, height, scrollWidth, overflowX, consoleErrors }.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { execSync } from 'node:child_process';
import { createRequire } from 'node:module';

const home = path.join(os.homedir(), '.cache', 'wpa-shot');
if (!fs.existsSync(path.join(home, 'node_modules', 'playwright'))) {
  fs.mkdirSync(home, { recursive: true });
  execSync('npm init -y >/dev/null 2>&1 && npm i --no-audit --no-fund playwright@1.49.1 && npx playwright install chromium', { cwd: home, stdio: 'inherit' });
}
const { chromium } = createRequire(path.join(home, 'x.js'))('playwright');

const a = process.argv.slice(2);
const target = a[0], out = a[1];
const opt = (k, d) => { const i = a.indexOf(k); return i > -1 ? a[i + 1] : d; };
const clicks = a.flatMap((v, i) => (v === '--click' ? [a[i + 1]] : []));
const width = +opt('--width', 1440), wait = +opt('--wait', 800);
if (!target || !out) { console.error('usage: wp-shot.mjs <url|file> <out.png> [--width N] [--click sel]... [--auth]'); process.exit(2); }

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width, height: 900 } });
const errors = [];
page.on('console', (m) => m.type() === 'error' && errors.push(m.text().slice(0, 200)));
page.on('pageerror', (e) => errors.push(String(e).slice(0, 200)));
if (a.includes('--auth')) {
  const env = Object.fromEntries(fs.readFileSync('.env', 'utf8').split('\n').map((l) => l.match(/^([A-Z_]+)='?(.*?)'?$/)).filter(Boolean).map((m) => [m[1], m[2]]));
  await page.goto(`${env.PROJECT_HOST}/wp-login.php`);
  await page.fill('#user_login', env.WP_ADMIN_USER || 'admin');
  await page.fill('#user_pass', env.WP_ADMIN_PASSWORD);
  await Promise.all([page.waitForNavigation(), page.click('#wp-submit')]);
}
await page.goto(/^https?:/.test(target) ? target : 'file://' + path.resolve(target), { waitUntil: 'networkidle' });
for (const c of clicks) { await page.click(c); await page.waitForTimeout(wait); }
await page.waitForTimeout(wait);
// trigger lazy/scroll-reveal content
await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); } window.scrollTo(0, 0); });
await page.screenshot({ path: out, fullPage: true });
const m = await page.evaluate(() => ({ height: document.documentElement.scrollHeight, scrollWidth: document.documentElement.scrollWidth }));
console.log(JSON.stringify({ out, width, height: m.height, scrollWidth: m.scrollWidth, overflowX: m.scrollWidth > width + 1, consoleErrors: errors.slice(0, 8) }));
await browser.close();
