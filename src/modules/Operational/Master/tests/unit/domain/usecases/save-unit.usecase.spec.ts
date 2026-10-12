import { describe, it, expect, vi, beforeEach } from 'vitest';
import { SaveUnitUseCase } from '@modules/Operational/Master/domain/usecases/save-unit.usecase';
import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';
import { left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('SaveUnitUseCase', () => {
    let useCase: SaveUnitUseCase;
    let mockRepo: UnitRepository;

    beforeEach(() => {
        mockRepo = {
            save: vi.fn(),
            paginate: vi.fn(),
            search: vi.fn(),
            changePerPage: vi.fn(),
            changePage: vi.fn(),
            delete: vi.fn(),
            export: vi.fn(),
            import: vi.fn(),
            downloadImportTemplate: vi.fn(),
        };
        useCase = new SaveUnitUseCase(mockRepo);
    });

    it('should call repository.save with correct data', async () => {
        const unitData = { code: 'PCS', name: 'Pieces', is_active: true };
        await useCase.execute(unitData);

        expect(mockRepo.save).toHaveBeenCalledWith(unitData);
    });

    it('should return Left(Failure) if repository.save fails', async () => {
        const unitData = { code: 'PCS', name: 'Pieces', is_active: true };
        const failure = new ServerFailure('Save failed');
        mockRepo.save = vi.fn().mockResolvedValue(left(failure));

        const result = await useCase.execute(unitData);

        expect(result.isLeft()).toBe(true);
        expect(result.fold(l => l, () => null)).toEqual(failure);
    });
});
