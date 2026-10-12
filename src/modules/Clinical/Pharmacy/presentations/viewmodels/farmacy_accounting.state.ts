// farmacy_accounting.state.ts — state types untuk FarmacyAccountingVM
export interface AccountingAccountModel {
    uuid: string;
    code: string;
    name: string;
    type: string;
    balance?: number;
}

export interface FarmacyAccountingState {
    isLoading: boolean;
}
