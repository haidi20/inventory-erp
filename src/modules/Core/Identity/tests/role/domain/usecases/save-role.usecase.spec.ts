import { describe, it, expect, vi, beforeEach } from 'vitest';
import { SaveRoleUseCase } from '@modules/Core/Identity/domain/usecases/save-role.usecase';
import type { RoleRepository } from '@modules/Core/Identity/domain/repositories/role.repository';
import { left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('SaveRoleUseCase', () => {
    let useCase: SaveRoleUseCase;
    let mockRepo: RoleRepository;

    beforeEach(() => {
        mockRepo = {
            save: vi.fn(),
            paginate: vi.fn(),
            changePerPage: vi.fn(),
            changePage: vi.fn(),
            delete: vi.fn(),
            export: vi.fn(),
            import: vi.fn(),
            downloadImportTemplate: vi.fn(),
        };
        useCase = new SaveRoleUseCase(mockRepo);
    });

    it('should call repository.save with correct data', async () => {
        const RoleData = { name: 'admin', label: 'Administrator', permissions: ['p1'] };
        await useCase.execute(RoleData);

        expect(mockRepo.save).toHaveBeenCalledWith(RoleData);
    });

    it('should return Left(Failure) if repository.save fails', async () => {
        const RoleData = { name: 'admin', label: 'Administrator', permissions: ['p1'] };
        const failure = new ServerFailure('Save failed');
        mockRepo.save = vi.fn().mockResolvedValue(left(failure));

        const result = await useCase.execute(RoleData);

        expect(result.isLeft()).toBe(true);
        expect(result.fold(l => l, r => null)).toEqual(failure);
    });
});

