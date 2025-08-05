const { chromium } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

class AuthStateManager {
  constructor(configLoader) {
    this.configLoader = configLoader;
    this.storageStatePath = path.join(__dirname, '..', '..', 'test-results', 'storage-state', 'auth.json');
    this.ensureStorageDir();
  }

  ensureStorageDir() {
    const storageDir = path.dirname(this.storageStatePath);
    if (!fs.existsSync(storageDir)) {
      fs.mkdirSync(storageDir, { recursive: true });
    }
  }

  async isAuthStateValid() {
    if (!fs.existsSync(this.storageStatePath)) {
      return false;
    }

    try {
      const authState = JSON.parse(fs.readFileSync(this.storageStatePath, 'utf8'));

      // Check if auth state has cookies and they're not expired
      if (!authState.cookies || authState.cookies.length === 0) {
        return false;
      }

      // Check for session cookies that might indicate valid authentication
      const hasSessionCookie = authState.cookies.some(cookie =>
        cookie.name.includes('session') ||
        cookie.name.includes('token') ||
        cookie.name.includes('laravel')
      );

      return hasSessionCookie;
    } catch (error) {
      console.warn('Error reading auth state:', error.message);
      return false;
    }
  }

  async createAuthState() {
    console.log('Creating new authentication state...');

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    // Ignore API errors to prevent test failures due to external API calls
    page.on('pageerror', (error) => {
      console.warn('Page error (ignored):', error.message);
    });

    page.on('requestfailed', (request) => {
      console.warn('Request failed (ignored):', request.url());
    });

    try {
      const baseURL = this.configLoader.get('app.baseURL');
      const email = this.configLoader.get('auth.email');
      const password = this.configLoader.get('auth.password');

      console.log(`Config - BaseURL: ${baseURL}`);
      console.log(`Config - Email: ${email}`);
      console.log(`Config - Password: ${password}`);

      // Navigate to login page with retry
      let retries = 3;
      while (retries > 0) {
        try {
          await page.goto(`${baseURL}/login`, {
            waitUntil: 'networkidle',
            timeout: 30000
          });
          break;
        } catch (error) {
          retries--;
          if (retries === 0) throw error;
          console.warn(`Retrying login page navigation... (${retries} attempts left)`);
          await page.waitForTimeout(2000);
        }
      }

      // Wait for login form to be ready
      await page.waitForSelector('input[name="login"]', { timeout: 10000 });
      await page.waitForSelector('input[name="password"]', { timeout: 10000 });
      await page.waitForSelector('button[type="submit"]', { timeout: 10000 });

      // Fill login form
      await page.fill('input[name="login"]', email);
      await page.fill('input[name="password"]', password);

      // Submit form and wait for navigation
      await Promise.all([
        page.waitForURL(url => !url.toString().includes('/login'), { timeout: 30000 }),
        page.click('button[type="submit"]')
      ]);

      // Verify login success
      const currentUrl = page.url();
      const isLoggedIn = !currentUrl.includes('/login');

      if (!isLoggedIn) {
        // Try to get error message
        const errorElement = await page.locator('.alert-danger, .error, .invalid-feedback').first();
        const errorMessage = await errorElement.textContent().catch(() => 'Unknown error');
        throw new Error(`Login failed. Current URL: ${currentUrl}. Error: ${errorMessage}`);
      }

      // Save authentication state
      await page.context().storageState({ path: this.storageStatePath });
      console.log(`Authentication state saved to: ${this.storageStatePath}`);

      return true;
    } catch (error) {
      console.error('Failed to create auth state:', error.message);
      throw error;
    } finally {
      await browser.close();
    }
  }

  async getAuthState() {
    const isValid = await this.isAuthStateValid();

    if (!isValid) {
      await this.createAuthState();
    } else {
      console.log('Using existing authentication state');
    }

    return this.storageStatePath;
  }

  clearAuthState() {
    if (fs.existsSync(this.storageStatePath)) {
      fs.unlinkSync(this.storageStatePath);
      console.log('Authentication state cleared');
    }
  }

  async refreshAuthState() {
    this.clearAuthState();
    return await this.createAuthState();
  }

  async getStoredCookies() {
    try {
      if (!fs.existsSync(this.storageStatePath)) {
        return [];
      }

      const authState = JSON.parse(fs.readFileSync(this.storageStatePath, 'utf8'));
      return authState.cookies || [];
    } catch (error) {
      console.warn('Error reading stored cookies:', error.message);
      return [];
    }
  }

  async getStorageState() {
    if (!fs.existsSync(this.storageStatePath)) {
      await this.createAuthState();
    }

    try {
      return JSON.parse(fs.readFileSync(this.storageStatePath, 'utf8'));
    } catch (error) {
      console.warn('Error reading storage state:', error.message);
      await this.createAuthState();
      return JSON.parse(fs.readFileSync(this.storageStatePath, 'utf8'));
    }
  }
}

module.exports = AuthStateManager;
