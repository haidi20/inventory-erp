#!/usr/bin/env bash

set -Eeuo pipefail

export E2E_MANAGE_SERVER=1

serverPid=""

cleanup() {
    local exitCode=$?

    if [[ -n "$serverPid" ]] && kill -0 "$serverPid" 2>/dev/null; then
        pkill -P "$serverPid" 2>/dev/null || true
        kill "$serverPid" 2>/dev/null || true
        wait "$serverPid" 2>/dev/null || true
    fi

    if command -v php >/dev/null 2>&1; then
        php artisan e2e:cleanup || true
    fi

    if [[ $exitCode -eq 0 ]]; then
        echo "PASS post-run E2E data cleanup completed"
    fi

    exit "$exitCode"
}
trap cleanup EXIT

start_server() {
    PHP_CLI_SERVER_WORKERS=4 php -S 127.0.0.1:8000 vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php &
    serverPid=$!

    for waitAttempt in $(seq 1 30); do
        if curl --silent --fail http://127.0.0.1:8000/up >/dev/null 2>&1; then
            echo "PASS Playwright Laravel server is ready"
            return 0
        fi

        sleep 1
    done

    echo "FAIL Playwright Laravel server did not become ready" >&2
    return 1
}

start_server
npm run e2e:report
