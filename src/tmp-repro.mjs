import { chromium } from '@playwright/test';

const base = 'http://127.0.0.1:8000';
const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto(base + '/login');
await page.waitForSelector('input[name="email"]');
await page.fill('input[name="email"]', 'superadmin@klinik.local');
await page.fill('input[name="password"]', 'superadmin');
await page.click('button[type="submit"]');
await page.waitForURL((u) => !u.pathname.includes('/login'), { timeout: 20000 });

for (const theme of ['dark', 'light']) {
  await page.evaluate((t) => localStorage.setItem('klinik-theme', t), theme);
  await page.goto(base + '/master/products');
  await page.waitForSelector('table', { timeout: 30000 });
  await page.getByTestId('btn-open-product-filter').click();
  await page.getByTestId('filter-company').click();
  await page.waitForSelector('.vs__dropdown-menu', { timeout: 10000 });
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'D:/projects/klinik-git/tmp/filter-' + theme + '-fixed.png' });
  await page.getByTestId('btn-close-product-filter').click();
  await page.waitForTimeout(300);
}
await browser.close();
