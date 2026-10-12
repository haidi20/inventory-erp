import { defineStore } from 'pinia';
import { ref, watch, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import * as backupDI from '@modules/Core/Platform/domain/di/backup.di';
import { createBackupDependencies, type BackupVMDependencies } from '@modules/Core/Platform/domain/di/backup.di';
import type { BackupModel, BackupFilters, BackupFormData } from '@modules/Core/Platform/domain/models/backup.model';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import Swal from 'sweetalert2';

// Local debounce helper
function debounce(fn: Function, delay: number) {
    let timeoutId: any;
    return function (...args: any[]) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(null, args), delay);
    };
}

export function createBackupVMStore(
    storeId = 'backup-vm',
    dependencies: BackupVMDependencies = backupDI,
) {
    return defineStore(storeId, () => {
        const {
            fetchBackupHistoryUseCase,
            restoreBackupUseCase,
            exportBackupUseCase,
            downloadBackupUseCase,
        } = dependencies;

        // UI State
        const backups = ref<BackupModel[]>([]);
        const meta = ref<any>({});
        const showRestoreForm = ref(false);
        const isExporting = ref(false);
        const isRestoring = ref(false);

        // Constant Options
        const historyTypes = [
            { id: 'manual', text: 'Manual Backup' },
            { id: 'system', text: 'System Backup' },
        ];

        const perPageOptions = [
            { id: 5, text: '5' },
            { id: 10, text: '10' },
            { id: 25, text: '25' },
            { id: 50, text: '50' },
            { id: 100, text: '100' },
        ];

        // Filter State
        const search = ref('');
        const historyType = ref<'manual' | 'system'>('manual');
        const perPage = ref(5);
        const currentPage = ref(1);
        const orderColumn = ref(0); // 0 is created_at
        const orderDirection = ref<'asc' | 'desc'>('desc');
        const page = usePage<any>();

        const canManageBackups = computed(() => {
            const permissions = Array.isArray(page.props.auth?.user?.permissions)
                ? page.props.auth.user.permissions
                : [];
            return permissions.includes('settings.view');
        });

        const flash = computed(() => ({
            success: page.props.flash?.success ?? null,
            error: page.props.flash?.error ?? null,
        }));

        // Form State (for Restore)
        const form = useForm<BackupFormData>({
            backup_file: null,
            create_pre_restore_backup: true,
        });

        async function fetchHistory(filters: Partial<BackupFilters> = {}) {
            const finalFilters: BackupFilters = {
                search: search.value,
                history_type: historyType.value,
                per_page: perPage.value,
                page: currentPage.value,
                order_column: orderColumn.value,
                order_direction: orderDirection.value,
                ...filters
            };

            const result = await fetchBackupHistoryUseCase.execute(finalFilters);
            result.fold(
                (failure: any) => { Swal.fire('Gagal!', failure.message, 'error'); },
                (data: any) => {
                    backups.value = data.data || [];
                    meta.value = data.meta || data;
                }
            );
        }

        const updateFilters = debounce(() => {
            currentPage.value = 1;
            fetchHistory();
        }, 300);

        watch([search, historyType, perPage, orderColumn, orderDirection], () => {
            updateFilters();
        });

        watch(() => page.props.auth?.api_token, (token) => {
            if (token) void fetchHistory();
        }, { immediate: true });

        function setBackups(items: BackupModel[]) {
            backups.value = items;
        }

        function toggleSort(column: number) {
            if (orderColumn.value === column) {
                orderDirection.value = orderDirection.value === 'asc' ? 'desc' : 'asc';
            } else {
                orderColumn.value = column;
                orderDirection.value = 'asc';
            }
        }

        async function exportBackup(notes: string = '', downloadAfter: boolean = true) {
            isExporting.value = true;
            const result = await exportBackupUseCase.execute({
                notes,
                download_after_export: downloadAfter
            });
            
            result.fold(
                (failure: any) => { Swal.fire('Gagal!', failure.message, 'error'); },
                () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Backup database berhasil dibuat.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                    });
                    fetchHistory();
                }
            );
            isExporting.value = false;
        }

        async function restoreBackup() {
            if (form.processing) return;
            
            const confirmResult = await Swal.fire({
                title: 'Konfirmasi Restore',
                text: 'Proses ini akan menimpa database Anda. Pastikan Anda memiliki cadangan terbaru!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Jalankan Restore!',
                cancelButtonText: 'Batal',
                confirmButtonColor: 'var(--danger)',
                reverseButtons: true
            });

            if (!confirmResult.isConfirmed) return;

            isRestoring.value = true;
            try {
                const result = await restoreBackupUseCase.execute(form.data());
                
                result.fold(
                    (failure: any) => {
                        if (failure instanceof ValidationFailure) {
                            form.setError(failure.errors as any);
                        } else {
                            Swal.fire('Gagal!', failure.message, 'error');
                        }
                    },
                    () => {
                        Swal.fire({
                            title: 'Restore Berhasil!',
                            text: 'Database telah dipulihkan. Halaman akan dimuat ulang.',
                            icon: 'success'
                        }).then(() => {
                            isRestoring.value = false;
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        });
                    }
                );
            } finally {
                isRestoring.value = false;
            }
        }

        async function downloadBackupFile(filename: string) {
            const result = await downloadBackupUseCase.execute(filename, historyType.value);
            result.fold(
                (failure: any) => { Swal.fire('Gagal!', failure.message, 'error'); },
                () => {}
            );
        }

        function openRestore() {
            form.reset();
            form.clearErrors();
            showRestoreForm.value = true;
        }

        function setRestoreFile(event: Event) {
            const input = event.target as HTMLInputElement;
            form.backup_file = input.files?.length ? input.files[0] : null;
        }

        function closeRestore() {
            showRestoreForm.value = false;
        }

        const initFilters = (filters: Partial<BackupFilters>): void => {
            search.value = filters.search || '';
            historyType.value = (filters as any).history_type || 'manual';
            perPage.value = filters.per_page || 5;
            currentPage.value = filters.page || 1;
            fetchHistory(); // Initial fetch
        };

        return {
            backups,
            meta,
            showRestoreForm,
            isExporting,
            isRestoring,
            canManageBackups,
            flash,
            historyTypes,
            perPageOptions,
            search,
            historyType,
            perPage,
            currentPage,
            orderColumn,
            orderDirection,
            form,
            fetchHistory,
            exportBackup,
            restoreBackup,
            downloadBackupFile,
            openRestore,
            setRestoreFile,
            closeRestore,
            initFilters,
            toggleSort,
            setBackups,
        };
    });
}

export const useBackupVM = createBackupVMStore();
