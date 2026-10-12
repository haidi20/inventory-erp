import { test, expect } from 'e2e';
import { apiRequest } from './support/api';

test.describe('Analytics and reporting API', { tags: ['analytics', 'api'] }, () => {
    test('exposes analytics dashboard and stable reports', async ({ app }) => {
        const paths = [
            '/dashboard',
            '/reports/equity',
            '/reports/adjusted-trial-balance',
            '/reports/ar-aging',
            '/reports/ap-aging',
            '/reports/inventory',
            '/reports/fixed-assets',
            '/reports/tax',
        ];

        for (const path of paths) {
            const response = await apiRequest(app, path);
            expect(response.status).toBe(200);
            expect(await response.text()).toBeTruthy();
        }
    });

    test('rejects trial balance without period', async ({ app }) => {
        const response = await apiRequest(app, '/reports/trial-balance');
        expect(response.status).toBe(422);
    });
});
