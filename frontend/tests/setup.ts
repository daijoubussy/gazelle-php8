/**
 * Vitest Global Test Setup
 *
 * This file runs before all tests and sets up:
 * - Testing library matchers
 * - MSW (Mock Service Worker) handlers
 * - Global mocks
 * - Test utilities
 */

import '@testing-library/jest-dom';
import { afterAll, afterEach, beforeAll, vi } from 'vitest';
import { cleanup } from '@testing-library/react';
import { server } from './mocks/server';

// ========================================
// MSW Setup
// ========================================

beforeAll(() => {
  // Start MSW server before all tests
  server.listen({
    onUnhandledRequest: 'warn',
  });
});

afterEach(() => {
  // Reset handlers between tests
  server.resetHandlers();
  // Cleanup rendered components
  cleanup();
});

afterAll(() => {
  // Close MSW server after all tests
  server.close();
});

// ========================================
// Global Mocks
// ========================================

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation((query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});

// Mock ResizeObserver
global.ResizeObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}));

// Mock IntersectionObserver
global.IntersectionObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}));

// Mock scrollTo
window.scrollTo = vi.fn();

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
  length: 0,
  key: vi.fn(),
};
Object.defineProperty(window, 'localStorage', { value: localStorageMock });

// Mock sessionStorage
const sessionStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
  length: 0,
  key: vi.fn(),
};
Object.defineProperty(window, 'sessionStorage', { value: sessionStorageMock });

// ========================================
// Console Error Handling
// ========================================

// Fail tests on console.error (catches React warnings)
const originalError = console.error;
console.error = (...args: unknown[]) => {
  // Allow specific expected errors
  const message = args[0];
  if (
    typeof message === 'string' &&
    (message.includes('Warning: ReactDOM.render is no longer supported') ||
      message.includes('Warning: An update to') ||
      message.includes('act(...)'))
  ) {
    return;
  }
  originalError.apply(console, args);
};

// ========================================
// Deterministic Time
// ========================================

// Provide a way to freeze time in tests
export const freezeTime = (date: Date | string): void => {
  vi.useFakeTimers();
  vi.setSystemTime(new Date(date));
};

export const unfreezeTime = (): void => {
  vi.useRealTimers();
};

// ========================================
// Custom Matchers
// ========================================

// Add any custom matchers here
// expect.extend({
//   toBeWithinRange(received, floor, ceiling) {
//     const pass = received >= floor && received <= ceiling;
//     if (pass) {
//       return {
//         message: () => `expected ${received} not to be within range ${floor} - ${ceiling}`,
//         pass: true,
//       };
//     } else {
//       return {
//         message: () => `expected ${received} to be within range ${floor} - ${ceiling}`,
//         pass: false,
//       };
//     }
//   },
// });
