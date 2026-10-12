import { test, expect } from 'e2e';
import { apiRequest, apiResponse, firstResourceItem, resourceUuid, silentDelete, uniqueCode } from './support/api';

test.describe('Clinical registration API', { tags: ['clinical', 'api'] }, () => {
    test('exposes patient, registration, encounter, and complaint lists', async ({ app }) => {
        for (const path of ['/patients', '/registrations', '/encounters', '/operational/complaints']) {
            const response = await apiRequest(app, path);
            expect(response.status).toBe(200);
            expect(await response.json()).toBeTruthy();
        }
    });

    test('runs patient CRUD lifecycle', async ({ app }) => {
        const code = uniqueCode('PAS');
        const created = await apiResponse(app, '/patients', {
            method: 'POST',
            body: JSON.stringify({
                medical_record_no: code,
                name: `E2E Pasien ${code}`,
                gender: 'L',
                phone: '081200000000',
                email: `${code.toLowerCase()}@e2e.klinik.local`,
                address: 'Alamat E2E',
                is_active: true,
            }),
        });
        expect(created.status).toBe(200);
        const uuid = resourceUuid(created.body);

        try {
            const shown = await apiResponse(app, `/patients/${uuid}`);
            expect(shown.status).toBe(200);
            expect((await apiResponse(app, `/patients/${uuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Pasien Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            expect((await apiRequest(app, `/patients/${uuid}`, { method: 'DELETE' })).status).toBe(204);
        }
    });

    test('rejects patient without a medical record number', async ({ app }) => {
        const response = await apiResponse(app, '/patients', {
            method: 'POST',
            body: JSON.stringify({ name: 'Pasien tanpa nomor RM' }),
        });
        expect(response.status).toBe(422);
    });

    test('validates encounter, lab, and radiology payloads', async ({ app }) => {
        const code = uniqueCode('REG');
        const patient = await apiResponse(app, '/patients', {
            method: 'POST',
            body: JSON.stringify({
                medical_record_no: code,
                name: `E2E Pasien Alur ${code}`,
                is_active: true,
            }),
        });
        expect(patient.status).toBe(200);
        const patientUuid = resourceUuid(patient.body);

        const invalidEncounter = await apiResponse(app, '/encounters', {
            method: 'POST',
            body: JSON.stringify({ patient_uuid: patientUuid }),
        });
        expect(invalidEncounter.status).toBe(422);

        const invalidLabOrder = await apiResponse(app, '/clinic/lab-orders', {
            method: 'POST',
            body: JSON.stringify({ clinical_notes: 'Lab E2E tanpa pasien' }),
        });
        expect(invalidLabOrder.status).toBe(422);

        const invalidRadiologyOrder = await apiResponse(app, '/clinic/radiology-orders', {
            method: 'POST',
            body: JSON.stringify({
                patient_uuid: patientUuid,
                modality: 'X-Ray',
            }),
        });
        expect(invalidRadiologyOrder.status).toBe(422);

        try {
        } finally {
            await silentDelete(app, `/patients/${patientUuid}`);
        }
    });

    test('validates complaints and missing complaint records', async ({ app }) => {
        const code = uniqueCode('CMP');
        const invalid = await apiResponse(app, '/operational/complaints', {
            method: 'POST',
            body: JSON.stringify({
                content: 'Pengaduan tanpa subjek dan tipe.',
            }),
        });
        expect(invalid.status).toBe(422);
        expect((await apiResponse(app, `/operational/complaints/${code}`)).status).toBe(404);
    });
});
