import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8000';

test.describe.serial('Simulasi Serangan Siber Terhadap Web SOC SPBE', () => {
  test('Tahap 1: Serangan SQL Injection pada Gerbang Login SPBE', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);

    await page.selectOption('#gw-portal', 'sipd-aset.kalselprov.go.id');
    await page.selectOption('#gw-ip', '180.252.88.12');
    await page.fill('#gw-nip', "' OR '1'='1' --");
    await page.fill('#gw-password', 'admin123');
    await page.click('#gw-submit-btn');

    const alertBox = page.locator('#gw-alert');
    await expect(alertBox).toBeVisible();
    await expect(alertBox).toContainText('BLOCKED');
    await expect(alertBox).toContainText('SQL_INJECTION');
  });

  test('Tahap 2: Serangan XSS pada Form Login & Parameter URL Web (Global WAF)', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);

    await page.selectOption('#gw-ip', '192.168.10.15');
    await page.fill('#gw-nip', '<script>alert(document.cookie)</script>');
    await page.fill('#gw-password', 'x');
    await page.click('#gw-submit-btn');

    const alertBox = page.locator('#gw-alert');
    await expect(alertBox).toContainText('BLOCKED');
    await expect(alertBox).toContainText('XSS_ATTACK');

    // Penyerang mencoba menyisipkan skrip lewat URL utama
    const wafResponse = await page.goto(`${BASE_URL}/?search=%3Cscript%3Ealert(1)%3C/script%3E`);
    expect(wafResponse?.status()).toBe(403);
  });

  test('Tahap 3: Brute Force / Credential Stuffing Beruntun (4x Percobaan)', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);

    await page.selectOption('#gw-ip', '203.0.113.77');
    await page.fill('#gw-nip', '6371012903910001');

    for (let attempt = 1; attempt <= 4; attempt++) {
      await page.fill('#gw-password', `TebakSandiKe_${attempt}`);
      await page.click('#gw-submit-btn');
      await page.waitForTimeout(350);
    }

    const alertBox = page.locator('#gw-alert');
    await expect(alertBox).toContainText('BLOCKED');
    await expect(alertBox).toContainText('BRUTE_FORCE_LOCKOUT');
  });

  test('Tahap 4: Percobaan Account Takeover IP Luar Kantor & Verifikasi Log di Dashboard SOC', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);

    // Penyerang memakai NIP & sandi ASN yang bocor, namun mengakses dari IP luar instansi
    await page.selectOption('#gw-ip', '10.10.99.5');
    await page.fill('#gw-nip', '6371021506880003');
    await page.fill('#gw-password', 'PasswordAsn2026');
    await page.click('#gw-submit-btn');

    await expect(page.locator('#gw-alert')).toContainText('SUSPICIOUS');
    await expect(page.locator('#gw-mfa-box')).toBeVisible();

    // Cek Dashboard SOC utama untuk memastikan seluruh jejak serangan tercatat
    await page.goto(`${BASE_URL}/`);
    await expect(page.locator('body')).toContainText('SQL_INJECTION');
    await expect(page.locator('body')).toContainText('BRUTE_FORCE_LOCKOUT');
  });
});