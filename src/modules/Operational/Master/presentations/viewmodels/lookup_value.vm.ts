import { computed } from 'vue';
import { defineStore } from 'pinia';
import { router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Swal from 'sweetalert2';
import {
    fetchLookupValues,
    saveLookupValue,
    deleteLookupValue,
    exportLookupValues,
    importLookupValues,
    downloadTemplate
} from '@modules/Operational/Master/domain/di/lookup_value.di';
import type { LookupValueFilters, LookupValueModel } from './lookup_value.state';
import { createLookupValueState } from './lookup_value.state';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';
import { bindImportMenuDismiss } from '@modules/Core/shared/core/helpers/import_menu';

export const useLookupValueVM = defineStore('lookup-value-vm', () => {
    const {
        isLoading, isSaving, isExporting, isImporting, showImportMenu, showForm,
        lookupset_uuid, lookupValues, meta, summary, filters, formData,
        flash, fetchSequence,
    } = createLookupValueState();

    function formatDate(timestamp: number | null | undefined): string {
        if (!timestamp) return '-';
        return new Date(timestamp * 1000).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    const formMode = computed(() => formData.value.uuid ? FormMode.EDIT : FormMode.CREATE);

    const apiUrl = computed(() => `/lov/sets/${lookupset_uuid.value}/values`);
    const queryParams = computed(() => ({
        search: filters.value.search,
        per_page: filters.value.per_page,
        page: filters.value.page,
    }));

    const init = (setId: string, initialFilters: LookupValueFilters, initialValues: any) => {
        showForm.value = false;
        lookupset_uuid.value = setId;
        filters.value = { ...filters.value, ...initialFilters };
        lookupValues.value = [];
        meta.value = null;
        summary.value.total = 0;
        if (initialValues) {
            lookupValues.value = initialValues.data;
            meta.value = initialValues.meta;
            summary.value.total = initialValues.total;
        }
    };

    const fetchValues = async () => {
        const seq = ++fetchSequence.current;
        isLoading.value = true;
        const result = await fetchLookupValues.execute(lookupset_uuid.value, filters.value);
        if (seq !== fetchSequence.current) { isLoading.value = false; return; } // superseded
        result.fold(
            (failure) => {
                console.error(failure.message);
            },
            (data) => {
                lookupValues.value = data.data;
                meta.value = data.meta;
                summary.value.total = data.total;
            }
        );
        isLoading.value = false;
    };

    const handleSearch = () => {
        filters.value.page = 1;
        applyFilters();
    };

    const applyFilters = () => {
        fetchSequence.current++; // cancel any in-flight fetchValues before Inertia nav
        router.get(route('inventory.master.daftar-nilai.values', { uuid: lookupset_uuid.value }), {
            ...filters.value
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                const props = page.props as any;
                lookupValues.value = props.values.data;
                meta.value = props.values.meta;
                summary.value.total = props.values.total;
            }
        });
    };

    const changePage = (page: number) => {
        if (typeof page !== 'number') {
            const parsedUrl = new URL(String(page), window.location.origin);
            filters.value.page = Number(parsedUrl.searchParams.get('page') || '1');
        } else {
            filters.value.page = page;
        }
        applyFilters();
    };

    const openCreate = () => {
        formData.value = {
            display_name: '',
            program_code: '',
            description: '',
            seq: lookupValues.value.length + 1,
            active_flag: 'Y',
            primary_flag: 'N',
            start_date: null,
            end_date: null,
            max_stock: null,
        };
        showForm.value = true;
    };

    const closeForm = () => {
        showForm.value = false;
    };

    const openEdit = (item: LookupValueModel) => {
        formData.value = {
            uuid: item.uuid,
            display_name: item.display_name,
            program_code: item.program_code,
            description: item.description,
            seq: item.seq,
            active_flag: item.active_flag,
            primary_flag: item.primary_flag ?? 'N',
            start_date: item.start_date ? new Date(item.start_date * 1000).toISOString().split('T')[0] : null,
            end_date: item.end_date ? new Date(item.end_date * 1000).toISOString().split('T')[0] : null,
            max_stock: item.max_stock ?? null,
        };
        showForm.value = true;
    };

    const save = async () => {
        isSaving.value = true;
        const result = await saveLookupValue.execute(lookupset_uuid.value, formData.value);
        result.fold(
            async (failure) => {
                await Swal.fire({
                    title: 'Gagal!',
                    text: failure.message,
                    icon: 'error',
                });
            },
            async () => {
                showForm.value = false;
                // Don't call fetchValues() here - it races with applyFilters()
                // The caller is responsible for refreshing (uiSearch / applyFilters)
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Data berhasil disimpan.',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false,
                });
            }
        );
        isSaving.value = false;
    };

    const remove = async (item: LookupValueModel) => {
        const confirmResult = await Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Nilai "${item.display_name}" akan dihapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });

        if (!confirmResult.isConfirmed) return;

        isLoading.value = true;
        const result = await deleteLookupValue.execute(item.uuid);
        result.fold(
            async (failure) => {
                await Swal.fire('Gagal!', failure.message, 'error');
            },
            async () => {
                await fetchValues();
                Swal.fire({
                    title: 'Terhapus!',
                    text: 'Data berhasil dihapus.',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false,
                });
            }
        );
        isLoading.value = false;
    };

    const exportData = async () => {
        isExporting.value = true;
        await exportLookupValues.execute(lookupset_uuid.value, filters.value);
        isExporting.value = false;
    };

    const importData = async (file: File) => {
        isImporting.value = true;
        const result = await importLookupValues.execute(lookupset_uuid.value, file);
        result.fold(
            async (failure) => {
                await Swal.fire('Gagal Impor!', failure.message, 'error');
            },
            async () => {
                await fetchValues();
                Swal.fire('Berhasil!', 'Data telah diimpor.', 'success');
            }
        );
        isImporting.value = false;
    };

    const downloadImportTemplate = async () => {
        await downloadTemplate.execute();
    };

    const openImportPicker = () => {
        document.getElementById('lookupValueImportInput')?.click();
    };


    const handleImport = async (event: Event) => {
        const target = event.target as HTMLInputElement;
        if (target.files?.length) {
            await importData(target.files[0]);
            target.value = '';
        }
    };
    const toggleImportMenu = (event?: MouseEvent) => {
        event?.stopPropagation();
        showImportMenu.value = !showImportMenu.value;
    };

    const closeImportMenu = () => {
        showImportMenu.value = false;
    };

    const openImportData = () => {
        showImportMenu.value = false;
        openImportPicker();
    };

    const closeImportMenuAndDownload = () => {
        showImportMenu.value = false;
        downloadImportTemplate();
    };

    let unbindImportMenuDismiss: (() => void) | null = null;
    const initImportMenuDismiss = () => {
        unbindImportMenuDismiss = bindImportMenuDismiss(closeImportMenu);
    };
    const disposeImportMenuDismiss = () => {
        unbindImportMenuDismiss?.();
        unbindImportMenuDismiss = null;
    };

    return {
        flash,
        isLoading, isSaving, isExporting, isImporting, showForm,
        lookupValues, meta, summary, filters, formData,
        formMode,
        lookupsetUuid: lookupset_uuid,
        apiUrl,
        queryParams,
        formatDate,
        init, fetchValues, handleSearch, changePage,
        openCreate, openEdit, closeForm, save, remove,
        exportData, importData, downloadImportTemplate, openImportPicker,
        showImportMenu, toggleImportMenu, closeImportMenu, openImportData, closeImportMenuAndDownload,
        handleImport,
        initImportMenuDismiss,
        disposeImportMenuDismiss,
    };
});
