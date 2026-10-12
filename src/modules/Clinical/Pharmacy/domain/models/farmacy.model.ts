export interface FarmacyRoleItem {
    uuid: string;
    name: string;
    label: string;
}

export interface FarmacyModel {
    uuid: string;
    name: string;
    email: string;
    role_uuid: string;
    role_name: string;
    role_label: string;
    role_uuids: string[];
    roles: FarmacyRoleItem[];
    employee_uuid?: string;
    employee_name?: string;
    created_at_label: string;
    avatar_initial: string;
    avatar_color: string;
    is_current_farmacy: boolean;
}

export interface FarmacyFormData {
    uuid?: string;
    name: string;
    email: string;
    password?: string;
    role_uuids: string[];
    employee_uuid?: string;
}

export interface FarmacyFilters {
    search?: string;
    search_column?: string;
    role?: string;
    sort_by?: 'name' | 'email';
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface FarmacySummary {
    total: number;
    dokter: number;
    other: number;
}

export interface FarmacyPaginationMeta {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
    links: FarmacyPaginationLink[];
}

export interface FarmacyPaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface FarmacyPaginatedResult {
    data: FarmacyModel[];
    meta: FarmacyPaginationMeta;
    summary: FarmacySummary;
}

export interface FarmacyMutationResult {
    message: string;
    data?: FarmacyModel;
}

export interface FarmacyImportResult {
    message: string;
    imported: number;
    skipped: number;
}

// --- Specific Farmacy Models ---

export enum PelayananStatus {
    WAITING = 'waiting',
    PREPARING = 'preparing',
    CHECKING = 'checking',
    HANDOVER = 'handover',
    COMPLETED = 'completed'
}

export interface DashboardSummary {
    income: number;
    expense: number;
    profit: number;
    transactionsToday: number;
    expiringSoon: number;
    lowStock: number;
}

export interface RecipeModel {
    uuid: string;
    recipe_no: string;
    patient_name: string;
    service_type: string;
    doctor_name: string;
    status: 'pending' | 'completed' | 'cancelled';
    recipe_type: string;
    created_at: string;
}

export interface RecipeFilters {
    status: string;
    date: string;
    search: string;
}

export interface FarmacyRecipeOptions {
    pharmacists: { id: string, name: string }[];
    warehouses: { id: string, name: string }[];
    businessUnits: string[];
}

export interface MedicationItem {
    uuid: string;
    name: string;
    batch: string;
    qty: string;
    price: number;
    subtotal: number;
    usage: string;
    note: string;
}

export interface AssessmentItem {
    field: string;
    result: 'ya' | 'tidak' | null;
    note: string;
}

export interface PrescriptionAssessment {
    administrative: AssessmentItem[];
    pharmaceutical: AssessmentItem[];
    clinical: AssessmentItem[];
    five_rights: AssessmentItem[];
}

export interface PatientProfile {
    recipe_no: string;
    name: string;
    birth_date: string;
    age: string;
    gender: string;
    nik: string;
    phone: string;
    admission_type: string;
    company: string;
    classification: string;
    pharmacist: string;
    unit: string;
    doctor: string;
    date: string;
    notes: string;
    pelayanan_status: PelayananStatus;
}

export interface FarmacyTransactionModel {
    uuid: string;
    invoice_no: string;
    customer_name: string;
    total_amount: number;
    payment_status: 'paid' | 'unpaid' | 'partial';
    date: string;
}

export type FarmacyMenuTone = 'blue' | 'green' | 'orange' | 'purple' | 'red' | 'yellow';

export interface FarmacyModuleMenuItem {
    title: string;
    description: string;
    icon: string;
    tone: FarmacyMenuTone;
    href?: string;
    badge?: string;
}

export interface FarmacyStatusCard {
    title: string;
    description: string;
    value: string;
    amount: string;
    icon: string;
    tone: FarmacyMenuTone;
}

export interface FarmacyRecentActivity {
    uuid: string;
    icon: string;
    reference: string;
    type: string;
    amount: string;
    time: string;
    status: string;
}

export interface FarmacyRecentActivitySource {
    uuid: string;
    icon: string;
    reference: string;
    type: string;
    amount: number;
    time: string;
    status: string;
}

export interface FarmacyTransactionDefaults {
    receivableCount: number;
    receivableAmount: number;
    payableCount: number;
    payableAmount: number;
    newOrderCount: number;
    lowStockCount: number;
}

export interface FarmacyTransactionHubData {
    salesMenus: FarmacyModuleMenuItem[];
    purchaseMenus: FarmacyModuleMenuItem[];
    stats: FarmacyTransactionDefaults;
    activities: FarmacyRecentActivity[];
}

export interface FarmacyStockMenuAction {
    label: string;
    icon: string;
}

export type FarmacyStockMenuRoute =
    | 'adjustments'
    | 'opname'
    | 'mutations'
    | 'opening-balance'
    | 'service-deposits'
    | 'residue-returns';

export interface FarmacyStockMenuItem {
    title: string;
    description: string;
    icon: string;
    tone: FarmacyMenuTone;
    href: string;
    routeKey: FarmacyStockMenuRoute;
    actions: FarmacyStockMenuAction[];
    badge?: string;
}

export interface FarmacyStockSummaryCard {
    title: string;
    value: string;
    description: string;
    icon: string;
    tone: FarmacyMenuTone;
}

export interface FarmacyStockActivity {
    uuid: string;
    icon: string;
    type: string;
    item: string;
    change: string;
    time: string;
    tone: FarmacyMenuTone;
}

export interface FarmacyInventoryHubData {
    menus: FarmacyStockMenuItem[];
    activities: FarmacyStockActivity[];
}

export type FarmacyStockAdjustmentStatus = 'Diposting' | 'Draft' | 'Dibatalkan';

export interface FarmacyStockAdjustmentRow {
    uuid: string;
    date: string;
    time: string;
    referenceNo: string;
    businessUnit: string;
    description: string;
    status: FarmacyStockAdjustmentStatus;
}

export type FarmacyStockAdjustmentPageRoute = 'list' | 'form';

export interface FarmacyStockAdjustmentOption {
    value: string;
    label: string;
}

export interface FarmacyStockAdjustmentBatchOption extends FarmacyStockAdjustmentOption {
    productId: string;
    stock: number;
}

export interface FarmacyStockAdjustmentFormItem {
    uuid: string;
    productId: string;
    batchId: string;
    unitPrice: number;
    quantity: number;
}

export interface FarmacyStockAdjustmentFormState {
    businessUnit: string;
    invoiceNo: string;
    warehouseId: string;
    transactionDate: string;
    description: string;
    items: FarmacyStockAdjustmentFormItem[];
}

export interface FarmacyStockAdjustmentOptions {
    businessUnits: FarmacyStockAdjustmentOption[];
    warehouses: FarmacyStockAdjustmentOption[];
    products: FarmacyStockAdjustmentOption[];
    batches: FarmacyStockAdjustmentBatchOption[];
}

export type FarmacyStockOpnameStatus = 'Diposting' | 'Draft' | 'Dibatalkan';

export interface FarmacyStockOpnameRow {
    uuid: string;
    date: string;
    referenceNo: string;
    businessUnit: string;
    warehouse: string;
    description: string;
    status: FarmacyStockOpnameStatus;
}

export interface FarmacyStockOpnameFormItem {
    uuid: string;
    productId: string;
    batchId: string;
    bookStock: number;
    physicalStock: number;
}

export interface FarmacyStockOpnameFormState {
    businessUnit: string;
    warehouseId: string;
    transactionDate: string;
    referenceNo: string;
    description: string;
    items: FarmacyStockOpnameFormItem[];
}

export type FarmacyStockMutationStatus = 'Diterima' | 'Draft' | 'Dibatalkan';

export interface FarmacyStockMutationRow {
    uuid: string;
    date: string;
    referenceNo: string;
    receiptNo: string;
    description: string;
    sourceWarehouse: string;
    destinationWarehouse: string;
    status: FarmacyStockMutationStatus;
    receivedBy: string;
}

export interface FarmacyStockMutationFormItem {
    uuid: string;
    productId: string;
    batchId: string;
    stock: number;
    quantity: number;
}

export interface FarmacyStockMutationFormState {
    businessUnit: string;
    sourceWarehouseId: string;
    destinationWarehouseId: string;
    transactionDate: string;
    referenceNo: string;
    description: string;
    items: FarmacyStockMutationFormItem[];
}

export interface FarmacyOpeningBalanceRow {
    uuid: string;
    productName: string;
    productCode: string;
    businessUnit: string;
    warehouse: string;
    totalStock: number;
    totalAsset: number;
    date: string;
}

export interface FarmacyOpeningBalanceFormState {
    businessUnit: string;
    productId: string;
    warehouseId: string;
    expiryDate: string;
    batchNumber: string;
    purchasePrice: number;
    totalStock: number;
}

export interface FarmacyOpeningBalanceOptions {
    businessUnits: FarmacyStockAdjustmentOption[];
    warehouses: FarmacyStockAdjustmentOption[];
    products: FarmacyStockAdjustmentOption[];
}

export interface InventoryModel {
    uuid: string;
    product_code: string;
    product_name: string;
    category: string;
    stock_qty: number;
    unit: string;
    min_stock: number;
    expiry_date: string;
}

export interface AccountingAccountModel {
    uuid: string;
    code: string;
    name: string;
    group: string;
    balance: number;
}

export interface ManagementContactModel {
    uuid: string;
    name: string;
    type: 'supplier' | 'customer';
    phone: string;
    address: string;
}

export interface ReportItem {
    name: string;
    description: string;
}
