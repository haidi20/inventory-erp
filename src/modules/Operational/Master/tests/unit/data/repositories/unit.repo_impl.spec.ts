import { describe, it, expect, vi, beforeEach, afterEach, beforeAll } from 'vitest';
import { UnitRepoImpl } from '@modules/Operational/Master/data/repositories/unit.repo_impl';
import { UnitSearchColumn } from '@modules/Operational/Master/domain/models/unit.model';
import apiService from '@modules/Core/shared/core/api_service';
import axios from 'axios';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('UnitRepoImpl - Consolidated Tests', () => {
    let repo: UnitRepoImpl;

    afterEach(() => {
        vi.restoreAllMocks();
    });

    describe('Unit Test (Mock)', () => {
        beforeEach(() => {
            repo = new UnitRepoImpl();
        });

        it('harus memicu apiService.get untuk pencarian dengan parameter lengkap', async () => {
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: { data: [] } } as any);

            const filters = {
                search: 'pcs',
                search_column: UnitSearchColumn.CODE,
                page: 2,
                per_page: 50,
                sort_by: 'code' as const,
                sort_direction: 'desc' as const,
            };

            const result = await repo.paginate(filters);

            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/masters/units', expect.objectContaining({
                params: filters,
            }));
        });

        it('harus mengembalikan ServerFailure saat API return 401 (Fix 401)', async () => {
            vi.spyOn(apiService, 'get').mockRejectedValue({
                response: { status: 401 },
            });

            const result = await repo.paginate({});

            expect(result.isLeft()).toBe(true);
            result.fold(
                (failure) => {
                    expect(failure).toBeInstanceOf(ServerFailure);
                    expect(failure.message).toContain('Sesi API berakhir');
                },
                () => { },
            );
        });

        it('harus memicu download file saat export dipanggil', async () => {
            const blob = new Blob(['test'], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: blob } as any);

            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() },
            });
            vi.stubGlobal('document', {
                createElement: vi.fn(() => ({ setAttribute: vi.fn(), click: vi.fn(), remove: vi.fn(), href: '' })),
                body: { appendChild: vi.fn() },
            });

            const result = await repo.export({ search: 'pcs' });

            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/masters/units/export', expect.objectContaining({
                params: { search: 'pcs' },
                responseType: 'blob',
            }));
        });

        it('harus memicu post dengan FormData saat import', async () => {
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: { success: true } } as any);
            const file = new File([''], 'unit.xlsx');

            const result = await repo.import(file);

            expect(result.isRight()).toBe(true);
            expect(apiService.post).toHaveBeenCalledWith('/masters/units/import', expect.any(FormData), expect.objectContaining({
                headers: { 'Content-Type': 'multipart/form-data' },
            }));
        });
    });

    describe('Real Integration Test', () => {
        let realToken: string | undefined = undefined;
        const BASE_URL = 'http://localhost:8000/api/v1';

        beforeAll(async () => {
            try {
                const loginResponse = await axios.post('http://localhost:8000/api/v1/auth/login', {
                    email: 'superadmin',
                    password: 'superadmin',
                });
                realToken = loginResponse.data.access_token;
                apiService.defaults.baseURL = BASE_URL;
                apiService.defaults.headers.common['Authorization'] = `Bearer ${realToken}`;
            } catch (error) {
                console.error('Integration Server Connection Failed');
            }
        });

        beforeEach(() => {
            repo = new UnitRepoImpl();
            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() },
            });
            vi.stubGlobal('document', {
                createElement: vi.fn(() => ({ setAttribute: vi.fn(), click: vi.fn(), remove: vi.fn(), href: '' })),
                body: { appendChild: vi.fn() },
            });
        });

        it('harus bisa menarik data dengan paginasi dan sorting', async () => {
            if (!realToken) return;

            const result = await repo.paginate({
                page: 1,
                per_page: 5,
                sort_by: 'code',
                sort_direction: 'asc',
            });

            expect(result.isRight()).toBe(true);
            const data = result.fold(() => null, (r) => r);
            expect(data.data).toBeDefined();
        });

        it('harus bisa mengunduh template import', async () => {
            if (!realToken) return;

            const result = await repo.downloadImportTemplate();
            expect(result.isRight()).toBe(true);
        });
    });
});
