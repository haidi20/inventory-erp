<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Services\ProductExportService;
use App\Modules\Operational\Master\Services\ProductImportService;
use App\Modules\Operational\Master\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ProductController
 *
 * Pengontrol API untuk manajemen data Produk.
 * Menggunakan ProductRepository untuk persistensi data dan ProductEntity untuk integritas tipe.
 */
class ProductController extends Controller
{
    private ProductService $service;

    private ProductExportService $exportService;

    private ProductImportService $importService;

    public function __construct(
        ProductService $service,
        ProductExportService $exportService,
        ProductImportService $importService
    ) {
        parent::__construct();
        $this->service = $service;
        $this->exportService = $exportService;
        $this->importService = $importService;
    }

    /**
     * Menampilkan daftar produk dengan paginasi.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = auth()->user();
        $isSuperadmin = $user->isSuperAdmin();
        $filterCompanyUuid = $isSuperadmin
            ? ($request->query('filter_company_uuid') ?? current_company_uuid())
            : null;

        $entity = new ProductEntity(
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
            is_superadmin: $isSuperadmin,
            filter_company_uuid: $filterCompanyUuid,
            search: $request->query('search'),
            search_column: $request->query('search_column', 'all'),
            page: (int) ($request->query('page', 1)),
            per_page: (int) ($request->query('per_page', 20)),
            filter_barcode: $request->query('filter_barcode'),
            filter_code: $request->query('filter_code'),
            filter_name: $request->query('filter_name'),
            filter_unit: $request->query('filter_unit'),
            filter_group_uuid: $request->query('filter_group_uuid'),
            filter_keterangan: $request->query('filter_keterangan'),
            filter_kind: $request->query('filter_kind'),
            filter_type_uuid: $request->query('filter_type_uuid'),
            filter_brand_uuid: $request->query('filter_brand_uuid'),
            filter_origin_uuid: $request->query('filter_origin_uuid'),
            filter_grade_uuid: $request->query('filter_grade_uuid'),
            filter_created_by: $request->query('filter_created_by'),
            filter_updated_by: $request->query('filter_updated_by'),
            filter_created_from: $request->query('filter_created_from'),
            filter_created_to: $request->query('filter_created_to'),
            filter_updated_from: $request->query('filter_updated_from'),
            filter_updated_to: $request->query('filter_updated_to'),
            filter_periode: $request->query('filter_periode'),
            sort_by: $request->query('sort_by'),
            sort_direction: $request->query('sort_direction')
        );

        $result = $this->service->getPageData($entity);

        $items = $result['items'];
        $collection = method_exists($items, 'items')
            ? collect($items->items())
            : collect($items);

        return $this->apiResource($collection->map(fn ($item) => $item->toArray())->values())
            ->additional([
                'meta' => [
                    'total' => $result['total'],
                    'page' => $result['current_page'] ?? $result['page'] ?? 1,
                    'per_page' => $result['per_page'],
                    'last_page' => method_exists($items, 'lastPage') ? $items->lastPage() : 1,
                ],
            ]);
    }

    /**
     * Menyediakan opsi-opsi dropdown untuk modal filter master produk.
     */
    public function filterOptions(Request $request): JsonResource
    {
        $user = auth()->user();
        $isSuperadmin = $user->isSuperAdmin();

        $entity = new ProductEntity(
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
            is_superadmin: $isSuperadmin,
            filter_company_uuid: $isSuperadmin
                ? ($request->query('filter_company_uuid') ?? current_company_uuid())
                : null
        );

        return $this->apiResource($this->service->getFilterOptions($entity));
    }

    public function options(Request $request): JsonResource
    {
        $type = (string) $request->query('type', '');
        $search = trim((string) $request->query('search', ''));
        $selected = (string) $request->query('selected', '');
        $limit = min(max((int) $request->query('limit', 30), 1), 50);

        return $this->apiResource($this->service->getDropdownOptions($type, $search, $selected, $limit));
    }

    /**
     * Menyimpan produk baru.
     */
    public function store(Request $request): JsonResource
    {
        foreach (['units', 'accounts', 'opening_balances', 'prices', 'discounts'] as $rowsKey) {
            if (is_string($request->input($rowsKey))) {
                $request->merge([$rowsKey => json_decode($request->input($rowsKey), true) ?: []]);
            }
        }

        // Validasi input (Sederhana untuk demo, sebaiknya gunakan FormRequest)
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'base_unit_uuid' => ['nullable', 'string', 'max:36', 'exists:units,uuid'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => 'nullable|string|max:500',
            'remarks' => 'nullable|string|max:500',
            'item_name_alt' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'group_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'type_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'brand_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'origin_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'grade_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'kind' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'price_buy' => 'numeric|min:0',
            'price_sell' => 'numeric|min:0',
            'opening_cost' => 'numeric|min:0',
            'min_stock' => 'integer|min:0',
            'max_stock' => 'integer|min:0',
            'max_order' => 'integer|min:0',
            'is_assembly' => 'boolean',
            'stock' => 'integer|min:0',
            'is_active' => 'boolean',
            'coa_sales_uuid' => 'nullable|string',
            'coa_inventory_uuid' => 'nullable|string',
            'coa_sales_discount_uuid' => 'nullable|string',
            'coa_hpp_uuid' => 'nullable|string',
            'coa_sales_return_uuid' => 'nullable|string',
            'coa_purchase_return_uuid' => 'nullable|string',
            'coa_suspend_uuid' => 'nullable|string',
            'units' => 'nullable|array',
            'accounts' => 'nullable|array',
            'opening_balances' => 'nullable|array',
            'prices' => 'nullable|array',
            'discounts' => 'nullable|array',
            'units.*.unit_uuid' => 'required_with:units|string|max:36',
            'units.*.conversion_ratio' => 'nullable|numeric|min:0.0001',
            'units.*.note' => 'nullable|string|max:500',
            'units.*.is_default' => 'nullable|boolean',
            'units.*.is_active' => 'nullable|boolean',
            'accounts.*.account_role' => 'required_with:accounts|string|max:50',
            'accounts.*.account_uuid' => 'required_with:accounts|string|max:36',
            'opening_balances.*.location_uuid' => 'required_with:opening_balances|string|max:36',
            'opening_balances.*.opening_qty' => 'nullable|numeric|min:0',
            'opening_balances.*.period' => 'nullable|string|max:7',
            'prices.*.price_level_uuid' => 'required_with:prices|string|max:36',
            'prices.*.price_buy' => 'nullable|numeric|min:0',
            'prices.*.price_sell' => 'nullable|numeric|min:0',
            'prices.*.discount_amount' => 'nullable|numeric|min:0',
            'prices.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'prices.*.base_qty' => 'nullable|numeric|min:0.0001',
            'prices.*.is_active' => 'nullable|boolean',
            'discounts.*.quantity' => 'required_with:discounts|numeric|min:0',
            'discounts.*.unit_uuid' => 'nullable|string|max:36',
            'discounts.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'discounts.*.discount_amount' => 'nullable|numeric|min:0',
            'discounts.*.base_qty' => 'nullable|numeric|min:0.0001',
        ]);

        foreach (['units', 'accounts', 'opening_balances', 'prices', 'discounts'] as $rowsKey) {
            $data[$rowsKey] ??= [];
        }
        $this->validateNestedRows($data);

        $user = auth()->user();
        $entity = ProductEntity::fromArray(array_merge($data, [
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid' => $user->uuid,
        ]));

        $saved = $this->service->save(
            $entity,
            $request->file('image'),
            $request->file('images', []),
            (array) $request->input('existing_image_paths', [])
        );

        $this->audit('product.create', null, null, $saved->toArray());

        return $this->apiResource($saved->toArray());
    }

    /**
     * Menampilkan detail produk.
     */
    public function show(string $uuid): JsonResource
    {
        $user = auth()->user();
        $entity = new ProductEntity(
            uuid: $uuid,
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $product = $this->service->findByUuid($entity);

        if (! $product) {
            abort(404, 'Product not found');
        }

        return $this->apiResource($product->toArray());
    }

    /**
     * Memperbarui data produk.
     */
    public function update(Request $request, string $uuid): JsonResource
    {
        foreach (['units', 'accounts', 'opening_balances', 'prices', 'discounts'] as $rowsKey) {
            if (is_string($request->input($rowsKey))) {
                $request->merge([$rowsKey => json_decode($request->input($rowsKey), true) ?: []]);
            }
        }

        $user = auth()->user();
        $entitySearch = new ProductEntity(
            uuid: $uuid,
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $existing = $this->service->findByUuid($entitySearch);
        if (! $existing) {
            abort(404, 'Product not found');
        }

        $data = $request->validate([
            'code' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'base_unit_uuid' => ['nullable', 'string', 'max:36', 'exists:units,uuid'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => 'nullable|string|max:500',
            'remarks' => 'nullable|string|max:500',
            'item_name_alt' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'group_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'type_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'brand_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'origin_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'grade_uuid' => 'nullable|string|exists:lookup_values,uuid',
            'kind' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'price_buy' => 'numeric|min:0',
            'price_sell' => 'numeric|min:0',
            'opening_cost' => 'numeric|min:0',
            'min_stock' => 'integer|min:0',
            'max_stock' => 'integer|min:0',
            'max_order' => 'integer|min:0',
            'is_assembly' => 'boolean',
            'stock' => 'integer|min:0',
            'is_active' => 'boolean',
            'coa_sales_uuid' => 'nullable|string',
            'coa_inventory_uuid' => 'nullable|string',
            'coa_sales_discount_uuid' => 'nullable|string',
            'coa_hpp_uuid' => 'nullable|string',
            'coa_sales_return_uuid' => 'nullable|string',
            'coa_purchase_return_uuid' => 'nullable|string',
            'coa_suspend_uuid' => 'nullable|string',
            'units' => 'nullable|array',
            'accounts' => 'nullable|array',
            'opening_balances' => 'nullable|array',
            'prices' => 'nullable|array',
            'discounts' => 'nullable|array',
            'units.*.unit_uuid' => 'required_with:units|string|max:36',
            'units.*.conversion_ratio' => 'nullable|numeric|min:0.0001',
            'units.*.note' => 'nullable|string|max:500',
            'units.*.is_default' => 'nullable|boolean',
            'units.*.is_active' => 'nullable|boolean',
            'accounts.*.account_role' => 'required_with:accounts|string|max:50',
            'accounts.*.account_uuid' => 'required_with:accounts|string|max:36',
            'opening_balances.*.location_uuid' => 'required_with:opening_balances|string|max:36',
            'opening_balances.*.opening_qty' => 'nullable|numeric|min:0',
            'opening_balances.*.period' => 'nullable|string|max:7',
            'prices.*.price_level_uuid' => 'required_with:prices|string|max:36',
            'prices.*.price_buy' => 'nullable|numeric|min:0',
            'prices.*.price_sell' => 'nullable|numeric|min:0',
            'prices.*.discount_amount' => 'nullable|numeric|min:0',
            'prices.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'prices.*.base_qty' => 'nullable|numeric|min:0.0001',
            'prices.*.is_active' => 'nullable|boolean',
            'discounts.*.quantity' => 'required_with:discounts|numeric|min:0',
            'discounts.*.unit_uuid' => 'nullable|string|max:36',
            'discounts.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'discounts.*.discount_amount' => 'nullable|numeric|min:0',
            'discounts.*.base_qty' => 'nullable|numeric|min:0.0001',
        ]);

        foreach (['units', 'accounts', 'opening_balances', 'prices', 'discounts'] as $rowsKey) {
            $data[$rowsKey] ??= [];
        }
        $this->validateNestedRows($data);

        $entity = ProductEntity::fromArray(array_merge($existing->toArray(), $data, [
            'actor_uuid' => $user->uuid,
        ]));

        $updated = $this->service->save(
            $entity,
            $request->file('image'),
            $request->file('images', []),
            (array) $request->input('existing_image_paths', [])
        );

        $this->audit('product.update', null, $existing->toArray(), $updated->toArray());

        return $this->apiResource($updated->toArray());
    }

    public function export(Request $request): StreamedResponse
    {
        $isSuperadmin = auth()->user()->isSuperAdmin();
        $entity = new ProductEntity(
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid(),
            is_superadmin: $isSuperadmin,
            filter_company_uuid: $isSuperadmin
                ? ($request->query('filter_company_uuid') ?? current_company_uuid())
                : null,
            search: $request->query('search'),
            search_column: $request->query('search_column', 'all'),
            filter_barcode: $request->query('filter_barcode'),
            filter_code: $request->query('filter_code'),
            filter_name: $request->query('filter_name'),
            filter_unit: $request->query('filter_unit'),
            filter_group_uuid: $request->query('filter_group_uuid'),
            filter_keterangan: $request->query('filter_keterangan'),
            filter_kind: $request->query('filter_kind'),
            filter_type_uuid: $request->query('filter_type_uuid'),
            filter_brand_uuid: $request->query('filter_brand_uuid'),
            filter_origin_uuid: $request->query('filter_origin_uuid'),
            filter_grade_uuid: $request->query('filter_grade_uuid'),
            filter_created_by: $request->query('filter_created_by'),
            filter_updated_by: $request->query('filter_updated_by'),
            filter_created_from: $request->query('filter_created_from'),
            filter_created_to: $request->query('filter_created_to'),
            filter_updated_from: $request->query('filter_updated_from'),
            filter_updated_to: $request->query('filter_updated_to'),
            filter_periode: $request->query('filter_periode'),
        );

        return $this->exportService->stream($entity);
    }

    public function importTemplate(): StreamedResponse
    {
        return $this->importService->template();
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);
        $companyUuid = current_company_uuid() ?? current_company_uuid();
        $result = $this->importService->import($request->file('file'), current_branch_uuid(), $companyUuid);

        return response()->json($result);
    }

    private function validateNestedRows(array $data): void
    {
        $rules = [
            'units' => ['unique' => ['unit_uuid'], 'label' => 'Satuan'],
            'accounts' => ['unique' => ['account_role'], 'label' => 'Role akun'],
            'opening_balances' => ['unique' => ['location_uuid', 'period'], 'label' => 'Gudang/periode saldo'],
            'prices' => ['unique' => ['price_level_uuid'], 'label' => 'Level harga'],
            'discounts' => ['unique' => ['quantity', 'unit_uuid'], 'label' => 'Diskon'],
        ];
        $defaultUnits = array_filter($data['units'] ?? [], static fn ($row): bool => (bool) ($row['is_default'] ?? false));
        if (count($defaultUnits) > 1) {
            throw ValidationException::withMessages(['units' => ['Hanya satu satuan yang boleh dijadikan default.']]);
        }

        foreach ($rules as $rowsKey => $config) {
            $seen = [];
            foreach (($data[$rowsKey] ?? []) as $index => $row) {
                $keyParts = array_map(static fn ($field) => (string) ($row[$field] ?? ''), $config['unique']);
                $key = implode('|', $keyParts);
                if ($key !== '' && ! in_array('', $keyParts, true) && isset($seen[$key])) {
                    throw ValidationException::withMessages(["{$rowsKey}.{$index}" => ["{$config['label']} duplikat."]]);
                }
                if ($key !== '') {
                    $seen[$key] = true;
                }
                if ($rowsKey === 'discounts' && (float) ($row['discount_percent'] ?? 0) > 0 && (float) ($row['discount_amount'] ?? 0) > 0) {
                    throw ValidationException::withMessages(["discounts.{$index}" => ['Diskon persen dan nominal tidak boleh diisi bersamaan.']]);
                }
            }
        }
    }

    /**
     * Menghapus produk (Soft Delete).
     *
     * @return Response
     */
    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $entitySearch = new ProductEntity(
            uuid: $uuid,
            branch_uuid: current_branch_uuid(),
            company_uuid: current_company_uuid()
        );
        $existing = $this->service->findByUuid($entitySearch);

        if ($existing) {
            $this->service->delete($existing);
            $this->audit('product.delete', null, $existing->toArray(), null);
        }

        return response()->noContent();
    }
}
