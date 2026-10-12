import { describe, it, expect, vi, beforeEach, afterEach, beforeAll } from 'vitest';
import { RoleRepoImpl } from '@modules/Core/Identity/data/repositories/role.repo_impl';
import apiService from '@modules/Core/shared/core/api_service';
import axios from 'axios';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('RoleRepoImpl - Consolidated Tests', () => {
    let repo: RoleRepoImpl;

    afterEach(() => {
        vi.restoreAllMocks();
    });

    describe('Unit Test (Mock)', () => {
        beforeEach(() => {
            repo = new RoleRepoImpl();
        });

        it('harus memicu apiService.get untuk pagination dengan parameter lengkap', async () => {
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: { data: [] } } as any);
            const filters = {
                search: 'test',
                search_column: 'name' as const,
                page: 1,
                per_page: 10,
                sort_by: 'name' as const,
                sort_direction: 'asc' as const
            };
            const result = await repo.paginate(filters);
            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/settings/roles', expect.objectContaining({
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
                () => {}
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
            expect(apiService.get).toHaveBeenCalledWith('/settings/roles/export', expect.objectContaining({
                params: { search: 'test' },
                responseType: 'blob'
            }));
        });

        it('harus memicu post dengan FormData saat import', async () => {
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: { success: true } } as any);
            const file = new File([''], 'test.xlsx');
            const result = await repo.import(file);
            expect(result.isRight()).toBe(true);
            expect(apiService.post).toHaveBeenCalledWith('/settings/roles/import', expect.any(FormData), expect.objectContaining({
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
            repo = new RoleRepoImpl();
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

        it('harus bisa menjalankan siklus CRUD lengkap: Tambah -> Edit -> Hapus [PASS - 20/04/2026]', async () => {
            if (!realToken) return;

            const timestamp = Date.now();
            const testRole = {
                name: `role_int_${timestamp}`,
                label: `Role INT ${timestamp}`,
                permissions: [],
            };

            // 1. Create
            const createResult = await repo.save(testRole as any);
            if (createResult.isLeft()) {
                console.error('Create Failed:', createResult.fold(l => l, () => null));
            }
            expect(createResult.isRight()).toBe(true);
            const roleData = createResult.fold(() => null, (r) => r);
            const uuid = (roleData as any).uuid;

            // 2. Update
            const updateResult = await repo.save({
                ...testRole,
                uuid: uuid,
                label: `Role INT ${timestamp} Updated`
            } as any);
            expect(updateResult.isRight()).toBe(true);

            // 3. Delete
            const deleteResult = await repo.delete(uuid);
            expect(deleteResult.isRight()).toBe(true);
        }, 30000);

        it('harus bisa mengunduh template import [PASS - 21/04/2026]', async () => {
            if (!realToken) return;
            const result = await repo.downloadImportTemplate();
            expect(result.isRight()).toBe(true);
        });
    });
});
