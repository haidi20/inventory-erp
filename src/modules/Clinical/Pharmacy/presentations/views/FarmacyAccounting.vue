<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyAccountingVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_accounting.vm';

const vm = useFarmacyAccountingVM();

onMounted(() => {
    vm.fetch();
});

</script>

<template>
    <Head title="Akuntansi Farmasi - Inventory" />

    <div class="page-header" style="margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <Link href="/" class="breadcrumb-link">
                    <i class="fa-solid fa-house"></i>
                </Link>
                <span>/</span> Operationals <span>/</span> <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmacy</Link> <span>/</span> Akuntansi
            </div>
            <div class="page-title" style="margin-bottom: 4px;">Akuntansi Farmasi</div>
            <div class="page-sub">Manajemen akun, penjurnalan otomatis, dan neraca saldo apotek.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <button type="button" class="btn btn-outline">
                <i class="fa-solid fa-book"></i> Jurnal Umum
            </button>
            <button type="button" class="btn btn-primary">
                <i class="fa-solid fa-plus-circle"></i> Tambah Akun
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 15px; font-weight: 800; color: var(--text);">Daftar Akun (COA)</h3>
        </div>
        <div class="table-wrap">
            <Transition name="fade">
                <div v-if="vm.isLoading" class="loading-overlay">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
            </Transition>
            <table>
                <thead>
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Grup Akun</th>
                        <th style="text-align: right;">Saldo Saat Ini</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="acc in vm.accounts" :key="acc.uuid">
                        <td class="font-bold">{{ acc.code }}</td>
                        <td>{{ acc.name }}</td>
                        <td><span class="badge badge-secondary">{{ acc.group }}</span></td>
                        <td style="text-align: right;">{{ vm.formatIDR(acc.balance) }}</td>
                        <td>
                            <div class="table-action-cell" style="justify-content: center;">
                                <button class="btn btn-outline btn-sm" title="Buku Besar">
                                    <i class="fa-solid fa-table-list"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.card { background: #fff; border-radius: 16px; border: 1px solid var(--border); position: relative; }
.font-bold { font-weight: 700; color: var(--text); }
.loading-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.7); display: grid; place-items: center; z-index: 10; font-size: 24px; color: var(--primary); }
</style>

