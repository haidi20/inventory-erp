// farmacy_inventory.state.ts — state types untuk FarmacyInventoryVM
export type FarmacyStockMenuRoute =
    | 'adjustments'
    | 'opname'
    | 'mutations'
    | 'opening-balance'
    | 'service-deposits'
    | 'residue-returns';

export interface FarmacyInventoryState {
    isLoading: boolean;
    activeMenuRoute: FarmacyStockMenuRoute | null;
}
