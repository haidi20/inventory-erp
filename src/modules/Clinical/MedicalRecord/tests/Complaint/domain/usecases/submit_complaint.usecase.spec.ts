import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import { SubmitComplaintUseCase } from '@modules/Clinical/MedicalRecord/domain/usecases/submit_complaint.usecase';

describe('SubmitComplaintUseCase', () => {
    const mockRepo = { submit: vi.fn() };
    const useCase = new SubmitComplaintUseCase(mockRepo as any);

    const validData = {
        subject: 'Antrian Lama',
        content: 'Antrian terlalu lama dan tidak ada informasi.',
        type: 'pengaduan',
        is_anonymous: false,
    };

    it('harus meneruskan data ke repository.submit', async () => {
        mockRepo.submit.mockResolvedValue(right(undefined));
        await useCase.execute(validData);
        expect(mockRepo.submit).toHaveBeenCalledWith(validData);
    });

    it('harus return Right saat submit sukses', async () => {
        mockRepo.submit.mockResolvedValue(right(undefined));
        const result = await useCase.execute(validData);
        expect(result.isRight()).toBe(true);
    });

    it('harus return Left(ValidationFailure) saat validasi gagal', async () => {
        mockRepo.submit.mockResolvedValue(left(new ValidationFailure('Validasi gagal', { subject: ['required'] })));
        const result = await useCase.execute({ ...validData, subject: '' });
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ValidationFailure), () => {});
    });

    it('harus submit secara anonim saat is_anonymous = true', async () => {
        mockRepo.submit.mockResolvedValue(right(undefined));
        const anonData = { ...validData, is_anonymous: true };
        await useCase.execute(anonData);
        expect(mockRepo.submit).toHaveBeenCalledWith(expect.objectContaining({ is_anonymous: true }));
    });
});
