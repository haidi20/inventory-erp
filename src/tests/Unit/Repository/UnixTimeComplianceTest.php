<?php

namespace Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class UnixTimeComplianceTest extends TestCase
{
    public function test_repository_files_do_not_contain_microsecond_tokens(): void
    {
        $patterns = [
            'microsecond keyword' => '/microsecond/i',
            'setMicrosecond call' => '/setMicrosecond\s*\(/',
            'explicit microsecond datetime format' => '/Y-m-d H:i:s\.u/',
            'format() with microsecond token' => '/format\s*\(\s*[\'"][^\'"]*\.u[^\'"]*[\'"]\s*\)/',
            'date() with microsecond format' => '/date\s*\(\s*[\'"]Y-m-d H:i:s\.u[\'"]\s*\)/',
        ];

        $violations = [];
        foreach ($this->phpFilesIn($this->repositoriesPath(), 'Repositories') as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                $violations[] = $file . ' (cannot read file)';
                continue;
            }

            foreach ($patterns as $label => $pattern) {
                if (preg_match($pattern, $content) === 1) {
                    $violations[] = "{$file} => {$label}";
                }
            }
        }

        $this->assertSame([], $violations, "Repository tidak boleh pakai microsecond.\n" . implode("\n", $violations));
    }

    public function test_repository_files_do_not_use_non_unixtime_date_helpers(): void
    {
        $patterns = [
            'whereDate()' => '/whereDate\s*\(/',
            'toDateString()' => '/toDateString\s*\(/',
            'toDateTimeString()' => '/toDateTimeString\s*\(/',
        ];

        $violations = [];
        foreach ($this->phpFilesIn($this->repositoriesPath(), 'Repositories') as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                $violations[] = $file . ' (cannot read file)';
                continue;
            }

            foreach ($patterns as $label => $pattern) {
                if (preg_match($pattern, $content) === 1) {
                    $violations[] = "{$file} => {$label}";
                }
            }
        }

        $this->assertSame([], $violations, "Repository harus konsisten pakai alur unixtime.\n" . implode("\n", $violations));
    }

    public function test_all_models_use_unix_date_format_u(): void
    {
        $missingDateFormat = [];
        $nonUnixDateFormat = [];

        foreach ($this->phpFilesIn($this->modelsPath(), 'Models') as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                $missingDateFormat[] = $file . ' (cannot read file)';
                continue;
            }

            if (preg_match('/protected\s+\$dateFormat\s*=\s*[\'"]U[\'"]\s*;/', $content) !== 1) {
                $missingDateFormat[] = $file;
            }

            if (preg_match('/Y-m-d H:i:s\.u|setMicrosecond|microsecond/i', $content) === 1) {
                $nonUnixDateFormat[] = $file;
            }
        }

        $this->assertSame([], $missingDateFormat, "Model wajib set dateFormat ke 'U'.\n" . implode("\n", $missingDateFormat));
        $this->assertSame([], $nonUnixDateFormat, "Model tidak boleh mengandung pola microsecond.\n" . implode("\n", $nonUnixDateFormat));
    }

    public function test_all_migrations_use_bigint_timestamps_for_audit_fields(): void
    {
        $patterns = [
            'timestamps()' => '/\$table->timestamps\s*\(/',
            'softDeletes()' => '/\$table->softDeletes\s*\(/',
        ];

        $violations = [];
        foreach ($this->phpFilesIn($this->migrationsPath()) as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            foreach ($patterns as $label => $pattern) {
                if (preg_match($pattern, $content) === 1) {
                    $violations[] = "{$file} => {$label}";
                }
            }
        }

        $this->assertSame([], $violations, "Migration harus pakai bigInteger untuk audit fields (Go Parity).\n" . implode("\n", $violations));
    }

    /**
     * @return array<int, string>
     */
    private function phpFilesIn(string $path, string $subFolder = ''): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }

            if (strtolower($item->getExtension()) !== 'php') {
                continue;
            }

            $fullPath = $item->getPathname();
            $normalizedPath = str_replace(DIRECTORY_SEPARATOR, '/', $fullPath);

            if ($subFolder !== '' && str_contains(strtolower($normalizedPath), '/modules/')) {
                if (!str_contains($normalizedPath, '/' . $subFolder . '/')) {
                    continue;
                }
            }

            $files[] = $fullPath;
        }

        sort($files);

        return $files;
    }

    private function repositoriesPath(): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'modules';
    }

    private function modelsPath(): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'modules';
    }

    private function migrationsPath(): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
    }
}

