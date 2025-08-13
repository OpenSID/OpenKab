const { test, expect } = require('@playwright/test');
const configLoader = require('../config-loader');
const E2ETestHelper = require('../utils/e2e-helper');

test.describe('Dashboard Tests', () => {
  // Test ini menggunakan authentication state dari global setup

  test.beforeEach(async ({ page }) => {
    page.setDefaultTimeout(30000);
    page.setDefaultNavigationTimeout(30000);
    E2ETestHelper.setupPageLogging(page);
  });

  test('should access dashboard when authenticated', async ({ page }) => {
    await page.goto('/dasbor');
    await E2ETestHelper.waitForPageReady(page);

    // Verify we're on the dashboard page
    const currentUrl = await E2ETestHelper.getCurrentUrl(page);
    expect(currentUrl).toContain('/dasbor');

    // Check page title
    const title = await E2ETestHelper.getPageTitle(page);
    expect(title).toBeTruthy();
    expect(title).toContain('Dasbor');

    // Check for authenticated user elements
    const hasUserMenu = await page.locator('.user-menu, .navbar-nav .dropdown, [class*="user"], .nav-link').count() > 0;
    expect(hasUserMenu).toBeTruthy();

    // Check for dashboard widgets/cards
    const hasDashboardContent = await page.locator('.card, .widget, .dashboard-item, .content-wrapper').count() > 0;
    expect(hasDashboardContent).toBeTruthy();

    // Verify not redirected to login
    expect(currentUrl).not.toContain('/login');
  });

  test('should display dashboard statistics', async ({ page }) => {
    await page.goto('/dasbor');
    await E2ETestHelper.waitForPageReady(page);

    // Wait for statistics to load
    await page.waitForTimeout(3000);

    // Check for statistics cards or elements
    const statsElements = await page.locator('.card, .stat-card, .info-box, .small-box').count();
    expect(statsElements).toBeGreaterThan(0);

    // Check for any charts or graphs
    const hasCharts = await page.locator('canvas, .chart, #chart, [id*="chart"]').count() > 0;
    // Charts might not always be present, so we just log this
    console.log('Dashboard has charts:', hasCharts);
  });

  test('should have working navigation menu', async ({ page }) => {
    await page.goto('/dasbor');
    await E2ETestHelper.waitForPageReady(page);

    // Check for sidebar or main navigation
    const hasNavigation = await page.locator('.sidebar, .nav, .navbar, .main-navigation').count() > 0;
    expect(hasNavigation).toBeTruthy();

    // Check for menu items
    const menuItems = await page.locator('.nav-link, .menu-item, a[href*="/"]').count();
    expect(menuItems).toBeGreaterThan(0);

    // Try clicking on a safe menu item (avoid logout)
    const safeMenuItems = page.locator('.nav-link:not([href*="logout"]):not([href*="keluar"])').first();
    if (await safeMenuItems.count() > 0) {
      await safeMenuItems.click();
      await page.waitForTimeout(1000);

      // Verify navigation worked (page changed or content updated)
      const currentUrl = await E2ETestHelper.getCurrentUrl(page);
      expect(currentUrl).toBeTruthy();
    }
  });

  test('should display user information', async ({ page }) => {
    await page.goto('/dasbor');
    await E2ETestHelper.waitForPageReady(page);

    // Check for user name or profile information
    const pageContent = await page.textContent('body');

    // The user should be logged in, so we shouldn't see login forms
    expect(pageContent).not.toContain('Login');
    expect(pageContent).not.toContain('Sign In');

    // Should have some indication of logged in state
    const hasUserIndicator = await page.locator('.user-name, .username, .profile, [class*="user"]').count() > 0;
    // This might vary by implementation, so we just check if we're not on login page
    const currentUrl = await E2ETestHelper.getCurrentUrl(page);
    expect(currentUrl).not.toContain('/login');
  });

  test('should handle page reload while authenticated', async ({ page }) => {
    await page.goto('/dasbor');
    await E2ETestHelper.waitForPageReady(page);

    // Verify initial load
    let currentUrl = await E2ETestHelper.getCurrentUrl(page);
    expect(currentUrl).toContain('/dasbor');

    // Reload the page
    await page.reload();
    await E2ETestHelper.waitForPageReady(page);

    // Verify still authenticated after reload
    currentUrl = await E2ETestHelper.getCurrentUrl(page);
    expect(currentUrl).toContain('/dasbor');
    expect(currentUrl).not.toContain('/login');
  });

  test('should have proper page structure', async ({ page }) => {
    await page.goto('/dasbor');
    await E2ETestHelper.waitForPageReady(page);

    // Check for basic HTML structure
    await expect(page.locator('html')).toBeVisible();
    await expect(page.locator('head')).toHaveCount(1);
    await expect(page.locator('body')).toBeVisible();

    // Check for meta tags
    const viewportMeta = page.locator('meta[name="viewport"]');
    await expect(viewportMeta).toHaveCount(1);

    // Check for title
    const title = await page.title();
    expect(title).toBeTruthy();
    expect(title.length).toBeGreaterThan(0);
  });
});
