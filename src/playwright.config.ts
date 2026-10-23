import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.KLINIK_TEST_BASE_URL ?? 'http://127.0.0.1:8000';
const manageServerInScript = process.env.E2E_MANAGE_SERVER === '1';

export default defineConfig({
  testDir: '.',
  testMatch: ['modules/**/*.view.spec.ts', 'tests/E2E/auth.setup.ts'],
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  timeout: 60000,
  globalTeardown: './tests/E2E/global-teardown.ts',
  reporter: process.env.CI
    ? [['line'], ['junit', { outputFile: 'test-results/junit.xml' }]]
    : [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL,
    trace: 'on-first-retry',
    headless: true,
    actionTimeout: 20000,
    navigationTimeout: 30000,
  },
  projects: [
    {
      name: 'setup',
      testMatch: 'tests/E2E/auth.setup.ts',
    },
    {
      name: 'chromium',
      dependencies: ['setup'],
      testIgnore: ['tests/E2E/**'],
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/user.json',
      },
    },
  ],
  webServer: manageServerInScript
    ? undefined
    : {
        command:
          'php -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php',
        url: baseURL,
        cwd: 'public',
        reuseExistingServer: true,
        timeout: 120_000,
        stdout: 'pipe',
        stderr: 'pipe',
      },
});
