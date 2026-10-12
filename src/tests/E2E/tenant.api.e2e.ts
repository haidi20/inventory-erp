import { test, expect } from 'e2e';
import { apiLoginToken, apiRequest, apiResponse, resourceUuid, uniqueCode } from './support/api';

type ApiJson = Record<string, any>;

function tenantHeaders(companyUuid: string, branchUuid: string): Record<string, string> {
    return {
        'x-klinik-company-uuid': companyUuid,
        'x-klinik-branch-uuid': branchUuid,
    };
}

test.describe('Tenant isolation API', { tags: ['api', 'security', 'tenant'] }, () => {
    test('isolates tenant data while allowing an explicit superadmin bypass', async ({ app }) => {
        const code = uniqueCode('TEN');
        let patientUuid: string | undefined;

        const company = await apiResponse(app, '/master/companies', {
            method: 'POST',
            body: JSON.stringify({
                name: `E2E Perusahaan Isolasi ${code}`,
                clinic_type: 'klinik pratama',
                registration_number: code,
                email: `${code.toLowerCase()}@e2e.klinik.local`,
                is_active: true,
            }),
        });
        expect(company.status).toBe(201);
        const companyUuid = resourceUuid(company.body);

        const branch = await apiResponse(app, '/master/branches', {
            method: 'POST',
            body: JSON.stringify({
                company_uuid: companyUuid,
                name: `E2E Cabang Isolasi ${code}`,
                short_name: code.replace(/[^A-Z0-9]/g, '').slice(-5),
                is_active: true,
            }),
        });
        expect(branch.status).toBe(201);
        const branchUuid = resourceUuid(branch.body);
        const headers = tenantHeaders(companyUuid, branchUuid);

        const patient = await apiRequest(app, '/patients', {
            method: 'POST',
            headers,
            body: JSON.stringify({
                medical_record_no: code,
                name: `E2E Pasien Isolasi ${code}`,
                gender: 'L',
                is_active: true,
            }),
        });
        expect(patient.status).toBe(200);
        patientUuid = resourceUuid(await patient.json());

        try {
            const superadminShow = await apiRequest(app, `/patients/${patientUuid}`, { headers });
            expect(superadminShow.status).toBe(200);

            const staffToken = await apiLoginToken(
                app,
                process.env.E2E_STAFF_EMAIL ?? 'admin@klinik.local',
                process.env.E2E_STAFF_PASSWORD ?? 'klinik123',
            );

            const forbiddenShow = await fetch(new URL(`/api/v1/patients/${patientUuid}`, app.baseUrl), {
                headers: { authorization: `Bearer ${staffToken}`, ...headers },
            });
            expect(forbiddenShow.status).toBe(403);

            const isolatedShow = await fetch(new URL(`/api/v1/patients/${patientUuid}`, app.baseUrl), {
                headers: { authorization: `Bearer ${staffToken}` },
            });
            expect(isolatedShow.status).toBe(404);
        } finally {
            if (patientUuid) {
                await apiRequest(app, `/patients/${patientUuid}`, { method: 'DELETE', headers });
            }
            await apiRequest(app, `/master/branches/${branchUuid}`, { method: 'DELETE', headers });
            await apiRequest(app, `/master/companies/${companyUuid}`, { method: 'DELETE' });
        }
    });
});
