/**
 * Playwright Global Setup
 *
 * Runs once before all tests to set up the test environment.
 */

import { chromium, FullConfig } from '@playwright/test';

async function globalSetup(config: FullConfig): Promise<void> {
  console.log('🚀 Starting global test setup...');

  // Verify the development server is running
  const baseURL = config.projects[0].use?.baseURL || 'http://localhost:5173';

  try {
    const browser = await chromium.launch();
    const page = await browser.newPage();

    // Wait for the dev server to be ready
    await page.goto(baseURL, { waitUntil: 'networkidle', timeout: 30000 });

    console.log(`✅ Development server is ready at ${baseURL}`);

    await browser.close();
  } catch (error) {
    console.error('❌ Failed to connect to development server');
    console.error('Make sure the dev server is running: npm run dev');
    throw error;
  }

  // Set up any global test state here
  // For example, you could:
  // - Seed a test database
  // - Create test user accounts
  // - Set up mock API servers

  console.log('✅ Global setup complete');
}

export default globalSetup;
