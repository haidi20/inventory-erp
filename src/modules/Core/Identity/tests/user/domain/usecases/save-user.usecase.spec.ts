import { describe, it, expect, vi, beforeEach } from 'vitest';
import { SaveUserUseCase } from '@modules/Core/Identity/domain/usecases/save-user.usecase';
import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';
import { left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';

describe('SaveUserUseCase', () => {
    let useCase: SaveUserUseCase;
    let mockRepo: UserRepository;

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
        useCase = new SaveUserUseCase(mockRepo);
    });

    it('should call repository.save with correct data', async () => {
        const userData = { name: 'John Doe', email: 'john@example.com', role_uuids: ['role-1'] };
        await useCase.execute(userData);

        expect(mockRepo.save).toHaveBeenCalledWith(userData);
    });

    it('should return Left(Failure) if repository.save fails', async () => {
        const userData = { name: 'John Doe', email: 'john@example.com', role_uuids: ['role-1'] };
        const failure = new ServerFailure('Save failed');
        mockRepo.save = vi.fn().mockResolvedValue(left(failure));

        const result = await useCase.execute(userData);

        expect(result.isLeft()).toBe(true);
        expect(result.fold(l => l, r => null)).toEqual(failure);
    });
});
