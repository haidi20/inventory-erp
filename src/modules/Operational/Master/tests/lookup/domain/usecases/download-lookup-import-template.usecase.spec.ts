import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DownloadLookupImportTemplateUseCase } from '@modules/Operational/Master/domain/usecases/download-lookup-import-template.usecase';

describe('DownloadLookupImportTemplateUseCase', () => {
    const mockRepo = { downloadImportTemplate: vi.fn() };
    const useCase = new DownloadLookupImportTemplateUseCase(mockRepo as any);

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

    it('harus return Left saat exception', async () => {
        mockRepo.downloadImportTemplate.mockRejectedValue(new Error('Network'));
        const result = await useCase.execute();
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
    });
});
