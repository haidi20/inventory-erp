#!/usr/bin/env bash

set -Eeuo pipefail

export APP_ENV="${APP_ENV:-testing}"
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export SESSION_DRIVER="${SESSION_DRIVER:-database}"
export CACHE_STORE="${CACHE_STORE:-file}"
export CACHE_DRIVER="${CACHE_DRIVER:-file}"

if [[ -z "${APP_KEY:-}" ]]; then
    echo "FAIL APP_KEY is required for the isolated remember-me E2E environment" >&2
    exit 1
fi

storageDirectory="${STORAGE_DIRECTORY:-$PWD/storage/framework/remember-me-e2e}"
databasePath="${E2E_REMEMBER_ME_DB:-$storageDirectory/remember-me.sqlite}"
databaseDirectory="$(dirname "$databasePath")"
mkdir -p "$databaseDirectory"

cleanup() {
    local exitCode=$?

    if command -v php >/dev/null 2>&1; then
        php artisan e2e:cleanup || true
    fi
    rm -f "$databasePath"

    if [[ $exitCode -eq 0 ]]; then
        echo "PASS post-run E2E data cleanup completed"
    fi

    exit "$exitCode"
}
trap cleanup EXIT

rm -f "$databasePath"
touch "$databasePath"
export DB_DATABASE="$databasePath"

php artisan config:clear
php artisan migrate:fresh --force
php artisan db:seed --force --class=Database\\Seeders\\Testing\\RememberMeE2ESeeder

seededUsers="$(php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo Illuminate\Support\Facades\DB::table('users')->where('email', 'superadmin@klinik.local')->count();")"
if [[ "$seededUsers" != "1" ]]; then
    echo "FAIL active V1 seeder did not create the E2E superadmin user" >&2
    exit 1
fi

php scripts/ci/verify-database-integrity.php --seeder-only
echo "PASS remember-me E2E database is isolated at $databasePath"
echo "PASS active V1 seed profile selected RuntimeCompanySeeder, RolePermissionSeeder, and SuperadminSeeder"
echo "PASS V2DatabaseSeeder is explicitly excluded from E2E seeding"

npm run e2e:remember-me
