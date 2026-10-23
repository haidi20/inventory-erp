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
     * log E2E, script server memakai router E2E dan docroot public, serta
     * artifact log E2E terdaftar di workflow.
     */
    public function test_public_playwright_ci_uses_managed_server(): void
    {
        $rootPath = dirname(__DIR__, 4);
        $scriptPath = $rootPath.'/src/scripts/ci/run-playwright-e2e.sh';
        $workflowPath = $rootPath.'/.github/workflows/ci.yml';
        $configPath = $rootPath.'/src/playwright.config.ts';

        $this->assertFileExists($scriptPath);

        $script = (string) file_get_contents($scriptPath);
        $workflow = (string) file_get_contents($workflowPath);
        $config = (string) file_get_contents($configPath);

        $this->assertStringContainsString('php -S 127.0.0.1:8000 -t public tests/E2E/server.php', $script);
        $this->assertStringContainsString('curl --silent --fail http://127.0.0.1:8000/up', $script);
        $this->assertStringContainsString('npm run e2e:report', $script);
        $this->assertStringContainsString('bash scripts/ci/run-playwright-e2e.sh 2>&1 | tee playwright-e2e.log', $workflow);
        $this->assertStringContainsString('playwright-e2e.log', $workflow);
        $this->assertStringContainsString('src/playwright-e2e.log', $workflow);
        $this->assertStringContainsString('const manageServerInScript = process.env.E2E_MANAGE_SERVER === \'1\';', $config);
        $this->assertStringContainsString('webServer: manageServerInScript', $config);
    }
}
