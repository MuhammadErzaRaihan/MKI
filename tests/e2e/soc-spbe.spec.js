import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8000';

test.describe('SOC SPBE End-to-End Flow', () => {
  test('1. Login IP kantor valid langsung ALLOWED tanpa kotak OTP', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);

    await page.click('[data-preset="office"]');
    await page.click('#gw-submit-btn');

    const alertBox = page.locator('#gw-alert');
    await expect(alertBox).toBeVisible();
    await expect(alertBox).toContainText('ALLOWED');
    await expect(alertBox).toContainText('OFFICE_IP_LOGIN');
    await expect(page.locator('#gw-mfa-box')).toBeHidden();
  });

  test('2. IP luar kantor memunculkan kotak MFA dan berhasil verifikasi Secret Key', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);

    await page.click('[data-preset="external"]');
    await page.click('#gw-submit-btn');

    const alertBox = page.locator('#gw-alert');
    const mfaBox = page.locator('#gw-mfa-box');

    await expect(alertBox).toContainText('SUSPICIOUS');
    await expect(alertBox).toContainText('SUSPICIOUS_EXTERNAL_IP');
    await expect(mfaBox).toBeVisible();

    await page.fill('#gw-token', 'SK-KALSEL-998');
    await page.click('#gw-submit-btn');

    await expect(alertBox).toContainText('ALLOWED');
    await expect(alertBox).toContainText('EXTERNAL_MFA_VERIFIED');
    await expect(mfaBox).toBeHidden();
  });

  test('3. Serangan SQL Injection terblokir di UI dan langsung tampil di Dashboard SOC + Export CSV', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);

    await page.click('[data-preset="sqli"]');
    await page.click('#gw-submit-btn');

    const alertBox = page.locator('#gw-alert');
    await expect(alertBox).toContainText('BLOCKED');
    await expect(alertBox).toContainText('SQL_INJECTION');

    // Buka Dashboard Admin & cek sinkronisasi telemetri real-time
    await page.goto(`${BASE_URL}/`);
    await expect(page.locator('#overview-latest-telemetry')).toContainText('SQL_INJECTION');
    await expect(page.locator('#overview-threat-badge')).toContainText('CRITICAL ANOMALY');

    // Uji unduhan file CSV asli dari browser
    const downloadPromise = page.waitForEvent('download');
    await page.click('#overview-export-btn');
    const download = await downloadPromise;
    expect(download.suggestedFilename()).toMatch(/^soc-audit-report-.*\.csv$/);
  });
});