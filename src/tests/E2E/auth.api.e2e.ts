import { test, expect } from 'e2e';
import { apiRequest } from './support/api';

test.describe('Authentication and session API', { tags: ['core', 'api'] }, () => {
    test('rejects protected API access without a bearer token', async ({ app }) => {
        const response = await fetch(new URL('/api/v1/settings/users', app.baseUrl), {
            headers: { accept: 'application/json' },
        });
        expect(response.status).toBe(401);
    });

    test('rejects invalid API credentials', async ({ app }) => {
        const response = await fetch(new URL('/api/v1/auth/login', app.baseUrl), {
            method: 'POST',
            headers: { 'content-type': 'application/json', accept: 'application/json' },
            body: JSON.stringify({ email: 'invalid@klinik.local', password: 'invalid-password' }),
        });
        expect(response.status).toBe(401);
    });

    test('returns the authenticated superadmin profile', async ({ app }) => {
        const response = await apiRequest(app, '/auth/me');
        expect(response.status).toBe(200);

        const body = (await response.json()) as Record<string, any>;
        expect(body?.data?.uuid ?? body?.uuid).toBeTruthy();
    });

    test('validates API login payload', async ({ app }) => {
        const response = await fetch(new URL('/api/v1/auth/login', app.baseUrl), {
            method: 'POST',
            headers: { 'content-type': 'application/json', accept: 'application/json' },
            body: JSON.stringify({ email: 'not-an-email', password: '123' }),
        });
        expect(response.status).toBe(422);
    });
});
