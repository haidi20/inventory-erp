import { describe, it, expect, vi, beforeEach } from 'vitest';
import { FetchUnitsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-units.usecase';
import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';

describe('FetchUnitsUseCase', () => {
    let useCase: FetchUnitsUseCase;
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
        } as unknown as UnitRepository;
        useCase = new FetchUnitsUseCase(mockRepo);
    });

    it('should call repository.paginate with correct filters', async () => {
        const filters = { search: 'pcs', per_page: 25 };
        await useCase.execute(filters);

        expect(mockRepo.paginate).toHaveBeenCalledWith(filters);
    });
});
