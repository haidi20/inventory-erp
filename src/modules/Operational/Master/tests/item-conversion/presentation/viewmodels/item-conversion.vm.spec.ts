import { describe, it, expect, vi, beforeEach } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure, ValidationFailure } from '@modules/Core/shared/core/failures';

// Mock DI singletons sebelum import VM
vi.mock('@modules/Operational/Master/domain/di/item-conversion.di', () => ({
    fetchItemConversionsUseCase: { execute: vi.fn().mockResolvedValue(right({ items: [], total: 0, current_page: 1, per_page: 15 })) },
    saveItemConversionUseCase: { execute: vi.fn().mockResolvedValue(right({ uuid: 'new' })) },
    deleteItemConversionUseCase: { execute: vi.fn().mockResolvedValue(right(undefined)) },
}));

vi.mock('sweetalert2', () => ({
    default: { fire: vi.fn().mockResolvedValue({ isConfirmed: true }) },
}));

import { useItemConversionVM } from '@modules/Operational/Master/presentations/viewmodels/item_conversion.vm';
import { fetchItemConversionsUseCase, saveItemConversionUseCase, deleteItemConversionUseCase } from '@modules/Operational/Master/domain/di/item-conversion.di';

describe('useItemConversionVM', () => {
    let vm: ReturnType<typeof useItemConversionVM>;

    beforeEach(() => {
        vi.clearAllMocks();
        vm = useItemConversionVM();
    });

    it('harus inisialisasi dengan state default', () => {
        expect(vm.items.value).toEqual([]);
        expect(vm.total.value).toBe(0);
        expect(vm.loading.value).toBe(false);
    });

    it('fetch harus memanggil useCase dan mengisi items', async () => {
        const mockItems = [{ uuid: '1', item_uuid: 'a', from_unit_uuid: 'u1', to_unit_uuid: 'u2', conv_ratio: 2, is_active: true }];
        (fetchItemConversionsUseCase.execute as any).mockResolvedValue(right({
            items: mockItems, total: 1, current_page: 1, per_page: 15
        }));

        await vm.fetch();

        expect(fetchItemConversionsUseCase.execute).toHaveBeenCalled();
        expect(vm.items.value).toEqual(mockItems);
        expect(vm.total.value).toBe(1);
        expect(vm.loading.value).toBe(false);
    });

    it('fetch harus tampilkan error saat gagal', async () => {
        (fetchItemConversionsUseCase.execute as any).mockResolvedValue(left(new ServerFailure('Server error')));
        const Swal = (await import('sweetalert2')).default;

        await vm.fetch();

        expect(Swal.fire).toHaveBeenCalledWith('Error', 'Server error', 'error');
    });

    it('save harus memanggil useCase dan refresh items', async () => {
        (saveItemConversionUseCase.execute as any).mockResolvedValue(right({ uuid: 'new-uuid' }));
        (fetchItemConversionsUseCase.execute as any).mockResolvedValue(right({ items: [], total: 0, current_page: 1, per_page: 15 }));

        const result = await vm.save({ item_uuid: 'a', from_unit_uuid: 'u1', to_unit_uuid: 'u2', conv_ratio: 5 });

        expect(saveItemConversionUseCase.execute).toHaveBeenCalled();
        expect(result).not.toBeNull();
    });

    it('remove harus memanggil delete setelah konfirmasi', async () => {
        (deleteItemConversionUseCase.execute as any).mockResolvedValue(right(undefined));
        (fetchItemConversionsUseCase.execute as any).mockResolvedValue(right({ items: [], total: 0, current_page: 1, per_page: 15 }));

        await vm.remove('target-uuid');

        expect(deleteItemConversionUseCase.execute).toHaveBeenCalledWith('target-uuid');
    });
});
