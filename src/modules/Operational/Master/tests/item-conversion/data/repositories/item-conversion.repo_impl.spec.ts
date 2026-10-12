import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { ItemConversionRepoImpl } from '@modules/Operational/Master/data/repositories/item-conversion.repo_impl';
import apiService from '@modules/Core/shared/core/api_service';
import { ServerFailure, ValidationFailure } from '@modules/Core/shared/core/failures';

describe('ItemConversionRepoImpl', () => {
    let repo: ItemConversionRepoImpl;

    beforeEach(() => { repo = new ItemConversionRepoImpl(); });
    afterEach(() => { vi.restoreAllMocks(); });

    describe('fetch', () => {
        it('harus return Right dengan summary saat API sukses', async () => {
            vi.spyOn(apiService, 'get').mockResolvedValue({
                data: { data: [{ uuid: '1' }], meta: { total: 1, current_page: 1, per_page: 15 } }
            } as any);

            const result = await repo.fetch({ search: '', page: 1, per_page: 15, sort_by: 'updated_at', sort_dir: 'desc' });

            expect(result.isRight()).toBe(true);
            result.fold(() => {}, (s) => {
                expect(s.items).toHaveLength(1);
                expect(s.total).toBe(1);
            });
            expect(apiService.get).toHaveBeenCalledWith('/masters/item-conversions', expect.any(Object));
        });

        it('harus return Left(ServerFailure) saat API error', async () => {
            vi.spyOn(apiService, 'get').mockRejectedValue(new Error('Network error'));
            const result = await repo.fetch({} as any);
            expect(result.isLeft()).toBe(true);
            result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
        });
    });

    describe('save', () => {
        it('harus return Right saat create sukses', async () => {
            vi.spyOn(apiService, 'post').mockResolvedValue({ data: { uuid: 'new-uuid' } } as any);
            const result = await repo.save({ item_uuid: 'a', from_unit_uuid: 'u1', to_unit_uuid: 'u2', conv_ratio: 2 });
            expect(result.isRight()).toBe(true);
        });

        it('harus return Left(ValidationFailure) saat 422', async () => {
            vi.spyOn(apiService, 'post').mockRejectedValue({
                response: { status: 422, data: { message: 'Validation error', errors: { item_uuid: ['required'] } } }
            });
            const result = await repo.save({});
            expect(result.isLeft()).toBe(true);
            result.fold((f) => expect(f).toBeInstanceOf(ValidationFailure), () => {});
        });

        it('harus return Left(ServerFailure) saat error umum', async () => {
            vi.spyOn(apiService, 'post').mockRejectedValue(new Error('Server error'));
            const result = await repo.save({});
            expect(result.isLeft()).toBe(true);
            result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
        });
    });

    describe('delete', () => {
        it('harus return Right saat delete sukses', async () => {
            vi.spyOn(apiService, 'delete').mockResolvedValue({} as any);
            const result = await repo.delete('some-uuid');
            expect(result.isRight()).toBe(true);
            expect(apiService.delete).toHaveBeenCalledWith('/masters/item-conversions/some-uuid');
        });

        it('harus return Left(ServerFailure) saat delete gagal', async () => {
            vi.spyOn(apiService, 'delete').mockRejectedValue(new Error('Server error'));
            const result = await repo.delete('uuid');
            expect(result.isLeft()).toBe(true);
            result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
        });
    });
});
