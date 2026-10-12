import { describe, it, expect, vi, beforeEach } from 'vitest';
import { FetchRolesUseCase } from '@modules/Core/Identity/domain/usecases/fetch-roles.usecase';
import type { RoleRepository } from '@modules/Core/Identity/domain/repositories/role.repository';

describe('FetchRolesUseCase', () => {
    let useCase: FetchRolesUseCase;
    let mockRepo: RoleRepository;

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
        } as unknown as RoleRepository;
        useCase = new FetchRolesUseCase(mockRepo);
    });

    it('should call repository.paginate with correct filters', async () => {
        const filters = { search: 'test', per_page: 25 };
        await useCase.execute(filters);

        expect(mockRepo.paginate).toHaveBeenCalledWith(filters);
    });
});

