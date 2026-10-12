import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import * as productDI from '@modules/Operational/Master/domain/di/product.di';
import type { ProductVMDependencies } from '@modules/Operational/Master/domain/di/product.di';
import type { ProductFilterOptions, ProductFilters, ProductFormData, ProductModel, ProductMeta, ProductSummary, ProductUnitRow, ProductOpeningBalanceRow, ProductPriceRow, ProductDiscountRow, ProductAccountRow } from './product.state';
import type { ProductFormOptions } from './product.state';
import { PRODUCT_DEFAULT_FILTERS, PRODUCT_PER_PAGE_OPTIONS, PRODUCT_SEARCH_COLUMNS, PRODUCT_MAX_IMAGE_ENTRIES, PRODUCT_MAX_FILE_SIZE, PRODUCT_ALLOWED_FILE_TYPES, PRODUCT_ALLOWED_FILE_EXTENSIONS, PRODUCT_TABLE_COLUMNS, PRODUCT_ACCOUNT_MAPPINGS } from './product.state';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import { validateProductForm } from './product_form.schema';
import apiService from '@modules/Core/shared/core/api_service';

function debounce(fn: Function, delay: number) {
    let timeoutId: ReturnType<typeof setTimeout> | undefined;
    return function (...args: any[]) {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(null, args), delay);
    };
}

export function isPdfFile(path: string): boolean {
    return path.toLowerCase().endsWith('.pdf');
}

interface ProductImageEntry {
    path?: string;
    file?: File;
    preview: string;
}

export function productUrlFilters(filters: ProductFilters): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value !== undefined && value !== null && value !== '')
            .map(([key, value]) => [key, String(value)]),
    );
}

function replaceUrlFilters(filters: ProductFilters): void {
    const query = productUrlFilters(filters);
    const url = new URL(window.location.href);
    url.search = new URLSearchParams(query).toString();
    window.history.replaceState({}, '', url.toString());
}

export function createProductVMStore(
    storeId = 'product-vm',
    dependencies: ProductVMDependencies = productDI,
) {
    return defineStore(storeId, () => {
        const { fetchProductsUseCase, fetchProductFilterOptionsUseCase, saveProductUseCase, deleteProductUseCase, exportProductsUseCase } = dependencies;

        const products    = ref<ProductModel[]>([]);
        const meta        = ref<ProductMeta>({} as ProductMeta);
        const summary     = ref<ProductSummary>({ total: 0 });
        const isLoading   = ref(false);
        const isExporting = ref(false);
        const isImporting = ref(false);
        const showForm    = ref(false);
        const editingItem = ref<ProductModel | null>(null);

        const showFilterModal = ref(false);
        const filterOptions   = ref<ProductFilterOptions>({ units: [], kinds: [], groups: [], types: [], brands: [], origins: [], grades: [], users: [] });
        const draftFilters    = ref<ProductFilters>({});
        const appliedFilters  = ref<ProductFilters>({});

        const search        = ref(PRODUCT_DEFAULT_FILTERS.search);
        const searchColumn  = ref(PRODUCT_DEFAULT_FILTERS.search_column);
        const sortBy        = ref(PRODUCT_DEFAULT_FILTERS.sort_by);
        const sortDirection = ref<'asc' | 'desc'>(PRODUCT_DEFAULT_FILTERS.sort_direction);
        const perPage       = ref(PRODUCT_DEFAULT_FILTERS.per_page);
        const currentPage   = ref(PRODUCT_DEFAULT_FILTERS.page);

        const searchColumns  = PRODUCT_SEARCH_COLUMNS;
        const perPageOptions = PRODUCT_PER_PAGE_OPTIONS;

        const page = usePage<{
            auth?: { user?: { roles?: string[]; permissions?: string[] } | null };
            filters?: ProductFilters;
            companies?: { uuid: string; name: string }[];
            default_company_uuid?: string | null;
        }>();

        const canManage = computed(() => {
            const roles = page.props.auth?.user?.roles ?? [];
            if (['owner', 'superadmin', 'admin'].some(r => roles.includes(r))) return true;
            return (page.props.auth?.user?.permissions ?? []).includes('master.manage');
        });

        const isSuperadmin = computed(() => (page.props.auth?.user?.roles ?? []).includes('superadmin'));
        const companies = computed(() => page.props.companies ?? []);
        const defaultCompanyUuid = computed(() => page.props.default_company_uuid ?? '');

        const form = useForm<ProductFormData>({
            uuid: undefined,
            code: '',
            name: '',
            barcode: '',
            description: '',
            remarks: '',
            item_name_alt: '',
            item_type: 'goods',
            group_uuid: undefined,
            type_uuid: undefined,
            brand_uuid: undefined,
            origin_uuid: undefined,
            grade_uuid: undefined,
            kind: '',
            location: '',
            base_unit_uuid: undefined,
            price_buy: 0,
            price_sell: 0,
            opening_cost: 0,
            min_stock: 0,
            max_stock: 0,
            max_order: 0,
            is_active: true,
            is_assembly: false,
            coa_sales_uuid: undefined,
            coa_inventory_uuid: undefined,
            coa_sales_discount_uuid: undefined,
            coa_hpp_uuid: undefined,
            coa_sales_return_uuid: undefined,
            coa_purchase_return_uuid: undefined,
            coa_suspend_uuid: undefined,
            image: null,
            images: [],
           existing_image_paths: [],
            accounts: [],
       });

        const imageEntries = ref<ProductImageEntry[]>([]);
        const isUploadingFiles = ref(false);
        const isSubmittingProduct = ref(false);
        const isEditing = computed(() => !!editingItem.value);

        const unitRows = ref<ProductUnitRow[]>([]);
        const openingBalanceRows = ref<ProductOpeningBalanceRow[]>([]);
        const priceRows = ref<ProductPriceRow[]>([]);
        const discountRows = ref<ProductDiscountRow[]>([]);
        const accountRows = ref<ProductAccountRow[]>([]);
        const accountOptions = ref<ProductFormOptions['accounts']>([]);
        const accountDefaults = ref<Pick<ProductFormData, 'coa_sales_uuid' | 'coa_inventory_uuid' | 'coa_sales_discount_uuid' | 'coa_hpp_uuid' | 'coa_sales_return_uuid' | 'coa_purchase_return_uuid' | 'coa_suspend_uuid'>>({});
        const accountMappings = computed(() => PRODUCT_ACCOUNT_MAPPINGS.map((mapping) => ({
            ...mapping,
            row: accountRows.value.find((row) => row.account_role === mapping.role) ?? { account_role: mapping.role },
        })));

        const selectedCompanyUuid = computed<string>({
            get: () => appliedFilters.value.filter_company_uuid ?? defaultCompanyUuid.value ?? 'all',
            set: (value: string) => {
                appliedFilters.value = { ...appliedFilters.value, filter_company_uuid: value };
                void fetchProducts({ filter_company_uuid: value, page: 1 });
            },
        });

        const companyFilterOptions = computed(() => [
            { uuid: 'all', name: 'Semua Perusahaan' },
            ...companies.value,
        ]);

        const unitFilterOptions = computed(() => [
            { uuid: '', label: 'Semua' },
            ...filterOptions.value.units.map(unit => ({ uuid: unit, label: unit })),
        ]);

        const kindFilterOptions = computed(() => [
            { uuid: '', label: 'Semua' },
            ...filterOptions.value.kinds.map(kind => ({ uuid: kind, label: kind })),
        ]);

        const userFilterOptions = computed(() => [
            { uuid: '', name: 'Semua' },
            ...filterOptions.value.users,
        ]);

        const hasActiveFilters = computed(() => Object.values(appliedFilters.value).some(
            (value) => value !== undefined && value !== null && value !== '',
        ));

        const productOptionReducer = (option: { uuid: string }) => option.uuid;

        const optionEndpoint = (type: string) => `/products/options?type=${type}`;

        async function fetchProducts(filters: ProductFilters = {}) {
            const finalFilters: ProductFilters = {
                ...appliedFilters.value,
                search: search.value,
                search_column: searchColumn.value,
                sort_by: sortBy.value,
                sort_direction: sortDirection.value,
                per_page: perPage.value,
                page: currentPage.value,
                ...filters,
            };

            if (typeof finalFilters.page === 'number') currentPage.value = finalFilters.page;
            replaceUrlFilters(finalFilters);

            isLoading.value = true;
            const result = await fetchProductsUseCase.execute(finalFilters);
            result.fold(
                (failure: any) => { isLoading.value = false; console.error(failure.message); },
                (data: any) => {
                    products.value = data.data ?? [];
                    meta.value = { ...(data.meta ?? data), links: data.links ?? data.meta?.links ?? [] };
                    summary.value = data.summary ?? { total: data.total ?? data.meta?.total ?? 0 };
                    isLoading.value = false;
                },
            );
        }

        const updateFilters = debounce(() => { currentPage.value = 1; fetchProducts({ page: 1 }); }, 300);
        watch([search, searchColumn, perPage], () => updateFilters());

        async function deleteProduct(product: ProductModel) {
            if (!canManage.value) return;
            const confirmResult = await Swal.fire({
                title: 'Hapus Produk?',
                text: `"${product.name}" tidak bisa dipulihkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ef4444',
            });
            if (!confirmResult.isConfirmed) return;

            const result = await deleteProductUseCase.execute(product.uuid);
            result.fold(
                (failure: any) => { void Swal.fire({ title: 'Gagal', text: failure.message, icon: 'error' }); },
                () => {
                    Swal.fire({ title: 'Dihapus!', text: 'Produk berhasil dihapus.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
                    fetchProducts();
                },
            );
        }

        function openImportPicker(): void {
            document.getElementById('product-import-file')?.click();
        }

        async function downloadImportTemplate(): Promise<void> {
            const response = await apiService.get('/products/import/template', { responseType: 'blob' });
            const url = window.URL.createObjectURL(response.data);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'products-import-template.csv');
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        }

        async function handleImportInput(event: Event): Promise<void> {
            const input = event.target as HTMLInputElement;
            const file = input.files?.[0];
            input.value = '';
            if (!file) return;
            isImporting.value = true;
            try {
                const payload = new FormData();
                payload.append('file', file);
                const response = await apiService.post('/products/import', payload, { headers: { 'Content-Type': 'multipart/form-data' } });
                const result = response.data;
                await Swal.fire({ title: 'Import selesai', text: `Berhasil menambah ${result.imported ?? 0} dan memperbarui ${result.updated ?? 0} produk.`, icon: result.errors?.length ? 'warning' : 'success' });
                fetchProducts();
            } catch (error: any) {
                await Swal.fire({ title: 'Gagal import', text: error.response?.data?.message ?? error.message, icon: 'error' });
            } finally {
                isImporting.value = false;
            }
        }
        async function exportProducts() {
            isExporting.value = true;
            const result = await exportProductsUseCase.execute({
                ...appliedFilters.value,
                search: search.value,
                search_column: searchColumn.value,
            });
            result.fold(
                (failure: any) => { void Swal.fire({ title: 'Gagal ekspor', text: failure.message, icon: 'error' }); },
                () => {},
            );
            isExporting.value = false;
        }

        async function fetchFilterOptions() {
            const result = await fetchProductFilterOptionsUseCase.execute();
            result.fold(
                (failure: any) => { console.error(failure.message); },
                (options: ProductFilterOptions) => { filterOptions.value = options; },
            );
        }

        function openFilterModal() {
            draftFilters.value = { ...appliedFilters.value };
            showFilterModal.value = true;
            fetchFilterOptions();
        }

        function closeFilterModal() {
            showFilterModal.value = false;
        }

        function applyFilters() {
            appliedFilters.value = { ...draftFilters.value };
            showFilterModal.value = false;
            currentPage.value = 1;
            void fetchProducts({ page: 1 });
        }

        function resetFilters() {
            draftFilters.value = {};
            appliedFilters.value = draftFilters.value;
            currentPage.value = 1;
            void fetchProducts({ page: 1 });
        }

        function openCreate() {
            if (!canManage.value) return;
            editingItem.value = null;
            form.defaults({ uuid: undefined, code: '', name: '', barcode: '', description: '', remarks: '', item_name_alt: '', item_type: 'goods', group_uuid: undefined, type_uuid: undefined, brand_uuid: undefined, origin_uuid: undefined, grade_uuid: undefined, kind: '', location: '', base_unit_uuid: undefined, price_buy: 0, price_sell: 0, opening_cost: 0, min_stock: 0, max_stock: 0, max_order: 0, is_active: true, is_assembly: false, coa_sales_uuid: undefined, coa_inventory_uuid: undefined, coa_sales_discount_uuid: undefined, coa_hpp_uuid: undefined, coa_sales_return_uuid: undefined, coa_purchase_return_uuid: undefined, coa_suspend_uuid: undefined, image: null, images: [], existing_image_paths: [], units: [], opening_balances: [], prices: [], discounts: [], accounts: [] });
            form.reset();
            form.clearErrors();
            unitRows.value = [];
            openingBalanceRows.value = [];
            priceRows.value = [];
            discountRows.value = [];
            accountRows.value = prepareAccountRows();
            form.accounts = accountRows.value.filter((row) => row.account_uuid);
            showForm.value = true;
        }

        function openEdit(product: ProductModel) {
            if (!canManage.value) return;
            editingItem.value = { ...product };
            form.defaults({
                uuid: product.uuid,
                code: product.code,
                name: product.name,
                barcode: product.barcode ?? '',
                description: product.description ?? '',
                remarks: product.remarks ?? '',
                item_name_alt: product.item_name_alt ?? '',
                item_type: product.item_type ?? 'goods',
                group_uuid: product.group_uuid ?? undefined,
                type_uuid: product.type_uuid ?? undefined,
                brand_uuid: product.brand_uuid ?? undefined,
                origin_uuid: product.origin_uuid ?? undefined,
                grade_uuid: product.grade_uuid ?? undefined,
                kind: product.kind ?? '',
                location: product.location ?? '',
                base_unit_uuid: product.base_unit_uuid ?? undefined,
                price_buy: product.price_buy ?? 0,
                price_sell: product.price_sell ?? 0,
                opening_cost: product.opening_cost ?? 0,
                min_stock: product.min_stock ?? 0,
                max_stock: product.max_stock ?? 0,
                max_order: product.max_order ?? 0,
                is_active: product.is_active,
                is_assembly: product.is_assembly ?? false,
                coa_sales_uuid: product.coa_sales_uuid ?? undefined,
                coa_inventory_uuid: product.coa_inventory_uuid ?? undefined,
                coa_sales_discount_uuid: product.coa_sales_discount_uuid ?? undefined,
                coa_hpp_uuid: product.coa_hpp_uuid ?? undefined,
                coa_sales_return_uuid: product.coa_sales_return_uuid ?? undefined,
                coa_purchase_return_uuid: product.coa_purchase_return_uuid ?? undefined,
                coa_suspend_uuid: product.coa_suspend_uuid ?? undefined,
                image: null,
                images: [],
                existing_image_paths: [],
                units: product.units ?? [],
                opening_balances: product.opening_balances ?? [],
                prices: product.prices ?? [],
                discounts: product.discounts ?? [],
                accounts: product.accounts ?? [],
            });
            form.reset();
            form.clearErrors();
            unitRows.value = [...(product.units ?? [])];
            openingBalanceRows.value = [...(product.opening_balances ?? [])];
            priceRows.value = [...(product.prices ?? [])];
            discountRows.value = [...(product.discounts ?? [])];
            accountRows.value = prepareAccountRows(product.accounts ?? []);
            form.accounts = accountRows.value.filter((row) => row.account_uuid);
            showForm.value = true;
        }

        function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); form.clearErrors(); unitRows.value = accountRows.value = []; openingBalanceRows.value = []; priceRows.value = []; discountRows.value = []; }

        function syncTabRows(): void {
            form.units = unitRows.value;
            form.opening_balances = openingBalanceRows.value;
            form.prices = priceRows.value;
            form.discounts = discountRows.value;
            form.accounts = accountRows.value.filter((row) => row.account_uuid);
        }

        function prepareAccountRows(accounts: ProductAccountRow[] = []): ProductAccountRow[] {
            return PRODUCT_ACCOUNT_MAPPINGS.map((mapping) => ({
                ...(accounts.find((account) => account.account_role === mapping.role) ?? {}),
                account_role: mapping.role,
                account_uuid: accounts.find((account) => account.account_role === mapping.role)?.account_uuid,
            }));
        }

        function setAccountByRole(role: string, accountUuid: string | null): void {
            const row = accountRows.value.find((account) => account.account_role === role);
            if (!row) return;
            const option = accountOptions.value?.find((item) => item.uuid === accountUuid);
            row.account_uuid = accountUuid ?? undefined;
            row.account_code = option?.code;
            row.account_name = option?.name;
            syncTabRows();
        }

        function accountLabel(accountUuid?: string): string {
            const option = accountOptions.value?.find((item) => item.uuid === accountUuid);
            return option ? `${option.code} - ${option.name}` : '-';
        }


        function setDefaultAccounts(): void {
            const findAccount = (role: string) => accountRows.value.find((row) => row.account_role === role)?.account_uuid;
            accountDefaults.value = {
                coa_sales_uuid: findAccount('sales'),
                coa_inventory_uuid: findAccount('inventory'),
                coa_sales_discount_uuid: findAccount('sales_discount'),
                coa_hpp_uuid: findAccount('hpp'),
                coa_sales_return_uuid: findAccount('sales_return'),
                coa_purchase_return_uuid: findAccount('purchase_return'),
                coa_suspend_uuid: findAccount('suspend'),
            };
        }

        function syncAccounts(): void {
            Object.assign(form, accountDefaults.value);
            setAccountByRole('sales', form.coa_sales_uuid ?? null);
            setAccountByRole('inventory', form.coa_inventory_uuid ?? null);
            setAccountByRole('sales_discount', form.coa_sales_discount_uuid ?? null);
            setAccountByRole('hpp', form.coa_hpp_uuid ?? null);
            setAccountByRole('sales_return', form.coa_sales_return_uuid ?? null);
            setAccountByRole('purchase_return', form.coa_purchase_return_uuid ?? null);
            setAccountByRole('suspend', form.coa_suspend_uuid ?? null);
        }

        function addUnitRow(): void {
            unitRows.value.push({ conversion_ratio: 1, is_default: unitRows.value.length === 0, is_active: true });
            syncTabRows();
        }

        const MAX_DEFAULT_UNITS = 1;

        function toggleUnitDefault(index: number, event: Event): void {
            const input = event.target as HTMLInputElement;
            const row = unitRows.value[index];
            if (!row) return;
            if (input.checked) {
                unitRows.value.forEach((item) => { item.is_default = false; });
            }
            row.is_default = input.checked;
        }

        function removeUnitRow(index: number): void {
            unitRows.value.splice(index, 1);
            syncTabRows();
        }

        function addOpeningBalanceRow(): void {
            openingBalanceRows.value.push({ opening_qty: 0, period: new Date().toISOString().slice(0, 7) });
            syncTabRows();
        }

        function removeOpeningBalanceRow(index: number): void {
            openingBalanceRows.value.splice(index, 1);
            syncTabRows();
        }

        function addPriceRow(): void {
            priceRows.value.push({ price_buy: Number(form.price_buy ?? 0), price_sell: Number(form.price_sell ?? 0), discount_amount: 0, discount_percent: 0, base_qty: 1, is_active: true });
            syncTabRows();
        }

        function removePriceRow(index: number): void {
            priceRows.value.splice(index, 1);
            syncTabRows();
        }

        function addDiscountRow(): void {
            discountRows.value.push({ quantity: 1, discount_percent: 0, discount_amount: 0, base_qty: 1 });
            syncTabRows();
        }

        function removeDiscountRow(index: number): void {
            discountRows.value.splice(index, 1);
            syncTabRows();
        }

        function applyFormDefaults(options: ProductFormOptions): void {
            const defaultOriginUuid = options.origins.find((origin) => origin.label.trim().toLowerCase() === 'indonesia')?.uuid
                ?? options.origins[0]?.uuid;

            if (!form.base_unit_uuid) form.base_unit_uuid = options.units[0]?.uuid;
            if (!form.group_uuid) form.group_uuid = options.groups[0]?.uuid;
            if (!form.kind) form.kind = options.kinds[0]?.uuid;
            if (!form.type_uuid) form.type_uuid = options.types[0]?.uuid;
            if (!form.brand_uuid) form.brand_uuid = options.brands[0]?.uuid;
            if (!form.origin_uuid) form.origin_uuid = defaultOriginUuid;
            if (!form.grade_uuid) form.grade_uuid = options.grades[0]?.uuid;
        }

        function clearImageEntries(): void {
            imageEntries.value.forEach((entry) => {
                if (entry.preview.startsWith('blob:')) URL.revokeObjectURL(entry.preview);
            });
            imageEntries.value = [];
        }


        function getProductFileExtension(file: File): string {
            const match = /\.([a-z0-9]+)$/i.exec(file.name);
            return match?.[1]?.toLowerCase() ?? '';
        }

        function isSupportedProductFile(file: File): boolean {
            return PRODUCT_ALLOWED_FILE_TYPES.has(file.type)
                && PRODUCT_ALLOWED_FILE_EXTENSIONS.has(getProductFileExtension(file));
        }

        function notifyUploadRejections(rejections: {
            unsupported: string[];
            oversized: string[];
            overLimit: string[];
        }): void {
            const hasRejections = rejections.unsupported.length
                || rejections.oversized.length
                || rejections.overLimit.length;

            if (!hasRejections) return;

            const details: string[] = [];
            if (rejections.unsupported.length) {
                details.push(`Format tidak didukung: ${rejections.unsupported.join(', ')}. Gunakan JPG, JPEG, PNG, WEBP, atau PDF.`);
            }
            if (rejections.oversized.length) {
                details.push(`Ukuran melebihi 2 MB: ${rejections.oversized.join(', ')}.`);
            }
            if (rejections.overLimit.length) {
                details.push(`Melebihi batas 10 file: ${rejections.overLimit.join(', ')}.`);
            }

            Swal.fire({
                icon: 'warning',
                title: 'Beberapa file ditolak',
                text: details.join(' '),
            });
        }

        function syncImageForm(): void {
            form.images = imageEntries.value
                .map((entry) => entry.file)
                .filter((file): file is File => Boolean(file));
            form.existing_image_paths = imageEntries.value
                .map((entry) => entry.path)
                .filter((path): path is string => Boolean(path));
        }

        function formatProductFileSize(size?: number): string {
            if (size === undefined || size === null) return '-';
            if (size < 1024) return `${size} B`;
            if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
            return `${(size / (1024 * 1024)).toFixed(1)} MB`;
        }

        function isPdfEntry(entry: { path?: string; file?: File }): boolean {
            if (entry.file) return entry.file.type === 'application/pdf';
            return entry.path?.toLowerCase().endsWith('.pdf') ?? false;
        }

        function getProductFileName(entry: { path?: string; file?: File }): string {
            return entry.file?.name ?? entry.path?.split('/').pop() ?? 'File';
        }

        async function addImageFiles(files: File[] | FileList | null): Promise<void> {
            if (!files) return;

            isUploadingFiles.value = true;
            const rejections = { unsupported: [] as string[], oversized: [] as string[], overLimit: [] as string[] };

            try {
                for (const file of Array.from(files)) {
                    if (!isSupportedProductFile(file)) {
                        rejections.unsupported.push(file.name);
                        continue;
                    }
                    if (file.size > PRODUCT_MAX_FILE_SIZE) {
                        rejections.oversized.push(file.name);
                        continue;
                    }
                    if (imageEntries.value.length >= PRODUCT_MAX_IMAGE_ENTRIES) {
                        rejections.overLimit.push(file.name);
                        continue;
                    }
                    imageEntries.value.push({ file, preview: URL.createObjectURL(file) });
                }

                notifyUploadRejections(rejections);
                syncImageForm();
            } finally {
                isUploadingFiles.value = false;
            }
        }

        function handleImageChange(event: Event): void {
            const input = event.target as HTMLInputElement;
            void addImageFiles(input.files);
            input.value = '';
        }

        function removeImage(index: number): void {
            const [entry] = imageEntries.value.splice(index, 1);
            if (entry?.preview.startsWith('blob:')) URL.revokeObjectURL(entry.preview);
            syncImageForm();
        }

        function makeImagePrimary(index: number): void {
            if (index <= 0 || index >= imageEntries.value.length) return;
            const [entry] = imageEntries.value.splice(index, 1);
            if (entry) imageEntries.value.unshift(entry);
            syncImageForm();
        }

        function clearImages(): void {
            clearImageEntries();
            form.images = imageEntries.value
                .map((item) => item.file)
                .filter((file): file is File => Boolean(file));
            form.existing_image_paths = imageEntries.value
                .map((item) => item.path)
                .filter((path): path is string => Boolean(path));
        }

        function updateProductFormOptions(options: ProductFormOptions): void {
            accountOptions.value = options.accounts ?? [];
        }
        function initializeProductForm(product: ProductModel | null, options: ProductFormOptions): void {
            clearImageEntries();
            accountOptions.value = options.accounts ?? [];

            if (product) {
                openEdit({ ...product, is_active: product.is_active ?? true } as ProductModel);
                const paths = product.images?.length ? product.images : (product.image ? [product.image] : []);
                imageEntries.value = paths.map((path) => ({ path, preview: `/storage/${path}` }));
                form.existing_image_paths = paths;
                syncTabRows();
                return;
            }

            openCreate();
            applyFormDefaults(options);
            syncTabRows();
        }

        async function submitProduct(): Promise<void> {
            if (isSubmittingProduct.value || isUploadingFiles.value) return;

            isSubmittingProduct.value = true;
            try {
                await submitForm();
            } finally {
                isSubmittingProduct.value = false;
            }
        }

        function cancelProduct(): void {
            router.get('/master/products');
        }

        function goToCreateProduct(): void {
            router.get('/master/products/create');
        }

        function goToEditProduct(product: ProductModel): void {
            router.get('/master/products/create', { edit: product.uuid });
        }

        function formatRupiah(value: unknown): string {
            if (value === null || value === undefined || value === '') return '-';
            return Number(value).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
            });
        }

        function validateTabRows(): string[] {
            const errors: string[] = [];
            const duplicate = (rows: Array<Record<string, unknown>>, fields: string[], label: string): void => {
                const seen = new Set<string>();
                rows.forEach((row, index) => {
                    const values = fields.map((field) => String(row[field] ?? '').trim());
                    if (values.some((value) => !value)) return;
                    const key = values.join('|');
                    if (seen.has(key)) errors.push(`${label} pada baris ${index + 1} duplikat.`);
                    seen.add(key);
                });
            };
            duplicate(unitRows.value as Array<Record<string, unknown>>, ['unit_uuid'], 'Satuan');
            const defaultCount = unitRows.value.filter((row) => row.is_default).length;
            if (defaultCount > 1) {
                errors.push('Hanya satu satuan yang boleh dijadikan default.');
            }
            duplicate(accountRows.value as Array<Record<string, unknown>>, ['account_role'], 'Role akun');
            duplicate(openingBalanceRows.value as Array<Record<string, unknown>>, ['location_uuid', 'period'], 'Gudang/periode saldo');
            duplicate(priceRows.value as Array<Record<string, unknown>>, ['price_level_uuid'], 'Level harga');
            duplicate(discountRows.value as Array<Record<string, unknown>>, ['quantity', 'unit_uuid'], 'Diskon');
            discountRows.value.forEach((row, index) => {
                if (Number(row.discount_percent ?? 0) > 0 && Number(row.discount_amount ?? 0) > 0) {
                    errors.push(`Diskon pada baris ${index + 1} tidak boleh mengisi persen dan nominal bersamaan.`);
                }
            });
            return errors;
        }
        async function submitForm(): Promise<boolean> {
            if (!canManage.value) return false;
            syncTabRows();
            const rowErrors = validateTabRows();
            if (rowErrors.length) {
                form.setError({ tabs: rowErrors.join(' ') } as any);
                void Swal.fire({ title: 'Data tabel belum valid', text: rowErrors.join(' '), icon: 'warning' });
                return false;
            }
            const fieldErrors = validateProductForm(form.data() as unknown as Record<string, unknown>);
            if (Object.keys(fieldErrors).length) {
                form.setError(fieldErrors as any);
                void Swal.fire({ title: 'Data belum lengkap', text: Object.values(fieldErrors).join(' '), icon: 'warning' });
                return false;
            }
            form.clearErrors();
            (form as any).processing = true;

            let isSaved = false;
            const result = await saveProductUseCase.execute({ ...form.data(), uuid: editingItem.value?.uuid });
            result.fold(
                (failure: any) => {
                    if (failure instanceof ValidationFailure) {
                        const errs: Record<string, string> = {};
                        for (const [k, v] of Object.entries(failure.errors)) { errs[k] = Array.isArray(v) ? v[0] : v as string; }
                        form.setError(errs as any);
                        const validationMessage = errs.code
                            ?? Object.values(errs)[0]
                            ?? failure.message
                            ?? 'Data produk tidak valid.';
                        Swal.fire({ title: 'Gagal menyimpan produk', text: validationMessage, icon: 'error', toast: true, position: 'top-end', timer: 4000, showConfirmButton: false, timerProgressBar: true });
                    } else {
                        Swal.fire({ title: 'Gagal', text: failure.message, icon: 'error' });
                    }
                },
                () => {
                    isSaved = true;
                    Swal.fire({ title: 'Berhasil!', text: editingItem.value ? 'Produk diperbarui.' : 'Produk baru dibuat.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
                    fetchProducts();
                    closeForm();
                },
            );
            (form as any).processing = false;
            return isSaved;
        }

        function toggleSort(column: string) {
            if (sortBy.value === column) { sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'; }
            else { sortBy.value = column; sortDirection.value = 'asc'; }
            currentPage.value = 1;
            fetchProducts({ page: 1 });
        }

        function changePage(url: string) {
            const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
            currentPage.value = p;
            fetchProducts({ page: p });
        }

        function initFilters(filters: ProductFilters = {}): void {
            appliedFilters.value = Object.fromEntries(
                Object.entries(filters).filter(([key]) => key.startsWith('filter_')),
            ) as ProductFilters;
            search.value        = filters.search          ?? PRODUCT_DEFAULT_FILTERS.search;
            searchColumn.value  = filters.search_column   ?? PRODUCT_DEFAULT_FILTERS.search_column;
            sortBy.value        = filters.sort_by         ?? PRODUCT_DEFAULT_FILTERS.sort_by;
            sortDirection.value = filters.sort_direction  ?? PRODUCT_DEFAULT_FILTERS.sort_direction;
            perPage.value       = filters.per_page        ?? PRODUCT_DEFAULT_FILTERS.per_page;
            currentPage.value   = filters.page            ?? PRODUCT_DEFAULT_FILTERS.page;
            fetchProducts();
        }

        return {
            products, meta, summary, isLoading, isExporting, isImporting, showForm, editingItem, canManage,
            isSuperadmin, companies, defaultCompanyUuid,
            showFilterModal, filterOptions, draftFilters, appliedFilters,
            fetchFilterOptions, openFilterModal, closeFilterModal, applyFilters, resetFilters,
            search, searchColumn, searchColumns, sortBy, sortDirection, perPage, perPageOptions, currentPage,
            form, fetchProducts, deleteProduct, exportProducts, openImportPicker, downloadImportTemplate, handleImportInput, openCreate, openEdit, closeForm, submitForm,
            toggleSort, changePage, initFilters,
            imageEntries, isEditing, selectedCompanyUuid,
            unitRows, openingBalanceRows, priceRows, discountRows,
            accountRows, accountMappings, setAccountByRole, accountLabel,
            addUnitRow, removeUnitRow, toggleUnitDefault, addOpeningBalanceRow, removeOpeningBalanceRow,
            addPriceRow, removePriceRow, addDiscountRow, removeDiscountRow,
            setDefaultAccounts, syncAccounts,
            companyFilterOptions, unitFilterOptions, kindFilterOptions, userFilterOptions,
            hasActiveFilters, productOptionReducer, optionEndpoint, tableColumns: PRODUCT_TABLE_COLUMNS, isPdfFile,
            isUploadingFiles, isSubmittingProduct, formatProductFileSize,
            applyFormDefaults, updateProductFormOptions, addImageFiles, handleImageChange, removeImage, makeImagePrimary, clearImages, initializeProductForm,
            isPdfEntry, getProductFileName,
            validateTabRows, submitProduct, cancelProduct, goToCreateProduct, goToEditProduct, formatRupiah,
        };
    });
}

export const useProductVM = createProductVMStore();
