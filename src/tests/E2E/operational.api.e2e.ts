import { test, expect } from 'e2e';
import { apiRequest, apiResponse, resourceUuid, silentDelete, uniqueCode } from './support/api';

type Resource = Record<string, any>;

async function firstResource(app: any, path: string): Promise<Resource> {
    const response = await apiRequest(app, path);
    expect(response.status).toBe(200);
    const body = (await response.json()) as any;
    const items = body?.data ?? body;
    expect(Array.isArray(items)).toBe(true);
    expect(items.length).toBeGreaterThan(0);
    return items[0];
}

test.describe('Operational master API', { tags: ['operational', 'api'] }, () => {
    test('exposes master list and option endpoints', async ({ app }) => {
        const paths = [
            '/products',
            '/products/filter-options',
            '/products/options?type=unit',
            '/products/options?type=warehouse',
            '/suppliers',
            '/employees',
            '/masters/units',
            '/masters/taxes',
            '/masters/treatments',
            '/masters/treatment-packages',
            '/masters/payment-terms',
            '/masters/price-levels',
            '/master/warehouses',
            '/lov/types',
            '/lov/sets',
        ];

        for (const path of paths) {
            const response = await apiRequest(app, path);
            expect(response.status).toBe(200);
            expect(await response.json()).toBeTruthy();
        }
    });

    test('runs full product CRUD lifecycle', async ({ app }) => {
        const code = uniqueCode('PRD');
        const unit = await firstResource(app, '/products/options?type=unit');
        const warehouse = await firstResource(app, '/products/options?type=warehouse');

        const created = await apiResponse(app, '/products', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Produk ${code}`,
                description: 'Data produk yang dibuat otomatis oleh E2E',
                barcode: code,
                base_unit_uuid: unit.uuid,
                price_buy: 15000,
                price_sell: 20000,
                min_stock: 2,
                max_stock: 100,
                is_active: true,
                units: [{ unit_uuid: unit.uuid, conversion_ratio: 1, is_default: true, is_active: true }],
                opening_balances: [{ location_uuid: warehouse.uuid, period: '2026-01', opening_qty: 10 }],
            }),
        });
        expect(created.status).toBe(200);
        const productUuid = resourceUuid(created.body);

        try {
            const shown = await apiResponse(app, `/products/${productUuid}`);
            expect(shown.status).toBe(200);

            const updated = await apiResponse(app, `/products/${productUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Produk Ubah ${code}`, price_sell: 22500 }),
            });
            expect(updated.status).toBe(200);
        } finally {
            await silentDelete(app, `/products/${productUuid}`);
        }
    });

    test('rejects product without required fields', async ({ app }) => {
        const response = await apiResponse(app, '/products', {
            method: 'POST',
            body: JSON.stringify({ name: 'Produk tanpa kode' }),
        });
        expect(response.status).toBe(422);
    });

    test('runs supplier CRUD lifecycle', async ({ app }) => {
        const code = uniqueCode('SUP');
        const created = await apiResponse(app, '/suppliers', {
            method: 'POST',
            body: JSON.stringify({
                name: `E2E Supplier ${code}`,
                email: `${code.toLowerCase()}@e2e.klinik.local`,
                phone: '081200000000',
                term_days: 14,
                is_active: true,
            }),
        });
        expect(created.status).toBe(200);
        const uuid = resourceUuid(created.body);

        try {
            expect((await apiResponse(app, `/suppliers/${uuid}`)).status).toBe(200);
            expect((await apiResponse(app, `/suppliers/${uuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Supplier Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/suppliers/${uuid}`);
        }
    });

    test('runs employee CRUD lifecycle', async ({ app }) => {
        const code = uniqueCode('EMP');
        const nik = code.replace(/[^0-9]/g, '').padEnd(8, '0').slice(0, 12);
        const created = await apiResponse(app, '/employees', {
            method: 'POST',
            body: JSON.stringify({
                nik,
                name: `E2E Dokter ${code}`,
                role: 'dokter',
                specialization: 'Umum',
                fee_default: 100000,
                is_active: true,
            }),
        });
        expect(created.status).toBe(200);
        const uuid = resourceUuid(created.body);

        try {
            expect((await apiResponse(app, `/employees/${uuid}`)).status).toBe(200);
            expect((await apiResponse(app, `/employees/${uuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Dokter Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/employees/${uuid}`);
        }
    });

    test('runs warehouse CRUD lifecycle', async ({ app }) => {
        const code = uniqueCode('WH');
        const created = await apiResponse(app, '/master/warehouses', {
            method: 'POST',
            body: JSON.stringify({ code, name: `E2E Gudang ${code}`, is_active: true }),
        });
        expect(created.status).toBe(200);
        const uuid = resourceUuid(created.body);

        try {
            expect((await apiResponse(app, `/master/warehouses/${uuid}`)).status).toBe(200);
            expect((await apiResponse(app, `/master/warehouses/${uuid}`, {
                method: 'PUT',
                body: JSON.stringify({ code, name: `E2E Gudang Ubah ${code}`, is_active: true }),
            })).status).toBe(200);
        } finally {
            await apiRequest(app, `/master/warehouses/${uuid}`, { method: 'DELETE' });
        }
    });

    test('runs master unit, tax, payment term, and price level CRUD', async ({ app }) => {
        const code = uniqueCode('MST');
        const shortCode = code.replace(/[^A-Z0-9]/g, '').slice(-5);

        const unit = await apiResponse(app, '/masters/units', {
            method: 'POST',
            body: JSON.stringify({ code, name: `E2E Unit ${code}`, is_active: true }),
        });
        expect(unit.status).toBe(200);
        const unitUuid = resourceUuid(unit.body);
        try {
            expect((await apiResponse(app, `/masters/units/${unitUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ code, name: `E2E Unit Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/masters/units/${unitUuid}`);
        }

        const tax = await apiResponse(app, '/masters/taxes', {
            method: 'POST',
            body: JSON.stringify({ tax_code: shortCode, percentage: 11, is_active: true }),
        });
        expect(tax.status).toBe(200);
        const taxUuid = resourceUuid(tax.body);
        try {
            expect((await apiResponse(app, `/masters/taxes/${taxUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ tax_code: shortCode, percentage: 12 }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/masters/taxes/${taxUuid}`);
        }

        const term = await apiResponse(app, '/masters/payment-terms', {
            method: 'POST',
            body: JSON.stringify({ name: `E2E Termin ${code}`, due_days: 30, is_active: true }),
        });
        expect(term.status).toBe(200);
        const termUuid = resourceUuid(term.body);
        try {
            expect((await apiResponse(app, `/masters/payment-terms/${termUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Termin Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/masters/payment-terms/${termUuid}`);
        }

        const level = await apiResponse(app, '/masters/price-levels', {
            method: 'POST',
            body: JSON.stringify({ code, name: `E2E Level ${code}`, is_active: true }),
        });
        expect(level.status).toBe(200);
        const levelUuid = resourceUuid(level.body);
        try {
            expect((await apiResponse(app, `/masters/price-levels/${levelUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ code, name: `E2E Level Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/masters/price-levels/${levelUuid}`);
        }
    });

    test('exports products and download templates', async ({ app }) => {
        for (const path of ['/products/export', '/products/import/template', '/masters/units/export', '/masters/units/import/template']) {
            const response = await apiRequest(app, path);
            expect(response.status).toBe(200);
        }
    });
});

test.describe('Operational transaction API', { tags: ['operational', 'api'] }, () => {
    test('runs service and service package CRUD', async ({ app }) => {
        const code = uniqueCode('SVC');
        const service = await apiResponse(app, '/services', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Layanan ${code}`,
                duration_minutes: 30,
                price: 150000,
                doctor_fee: 50000,
                is_active: true,
            }),
        });
        expect(service.status).toBe(201);
        const serviceUuid = resourceUuid(service.body);

        const servicePackage = await apiResponse(app, '/service-packages', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Paket ${code}`,
                price: 250000,
                is_active: true,
                items: [{ item_type: 'service', item_uuid: serviceUuid, qty: 1 }],
            }),
        });
        expect(servicePackage.status).toBe(201);
        const packageUuid = resourceUuid(servicePackage.body);

        try {
            expect((await apiResponse(app, `/services/${serviceUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Layanan Ubah ${code}` }),
            })).status).toBe(200);
            expect((await apiResponse(app, `/service-packages/${packageUuid}`, {
                method: 'PUT',
                body: JSON.stringify({ name: `E2E Paket Ubah ${code}` }),
            })).status).toBe(200);
        } finally {
            await silentDelete(app, `/service-packages/${packageUuid}`);
            await silentDelete(app, `/services/${serviceUuid}`);
        }
    });

    test('creates and reads a sale from a service', async ({ app }) => {
        const code = uniqueCode('SAL');
        const service = await apiResponse(app, '/services', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Penjualan ${code}`,
                duration_minutes: 30,
                price: 120000,
                doctor_fee: 50000,
                is_active: true,
            }),
        });
        expect(service.status).toBe(201);
        const serviceUuid = resourceUuid(service.body);

        try {
            const sale = await apiResponse(app, '/sales', {
                method: 'POST',
                body: JSON.stringify({
                    sale_no: code,
                    paid: 120000,
                    payment_status: 'paid',
                    payment_method: 'cash',
                    items: [{ item_type: 'service', item_uuid: serviceUuid, qty: 1, price: 120000 }],
                }),
            });
            expect(sale.status).toBe(201);
            const saleUuid = resourceUuid(sale.body);
            expect((await apiResponse(app, `/sales/${saleUuid}`)).status).toBe(200);
        } finally {
            await silentDelete(app, `/services/${serviceUuid}`);
        }
    });

    test('creates and deletes a purchase order', async ({ app }) => {
        const code = uniqueCode('PO');
        const supplier = await apiResponse(app, '/suppliers', {
            method: 'POST',
            body: JSON.stringify({ name: `E2E Supplier PO ${code}`, is_active: true }),
        });
        const supplierUuid = resourceUuid(supplier.body);
        const unit = await firstResource(app, '/products/options?type=unit');
        const warehouse = await firstResource(app, '/products/options?type=warehouse');
        const product = await apiResponse(app, '/products', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Produk PO ${code}`,
                base_unit_uuid: unit.uuid,
                is_active: true,
            }),
        });
        const productUuid = resourceUuid(product.body);

        try {
            const purchaseOrder = await apiResponse(app, '/operational/purchase-orders', {
                method: 'POST',
                body: JSON.stringify({
                    supplier_uuid: supplierUuid,
                    location_uuid: warehouse.uuid,
                    document_no: code,
                    date: Math.floor(Date.now() / 1000),
                    notes: 'Order E2E',
                    items: [{ product_uuid: productUuid, unit_uuid: unit.uuid, quantity: 5, price: 10000 }],
                }),
            });
            expect(purchaseOrder.status).toBe(200);
            const orderUuid = resourceUuid(purchaseOrder.body);
            expect((await apiResponse(app, `/operational/purchase-orders/${orderUuid}`)).status).toBe(200);
            expect((await apiResponse(app, `/operational/purchase-orders/${orderUuid}`, { method: 'DELETE' })).status).toBe(204);
        } finally {
            await silentDelete(app, `/products/${productUuid}`);
            await silentDelete(app, `/suppliers/${supplierUuid}`);
        }
    });

    test('creates and deletes BOM and assembly', async ({ app }) => {
        const code = uniqueCode('MFG');
        const unit = await firstResource(app, '/products/options?type=unit');
        const warehouse = await firstResource(app, '/products/options?type=warehouse');
        const product = await apiResponse(app, '/products', {
            method: 'POST',
            body: JSON.stringify({ code, name: `E2E Produk MFG ${code}`, base_unit_uuid: unit.uuid, is_active: true }),
        });
        const productUuid = resourceUuid(product.body);

        try {
            const bom = await apiResponse(app, '/operational/boms', {
                method: 'POST',
                body: JSON.stringify({ item_uuid: productUuid, unit_uuid: unit.uuid, qty: 2, description: 'BOM E2E' }),
            });
            expect(bom.status).toBe(200);
            const bomUuid = resourceUuid(bom.body);
            expect((await apiResponse(app, `/operational/boms/${bomUuid}`)).status).toBe(200);
            expect((await apiRequest(app, `/operational/boms/${bomUuid}`, { method: 'DELETE' })).status).toBe(204);

            const assembly = await apiResponse(app, '/operational/assemblies', {
                method: 'POST',
                body: JSON.stringify({
                    item_uuid: productUuid,
                    unit_uuid: unit.uuid,
                    location_uuid: warehouse.uuid,
                    date: Math.floor(Date.now() / 1000),
                    qty: 1,
                    status: 'draft',
                }),
            });
            expect(assembly.status).toBe(200);
            const assemblyUuid = resourceUuid(assembly.body);
            expect((await apiResponse(app, `/operational/assemblies/${assemblyUuid}`)).status).toBe(200);
            expect((await apiRequest(app, `/operational/assemblies/${assemblyUuid}`, { method: 'DELETE' })).status).toBe(204);
        } finally {
            await silentDelete(app, `/products/${productUuid}`);
        }
    });
});
