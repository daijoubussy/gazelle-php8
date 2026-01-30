/**
 * MSW (Mock Service Worker) Server Configuration
 *
 * Intercepts network requests during tests and returns mock responses.
 * This ensures tests are deterministic and don't depend on external APIs.
 */

import { setupServer } from 'msw/node';
import { handlers } from './handlers';

// Create MSW server with default handlers
export const server = setupServer(...handlers);

// Export utilities for tests
export { handlers };
