type AppFixture = {
    readonly baseUrl: string;
};

type ApiJson = Record<string, unknown>;

type ApiResponse = {
    status: number;
    body: unknown;
    headers: Headers;
};

const tokenPromises = new Map<string, Promise<string>>();

function apiEmail(): string {
    return process.env.E2E_SUPERADMIN_EMAIL ?? 'superadmin@klinik.local';
}

function apiPassword(): string {
    return process.env.E2E_SUPERADMIN_PASSWORD ?? 'superadmin';
}

function apiUrl(app: AppFixture, path: string): URL {
    const url = new URL(path.startsWith('/api/') ? path : `/api/v1/${path.replace(/^\//, '')}`, app.baseUrl);
    if (url.origin !== new URL('/', app.baseUrl).origin) {
        throw new Error(`${url.toString()} is outside the e2e application origin`);
    }
    return url;
}

async function authenticate(app: AppFixture): Promise<string> {
    const cacheKey = new URL('/', app.baseUrl).origin;
    let tokenPromise = tokenPromises.get(cacheKey);

    if (!tokenPromise) {
        tokenPromise = (async () => {
            const response = await fetch(new URL('/api/v1/auth/login', app.baseUrl), {
                method: 'POST',
                headers: { 'content-type': 'application/json', accept: 'application/json' },
                body: JSON.stringify({ email: apiEmail(), password: apiPassword() }),
            });

            if (!response.ok) {
                throw new Error(`E2E API login failed with HTTP ${response.status}`);
            }

            const body = (await response.json()) as ApiJson;
            const token = body.access_token ?? (body.data as ApiJson | undefined)?.access_token;
            if (typeof token !== 'string' || token.length === 0) {
                throw new Error('E2E API login response does not contain access_token');
            }
            return token;
        })();
        tokenPromises.set(cacheKey, tokenPromise);
    }

    return tokenPromise;
}

export async function apiLoginToken(app: AppFixture, email: string, password: string): Promise<string> {
    const response = await fetch(new URL('/api/v1/auth/login', app.baseUrl), {
        method: 'POST',
        headers: { 'content-type': 'application/json', accept: 'application/json' },
        body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
        throw new Error(`E2E API login failed for ${email} with HTTP ${response.status}`);
    }

    const body = (await response.json()) as ApiJson;
    const token = body.access_token ?? (body.data as ApiJson | undefined)?.access_token;
    if (typeof token !== 'string' || token.length === 0) {
        throw new Error(`E2E API login response for ${email} does not contain access_token`);
    }

    return token;
}

export async function apiRequest(
    app: AppFixture,
    path: string,
    init: RequestInit = {},
): Promise<Response> {
    const headers = new Headers(init.headers);
    headers.set('authorization', `Bearer ${await authenticate(app)}`);
    headers.set('accept', 'application/json');
    if (init.body && !headers.has('content-type')) {
        headers.set('content-type', 'application/json');
    }

    return fetch(apiUrl(app, path), { ...init, headers });
}

export async function apiResponse(
    app: AppFixture,
    path: string,
    init: RequestInit = {},
): Promise<ApiResponse> {
    const response = await apiRequest(app, path, init);
    const text = await response.text();
    let body: unknown = text;
    try {
        body = text.length > 0 ? JSON.parse(text) : null;
    } catch {
        body = text;
    }

    return { status: response.status, body, headers: response.headers };
}

export function uniqueCode(prefix: string): string {
    return `E2E-${prefix}-${Date.now().toString(36)}-${Math.random().toString(16).slice(2, 8)}`.toUpperCase();
}

export function resourceUuid(body: unknown): string {
    const candidates = [
        body,
        (body as ApiJson | null)?.data,
        ((body as ApiJson | null)?.data as ApiJson | undefined)?.data,
    ];

    for (const candidate of candidates) {
        const uuid = (candidate as ApiJson | undefined)?.uuid;
        if (typeof uuid === 'string' && uuid.length > 0) {
            return uuid;
        }
    }

    throw new Error('API response does not contain a resource uuid');
}

export async function firstResourceItem(app: AppFixture, path: string): Promise<Record<string, any>> {
    const response = await apiRequest(app, path);
    if (response.status !== 200) {
        throw new Error(`GET ${path} returned HTTP ${response.status}`);
    }

    const body = await response.json();
    const items = (body as ApiJson)?.data ?? body;
    if (!Array.isArray(items) || items.length === 0) {
        throw new Error(`GET ${path} returned no resource items`);
    }
    return items[0] as Record<string, any>;
}

export async function silentDelete(app: AppFixture, path: string): Promise<void> {
    try {
        await apiRequest(app, path, { method: 'DELETE' });
    } catch {
        return;
    }
}
