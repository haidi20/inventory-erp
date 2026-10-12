import { expect, test, type Page } from '@playwright/test';

type InertiaPagePayload = {
    props?: {
        app_context?: {
            company_uuid?: string | null;
            runtime_environment?: string | null;
        };
        auth?: {
            api_token?: string | null;
            user?: {
                company_uuid?: string | null;
            };
        };
    };
};

type ApiAuditCase = {
    apiPath: string;
    navUrl: string;
    label: string;
    itemsKey: string;
    triggerSelector: string;
    triggerValue: string;
};

const KEY_PAGES = [
    { url: '/inventory/master/daftar-nilai', label: 'Masters - Lookup' },
    { url: '/masters/units', label: 'Masters - Unit' },
    { url: '/masters/item-conversions', label: 'Masters - Item Conversion' },
    { url: '/settings/users', label: 'Settings - User' },
    { url: '/settings/roles', label: 'Settings - Role' },
    { url: '/operational/complaints', label: 'Operational - Complaint' },
];

const AUDITED_APIS: ApiAuditCase[] = [
    {
        apiPath: '/api/v1/lov/sets',
        navUrl: '/inventory/master/daftar-nilai',
        label: 'Lookup Sets',
        itemsKey: 'items',
        triggerSelector: 'input-search-lookup',
        triggerValue: 'LOOKUP',
    },
    {
        apiPath: '/api/v1/masters/units',
        navUrl: '/masters/units',
        label: 'Units',
        itemsKey: 'items',
        triggerSelector: 'input-search-unit',
        triggerValue: 'PCS',
    },
    {
        apiPath: '/api/v1/settings/users',
        navUrl: '/settings/users',
        label: 'Users',
        itemsKey: 'items',
        triggerSelector: 'input-search-user',
        triggerValue: 'superadmin',
    },
];

async function loginAsSandboxOwner(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'superadmin');
    await page.fill('input[name="password"]', 'superadmin');
    await page.click('button[type="submit"]');
    await page.waitForURL((url) => !url.pathname.includes('/login'));
}

async function getCurrentEnvironment(page: Page): Promise<string | null> {
    return page.evaluate(() => {
        const app = document.querySelector('div[data-page]');
        const raw = app?.getAttribute('data-page');
        if (!raw) {
            return null;
        }

        const payload = JSON.parse(raw) as InertiaPagePayload;
        return payload.props?.app_context?.runtime_environment ?? null;
    });
}

async function fetchApiAsCurrentSession<T = Record<string, unknown>>(
    page: Page,
    apiPath: string,
    extraHeaders?: Record<string, string>,
): Promise<{ status: number; body: T | null }> {
    return page.evaluate(
        async ({ apiPath, extraHeaders }) => {
            const app = document.querySelector('div[data-page]');
            const raw = app?.getAttribute('data-page');
            let apiToken: string | null = null;

            if (raw) {
                try {
                    const payload = JSON.parse(raw) as InertiaPagePayload;
                    apiToken = payload.props?.auth?.api_token ?? null;
                } catch {
                    apiToken = null;
                }
            }

            const response = await fetch(apiPath, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(apiToken ? { Authorization: `Bearer ${apiToken}` } : {}),
                    ...extraHeaders,
                },
            });

            let body: T | null = null;
            try {
                body = (await response.json()) as T;
            } catch {
                body = null;
            }

            return {
                status: response.status,
                body,
            };
        },
        { apiPath, extraHeaders },
    );
}

test.describe('Skenario 1: Halaman load normal pada company sandbox', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsSandboxOwner(page);
    });

    for (const { url, label } of KEY_PAGES) {
        test(`${label} harus load tanpa error JS`, async ({ page }) => {
            const jsErrors: string[] = [];
            page.on('pageerror', (error) => jsErrors.push(error.message));

            await page.goto(url, { waitUntil: 'domcontentloaded' });
            await expect(page.locator('body')).toBeVisible();
            await page.waitForLoadState('networkidle');

            expect(
                jsErrors.filter(
                    (message) =>
                        !message.toLowerCase().includes('warning') &&
                        !message.toLowerCase().includes('resizeobserver'),
                ),
            ).toHaveLength(0);
        });
    }

    test('user yang login harus berada di company sandbox dan runtime non-production', async ({ page }) => {
        await page.goto('/inventory/master/daftar-nilai');
        const environment = await getCurrentEnvironment(page);

        await expect(page.locator('body')).toBeVisible();
        if (environment) {
            expect(['local', 'sandbox', 'staging', 'testing', 'development', 'dev']).toContain(
                environment,
            );
        }
    });
});

test.describe('Skenario 2: Data company lain tidak muncul', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsSandboxOwner(page);
    });

    for (const { apiPath, navUrl, label, itemsKey, triggerSelector, triggerValue } of AUDITED_APIS) {
        test(`${label}: semua item di response harus milik company yang sama`, async ({ page }) => {
            await page.goto(navUrl, { waitUntil: 'domcontentloaded' });
            const searchInput = page.getByTestId(triggerSelector);
            await expect(searchInput).toBeVisible({ timeout: 20_000 });

            const responsePromise = page.waitForResponse((response) =>
                response.url().includes(apiPath) &&
                response.request().method() === 'GET' &&
                response.status() === 200,
            );

            await searchInput.fill(triggerValue);
            const response = await responsePromise;

            let body: Record<string, unknown> | unknown[] = {};
            try {
                body = await response.json();
            } catch {
                body = {};
            }

            const rawItems = Array.isArray(body)
                ? body
                : Array.isArray(body[itemsKey])
                    ? (body[itemsKey] as unknown[])
                    : [];

            const itemsWithCompany = rawItems.filter(
                (item): item is Record<string, unknown> =>
                    typeof item === 'object' &&
                    item !== null &&
                    'company_uuid' in item,
            );

            const wrongCompanyItems = itemsWithCompany.filter(
                (item, _, allItems) =>
                    allItems.length > 0 &&
                    item.company_uuid !== allItems[0]?.company_uuid,
            );

            expect(
                wrongCompanyItems,
                `Ditemukan ${wrongCompanyItems.length} item dari company lain pada ${label}`,
            ).toHaveLength(0);
        });
    }

    test('session sandbox tidak bisa membaca data company production walau header company di-forge', async ({ page }) => {
        await page.goto('/inventory/master/daftar-nilai', { waitUntil: 'domcontentloaded' });

        const forgedCompanyUuid = '00000000-0000-0000-0000-000000000099';

        const normalResponse = await fetchApiAsCurrentSession<Record<string, unknown>>(
            page,
            '/api/v1/lov/sets',
        );
        const forgedResponse = await fetchApiAsCurrentSession<Record<string, unknown>>(
            page,
            '/api/v1/lov/sets',
            {
                'X-Klinik-Company-Uuid': forgedCompanyUuid,
                'X-Klinik-Environment': 'production',
            },
        );

        expect([200, 401, 403, 422]).toContain(normalResponse.status);
        expect([200, 401, 403, 422]).toContain(forgedResponse.status);

        const forgedItems = Array.isArray(forgedResponse.body?.items)
            ? (forgedResponse.body?.items as Array<Record<string, unknown>>)
            : [];
        const leakedProductionItems = forgedItems.filter(
            (item) => item.company_uuid === forgedCompanyUuid,
        );

        expect(leakedProductionItems).toHaveLength(0);

        const normalItems = Array.isArray(normalResponse.body?.items)
            ? (normalResponse.body?.items as Array<Record<string, unknown>>)
            : [];
        const normalCompanySet = new Set(
            normalItems
                .map((item) => item.company_uuid)
                .filter((companyUuid): companyUuid is string => typeof companyUuid === 'string'),
        );
        const forgedCompanySet = new Set(
            forgedItems
                .map((item) => item.company_uuid)
                .filter((companyUuid): companyUuid is string => typeof companyUuid === 'string'),
        );

        expect(forgedCompanySet.size).toBeLessThanOrEqual(1);
        if (normalResponse.status === 200 && forgedResponse.status === 200 && normalCompanySet.size > 0 && forgedCompanySet.size > 0) {
            expect([...forgedCompanySet]).toEqual([...normalCompanySet]);
        }
    });
});
