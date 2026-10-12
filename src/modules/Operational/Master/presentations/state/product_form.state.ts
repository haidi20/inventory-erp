export type ProductFormTab = 'detail' | 'account' | 'saldo' | 'harga' | 'diskon';

export interface ProductFormTabOption {
    id: ProductFormTab;
    label: string;
}

export const PRODUCT_FORM_DEFAULT_TAB: ProductFormTab = 'detail';

export const PRODUCT_FORM_TABS: readonly ProductFormTabOption[] = [
    { id: 'detail', label: 'Detail' },
    { id: 'account', label: 'Akun' },
    { id: 'saldo', label: 'Saldo Awal Program' },
    { id: 'harga', label: 'Harga' },
    { id: 'diskon', label: 'Diskon' },
];
