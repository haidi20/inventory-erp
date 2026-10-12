<?php

$path = $argv[1] ?? 'coverage/clover.xml';
$threshold = (float) ($argv[2] ?? 1);

if (! is_file($path)) {
    fwrite(STDERR, "Coverage report tidak ditemukan: {$path}\n");
    exit(1);
}

$xml = simplexml_load_file($path);
$metrics = $xml?->xpath('//project/metrics')[0] ?? null;

if ($metrics === null) {
    fwrite(STDERR, "Report coverage tidak memiliki metrics project.\n");
    exit(1);
}

$statements = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, "Coverage kosong: tidak ada statement yang diukur.\n");
    exit(1);
}

$actual = ($covered / $statements) * 100;

printf("Coverage: %.2f%% (%d/%d statements, gate %.2f%%)\n", $actual, $covered, $statements, $threshold);

if ($actual < $threshold) {
    fwrite(STDERR, "Coverage gate gagal: {$actual}% < {$threshold}%.\n");
    exit(1);
}
