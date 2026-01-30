import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],

  test: {
    // Environment
    environment: 'jsdom',

    // Global setup
    globals: true,
    setupFiles: ['./tests/setup.ts'],

    // Include patterns
    include: [
      'src/**/*.{test,spec}.{ts,tsx}',
      'tests/unit/**/*.{test,spec}.{ts,tsx}',
    ],

    // Exclude patterns
    exclude: [
      'node_modules',
      'dist',
      'tests/e2e/**',
      'tests/component/**',
    ],

    // Coverage configuration
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html', 'lcov', 'json'],
      reportsDirectory: './coverage',
      include: ['src/**/*.{ts,tsx}'],
      exclude: [
        'src/**/*.d.ts',
        'src/**/*.test.{ts,tsx}',
        'src/**/*.spec.{ts,tsx}',
        'src/main.tsx',
        'src/vite-env.d.ts',
      ],
      // Coverage thresholds
      thresholds: {
        statements: 70,
        branches: 70,
        functions: 70,
        lines: 70,
      },
    },

    // Reporters
    reporters: ['default', 'html'],
    outputFile: {
      html: './reports/vitest/index.html',
    },

    // Timeouts
    testTimeout: 10000,
    hookTimeout: 10000,

    // Isolation
    isolate: true,
    pool: 'forks',

    // Watch mode exclusions
    watchExclude: ['node_modules', 'dist'],

    // Dependency optimization
    deps: {
      optimizer: {
        web: {
          include: ['@splunk/react-ui', '@splunk/themes'],
        },
      },
    },
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, './src'),
      '@components': resolve(__dirname, './src/components'),
      '@pages': resolve(__dirname, './src/pages'),
      '@hooks': resolve(__dirname, './src/hooks'),
      '@services': resolve(__dirname, './src/services'),
      '@utils': resolve(__dirname, './src/utils'),
      '@themes': resolve(__dirname, './src/themes'),
      '@types': resolve(__dirname, './src/types'),
      '@tests': resolve(__dirname, './tests'),
    },
  },
});
