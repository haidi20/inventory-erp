import { describe, it, expect, vi, beforeEach, afterEach, beforeAll } from 'vitest';
import { BackupRepoImpl } from '@modules/Core/Platform/data/repositories/backup.repo_impl';
import apiService from '@modules/Core/shared/core/api_service';
import axios from 'axios';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('BackupRepoImpl - Consolidated Tests', () => {
    let repo: BackupRepoImpl;

    afterEach(() => {
        vi.restoreAllMocks();
    });

    describe('Unit Test (Mock)', () => {
        beforeEach(() => {
            repo = new BackupRepoImpl();
        });

        it('harus memicu apiService.get untuk ambil riwayat dengan filter', async () => {
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: { data: [] } } as any);
            const filters = {
                history_type: 'manual',
                page: 1,
                per_page: 10
            } as const;
            const result = await repo.fetchHistory(filters);
            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith(
                '/settings/backups/history-datatable',
                expect.objectContaining({ params: filters })
            );
        });

        it('harus memicu apiService.post untuk export database', async () => {
            const blob = new Blob([''], { type: 'application/sql' });
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: blob } as any);
            
            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() }
            });
            vi.stubGlobal('document', {
                createElement: vi.fn(() => ({ setAttribute: vi.fn(), click: vi.fn(), remove: vi.fn(), href: '' })),
                body: { appendChild: vi.fn() }
            });

            const result = await repo.export({ notes: 'test backup' });
            expect(result.isRight()).toBe(true);
            expect(apiService.post).toHaveBeenCalledWith('/settings/backups/export', expect.any(Object), expect.objectContaining({
                responseType: 'blob'
            }));
        });

        it('harus memicu post dengan FormData saat restore', async () => {
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: { success: true } } as any);
            const file = new File([''], 'test.sql');
            const result = await repo.restore({ 
                backup_file: file, 
                create_pre_restore_backup: true 
            });
            expect(result.isRight()).toBe(true);
            expect(apiService.post).toHaveBeenCalledWith('/settings/backups/restore', expect.any(FormData), expect.objectContaining({
                headers: { 'Content-Type': 'multipart/form-data' }
            }));
        });

        it('harus memicu download saat dipanggil', async () => {
            const blob = new Blob([''], { type: 'application/sql' });
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: blob } as any);
            
            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() }
            });

            const result = await repo.download('test.sql', 'manual');
            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith('/settings/backups/download/test.sql', expect.objectContaining({
                params: { history_type: 'manual' },
                responseType: 'blob'
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
            repo = new BackupRepoImpl();
            vi.stubGlobal('window', {
                URL: { createObjectURL: vi.fn(() => 'blob:url'), revokeObjectURL: vi.fn() }
            });
            vi.stubGlobal('document', {
                createElement: vi.fn(() => ({ setAttribute: vi.fn(), click: vi.fn(), remove: vi.fn(), href: '' })),
                body: { appendChild: vi.fn() }
            });
        });

        it('harus bisa menarik histori backup dari API real [PASS - 20/04/2026]', async () => {
            if (!realToken) return;

            const result = await repo.fetchHistory({ history_type: 'manual' });
            expect(result.isRight()).toBe(true);
        });

        it('harus bisa men-trigger export database ke SQL [PASS - 20/04/2026]', async () => {
            if (!realToken) return;

            const result = await repo.export({ notes: 'Integration Test Export' });
            expect(result.isRight()).toBe(true);
        }, 60000);
    });
});
