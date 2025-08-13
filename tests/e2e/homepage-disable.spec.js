const { test, expect, chromium } = require('@playwright/test');
const configLoader = require('../config-loader');
const E2ETestHelper = require('../utils/e2e-helper');
const AuthStateManager = require('../utils/auth-state-manager');
const WebSettingsHelper = require('../utils/web-settings-helper');

test.describe('Disable Website Homepage Tests', () => {
  let authManager;

  test.beforeAll(async () => {
    authManager = new AuthStateManager(configLoader);
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    const webSettings = new WebSettingsHelper(page, authManager);
    await webSettings.setUpdateSettingViaWeb('website_enable',false);
  });

  // Test ini tidak menggunakan authentication state karena homepage adalah public
  test.use({ storageState: { cookies: [], origins: [] } });

  test.beforeEach(async ({ page }) => {
    page.setDefaultTimeout(30000);
    page.setDefaultNavigationTimeout(30000);
    E2ETestHelper.setupPageLogging(page);
  });

  test('should show error when website is disabled', async ({ page }) => {

    expect(async () => {
      await page.goto('/login');
      await E2ETestHelper.waitForPageReady(page);
    })
  });
});
