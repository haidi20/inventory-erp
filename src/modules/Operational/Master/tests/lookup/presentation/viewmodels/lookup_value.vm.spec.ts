import { describe, it, expect, vi, beforeEach } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';

vi.mock('@modules/Operational/Master/domain/di/lookup_value.di', () => ({
    fetchLookupValues: { execute: vi.fn().mockResolvedValue(right({ data: [], meta: null, total: 0 })) },
    saveLookupValue: { execute: vi.fn().mockResolvedValue(right({ uuid: 'v1' })) },
    deleteLookupValue: { execute: vi.fn().mockResolvedValue(right(undefined)) },
    exportLookupValues: { execute: vi.fn().mockResolvedValue(right(undefined)) },
    importLookupValues: { execute: vi.fn().mockResolvedValue(right(undefined)) },
    downloadTemplate: { execute: vi.fn().mockResolvedValue(right(undefined)) },
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { post: vi.fn() },
    usePage: () => ({
        props: {
            flash: {},
            errors: {},
        },
    }),
}));

vi.mock('ziggy-js', () => ({ route: vi.fn((name: string) => `/${name}`) }));

vi.mock('sweetalert2', () => ({
    default: { fire: vi.fn().mockResolvedValue({ isConfirmed: true }) },
}));

import { useLookupValueVM } from '@modules/Operational/Master/presentations/viewmodels/lookup_value.vm';
import { fetchLookupValues, saveLookupValue, deleteLookupValue } from '@modules/Operational/Master/domain/di/lookup_value.di';

describe('useLookupValueVM', () => {
    let vm: ReturnType<typeof useLookupValueVM>;

    beforeEach(() => {
        vi.clearAllMocks();
        setActivePinia(createPinia());
        vm = useLookupValueVM();
    });

    it('harus inisialisasi dengan state default', () => {
        expect(vm.lookupValues).toEqual([]);
        expect(vm.isLoading).toBe(false);
        expect(vm.showForm).toBe(false);
    });

    it('init harus mengisi setId dan filters awal', () => {
        vm.init('set-uuid', { search: '', page: 1, per_page: 10 }, null);
        expect(vm.filters.page).toBe(1);
        expect(vm.filters.per_page).toBe(10);
    });

    it('fetchValues harus mengisi lookupValues', async () => {
        const mockValues = [{ uuid: 'v1', display_name: 'Val', program_code: 'VAL', description: '', seq: 1, active_flag: 'Y' }];
        (fetchLookupValues.execute as any).mockResolvedValue(right({ data: mockValues, meta: null, total: 1 }));

        vm.init('set-uuid', {} as any, null);
        await vm.fetchValues();

        expect(fetchLookupValues.execute).toHaveBeenCalled();
        expect(vm.lookupValues).toEqual(mockValues);
    });

    it('fetchValues harus tampilkan error saat gagal', async () => {
        (fetchLookupValues.execute as any).mockResolvedValue(left(new ServerFailure('Error')));

        vm.init('set-uuid', {} as any, null);
        await vm.fetchValues();

        expect(vm.lookupValues).toEqual([]);
    });

    it('save harus memanggil saveLookupValue', async () => {
        (saveLookupValue.execute as any).mockResolvedValue(right({ uuid: 'new' }));
        (fetchLookupValues.execute as any).mockResolvedValue(right({ data: [], meta: null, total: 0 }));

        vm.init('set-uuid', {} as any, null);
        vm.formData.display_name = 'Test';
        vm.formData.program_code = 'TEST';
        await vm.save();

        expect(saveLookupValue.execute).toHaveBeenCalled();
    });
});
