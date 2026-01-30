/**
 * Playwright Global Teardown
 *
 * Runs once after all tests to clean up the test environment.
 */

import { FullConfig } from '@playwright/test';

async function globalTeardown(config: FullConfig): Promise<void> {
  console.log('🧹 Starting global test teardown...');

  // Clean up any global test state here
  // For example, you could:
  // - Clean up test database records
  // - Delete test files
  // - Stop mock servers

  console.log('✅ Global teardown complete');
}

export default globalTeardown;
