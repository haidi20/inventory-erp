import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { AuthRepoImpl } from '@modules/Core/Identity/data/repositories/auth.repo_impl';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('AuthRepoImpl', () => {
    let repo: AuthRepoImpl;

    beforeEach(() => {
        repo = new AuthRepoImpl();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    // ── preCheckRoles ────────────────────────────────────────────────────────

    describe('preCheckRoles', () => {
        it('harus return Right dengan data roles saat API sukses (single role)', async () => {
            const mockResponse = {
                success: true,
                roles: [{ name: 'owner', label: 'Owner' }],
            };

            vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
                ok: true,
                json: vi.fn().mockResolvedValue(mockResponse),
            }));

            const result = await repo.preCheckRoles('superadmin', 'superadmin', 'csrf-token');

            expect(result.isRight()).toBe(true);
            result.fold(
                () => { throw new Error('should not be left'); },
                (data) => {
                    expect(data.success).toBe(true);
                    expect(data.roles).toHaveLength(1);
                    expect(data.roles[0].name).toBe('owner');
                }
            );
        });

        it('harus return Right dengan multiple roles saat user punya lebih dari satu role', async () => {
            const mockResponse = {
                success: true,
                roles: [
                    { name: 'owner', label: 'Owner' },
                    { name: 'dokter', label: 'Dokter' },
                ],
            };

            vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
                ok: true,
                json: vi.fn().mockResolvedValue(mockResponse),
            }));

            const result = await repo.preCheckRoles('multi@klinik.local', 'pass123', 'csrf-token');

            expect(result.isRight()).toBe(true);
            result.fold(
                () => { throw new Error('should not be left'); },
                (data) => {
                    expect(data.roles).toHaveLength(2);
                }
            );
        });

        it('harus memanggil fetch dengan header X-CSRF-TOKEN dan Content-Type yang benar', async () => {
            const mockFetch = vi.fn().mockResolvedValue({
                ok: true,
                json: vi.fn().mockResolvedValue({ success: true, roles: [] }),
            });
            vi.stubGlobal('fetch', mockFetch);

            await repo.preCheckRoles('test@klinik.local', 'pass', 'my-csrf-token');

            expect(mockFetch).toHaveBeenCalledWith(
                '/login/pre-check',
                expect.objectContaining({
                    method: 'POST',
                    headers: expect.objectContaining({
                        'X-CSRF-TOKEN': 'my-csrf-token',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }),
                    body: JSON.stringify({ email: 'test@klinik.local', password: 'pass' }),
                })
            );
        });

        it('harus return Left(ServerFailure) saat response tidak ok (401)', async () => {
            vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
                ok: false,
                status: 401,
            }));

            const result = await repo.preCheckRoles('wrong@klinik.local', 'wrongpass', 'csrf');

            expect(result.isLeft()).toBe(true);
            result.fold(
                (failure) => {
                    expect(failure).toBeInstanceOf(ServerFailure);
                    expect(failure.message).toBe('Kredensial tidak valid.');
                },
                () => { throw new Error('should not be right'); }
            );
        });

        it('harus return Left(ServerFailure) saat fetch throw exception (network error)', async () => {
            vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('Network error')));

            const result = await repo.preCheckRoles('test@klinik.local', 'pass', 'csrf');

            expect(result.isLeft()).toBe(true);
            result.fold(
                (failure) => {
                    expect(failure).toBeInstanceOf(ServerFailure);
                    expect(failure.message).toBe('Gagal terhubung ke server.');
                },
                () => { throw new Error('should not be right'); }
            );
        });
    });
});
