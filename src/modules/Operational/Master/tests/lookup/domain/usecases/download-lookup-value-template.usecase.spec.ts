import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DownloadLookupValueTemplate } from '@modules/Operational/Master/domain/usecases/download-lookup-value-template.usecase';

describe('DownloadLookupValueTemplate', () => {
    const mockRepo = { downloadTemplate: vi.fn() };
    const useCase = new DownloadLookupValueTemplate(mockRepo as any);

    it('harus memanggil repository.downloadTemplate', async () => {
        mockRepo.downloadTemplate.mockResolvedValue(right(undefined));
        await useCase.execute();
        expect(mockRepo.downloadTemplate).toHaveBeenCalled();
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.downloadTemplate.mockResolvedValue(right(undefined));
        expect((await useCase.execute()).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.downloadTemplate.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute()).isLeft()).toBe(true);
    });
});
