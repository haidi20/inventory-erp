import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { createSelect2VMStore } from '@modules/Core/shared/Components/common/select2.vm';

const { apiGetMock } = vi.hoisted(() => ({
    apiGetMock: vi.fn(),
}));

vi.mock('@modules/Core/shared/core/api_service', () => ({
    default: {
        get: apiGetMock,
    },
}));

describe('Select2VM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.useFakeTimers();
        apiGetMock.mockReset();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('memfilter opsi client-side berdasarkan label', () => {
        const useVM = createSelect2VMStore('select2-client-test', {
            label: 'name',
            valueKey: 'id',
        });
        const vm = useVM();

        vm.syncConfig({
            mode: 'client',
            options: [
                { id: 1, name: 'Alpha' },
                { id: 2, name: 'Beta' },
            ],
        });
        vm.onSearch('alpha');

        expect(vm.visibleOptions).toHaveLength(1);
        expect(vm.reduceOption(vm.visibleOptions[0])).toBe(1);
        expect(vm.getSelectedName(1)).toBe('Alpha');
    });

    it('menunda request server-side setelah debounce', async () => {
        apiGetMock.mockResolvedValue({
            data: { data: [{ uuid: 'unit-1', name: 'Tablet' }] },
        });

        const useVM = createSelect2VMStore('select2-debounce-test', {
            label: 'name',
            valueKey: 'uuid',
        });
        const vm = useVM();

        vm.syncConfig({
            endpoint: '/products/options',
            params: { type: 'unit' },
            selectedValue: null,
        });
        vm.onSearch('tab');

        expect(apiGetMock).not.toHaveBeenCalled();
        await vi.runOnlyPendingTimersAsync();

        expect(apiGetMock).toHaveBeenCalledWith('/products/options', {
            params: {
                type: 'unit',
                search: 'tab',
                selected: null,
            },
        });
        expect(vm.visibleOptions).toHaveLength(1);
        expect(vm.isLoading).toBe(false);
    });

    it('mempertahankan opsi server ketika nilai terpilih berubah', async () => {
        apiGetMock.mockResolvedValue({
            data: { data: [{ uuid: 'unit-1', name: 'Tablet' }] },
        });

        const useVM = createSelect2VMStore('select2-preserve-test', {
            label: 'name',
            valueKey: 'uuid',
        });
        const vm = useVM();
        vm.syncConfig({ endpoint: '/products/options', selectedValue: null });
        await vm.loadOptions('');

        vm.syncConfig({ endpoint: '/products/options', selectedValue: 'unit-1' });

        expect(vm.visibleOptions).toHaveLength(1);
        expect(vm.getOptionLabel(vm.visibleOptions[0])).toBe('Tablet');
    });

    it('memakai label atau name sebagai fallback label opsi', () => {
        const useVM = createSelect2VMStore('select2-fallback-test', {
            label: 'name',
            valueKey: 'uuid',
        });
        const vm = useVM();

        expect(vm.getOptionLabel({ uuid: 'unit-1', label: 'Label Ampul' })).toBe('Label Ampul');
        expect(vm.getOptionLabel({ uuid: 'unit-2', name: 'Ampul' })).toBe('Ampul');
    });

    it('memuat opsi terpilih yang belum ada di daftar', async () => {
        apiGetMock.mockResolvedValue({
            data: { data: [{ uuid: 'unit-selected', name: 'Ampul' }] },
        });

        const useVM = createSelect2VMStore('select2-selected-test', {
            label: 'name',
            valueKey: 'uuid',
        });
        const vm = useVM();

        vm.syncConfig({
            endpoint: '/products/options',
            selectedValue: 'unit-selected',
        });
        await vm.ensureSelectedOption('unit-selected');

        expect(vm.getSelectedName('unit-selected')).toBe('Ampul');
    });

    it('mengabaikan response server-side yang sudah usang', async () => {
        let resolveStaleRequest!: (value: any) => void;
        apiGetMock
            .mockImplementationOnce(() => new Promise((resolve) => {
                resolveStaleRequest = resolve;
            }))
            .mockResolvedValueOnce({
                data: { data: [{ uuid: 'fresh', name: 'Fresh' }] },
            });

        const useVM = createSelect2VMStore('select2-race-test', {
            label: 'name',
            valueKey: 'uuid',
        });
        const vm = useVM();
        vm.syncConfig({ endpoint: '/products/options' });

        const staleRequest = vm.loadOptions('old');
        await vm.loadOptions('new');
        resolveStaleRequest({ data: { data: [{ uuid: 'stale', name: 'Stale' }] } });
        await staleRequest;

        expect(vm.visibleOptions).toHaveLength(1);
        expect(vm.getSelectedName('fresh')).toBe('Fresh');
        expect(vm.isLoading).toBe(false);
    });
});
