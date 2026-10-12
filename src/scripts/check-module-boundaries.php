<?php

/**
 * CI job: ci:module-boundaries
 *
 * Menegakkan aturan batas layer antar modul sesuai _plan.md Section 11b.3:
 *   R1 Service tidak boleh import Model (App\Modules\*\Models\* maupun App\Models\*).
 *   R2 Controller tidak boleh import App\Models\* atau memanggil Repository langsung.
 *   R3 Repository modul lain tidak boleh di-import lintas top-level module.
 *   R4 Tidak boleh `use` namespace lama (App\Services\ / App\Repositories\ / App\Entities\).
 *
 * Usage:
 *   php scripts/check-module-boundaries.php               # cek (toleransi baseline)
 *   php scripts/check-module-boundaries.php --generate-baseline  # tulis baseline dari status kini
 *   php scripts/check-module-boundaries.php --strict       # abaikan baseline, fail pada semua
 *
 * Exit 0 = lulus, 1 = ada pelanggaran baru, 2 = error.
 */

$modulesDir = __DIR__ . '/../modules';
$baselineFile = __DIR__ . '/.module-boundaries-baseline.txt';

if (!is_dir($modulesDir)) {
    fwrite(STDERR, "Direktori modules tidak ditemukan: {$modulesDir}\n");
    exit(2);
}

$generateBaseline = in_array('--generate-baseline', $argv, true);
$strict = in_array('--strict', $argv, true);

$violations = [];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulesDir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getRealPath();
    $rel = substr($path, strlen(realpath($modulesDir)) + 1);
    $parts = explode('/', str_replace('\\', '/', $rel));
    $myModule = $parts[0] ?? null;
    $subPath = implode('/', array_slice($parts, 1));

    $src = file_get_contents($path);
    if ($src === false) {
        continue;
    }

    $lines = explode("\n", $src);
    $useLines = [];
    foreach ($lines as $i => $line) {
        if (preg_match('/^\s*use\s+([\\\\\w]+)\s*;/', $line, $m)) {
            $useLines[] = ['line' => $i + 1, 'fqcn' => ltrim($m[1], '\\'), 'raw' => trim($line)];
        }
    }

    $report = function (string $rule, int $line, string $raw) use ($rel, &$violations): void {
        $key = sprintf('[%s] %s:%d  %s', $rule, $rel, $line, $raw);
        $violations[$key] = $key;
    };

    // R4: legacy namespace
    foreach ($useLines as $u) {
        foreach (['App\\Services\\', 'App\\Repositories\\', 'App\\Entities\\'] as $legacy) {
            if (str_starts_with($u['fqcn'], $legacy)) {
                $report('R4-legacy', $u['line'], $u['raw']);
            }
        }
    }

    // R1: Services cannot import Models
    if (preg_match('#(^|/)Services/#', $subPath)) {
        foreach ($useLines as $u) {
            if (preg_match('#^App\\\\Modules\\\\\w+\\\\.*\\\\Models\\\\#', $u['fqcn'])
                || str_starts_with($u['fqcn'], 'App\\Models\\')) {
                $report('R1-service-no-model', $u['line'], $u['raw']);
            }
        }
    }

    // R2: Controllers cannot import App\Models\* or call Repository directly
    if (preg_match('#(^|/)Http/Controllers/#', $subPath)) {
        foreach ($useLines as $u) {
            if (str_starts_with($u['fqcn'], 'App\\Models\\')) {
                $report('R2-controller-no-model', $u['line'], $u['raw']);
            }
        }
        foreach ($lines as $i => $line) {
            if (preg_match('/new\s+\w*Repository\s*\(/', $line)
                || preg_match('/\b\w+Repository::/', $line)) {
                $report('R2-controller-no-repo', $i + 1, trim($line));
            }
        }
    }

    // R3: cross-module Repository import forbidden
    foreach ($useLines as $u) {
        if (preg_match('#^App\\\\Modules\\\\(\w+)\\\\.*\\\\Repositories\\\\#', $u['fqcn'], $m)) {
            if ($m[1] !== $myModule) {
                $report('R3-cross-module-repo', $u['line'], $u['raw']);
            }
        }
    }
}

$violations = array_values($violations);
sort($violations);

if ($generateBaseline) {
    $body = implode("\n", $violations) . "\n";
    file_put_contents($baselineFile, $body);
    fwrite(STDOUT, sprintf("Baseline ditulis: %s (%d pelanggaran dikenali).\n", $baselineFile, count($violations)));
    exit(0);
}

$baseline = [];
if (!$strict && is_file($baselineFile)) {
    $baseline = array_flip(array_filter(explode("\n", file_get_contents($baselineFile))));
}

$newViolations = [];
foreach ($violations as $v) {
    if (!isset($baseline[$v])) {
        $newViolations[] = $v;
    }
}

if ($newViolations === []) {
    $tolerated = count($violations) - count($newViolations);
    fwrite(STDOUT, sprintf(
        "ci:module-boundaries -- OK. 0 pelanggaran baru. (%d dikenali baseline, jalankan --strict untuk audit penuh)\n",
        $tolerated
    ));
    exit(0);
}

fwrite(STDERR, sprintf("ci:module-boundaries -- GAGAL, %d pelanggaran BARU:\n", count($newViolations)));
foreach ($newViolations as $v) {
    fwrite(STDERR, "  " . $v . "\n");
}
fwrite(STDERR, "Tip: perbaiki pelanggaran ini, atau jika sudah disepakati sementara jalankan --generate-baseline.\n");
exit(1);