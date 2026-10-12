import { test, expect } from 'e2e';
import { apiRequest, apiResponse, resourceUuid, silentDelete, uniqueCode } from './support/api';

test.describe('Finance accounting API', { tags: ['finance', 'api'] }, () => {
    test('exposes accounting lists and stable financial reports', async ({ app }) => {
        const paths = [
            '/accounts',
            '/journals',
            '/general-ledger',
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
            expect(await response.json()).toBeTruthy();
        }
    });

    test('validates trial balance period requirement', async ({ app }) => {
        const response = await apiResponse(app, '/reports/trial-balance');
        expect(response.status).toBe(422);
    });

    test('runs account CRUD lifecycle', async ({ app }) => {
        const code = uniqueCode('COA');
        const created = await apiResponse(app, '/accounts', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Kas ${code}`,
                type: 'asset',
                is_active: true,
            }),
        });
        expect(created.status).toBe(201);
        const uuid = resourceUuid(created.body);

        try {
            expect((await apiResponse(app, `/accounts/${uuid}`)).status).toBe(200);
            expect((await apiResponse(app, `/accounts/${uuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Kas Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/accounts/${uuid}`);
        }
    });

    test('rejects account without required fields', async ({ app }) => {
        const response = await apiResponse(app, '/accounts', {
            method: 'POST',
            body: JSON.stringify({ name: 'Akun tanpa kode' }),
        });
        expect(response.status).toBe(422);
    });

    test('blocks journal creation when the accounting period is missing', async ({ app }) => {
        const code = uniqueCode('JRN');
        const debitAccount = await apiResponse(app, '/accounts', {
            method: 'POST',
            body: JSON.stringify({ code, name: `E2E Kas Jurnal ${code}`, type: 'asset', is_active: true }),
        });
        expect(debitAccount.status).toBe(201);
        const debitUuid = resourceUuid(debitAccount.body);

        const creditAccount = await apiResponse(app, '/accounts', {
            method: 'POST',
            body: JSON.stringify({ code: `${code}C`, name: `E2E Pendapatan Jurnal ${code}`, type: 'revenue', is_active: true }),
        });
        expect(creditAccount.status).toBe(201);
        const creditUuid = resourceUuid(creditAccount.body);

        try {
            const journal = await apiResponse(app, '/journals', {
                method: 'POST',
                body: JSON.stringify({
                    reference_no: code,
                    date: new Date().toISOString().slice(0, 10),
                    description: 'Jurnal E2E seimbang',
                    reference_uuid: debitUuid,
                    reference_type: 'general',
                    status: 'posted',
                    lines: [
                        { account_uuid: debitUuid, debit: 100000, credit: 0, description: 'Debit E2E' },
                        { account_uuid: creditUuid, debit: 0, credit: 100000, description: 'Kredit E2E' },
                    ],
                }),
            });
            expect(journal.status).toBe(422);
        } finally {
            await silentDelete(app, `/accounts/${debitUuid}`);
            await silentDelete(app, `/accounts/${creditUuid}`);
        }
    });

    test('rejects an unbalanced journal', async ({ app }) => {
        const code = uniqueCode('JRN');
        const debitAccount = await apiResponse(app, '/accounts', {
            method: 'POST',
            body: JSON.stringify({ code, name: `E2E Kas Tidak Seimbang ${code}`, type: 'asset', is_active: true }),
        });
        const debitUuid = resourceUuid(debitAccount.body);

        const creditAccount = await apiResponse(app, '/accounts', {
            method: 'POST',
            body: JSON.stringify({ code: `${code}C`, name: `E2E Pendapatan Tidak Seimbang ${code}`, type: 'revenue', is_active: true }),
        });
        const creditUuid = resourceUuid(creditAccount.body);

        try {
            const journal = await apiResponse(app, '/journals', {
                method: 'POST',
                body: JSON.stringify({
                    reference_no: code,
                    date: new Date().toISOString().slice(0, 10),
                    reference_uuid: debitUuid,
                    status: 'posted',
                    lines: [
                        { account_uuid: debitUuid, debit: 100000, credit: 0 },
                        { account_uuid: creditUuid, debit: 0, credit: 90000 },
                    ],
                }),
            });
            expect(journal.status).toBe(422);
        } finally {
            await silentDelete(app, `/accounts/${debitUuid}`);
            await silentDelete(app, `/accounts/${creditUuid}`);
        }
    });
});
