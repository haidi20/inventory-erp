import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type {
    FarmacyModuleMenuItem,
    FarmacyRecentActivity,
    FarmacyStatusCard,
    FarmacyTransactionDefaults,
    FarmacyTransactionHubData,
    FarmacyTransactionModel,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const formatIDR = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

export const useFarmacyTransactionVM = defineStore('farmacy-transaction-vm', () => {
    const { getTransactionHubDataUseCase, getTransactionsUseCase } = farmacyDependencies;

    const transactions = ref<FarmacyTransactionModel[]>([]);
    const transactionHubData = ref<FarmacyTransactionHubData | null>(null);
    const isLoading = ref(false);

    const salesMenus = computed<FarmacyModuleMenuItem[]>(() => transactionHubData.value?.salesMenus ?? []);
    const purchaseMenus = computed<FarmacyModuleMenuItem[]>(() => transactionHubData.value?.purchaseMenus ?? []);
    const transactionStats = computed<FarmacyTransactionDefaults | null>(() => transactionHubData.value?.stats ?? null);

    const unpaidTransactions = computed(() => transactions.value.filter((transaction) => transaction.payment_status !== 'paid'));
    const totalReceivable = computed(() => unpaidTransactions.value.reduce((sum, transaction) => sum + transaction.total_amount, 0));
    const newOrderCount = computed(() => Math.max(transactionStats.value?.newOrderCount ?? 0, Math.min(9, transactions.value.length)));
    const lowStockCount = computed(() => transactionStats.value?.lowStockCount ?? 0);

    const statusCards = computed<FarmacyStatusCard[]>(() => [
        {
            title: 'Piutang Jatuh Tempo',
            description: `${unpaidTransactions.value.length || transactionStats.value?.receivableCount || 0} faktur perlu follow-up`,
            value: `${unpaidTransactions.value.length || transactionStats.value?.receivableCount || 0} faktur`,
            amount: formatIDR(totalReceivable.value || transactionStats.value?.receivableAmount || 0),
            icon: 'fa-solid fa-hand-holding-dollar',
            tone: 'red',
        },
        {
            title: 'Hutang Jatuh Tempo',
            description: 'Tagihan supplier menunggu pembayaran',
            value: `${transactionStats.value?.payableCount ?? 0} faktur`,
            amount: formatIDR(transactionStats.value?.payableAmount ?? 0),
            icon: 'fa-solid fa-file-circle-exclamation',
            tone: 'orange',
        },
        {
            title: 'Pesanan Baru',
            description: 'Pesanan masuk perlu diproses',
            value: `${newOrderCount.value} pesanan`,
            amount: 'Perlu diproses',
            icon: 'fa-solid fa-clipboard-list',
            tone: 'blue',
        },
        {
            title: 'Stok Menipis',
            description: 'Item berada di bawah batas minimum',
            value: `${lowStockCount.value} item`,
            amount: 'Perlu order',
            icon: 'fa-solid fa-triangle-exclamation',
            tone: 'yellow',
        },
    ]);

    const recentActivities = computed<FarmacyRecentActivity[]>(() => {
        const transactionActivities = transactions.value.slice(0, 5).map<FarmacyRecentActivity>((transaction, index) => ({
            uuid: transaction.uuid,
            icon: index % 2 === 0 ? 'fa-solid fa-receipt' : 'fa-solid fa-credit-card',
            reference: transaction.invoice_no,
            type: index % 2 === 0 ? 'Penjualan' : 'Piutang',
            amount: formatIDR(transaction.total_amount),
            time: index === 0 ? '2 jam lalu' : index === 1 ? '5 jam lalu' : 'Kemarin',
            status: transaction.payment_status === 'paid' ? 'Selesai' : transaction.payment_status === 'partial' ? 'Sebagian' : 'Pending',
        }));

        return transactionActivities.length ? transactionActivities : transactionHubData.value?.activities ?? [];
    });

    const fetchTransactionHubData = async () => {
        const result = await getTransactionHubDataUseCase.execute();
        result.fold(() => {}, (data) => { transactionHubData.value = data; });
    };

    const fetch = async () => {
        isLoading.value = true;
        await fetchTransactionHubData();
        const transactionsResult = await getTransactionsUseCase.execute();
        transactionsResult.fold(() => {}, (data) => { transactions.value = data; });
        isLoading.value = false;
    };

    return {
        transactions,
        transactionHubData,
        transactionStats,
        isLoading,
        salesMenus,
        purchaseMenus,
        statusCards,
        recentActivities,
        fetchTransactionHubData,
        fetch,
    };
});
