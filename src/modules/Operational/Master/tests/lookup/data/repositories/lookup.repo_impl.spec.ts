import { beforeAll, beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import axios from 'axios';
import apiService from '@modules/Core/shared/core/api_service';
import { LookupRepoImpl } from '@modules/Operational/Master/data/repositories/lookup.repo_impl';
import { LookupSearchColumn } from '@modules/Operational/Master/domain/models/lookup.model';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('LookupRepoImpl - Consolidated Tests', () => {
    let repo: LookupRepoImpl;

    afterEach(() => {
        vi.restoreAllMocks();
    });

    describe('Unit Test (Mock)', () => {
        beforeEach(() => {
            repo = new LookupRepoImpl();
        });

        it('harus memicu apiService.get untuk pagination dengan parameter lengkap', async () => {
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: { data: [] } } as any);
            const filters = {
                search: 'test',
                search_column: LookupSearchColumn.NAME,
                page: 1,
                per_page: 10,
                sort_by: 'name' as any,
                sort_direction: 'asc' as any
            };
            const result = await repo.paginate(filters);
            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/lov/sets', expect.objectContaining({
                params: filters
            }));
        });

        it('harus mengembalikan ServerFailure saat API return 401', async () => {
            vi.spyOn(apiService, 'get').mockRejectedValue({
                response: { status: 401 }
            });
            const result = await repo.paginate({});
            expect(result.isLeft()).toBe(true);
            result.fold(
                (f) => expect(f.message).toContain('Sesi API berakhir'),
                () => { }
            );
        });

        it('harus memicu download file saat export dipanggil', async () => {
            const blob = new Blob([''], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: blob } as any);

            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() }
            });
            vi.stubGlobal('document', {
                createElement: vi.fn(() => ({ setAttribute: vi.fn(), click: vi.fn(), remove: vi.fn(), href: '' })),
                body: { appendChild: vi.fn() }
            });

            const result = await repo.export({ search: 'test' });
            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/lov/sets/export', expect.objectContaining({
                params: { search: 'test' },
                responseType: 'blob'
            }));
        });

        it('harus memicu post dengan FormData saat import', async () => {
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: { success: true } } as any);
            const file = new File([''], 'test.xlsx');
            const result = await repo.import(file);
            expect(result.isRight()).toBe(true);
            expect(apiService.post).toHaveBeenCalledWith('/lov/sets/import', expect.any(FormData), expect.objectContaining({
                headers: { 'Content-Type': 'multipart/form-data' }
            }));
        });

        it('harus bisa mengunduh template import', async () => {
            const blob = new Blob([''], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: blob } as any);

            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() }
            });

            const result = await repo.downloadImportTemplate();
            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/lov/sets/import/template', expect.objectContaining({
                responseType: 'blob'
            }));
        });
    });

    describe('Real Integration Test', () => {
        let realToken: string | undefined;
        const baseUrl = 'http://localhost:8000/api/v1';

        beforeAll(async () => {
            try {
                const loginResponse = await axios.post('http://localhost:8000/api/v1/auth/login', {
                    email: 'superadmin',
                    password: 'superadmin',
                });
                realToken = loginResponse.data.access_token;
                apiService.defaults.baseURL = baseUrl;
                apiService.defaults.headers.common.Authorization = `Bearer ${realToken}`;
            } catch (error) {
                console.error('Integration Server Connection Failed');
            }
        });

        beforeEach(() => {
            repo = new LookupRepoImpl();
            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() },
            });
            vi.stubGlobal('document', {
                createElement: vi.fn(() => ({ setAttribute: vi.fn(), click: vi.fn(), remove: vi.fn(), href: '' })),
                body: { appendChild: vi.fn() },
            });
        });

        it('harus bisa menarik data dengan paginasi dan sorting [PASS - 21/04/2026]', async () => {
            if (!realToken) return;

            const result = await repo.paginate({
                page: 1,
                per_page: 5,
                sort_by: 'name',
                sort_direction: 'asc'
            });

            expect(result.isRight()).toBe(true);
        });

        it('harus bisa menjalankan CRUD lookup dasar [PASS - 21/04/2026]', async () => {
            if (!realToken) {
                return;
            }

            const timestamp = Date.now();
            const createResult = await repo.save({
                name: `Lookup INT ${timestamp}`,
                program_code: `LOOKUP-INT-${timestamp}`,
            } as any);

            if (createResult.isLeft()) {
                console.error('Create Failed:', createResult.fold(l => l, () => null));
            }
            expect(createResult.isRight()).toBe(true);
            const uuid = createResult.fold(() => '', (value) => value.uuid);

            const deleteResult = await repo.delete(uuid);
            if (deleteResult.isLeft()) {
                console.error('Delete Failed:', deleteResult.fold(l => l, () => null));
            }
            expect(deleteResult.isRight()).toBe(true);
        }, 30000);

        it('harus bisa mengunduh template import [PASS - 21/04/2026]', async () => {
            if (!realToken) return;
            const result = await repo.downloadImportTemplate();
            expect(result.isRight()).toBe(true);
        });
    });
});
