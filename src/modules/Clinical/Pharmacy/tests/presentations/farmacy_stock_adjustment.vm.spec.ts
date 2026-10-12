import { beforeEach, describe, expect, it } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useFarmacyStockAdjustmentVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_adjustment.vm';

describe('FarmacyStockAdjustmentVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('memakai state awal dari file state', () => {
        const vm = useFarmacyStockAdjustmentVM();

        expect(vm.activeRoute).toBe('list');
        expect(vm.formMode).toBe('create');
        expect(vm.formModeLabel).toBe('Tambah');
        expect(vm.formTitle).toBe('Tambah Penyesuaian Persediaan');
        expect(vm.form.items).toHaveLength(1);
    });

    it('menambah dan menghapus item penyesuaian', () => {
        const vm = useFarmacyStockAdjustmentVM();

        vm.addItem();
        expect(vm.form.items).toHaveLength(2);

        vm.removeItem(vm.form.items[1].uuid);
        expect(vm.form.items).toHaveLength(1);
    });

    it('menghitung subtotal dan total penyesuaian', () => {
        const vm = useFarmacyStockAdjustmentVM();
        const item = vm.form.items[0];

        item.unitPrice = 15000;
        item.quantity = 2;

        expect(vm.getItemSubtotal(item)).toBe(30000);
        expect(vm.totalAdjustment).toBe(30000);
    });

    it('mengubah mode form menjadi edit', async () => {
        const vm = useFarmacyStockAdjustmentVM();

        await vm.initForm('edit', 'unknown-uuid');

        expect(vm.formMode).toBe('edit');
        expect(vm.formModeLabel).toBe('Ubah');
        expect(vm.formTitle).toBe('Ubah Penyesuaian Persediaan');
    });
});
