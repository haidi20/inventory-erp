import { describe, it, expect, vi, beforeEach } from 'vitest';
import { FetchUsersUseCase } from '@modules/Core/Identity/domain/usecases/fetch-users.usecase';
import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';

describe('FetchUsersUseCase', () => {
    let useCase: FetchUsersUseCase;
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
        } as unknown as UserRepository;
        useCase = new FetchUsersUseCase(mockRepo);
    });

    it('should call repository.paginate with correct filters', async () => {
        const filters = { search: 'test', per_page: 25 };
        await useCase.execute(filters);

        expect(mockRepo.paginate).toHaveBeenCalledWith(filters);
    });
});
