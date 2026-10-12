import { computed, reactive, ref } from 'vue';
import Swal from 'sweetalert2';
import type { Failure } from '@modules/Core/shared/core/failures';
import type { ItemConversion, ItemConversionFilters, ItemConversionSummary } from '@modules/Operational/Master/domain/models/item-conversion.model';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';
import { fetchItemConversionsUseCase, saveItemConversionUseCase, deleteItemConversionUseCase } from '@modules/Operational/Master/domain/di/item-conversion.di';

export function useItemConversionVM() {
    const items = ref<ItemConversion[]>([]);
    const total = ref(0);
    const loading = ref(false);
    const showForm = ref(false);
    const selectedItem = ref<ItemConversion | null>(null);
    const form = reactive<Partial<ItemConversion>>({
        uuid: undefined,
        item_uuid: '',
        from_unit_uuid: '',
        to_unit_uuid: '',
        conv_ratio: 1,
        is_active: true,
    });
    const filters = reactive<ItemConversionFilters>({
        search: '',
        sort_by: 'updated_at',
        sort_dir: 'desc',
        page: 1,
        per_page: 5
    });

    const fetch = async () => {
        loading.value = true;
        const result = await fetchItemConversionsUseCase.execute(filters);
        result.fold(
            (failure: Failure) => {
                Swal.fire('Error', failure.message, 'error');
            },
            (summary: ItemConversionSummary) => {
                items.value = summary.items;
                total.value = summary.total;
            }
        );
        loading.value = false;
    };

    const save = async (data: Partial<ItemConversion>) => {
        loading.value = true;
        const result = await saveItemConversionUseCase.execute(data);
        return result.fold(
            (failure: Failure) => {
                Swal.fire('Error', failure.message, 'error');
                return null;
            },
            (item: ItemConversion) => {
                Swal.fire('Berhasil', 'Data konversi berhasil disimpan.', 'success');
                fetch();
                return item;
            }
        );
    };

    const remove = async (uuid: string) => {
        const confirm = await Swal.fire({
            title: 'Hapus data?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        loading.value = true;
        const result = await deleteItemConversionUseCase.execute(uuid);
        result.fold(
            (failure: Failure) => Swal.fire('Error', failure.message, 'error'),
            () => {
                fetch();
                return Swal.fire('Terhapus', 'Data konversi berhasil dihapus.', 'success');
            }
        );
        loading.value = false;
    };

    const handleSearch = (query: string) => {
        filters.search = query;
        filters.page = 1;
        fetch();
    };

    const handlePageChange = (page: number) => {
        filters.page = page;
        fetch();
    };

    const handleSort = (column: string) => {
        if (filters.sort_by === column) {
            filters.sort_dir = filters.sort_dir === 'asc' ? 'desc' : 'asc';
        } else {
            filters.sort_by = column;
            filters.sort_dir = 'asc';
        }
        fetch();
    };

    const paginationLinks = computed(() => {
        const lastPage = Math.ceil(total.value / (filters.per_page || 5));
        const links = [];

        for (let page = 1; page <= lastPage; page += 1) {
            links.push({
                url: page.toString(),
                label: page.toString(),
                active: page === filters.page,
            });
        }

        return links;
    });

    const openCreate = () => {
        selectedItem.value = null;
        Object.assign(form, {
            uuid: undefined,
            item_uuid: '',
            from_unit_uuid: '',
            to_unit_uuid: '',
            conv_ratio: 1,
            is_active: true,
        });
        showForm.value = true;
    };

    const openEdit = (item: ItemConversion) => {
        selectedItem.value = item;
        Object.assign(form, {
            uuid: item.uuid,
            item_uuid: item.item_uuid ?? '',
            from_unit_uuid: item.from_unit_uuid,
            to_unit_uuid: item.to_unit_uuid,
            conv_ratio: item.conv_ratio,
            is_active: item.is_active,
        });
        showForm.value = true;
    };

    const closeForm = () => {
        showForm.value = false;
    };

    const formMode = computed(() => selectedItem.value ? FormMode.EDIT : FormMode.CREATE);

    const submitConversionForm = async () => {
        const result = await save(form);
        if (result) {
            closeForm();
        }
    };

    return {
        items,
        total,
        loading,
        showForm,
        selectedItem,
        form,
        formMode,
        filters,
        fetch,
        save,
        remove,
        handleSearch,
        handlePageChange,
        handleSort,
        paginationLinks,
        openCreate,
        openEdit,
        closeForm,
        submitConversionForm,
    };
}
