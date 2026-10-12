<script setup lang="ts">
import { CHART_OF_ACCOUNTS_COLUMNS, createChartOfAccountsUIState } from '@modules/Finance/Journal/presentations/viewmodels/chart_of_accounts.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { useChartOfAccountsVM } from '@modules/Finance/Journal/presentations/viewmodels/chart_of_accounts.vm';
import ChartOfAccountsForm from '@modules/Finance/Journal/presentations/views/Partials/ChartOfAccountsForm.vue';

const columns = CHART_OF_ACCOUNTS_COLUMNS;
const vm = useChartOfAccountsVM();
const { datatableRef } = createChartOfAccountsUIState();

onMounted(() => {
    vm.registerReload(() => datatableRef.value?.reload());
});
</script>

<template>
    <Head title="Chart of Accounts - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Chart of Accounts</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Chart of Accounts</div>
            <div class="page-sub">Bagan akun lengkap perusahaan.</div>
        </div>
        <button
            v-if="vm.canManageAccounts"
            type="button"
            class="btn btn-primary"
            @click="vm.openCreate"
            data-testid="btn-create-account"
        >
            <i class="fa-solid fa-plus-circle"></i> Tambah Akun
        </button>
    </div>

    <DataTable
        ref="datatableRef"
        store-id="chartofaccounts-datatable"
        url="/accounts"
        :columns="columns"
        :default-per-page="10"
        search-placeholder=""
    >
        <template #cell-is_active="{ row }">
            <span :data-testid="`coa-status-${row.code}`">{{ row.is_active ? 'Aktif' : 'Nonaktif' }}</span>
        </template>
        <template v-if="vm.canManageAccounts" #cell-actions="{ row }">
            <div class="account-actions">
                <button
                    type="button"
                    class="btn btn-sm btn-outline"
                    :disabled="vm.deletingUuid === row.uuid"
                    @click="vm.openEdit(row)"
                    title="Edit"
                    :data-testid="`btn-edit-account-${row.code}`"
                >
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline btn-danger-soft"
                    :disabled="vm.deletingUuid === row.uuid"
                    @click="vm.deleteAccount(row)"
                    title="Hapus"
                    :data-testid="`btn-delete-account-${row.code}`"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>

    <ChartOfAccountsForm v-if="vm.showForm" />
</template>
