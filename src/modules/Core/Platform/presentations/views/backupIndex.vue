<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useBackupVM } from '@modules/Core/Platform/presentations/viewmodels/backup.vm';
import BackupTable from '@modules/Core/Platform/presentations/views/Partials/backupTable.vue';
import BackupFiltersComp from '@modules/Core/Platform/presentations/views/Partials/backupFilters.vue';
import BackupForm from '@modules/Core/Platform/presentations/views/Partials/backupForm.vue';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';

const props = defineProps<{
    filters?: any;
    database?: any;
    recentBackups?: any[];
}>();

const vm = useBackupVM();

onMounted(() => {
    vm.initFilters(props.filters || {});
    if (props.recentBackups) {
        vm.setBackups(props.recentBackups);
    }
});

</script>

<template>
    <Head title="Backup & Restore - Inventory" />

    <div v-if="vm.flash.success" class="alert-banner alert-success">
        <i class="fa-solid fa-circle-check"></i>
        {{ vm.flash.success }}
    </div>
    <div v-else-if="vm.flash.error" class="alert-banner alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        {{ vm.flash.error }}
    </div>

    <!-- Database Info Hero -->
    <div v-if="database" class="db-hero" style="margin-bottom: 32px;">
        <div class="db-hero-main">
            <div class="db-kicker">
                <i class="fa-solid fa-shield-heart"></i>
                Pusat Cadangan Database
            </div>
            <div class="db-title">Backup Data Klinik yang Lebih Aman</div>
            <div class="db-description">
                Gunakan halaman ini untuk mencadangkan database atau memulihkan data. Sistem melakukan validasi ketat sebelum mengganti data aktif Anda.
            </div>
            <div class="db-chip-row">
                <div class="db-chip"><i class="fa-solid fa-plug-circle-check"></i> {{ database.connection?.toUpperCase() }}</div>
                <div class="db-chip"><i class="fa-solid fa-server"></i> {{ database.driver?.toUpperCase() }}</div>
                <div class="db-chip"><i class="fa-solid fa-database"></i> {{ database.database_name }}</div>
            </div>
        </div>
        <div class="db-hero-side">
            <div class="db-side-card">
                <div class="eyebrow">Status Sistem</div>
                <div class="headline">Pastikan tidak ada user lain saat melakukan restore.</div>
                <div class="db-side-stats">
                    <div class="db-side-stat">
                        <div class="label">Penyimpanan</div>
                        <div class="value">{{ database.storage_disk?.toUpperCase() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-header" style="margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span>/</span> Management <span>/</span> <span style="color:var(--text);">Backup</span>
            </div>
            <div class="page-title" style="margin-bottom: 4px;">Backup & Restore</div>
            <div class="page-sub">Kelola cadangan sistem, riwayat pencadangan otomatis, dan pemulihan secara aman.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <button 
                type="button" 
                @click="vm.exportBackup('Manual backup oleh pengguna')"
                class="btn btn-outline" 
                :disabled="vm.isExporting"
                data-testid="btn-manual-backup"
            >
                <i class="fa-solid fa-database" style="color:var(--primary);"></i> 
                {{ vm.isExporting ? 'Mencadangkan...' : 'Ciptakan Cadangan' }}
            </button>
            <button 
                v-if="vm.canManageBackups" 
                type="button" 
                @click="vm.openRestore" 
                class="btn btn-primary"
                data-testid="btn-open-restore"
            >
                <i class="fa-solid fa-rotate-left"></i> Restore Cadangan
            </button>
        </div>
    </div>

    <!-- Filters -->
    <BackupFiltersComp />

    <!-- Table Section -->
    <div style="margin-top: 32px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding: 0 4px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text);">Riwayat Pencadangan</h3>
            </div>
            <div class="badge badge-secondary">{{ vm.meta?.total || 0 }} total records</div>
        </div>
        
        <BackupTable :backups="vm.backups" />
        
        <div v-if="vm.meta?.total > 0" class="table-footer">
            <div class="footer-stats">
                Menampilkan {{ vm.meta?.from || 0 }} - {{ vm.meta?.to || 0 }} dari {{ vm.meta?.total || 0 }} data
            </div>
            <Pagination :links="vm.meta?.links" />
        </div>
    </div>

    <!-- Restore Modal -->
    <BackupForm v-if="vm.showRestoreForm" />
</template>

<style scoped>
.db-hero {
    position: relative;
    display:grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(280px, .9fr);
    gap:18px;
    padding:24px;
    border-radius:24px;
    background:
        radial-gradient(circle at top right, rgba(99, 102, 241, .14), transparent 32%),
        linear-gradient(135deg, #ffffff 0%, #f8fbff 50%, #f2f7ff 100%);
    border:1px solid rgba(148, 163, 184, .18);
    box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
    overflow:hidden;
}
.db-hero-main { position:relative; z-index:1; display:grid; gap:14px; }
.db-kicker {
    display:inline-flex;
    align-items:center;
    gap:8px;
    width:max-content;
    padding:8px 12px;
    border-radius:999px;
    background:rgba(99, 102, 241, .08);
    color:var(--primary);
    font-size:12px;
    font-weight:800;
    letter-spacing:.3px;
    text-transform:uppercase;
}
.db-title {
    font-size:24px;
    line-height:1.1;
    letter-spacing:-.6px;
    color:#0f172a;
    font-weight:900;
}
.db-description {
    max-width:760px;
    font-size:13.5px;
    line-height:1.8;
    color:#475569;
}
.db-chip-row {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}
.db-chip {
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border-radius:999px;
    background:#fff;
    border:1px solid rgba(148, 163, 184, .2);
    box-shadow:0 6px 16px rgba(15, 23, 42, .04);
    font-size:12px;
    font-weight:700;
    color:#334155;
}
.db-chip i { color:var(--primary); }

.db-hero-side {
    position:relative;
    z-index:1;
    display:grid;
    gap:12px;
    align-content:start;
}
.db-side-card {
    background:#0f172a;
    color:#fff;
    border-radius:20px;
    padding:18px;
    box-shadow:0 18px 36px rgba(15, 23, 42, .18);
    display:grid;
    gap:14px;
}
.db-side-card .eyebrow {
    font-size:11px;
    font-weight:800;
    letter-spacing:.6px;
    text-transform:uppercase;
    color:#93c5fd;
}
.db-side-card .headline {
    font-size:15px;
    line-height:1.5;
    font-weight:800;
}
.db-side-stats {
    display:grid;
    grid-template-columns: 1fr;
    gap:12px;
}
.db-side-stat {
    padding:14px 16px;
    border-radius:16px;
    background:#fff;
    border:1px solid rgba(148, 163, 184, .18);
    box-shadow:0 10px 24px rgba(15, 23, 42, .05);
    display:grid;
    gap:4px;
}
.db-side-stat .label {
    font-size:11px;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:.4px;
    font-weight:800;
}
.db-side-stat .value {
    margin-top:0;
    font-size:18px;
    font-weight:900;
    color:#0f172a;
}

.table-footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-stats {
    font-size: 13px;
    font-weight: 700;
    color: var(--muted);
}

.alert-banner {
    margin-bottom: 20px;
    padding: 14px 16px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 700;
}

.alert-success {
    background: #ecfdf5;
    color: #166534;
    border: 1px solid #86efac;
}

.alert-error {
    background: #fff1f2;
    color: #be123c;
    border: 1px solid #fecdd3;
}
</style>

