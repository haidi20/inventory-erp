<?php

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);
$app = require $projectRoot.'/bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

$arguments = array_slice($argv, 1);
$seederOnly = in_array('--seeder-only', $arguments, true);
$migrationsOnly = in_array('--migrations-only', $arguments, true);

function assertSeederIsUnregistered(string $projectRoot): void
{
    $seederDirectories = [
        $projectRoot.'/database/seeders',
        $projectRoot.'/modules',
    ];
    $violations = [];

    foreach ($seederDirectories as $seederDirectory) {
        if (! is_dir($seederDirectory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($seederDirectory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            if (
                ! $file->isFile()
                || $file->getExtension() !== 'php'
                || ! str_contains($path, '/database/seeders/')
                || str_contains($path, '/database/seeders/V2/')
            ) {
                continue;
            }

            if (str_contains((string) file_get_contents($path), 'V2DatabaseSeeder')) {
                $violations[] = $path;
            }
        }
    }

    if ($violations !== []) {
        throw new RuntimeException(
            'V2 seeder is registered outside database/seeders/V2: '.implode(', ', $violations)
        );
    }

    echo "PASS V2DatabaseSeeder remains unregistered outside V2\n";
}

function runArtisanCommand(string $command, array $parameters = []): string
{
    $exitCode = Artisan::call($command, $parameters);
    $output = Artisan::output();

    echo $output;

    if ($exitCode !== 0) {
        throw new RuntimeException(sprintf('%s failed with exit code %d', $command, $exitCode));
    }

    return $output;
}

function assertMigrationLifecycle(string $projectRoot): void
{
    $storageDirectory = $projectRoot.'/storage/framework';

    if (! is_dir($storageDirectory) && ! mkdir($storageDirectory, 0775, true) && ! is_dir($storageDirectory)) {
        throw new RuntimeException('Unable to prepare storage/framework for the SQLite evidence database');
    }

    $databasePath = $storageDirectory.'/devsecops-migration.sqlite';

    if (is_file($databasePath) && ! unlink($databasePath)) {
        throw new RuntimeException('Unable to remove the previous SQLite evidence database');
    }

    if (! touch($databasePath)) {
        throw new RuntimeException('Unable to create the SQLite evidence database');
    }

    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => $databasePath,
        'telescope.storage.database.connection' => 'sqlite',
        'pulse.storage.database.connection' => 'sqlite',
    ]);
    DB::purge('sqlite');

    $migrateParameters = ['--database' => 'sqlite', '--force' => true];
    $firstRun = runArtisanCommand('migrate', $migrateParameters);

    if (str_contains($firstRun, 'Nothing to migrate.')) {
        throw new RuntimeException('The first migration run did not create the schema');
    }

    $secondRun = runArtisanCommand('migrate', $migrateParameters);

    if (! str_contains($secondRun, 'Nothing to migrate.')) {
        throw new RuntimeException('The second migration run was not idempotent');
    }

    runArtisanCommand('migrate:rollback', ['--database' => 'sqlite', '--force' => true]);
    $reapplyRun = runArtisanCommand('migrate', $migrateParameters);

    if (
        str_contains($reapplyRun, 'Nothing to migrate.')
        || DB::table('migrations')->count() === 0
    ) {
        throw new RuntimeException('Migration rollback/reapply lifecycle failed');
    }

    echo "PASS SQLite fresh migration, idempotent rerun, rollback, and reapply completed\n";
}

if ($seederOnly && $migrationsOnly) {
    fwrite(STDERR, "Use either --seeder-only or --migrations-only\n");
    exit(1);
}

try {
    if (! $migrationsOnly) {
        assertSeederIsUnregistered($projectRoot);
    }

    if (! $seederOnly) {
        assertMigrationLifecycle($projectRoot);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL '.$exception->getMessage().PHP_EOL);
    exit(1);
}
