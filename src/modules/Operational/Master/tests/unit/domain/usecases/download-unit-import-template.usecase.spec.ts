import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DownloadUnitImportTemplateUseCase } from '@modules/Operational/Master/domain/usecases/download-unit-import-template.usecase';

describe('DownloadUnitImportTemplateUseCase', () => {
    const mockRepo = { downloadImportTemplate: vi.fn() };
    const useCase = new DownloadUnitImportTemplateUseCase(mockRepo as any);

    it('harus memanggil repository.downloadImportTemplate', async () => {
        mockRepo.downloadImportTemplate.mockResolvedValue(right(undefined));
        await useCase.execute();
        expect(mockRepo.downloadImportTemplate).toHaveBeenCalled();
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.downloadImportTemplate.mockResolvedValue(right(undefined));
        expect((await useCase.execute()).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.downloadImportTemplate.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute()).isLeft()).toBe(true);
    });
});
