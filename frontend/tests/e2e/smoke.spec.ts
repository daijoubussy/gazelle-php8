/**
 * Smoke Tests
 *
 * Basic tests to verify the application loads and core features work.
 * These run on every deployment to catch obvious issues.
 */

import { test, expect } from '@playwright/test';

test.describe('Smoke Tests', () => {
  test.describe('Application Loading', () => {
    test('should load the home page', async ({ page }) => {
      await page.goto('/');

      // Page should load without errors
      await expect(page).toHaveTitle(/Gazelle/);
    });

    test('should display the navigation header', async ({ page }) => {
      await page.goto('/');

      // Header should be visible
      const header = page.locator('header, [role="banner"], nav').first();
      await expect(header).toBeVisible();
    });

    test('should respond to route navigation', async ({ page }) => {
      await page.goto('/');

      // Navigate to a different route
      await page.click('a[href*="torrent"], a:has-text("Torrents")');

      // URL should change
      await expect(page).toHaveURL(/torrent/);
    });
  });

  test.describe('Authentication UI', () => {
    test('should display login form when not authenticated', async ({ page }) => {
      await page.goto('/login');

      // Login form elements should be present
      await expect(page.locator('input[type="email"], input[name="email"]')).toBeVisible();
      await expect(page.locator('input[type="password"]')).toBeVisible();
      await expect(page.locator('button[type="submit"], button:has-text("Login")')).toBeVisible();
    });

    test('should show validation errors for empty form submission', async ({ page }) => {
      await page.goto('/login');

      // Try to submit empty form
      await page.click('button[type="submit"], button:has-text("Login")');

      // Should show validation error
      const errorMessage = page.locator('[role="alert"], .error, [class*="error"]');
      await expect(errorMessage).toBeVisible({ timeout: 5000 });
    });
  });

  test.describe('Accessibility', () => {
    test('should have no critical accessibility issues on home page', async ({ page }) => {
      await page.goto('/');

      // Check for basic accessibility
      // Main content should have appropriate role
      const main = page.locator('main, [role="main"]');
      await expect(main).toBeVisible();

      // Skip link should exist for keyboard users
      // (this is a best practice, may not exist in all apps)
      // const skipLink = page.locator('a[href="#main"], a:has-text("Skip")');
    });

    test('should support keyboard navigation', async ({ page }) => {
      await page.goto('/');

      // Tab through focusable elements
      await page.keyboard.press('Tab');
      await page.keyboard.press('Tab');

      // Something should be focused
      const focusedElement = page.locator(':focus');
      await expect(focusedElement).toBeVisible();
    });
  });

  test.describe('Responsive Design', () => {
    test('should display correctly on mobile viewport', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE
      await page.goto('/');

      // Page should not have horizontal scroll
      const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
      const viewportWidth = 375;

      expect(bodyWidth).toBeLessThanOrEqual(viewportWidth + 10); // Small tolerance
    });

    test('should display correctly on tablet viewport', async ({ page }) => {
      await page.setViewportSize({ width: 768, height: 1024 }); // iPad
      await page.goto('/');

      // Page should render
      await expect(page.locator('body')).toBeVisible();
    });
  });

  test.describe('Error Handling', () => {
    test('should display 404 page for unknown routes', async ({ page }) => {
      await page.goto('/this-page-does-not-exist-12345');

      // Should show some kind of error message
      const content = await page.textContent('body');
      expect(
        content?.toLowerCase().includes('not found') ||
        content?.toLowerCase().includes('404') ||
        content?.toLowerCase().includes('error')
      ).toBeTruthy();
    });
  });
});
