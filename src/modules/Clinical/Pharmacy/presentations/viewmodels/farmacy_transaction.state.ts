// farmacy_transaction.state.ts — state types untuk FarmacyTransactionVM (pharmacy transaction hub)
export interface FarmacyTransactionItem {
    uuid: string;
    invoice_no: string;
    total_amount: number;
    payment_status: 'paid' | 'partial' | 'unpaid';
    created_at?: string;
}

export interface FarmacyTransactionState {
    isLoading: boolean;
}

export interface FarmacyStatusCard {
    title: string;
    description: string;
    value: string;
    amount: string;
    icon: string;
    tone: 'red' | 'orange' | 'blue' | 'yellow' | 'green';
}
