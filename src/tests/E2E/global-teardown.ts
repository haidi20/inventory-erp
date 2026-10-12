import { execSync } from 'node:child_process';

export default function globalTeardown(): void {
    if (process.env.E2E_SKIP_CLEANUP === '1') {
        return;
    }

    execSync('php artisan e2e:cleanup', {
        cwd: process.cwd(),
        stdio: 'inherit',
    });
}
