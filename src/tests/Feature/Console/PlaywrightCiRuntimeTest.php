<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use PHPUnit\Framework\TestCase;

class PlaywrightCiRuntimeTest extends TestCase
{
    /**
     * Param: Tidak ada parameter.
     *
     * Proses: Memverifikasi workflow public menjalankan Playwright dengan
     * webServer bawaan Playwright dan output test tersimpan ke log E2E
     * agar kegagalan start maupun test dapat didiagnosis dari artifact.
     *
     * Output: Test lulus bila workflow menjalankan Playwright dengan tee ke
     * log E2E, config webServer memakai artisan serve tanpa --no-reload,
     * dan artifact log E2E terdaftar di workflow.
     */
    public function test_public_playwright_ci_uses_managed_server(): void
    {
        $rootPath = dirname(__DIR__, 4);
        $workflowPath = $rootPath.'/.github/workflows/ci.yml';
        $configPath = $rootPath.'/src/playwright.config.ts';

        $workflow = (string) file_get_contents($workflowPath);
        $config = (string) file_get_contents($configPath);

        $this->assertStringContainsString('npm run e2e:report 2>&1 | tee playwright-e2e.log', $workflow);
        $this->assertStringContainsString('playwright-e2e.log', $workflow);
        $this->assertStringContainsString('php artisan serve --host=127.0.0.1 --port=8000', $config);
        $this->assertStringNotContainsString('--no-reload', $config);
        $this->assertStringContainsString('const manageServerInScript = process.env.E2E_MANAGE_SERVER === \'1\';', $config);
        $this->assertStringContainsString('webServer: manageServerInScript', $config);
    }
}
