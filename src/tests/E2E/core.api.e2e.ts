import { test, expect } from 'e2e';
import { apiRequest, apiResponse, resourceUuid, silentDelete, uniqueCode } from './support/api';

test.describe('Core organization API', { tags: ['core', 'api'] }, () => {
    test('lists companies and branches', async ({ app }) => {
        for (const path of ['/master/companies', '/master/branches']) {
            const response = await apiRequest(app, path);
            expect(response.status).toBe(200);
            expect(await response.json()).toBeTruthy();
        }
    });

    test('creates, reads, updates, and deletes a company and branch', async ({ app }) => {
        const code = uniqueCode('ORG');
        let branchUuid: string | undefined;

        const companyResponse = await apiResponse(app, '/master/companies', {
            method: 'POST',
            body: JSON.stringify({
                name: `E2E Perusahaan ${code}`,
                clinic_type: 'klinik pratama',
                registration_number: code,
                email: `${code.toLowerCase()}@e2e.klinik.local`,
                is_active: true,
            }),
        });
        expect(companyResponse.status).toBe(201);
        const companyUuid = resourceUuid(companyResponse.body);

        try {
            const showCompany = await apiResponse(app, `/master/companies/${companyUuid}`);
            expect(showCompany.status).toBe(200);

            const updateCompany = await apiResponse(app, `/master/companies/${companyUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Perusahaan Ubah ${code}` }),
            });
            expect(updateCompany.status).toBe(200);

            const branchResponse = await apiResponse(app, '/master/branches', {
                method: 'POST',
                body: JSON.stringify({
                    company_uuid: companyUuid,
                    name: `E2E Cabang ${code}`,
                    short_name: code.replace(/[^A-Z0-9]/g, '').slice(-5),
                    is_active: true,
                }),
            });
            expect(branchResponse.status).toBe(201);
            branchUuid = resourceUuid(branchResponse.body);

            const branchUpdate = await apiResponse(app, `/master/branches/${branchUuid}`, {
                method: 'PUT',
                body: JSON.stringify({
                    company_uuid: companyUuid,
                    name: `E2E Cabang Ubah ${code}`,
                    short_name: code.replace(/[^A-Z0-9]/g, '').slice(-5),
                }),
            });
            expect(branchUpdate.status).toBe(200);
        } finally {
            if (branchUuid) {
                await silentDelete(app, `/master/branches/${branchUuid}`);
            }
            await silentDelete(app, `/master/companies/${companyUuid}`);
        }
    });

    test('rejects company without a name', async ({ app }) => {
        const response = await apiResponse(app, '/master/companies', {
            method: 'POST',
            body: JSON.stringify({ name: '' }),
        });
        expect([422, 500]).toContain(response.status);
    });
});
