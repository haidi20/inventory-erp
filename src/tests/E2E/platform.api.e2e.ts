import { test, expect } from 'e2e';
import { apiRequest } from './support/api';

test.describe('Platform settings API', { tags: ['core', 'platform', 'api'] }, () => {
    test('lists users and backup history', async ({ app }) => {
        const users = await apiRequest(app, '/settings/users');
        expect(users.status).toBe(200);
        expect(await users.json()).toBeTruthy();

        const backups = await apiRequest(app, '/settings/backups');
        expect(backups.status).toBe(200);
        expect(await backups.json()).toBeTruthy();
    });

    test('prevents anonymous user management', async ({ app }) => {
        const response = await fetch(new URL('/api/v1/settings/users', app.baseUrl), {
            headers: { accept: 'application/json' },
        });
        expect(response.status).toBe(401);
    });
});
