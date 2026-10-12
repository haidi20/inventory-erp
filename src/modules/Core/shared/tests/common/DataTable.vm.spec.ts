import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { createDataTableVMStore } from '@modules/Core/shared/Components/common/DataTable.vm';
import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

const rows = Array.from({ length: 12 }, (_, index) => ({
    id: index + 1,
    name: index % 2 === 0 ? `Alpha ${index}` : `Beta ${index}`,
}));

const nameColumn: DataTableColumn = { key: 'name', label: 'Nama', sortable: true };

describe('DataTableVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.useFakeTimers();
    });

    it('filters client-side items after debounced search input', () => {
        const useVM = createDataTableVMStore('datatable-filter-test');
        const vm = useVM();

        vm.syncConfig({ items: rows });
        vm.currentSearch = 'alpha';
        vm.onSearchInput();
        vi.advanceTimersByTime(350);

        expect(vm.totalItems).toBe(6);
        expect(vm.pagedItems.every((row) => row.name.toLowerCase().includes('alpha'))).toBe(true);
    });

    it('sorts client-side items and toggles direction', () => {
        const useVM = createDataTableVMStore('datatable-sort-test');
        const vm = useVM();

        vm.syncConfig({ items: [{ name: 'Charlie' }, { name: 'Alpha' }, { name: 'Bravo' }] });
        vm.toggleSort(nameColumn);

        expect(vm.sortDirection).toBe('asc');
        expect(vm.filteredItems.map((row) => row.name)).toEqual(['Alpha', 'Bravo', 'Charlie']);

        vm.toggleSort(nameColumn);

        expect(vm.sortDirection).toBe('desc');
        expect(vm.filteredItems.map((row) => row.name)).toEqual(['Charlie', 'Bravo', 'Alpha']);
    });

    it('paginates client-side items', () => {
        const useVM = createDataTableVMStore('datatable-pagination-test');
        const vm = useVM();

        vm.syncConfig({ items: rows });

        expect(vm.totalItems).toBe(12);
        expect(vm.currentLastPage).toBe(2);
        expect(vm.pagedItems).toHaveLength(10);

        vm.goToPage(2);

        expect(vm.page).toBe(2);
        expect(vm.pagedItems).toHaveLength(2);
        expect(vm.displayStart).toBe(11);
        expect(vm.displayEnd).toBe(12);
    });

    it('ignores sorting for non-sortable columns', () => {
        const useVM = createDataTableVMStore('datatable-sortable-test');
        const vm = useVM();

        vm.syncConfig({ items: rows });
        vm.toggleSort({ key: 'name', label: 'Nama', sortable: false });

        expect(vm.sortBy).toBe('');
    });
});
