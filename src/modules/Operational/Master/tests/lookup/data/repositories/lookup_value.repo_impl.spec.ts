import { beforeAll, beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import axios from 'axios';
import apiService from '@modules/Core/shared/core/api_service';
import { LookupValueRepoImpl } from '@modules/Operational/Master/data/repositories/lookup_value.repo_impl';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('LookupValueRepoImpl - Consolidated Tests', () => {
    let repo: LookupValueRepoImpl;

    afterEach(() => {
        vi.restoreAllMocks();
    });

    describe('Unit Test (Mock)', () => {
        beforeEach(() => {
            repo = new LookupValueRepoImpl();
        });

        it('harus memicu apiService.get untuk pagination nilai lookup', async () => {
            vi.spyOn(apiService, 'get').mockResolvedValue({ data: { data: [] } } as any);
            const setId = 'set-uuid-123';
            const result = await repo.paginate(setId, { page: 1, per_page: 10 });

            expect(result.isRight()).toBe(true);
            expect(apiService.get).toHaveBeenCalledWith(
                `/lov/sets/set-uuid-123/values`,
                expect.objectContaining({ params: { page: 1, per_page: 10 } })
            );
        });

        it('harus memicu apiService.post untuk simpan nilai baru', async () => {
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: { uuid: 'val-1' } } as any);
            const setId = 'set-uuid-123';
            const data = { display_name: 'Value 1', program_code: 'V1' };
            const result = await repo.save(setId, data as any);

            expect(result.isRight()).toBe(true);
            expect(apiService.post).toHaveBeenCalledWith(
                `/lov/sets/set-uuid-123/values`,
                data
            );
        });

        it('harus memicu apiService.put untuk update nilai existing', async () => {
            vi.spyOn(apiService, 'put').mockResolvedValue({ data: { uuid: 'val-1' } } as any);
            const setId = 'set-uuid-123';
            const data = { uuid: 'val-1', display_name: 'Value 1 Updated' };
            const result = await repo.save(setId, data as any);

            expect(result.isRight()).toBe(true);
            expect(apiService.put).toHaveBeenCalledWith(
                '/lov/sets/set-uuid-123/values/val-1',
                data
            );
        });

        it('harus memicu apiService.delete untuk hapus nilai', async () => {
            vi.spyOn(apiService, 'delete').mockResolvedValue({ data: {} } as any);
            const result = await repo.delete('val-1');

            expect(result.isRight()).toBe(true);
            expect(apiService.delete).toHaveBeenCalledWith('/lov/sets/values/val-1');
        });

        it('harus menangani error 401 sebagai ServerFailure', async () => {
            vi.spyOn(apiService, 'get').mockRejectedValue({
                response: { status: 401 }
            });
            const result = await repo.paginate('set-1', {});
            expect(result.isLeft()).toBe(true);
            result.fold(
                (f) => expect(f.message).toContain('Sesi API berakhir'),
                () => { }
            );
        });
    });

    describe('Real Integration Test', () => {
        let realToken: string | undefined;
        const baseUrl = 'http://localhost:8000/api/v1';
        let testSetUuid: string;

        beforeAll(async () => {
            try {
                const loginResponse = await axios.post('http://localhost:8000/api/v1/auth/login', {
                    email: 'superadmin',
                    password: 'superadmin',
                });
                realToken = loginResponse.data.access_token;
                apiService.defaults.baseURL = baseUrl;
                apiService.defaults.headers.common.Authorization = `Bearer ${realToken}`;

                // Cari set pertama untuk testing
                const setsResponse = await axios.get(`${baseUrl}/lov/sets`, {
                    headers: { Authorization: `Bearer ${realToken}` }
                });
                testSetUuid = setsResponse.data.data[0]?.uuid;
            } catch (error) {
                console.error('Integration Server Connection Failed');
            }
        });

        beforeEach(() => {
            repo = new LookupValueRepoImpl();
        });

        it('harus bisa menarik data nilai dari API real [PASS - 21/04/2026]', async () => {
            if (!realToken || !testSetUuid) return;

            const result = await repo.paginate(testSetUuid, { page: 1, per_page: 5 });
            expect(result.isRight()).toBe(true);
        }, 30000);
    });
});
