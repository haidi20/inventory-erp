import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const mockUsePage = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    usePage: mockUsePage,
}));

describe('api_service runtime context', () => {
    beforeEach(() => {
        vi.resetModules();
        vi.unstubAllGlobals();
        process.env.KLINIK_RUNTIME_ENV = 'sandbox';
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('mengirim header environment dan company dari Inertia props untuk request tenant-owned', async () => {
        mockUsePage.mockReturnValue({
            props: {
                auth: {
                    api_token: 'token-sandbox',
                    user: {
                        company_uuid: 'company-from-user',
                    },
                },
                app_context: {
                    runtime_environment: 'sandbox',
                    company_uuid: 'company-from-context',
                },
            },
        });

        const { default: apiService } = await import('@modules/Core/shared/core/api_service');

        const response = await apiService.get('/lov/sets', {
            adapter: async (config) => ({
                data: config.headers,
                status: 200,
                statusText: 'OK',
                headers: {},
                config,
            }),
        });

        expect(response.data.Authorization).toBe('Bearer token-sandbox');
        expect(response.data['X-Klinik-Environment']).toBe('sandbox');
        expect(response.data['X-Klinik-Company-Uuid']).toBe('company-from-context');
    });

    it('fallback ke env sandbox saat usePage tidak tersedia', async () => {
        mockUsePage.mockImplementation(() => {
            throw new Error('outside inertia');
        });

        const { default: apiService } = await import('@modules/Core/shared/core/api_service');

        const response = await apiService.get('/masters/units', {
            adapter: async (config) => ({
                data: config.headers,
                status: 200,
                statusText: 'OK',
                headers: {},
                config,
            }),
        });

        expect(response.data['X-Klinik-Environment']).toBe('sandbox');
        expect(response.data['X-Klinik-Company-Uuid']).toBeUndefined();
    });
});
