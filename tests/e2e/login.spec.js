const { test, expect } = require('@playwright/test');
const configLoader = require('../config-loader');
const E2ETestHelper = require('../utils/e2e-helper');
const AuthStateManager = require('../utils/auth-state-manager');

test.describe('Login Page Tests', () => {
  // Test ini tidak menggunakan authentication state karena kita ingin test login
  test.use({ storageState: { cookies: [], origins: [] } });

  test.beforeEach(async ({ page }) => {
    page.setDefaultTimeout(30000);
    page.setDefaultNavigationTimeout(30000);
    E2ETestHelper.setupPageLogging(page);
  });

  test('should display login page correctly', async ({ page }) => {
    await page.goto('/login');
    await E2ETestHelper.waitForPageReady(page);

    // Check page title
    const title = await E2ETestHelper.getPageTitle(page);
    expect(title).toBeTruthy();

    // Check login form elements
    await expect(page.locator('input[name="login"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();

    // Check for CSRF token
    const csrfTokenInput = page.locator('input[name="_token"]');
    const csrfTokenMeta = page.locator('meta[name="csrf-token"]');

    const hasCSRFInput = await csrfTokenInput.count() > 0;
    const hasCSRFMeta = await csrfTokenMeta.count() > 0;

    expect(hasCSRFInput || hasCSRFMeta).toBeTruthy();
  });

  test('should login successfully with valid credentials', async ({ page }) => {
    const email = configLoader.get('auth.email');
    const password = configLoader.get('auth.password');

    await page.goto('/login');
    await E2ETestHelper.waitForPageReady(page);

    // Fill login form
    await E2ETestHelper.fillFormField(page, 'input[name="login"]', email);
    await E2ETestHelper.fillFormField(page, 'input[name="password"]', password);

    // Submit form and wait for navigation
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      E2ETestHelper.clickElement(page, 'button[type="submit"]')
    ]);

    // Verify successful login - should be redirected away from login page
    const currentUrl = await E2ETestHelper.getCurrentUrl(page);
    expect(currentUrl).not.toContain('/login');

    // Should be redirected to dashboard or main page
    expect(currentUrl).toMatch(/\/(dasbor|dashboard|admin|home)/);

    // Verify we're authenticated by checking for user elements
    await page.waitForTimeout(2000);
    const hasAuthenticatedElements = await page.locator('.user-menu, .logout, .nav-link, .username').count() > 0;
    expect(hasAuthenticatedElements).toBeTruthy();
  });

  test('should show error for invalid credentials', async ({ page }) => {
    await page.goto('/login');
    await E2ETestHelper.waitForPageReady(page);

    // Fill with invalid credentials
    await E2ETestHelper.fillFormField(page, 'input[name="login"]', 'invalid@email.com');
    await E2ETestHelper.fillFormField(page, 'input[name="password"]', 'wrongpassword');

    // Submit form
    await E2ETestHelper.clickElement(page, 'button[type="submit"]');
    await page.waitForTimeout(2000);

    // Should still be on login page or show error
    const currentUrl = await E2ETestHelper.getCurrentUrl(page);
    const isOnLoginPage = currentUrl.includes('/login');

    // Check for error messages
    const hasErrorMessage = await page.locator('.alert-danger, .error, .invalid-feedback, [class*="error"]').count() > 0;

    // Either should stay on login page OR show error message
    expect(isOnLoginPage || hasErrorMessage).toBeTruthy();
  });

  test('should validate required fields', async ({ page }) => {
    await page.goto('/login');
    await E2ETestHelper.waitForPageReady(page);

    // Try to submit without filling fields
    await E2ETestHelper.clickElement(page, 'button[type="submit"]');
    await page.waitForTimeout(1000);

    // Should still be on login page
    const currentUrl = await E2ETestHelper.getCurrentUrl(page);
    expect(currentUrl).toContain('/login');

    // Check for validation messages or HTML5 validation
    const emailField = page.locator('input[name="login"]');
    const passwordField = page.locator('input[name="password"]');

    // HTML5 validation might prevent form submission
    const emailValidation = await emailField.evaluate(el => el.validationMessage);
    const passwordValidation = await passwordField.evaluate(el => el.validationMessage);

    // At least one field should have validation message or we should still be on login page
    expect(emailValidation || passwordValidation || currentUrl.includes('/login')).toBeTruthy();
  });

  test('should redirect authenticated user away from login', async ({ page }) => {
    // First, login with valid credentials
    const authManager = new AuthStateManager(configLoader);

    // Get or create auth state
    await authManager.getAuthState();

    // Get stored cookies and add them to the page context
    const cookies = await authManager.getStoredCookies();
    if (cookies.length > 0) {
      await page.context().addCookies(cookies);
    }

    // Try to access login page when already authenticated
    await page.goto('/login');
    await E2ETestHelper.waitForPageReady(page);

    // Should be redirected away from login page
    const currentUrl = await E2ETestHelper.getCurrentUrl(page);

    // Either redirected immediately or after a short delay
    if (currentUrl.includes('/login')) {
      await page.waitForTimeout(2000);
      const newUrl = await E2ETestHelper.getCurrentUrl(page);
      expect(newUrl).not.toContain('/login');
    } else {
      expect(currentUrl).not.toContain('/login');
    }
  });

  test('should handle remember me functionality', async ({ page }) => {
    await page.goto('/login');
    await E2ETestHelper.waitForPageReady(page);

    const email = configLoader.get('auth.email');
    const password = configLoader.get('auth.password');

    // Fill login form
    await E2ETestHelper.fillFormField(page, 'input[name="login"]', email);
    await E2ETestHelper.fillFormField(page, 'input[name="password"]', password);

    // Check remember me if it exists
    const rememberCheckbox = page.locator('input[name="remember"], input[type="checkbox"]');
    if (await rememberCheckbox.count() > 0) {
      await rememberCheckbox.check();
    }

    // Submit form
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      E2ETestHelper.clickElement(page, 'button[type="submit"]')
    ]);

    // Verify successful login
    const currentUrl = await E2ETestHelper.getCurrentUrl(page);
    expect(currentUrl).not.toContain('/login');
  });
});
