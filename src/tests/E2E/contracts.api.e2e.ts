import { test, expect } from 'e2e';
import { apiRequest, apiResponse, resourceUuid, uniqueCode } from './support/api';

test.describe('Cross-module API contracts', { tags: ['api', 'contract'] }, () => {
    test('validates mutation payloads across modules', async ({ app }) => {
        const cases: Array<[string, Record<string, unknown>]> = [
            ['/settings/users', {}],
            ['/registrations', {}],
            ['/products', {}],
            ['/accounts', {}],
            ['/masters/treatments', {}],
            ['/lov/sets', {}],
        ];

        for (const [path, payload] of cases) {
            const response = await apiResponse(app, path, {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            expect(response.status, path).toBe(422);
        }
    });

    test.skip('runs treatment CRUD lifecycle', async ({ app }) => {
        const code = uniqueCode('TRT');
        const created = await apiResponse(app, '/masters/treatments', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Treatment ${code}`,
                duration_minutes: 30,
                price: 180000,
                doctor_fee: 60000,
                is_active: true,
            }),
        });
        expect(created.status).toBe(200);
        const uuid = resourceUuid(created.body);

        try {
            const list = await apiRequest(app, '/masters/treatments');
            expect(list.status).toBe(200);

            const updated = await apiResponse(app, `/masters/treatments/${uuid}`, {
                method: 'PUT',
                body: JSON.stringify({
                    name: `E2E Treatment Ubah ${code}`,
                    duration_minutes: 45,
                    price: 210000,
                    doctor_fee: 70000,
                    is_active: true,
                }),
            });
            expect(updated.status).toBe(200);
        } finally {
            expect((await apiRequest(app, `/masters/treatments/${uuid}`, { method: 'DELETE' })).status).toBe(204);
        }
    });
});
