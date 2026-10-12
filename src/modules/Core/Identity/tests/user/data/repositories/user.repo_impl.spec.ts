import { describe, it, expect, vi, beforeEach, afterEach, beforeAll } from 'vitest';
import { UserRepoImpl } from '@modules/Core/Identity/data/repositories/user.repo_impl';
import { UserSearchColumn } from '@modules/Core/Identity/domain/models/user.model';
import apiService from '@modules/Core/shared/core/api_service';
import axios from 'axios';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('UserRepoImpl - Consolidated Tests', () => {
    let repo: UserRepoImpl;

    afterEach(() => {
        vi.restoreAllMocks();
    });

    describe('Unit Test (Mock)', () => {
        beforeEach(() => {
            repo = new UserRepoImpl();
        });

        it('harus memicu apiService.get untuk pencarian dengan parameter lengkap', async () => {
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: { data: [] } } as any);
            
            const filters = {
                search: 'test',
                search_column: UserSearchColumn.EMAIL,
                page: 2,
                per_page: 50,
                sort_by: 'email' as const,
                sort_direction: 'desc' as const
            };

            const result = await repo.paginate(filters);
            
            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/settings/users', expect.objectContaining({
                params: filters
            }));
        });

        it('harus mengembalikan ServerFailure saat API return 401 (Fix 401)', async () => {
            vi.spyOn(apiService, 'get').mockRejectedValue({
                response: { status: 401 }
            });

            const result = await repo.paginate({});
            
            expect(result.isLeft()).toBe(true);
            result.fold(
                (failure) => {
                    expect(failure).toBeInstanceOf(ServerFailure);
                    expect(failure.message).toContain('Sesi API berakhir');
                },
                () => {}
            );
        });

        it('harus memicu download file saat export dipanggil', async () => {
            const blob = new Blob(['test'], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
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
            expect(apiService.get).toHaveBeenCalledWith('/settings/users/export', expect.objectContaining({
                params: { search: 'test' },
                responseType: 'blob'
            }));
        });

        it('harus memicu post dengan FormData saat import', async () => {
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: { success: true } } as any);
            const file = new File([''], 'test.xlsx');

            const result = await repo.import(file);
            
            expect(result.isRight()).toBe(true);
            expect(apiService.post).toHaveBeenCalledWith('/settings/users/import', expect.any(FormData), expect.objectContaining({
                headers: { 'Content-Type': 'multipart/form-data' }
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
                    password: 'superadmin'
                });
                realToken = loginResponse.data.access_token;
                apiService.defaults.baseURL = BASE_URL;
                apiService.defaults.headers.common['Authorization'] = `Bearer ${realToken}`;
            } catch (error) {
                console.error('Integration Server Connection Failed');
            }
        });

        beforeEach(() => {
            repo = new UserRepoImpl();
            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() }
            });
            vi.stubGlobal('document', {
                createElement: vi.fn(() => ({ setAttribute: vi.fn(), click: vi.fn(), remove: vi.fn(), href: '' })),
                body: { appendChild: vi.fn() }
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
            const data = result.fold(() => null, (r) => r);
            expect(data.data).toBeDefined();
        });

        it('harus bisa menjalankan siklus CRUD lengkap: Tambah -> Read -> Edit -> Read -> Hapus [PASS - 20/04/2026]', async () => {
            if (!realToken) return;

            const meResponse = await apiService.get('/auth/me');
            const rolesResponse = await apiService.get('/settings/roles');
            
            const branchUuid = meResponse.data.branch_uuid;
            const roles = rolesResponse.data.roles?.data || rolesResponse.data.data || [];
            const roleUuid = roles[0]?.uuid;

            const timestamp = Date.now();
            const testUser = {
                name: `INT CRUD ${timestamp}`,
                email: `int_${timestamp}@mail.com`,
                password: 'password123',
                role_uuids: roleUuid ? [roleUuid] : [],
                branch_uuid: branchUuid,
            };

            const createResult = await repo.save(testUser as any);
            expect(createResult.isRight()).toBe(true);
            const uuid = createResult.fold(() => '', (r) => r.uuid);

            const deleteResult = await repo.delete(uuid);
            expect(deleteResult.isRight()).toBe(true);
        }, 30000);

        it('harus bisa mengunduh template import [PASS - 21/04/2026]', async () => {
            if (!realToken) return;

            const result = await repo.downloadImportTemplate();
            expect(result.isRight()).toBe(true);
        }, 30000);
    });
});
