import { test, expect } from 'e2e';
import { apiResponse } from './support/api';

test.describe('Cross-module API security', { tags: ['api', 'security'] }, () => {
    test('rejects protected module endpoints without authentication', async ({ app }) => {
        const paths = [
            '/master/companies',
            '/patients',
            '/products',
            '/accounts',
            '/settings/users',
            '/reports/income-statement',
        ];

        for (const path of paths) {
            const response = await fetch(new URL(`/api/v1${path}`, app.baseUrl), {
                headers: { accept: 'application/json' },
            });
            expect(response.status, path).toBe(401);
        }
    });

    test('returns 404 for missing resources in every domain', async ({ app }) => {
        const missingUuid = crypto.randomUUID();
        const paths = [
            `/master/companies/${missingUuid}`,
            `/patients/${missingUuid}`,
            `/products/${missingUuid}`,
            `/accounts/${missingUuid}`,
            `/suppliers/${missingUuid}`,
            `/registrations/${missingUuid}`,
        ];

        for (const path of paths) {
            const response = await apiResponse(app, path);
            expect(response.status, path).toBe(404);
        }
    });
});
