<?php

/**
 * Codemod: pindahkan FQN inline (\App\..., \Database\..., \Tests\...) ke use statement di atas file.
 * Tokenizer-based (token_get_all) sehingga string/docblock tidak tersentuh.
 *
 * Usage:
 *   php scripts/refactor-imports.php --dry-run
 *   php scripts/refactor-imports.php
 */

$isDryRun = in_array('--dry-run', $argv, true);
$root = dirname(__DIR__);
$importableRoots = ['App', 'Database', 'Tests'];

$files = array_filter(array_map('trim', explode("\n", (string) shell_exec('git ls-files "*.php"'))));

$changedFiles = [];
$skippedFiles = [];

foreach ($files as $rel) {
    if ($rel === '' || str_starts_with($rel, 'scripts/refactor-imports.php')) {
        continue;
    }

    $abs = $root . '/' . $rel;
    $code = file_get_contents($abs);
    $result = refactor($code);

    if ($result === null || $result === $code) {
        continue;
    }

    if ($isDryRun) {
        $changedFiles[] = $rel;
        continue;
    }

    file_put_contents($abs, $result);

    $lintOutput = [];
    exec('php -l ' . escapeshellarg($abs) . ' 2>&1', $lintOutput, $lintCode);
    if ($lintCode !== 0) {
        var_export($lintOutput);
        file_put_contents($abs, $code);
        $skippedFiles[] = $rel;
        echo "LINT FAIL (reverted): {$rel}\n";
        continue;
    }

    $changedFiles[] = $rel;
}

echo ($isDryRun ? '[DRY RUN] ' : '') . 'File diubah: ' . count($changedFiles) . "\n";
foreach ($changedFiles as $file) {
    echo "  {$file}\n";
}

if ($skippedFiles !== []) {
    echo 'File di-skip (lint gagal): ' . count($skippedFiles) . "\n";
}

function refactor(string $code): ?string
{
    $tokens = token_get_all($code);
    $count = count($tokens);

    // 1. Peta import existing (top-level region).
    $existing = [];
    $regionHasGroupedOrNonClass = false;
    $firstUseIndex = -1;
    $lastUseIndex = -1;
    $namespaceEndIndex = -1;

    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];

        if (! is_array($token)) {
            break;
        }

        [$type, $text] = $token;

        if (in_array($type, [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        if ($type === T_DECLARE) {
            while ($i < $count && $tokens[$i] !== ';') {
                $i++;
            }

            continue;
        }

        if ($type === T_NAMESPACE) {
            while ($i < $count && $tokens[$i] !== ';') {
                $i++;
            }

            $namespaceEndIndex = $i;

            continue;
        }

        if ($type !== T_USE) {
            break;
        }

        $end = $i;
        while ($end < $count && $tokens[$end] !== ';') {
            $end++;
        }

        $statement = '';
        for ($k = $i; $k <= $end; $k++) {
            $statement .= is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
        }

        parseUseStatement($statement, $existing, $regionHasGroupedOrNonClass);

        if ($firstUseIndex === -1) {
            $firstUseIndex = $i;
        }

        $lastUseIndex = $end;
        $i = $end;
    }

    // 2. Kumpulkan FQN inline di seluruh body (T_NAME_FULLY_QUALIFIED).
    $inline = [];

    foreach ($tokens as $token) {
        if (! is_array($token) || $token[0] !== T_NAME_FULLY_QUALIFIED) {
            continue;
        }

        $fqn = ltrim($token[1], '\\');
        $firstSegment = strtok($fqn, '\\');

        if (! in_array($firstSegment, ['App', 'Database', 'Tests'], true)) {
            continue;
        }

        $inline[$fqn] = true;
    }

    if ($inline === []) {
        return null;
    }

    // 3. Nama kelas yang dideklarasikan di file ini (hindari collision self-reference).
    $declared = [];

    foreach ($tokens as $index => $token) {
        if (is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
            $next = $tokens[$index + 1] ?? null;
            $nextNext = $tokens[$index + 2] ?? null;

            if (is_array($next) && $next[0] === T_WHITESPACE && is_array($nextNext) && $nextNext[0] === T_STRING) {
                $declared[strtolower($nextNext[1])] = true;
            }
        }
    }

    // 4. Tentukan FQN yang bisa diimport + nama pendeknya.
    $newImports = [];
    $replacements = [];

    foreach (array_keys($inline) as $fqn) {
        $short = substr($fqn, (int) strrpos($fqn, '\\') + 1);

        if (isset($existing[$short])) {
            if (strcasecmp($existing[$short], $fqn) === 0) {
                $replacements[$fqn] = $short;
            }

            continue;
        }

        if (isset($declared[strtolower($short)]) || isset($newImports[$short])) {
            continue;
        }

        $newImports[$short] = $fqn;
        $replacements[$fqn] = $short;
    }

    if ($replacements === [] && $newImports === []) {
        return null;
    }

    // 5. Rebuild source dengan substitusi token.
    $out = '';

    foreach ($tokens as $token) {
        if (is_array($token) && $token[0] === T_NAME_FULLY_QUALIFIED) {
            $fqn = ltrim($token[1], '\\');

            if (isset($replacements[$fqn])) {
                $out .= $replacements[$fqn];

                continue;
            }
        }

        $out .= is_array($token) ? $token[1] : $token;
    }

    // 6. Sisipkan/merge use statements.
    if ($newImports === []) {
        return $out;
    }

    $lines = $newImports;
    uasort($lines, static fn (string $left, string $right): int => strcmp($left, $right));

    if ($firstUseIndex !== -1 && ! $regionHasGroupedOrNonClass) {
        $all = array_merge(
            array_map(
                static fn (string $alias, string $fqn): string => $alias . '|' . $fqn,
                array_keys($existing),
                array_values($existing)
            ),
            array_map(
                static fn (string $alias, string $fqn): string => $alias . '|' . $fqn,
                array_keys($lines),
                array_values($lines)
            )
        );

        $all = array_unique($all);
        sort($all);

        $merged = array_map(
            static function (string $entry): string {
                [$alias, $fqn] = explode('|', $entry, 2);
                $natural = substr($fqn, (int) strrpos($fqn, '\\') + 1);

                return 'use ' . $fqn . ($alias === $natural ? '' : ' as ' . $alias) . ';';
            },
            $all
        );

        $startOffset = tokenStartOffset($tokens, $firstUseIndex);
        $endOffset = tokenEndOffset($tokens, $lastUseIndex);

        return substr($out, 0, $startOffset)
            . implode("\n", $merged)
            . substr($out, $endOffset);
    }

    $block = "\n" . implode("\n", array_map(
        static fn (string $fqn): string => 'use ' . $fqn . ';',
        $lines
    )) . "\n";

    if ($lastUseIndex !== -1) {
        $endOffset = tokenEndOffset($tokens, $lastUseIndex);

        return substr($out, 0, $endOffset) . $block . substr($out, $endOffset);
    }

    if ($namespaceEndIndex !== -1) {
        $endOffset = tokenEndOffset($tokens, $namespaceEndIndex);

        return substr($out, 0, $endOffset) . $block . substr($out, $endOffset);
    }

    $openTagEnd = tokenEndOffset($tokens, 0);

    return substr($out, 0, $openTagEnd) . $block . substr($out, $openTagEnd);
}

function parseUseStatement(string $statement, array &$existing, bool &$hasGroupedOrNonClass): void
{
    $statement = trim($statement);
    $statement = preg_replace('/^use\s+/', '', $statement);
    $statement = rtrim($statement, ';');

    if (preg_match('/^(function|const)\s/', $statement)) {
        $hasGroupedOrNonClass = true;

        return;
    }

    if (str_contains($statement, '{')) {
        $hasGroupedOrNonClass = true;

        return;
    }

    foreach (explode(',', $statement) as $clause) {
        $clause = trim($clause);

        if ($clause === '') {
            continue;
        }

        if (preg_match('/^(\\\\?[A-Za-z0-9_\\\\]+)(?:\s+as\s+([A-Za-z0-9_]+))?$/i', $clause, $matches)) {
            $fqn = ltrim($matches[1], '\\');
            $short = $matches[2] ?? substr($fqn, (int) strrpos($fqn, '\\') + 1);
            $existing[$short] = $fqn;
        }
    }
}

function tokenStartOffset(array $tokens, int $index): int
{
    $offset = 0;

    for ($i = 0; $i < $index; $i++) {
        $offset += strlen(is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i]);
    }

    return $offset;
}

function tokenEndOffset(array $tokens, int $index): int
{
    return tokenStartOffset($tokens, $index + 1);
}
