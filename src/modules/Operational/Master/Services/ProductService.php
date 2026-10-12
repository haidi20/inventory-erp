<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Repositories\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Core\Organization\Services\CompanyService;

/**
 * ProductService
 * 
 * Layanan bisnis untuk manajemen Master Produk.
 */
class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private BranchService $branchService,
        private CompanyService $companyService
    )
    {
    }

    /**
     * Mengambil data halaman produk
     */
    public function index(ProductEntity $filter): array
    {
        return $this->productRepository->getPageData($filter);
    }

    /**
     * Menyimpan data produk baru atau pembaruan
     */
    public function save(ProductEntity $productEntity, ?UploadedFile $image = null, array $images = [], array $existingImagePaths = []): ProductEntity
    {
        Validator::make($productEntity->toArray(), [
            'uuid' => ['nullable', 'string', 'uuid'],
            'branch_uuid' => ['required', 'string', 'uuid'],
            'company_uuid' => ['required', 'string', 'uuid'],
            'code' => [
                'required', 
                'string', 
                'max:255', 
                'string',
                'max:255'
            ],
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'product_uuid' => ['nullable', 'string', 'max:36'],
            'base_unit_uuid' => ['nullable', 'string', 'max:36'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0'],
            'max_order' => ['nullable', 'numeric', 'min:0'],
            'base_price_buy' => ['nullable', 'numeric', 'min:0'],
            'base_price_sell' => ['nullable', 'numeric', 'min:0'],
            'storage_bin_code' => ['nullable', 'string', 'max:100'],
            'is_bom_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ])->validate();

        Validator::make([
            'image' => $image,
            'images' => $images,
            'existing_image_paths' => $existingImagePaths,
            'total_images' => count($existingImagePaths) + count($images) + ($image ? 1 : 0),
        ], [
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:2048'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:2048'],
            'existing_image_paths' => ['nullable', 'array'],
            'existing_image_paths.*' => ['nullable', 'string', 'max:255'],
            'total_images' => ['required', 'integer', 'max:10'],
        ])->validate();

        $ownedProduct = null;
        if ($productEntity->uuid) {
            $ownedProduct = $this->productRepository->findByUuid($productEntity);

            if (!$ownedProduct) {
                throw ValidationException::withMessages(['uuid' => ['The selected product uuid is invalid.']]);
            }
        }

        if (!$this->branchService->findByUuid($productEntity->branch_uuid)) {
            throw ValidationException::withMessages(['branch_uuid' => ['The selected branch uuid is invalid.']]);
        }

        if (!$this->companyService->findByUuid($productEntity->company_uuid)) {
            throw ValidationException::withMessages(['company_uuid' => ['The selected company uuid is invalid.']]);
        }

        $existing = $this->productRepository->getByCode($productEntity);
        if ($existing && $existing->uuid !== $productEntity->uuid) {
            throw ValidationException::withMessages(['code' => ['Kode produk sudah digunakan.']]);
        }

        $this->validateExistingImagePaths($existingImagePaths, $ownedProduct);

        $uploadedImagePaths = [];

        try {
            if ($image) {
                $uploadedImagePaths[] = $image->store('items', 'public');
            }

            foreach ($images as $uploadedImage) {
                $uploadedImagePaths[] = $uploadedImage->store('items', 'public');
            }

            $previousImagePaths = $ownedProduct?->images ?? [];
            $productEntity->images = array_values(array_unique(array_merge($existingImagePaths, $uploadedImagePaths)));
            $productEntity->image = $productEntity->images[0] ?? null;

            $saved = $this->productRepository->save($productEntity, [
                'units' => $productEntity->units ?? [],
                'accounts' => $productEntity->accounts ?? [],
                'opening_balances' => $productEntity->opening_balances ?? [],
                'prices' => $productEntity->prices ?? [],
                'discounts' => $productEntity->discounts ?? [],
            ]);
            $this->deleteRemovedProductFiles($previousImagePaths, $productEntity->images);

            return $saved;
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($uploadedImagePaths);
            throw $exception;
        }
    }

    private function validateExistingImagePaths(array $existingImagePaths, ?ProductEntity $ownedProduct): void
    {
        if (empty($existingImagePaths)) {
            return;
        }

        $ownedImagePaths = $ownedProduct?->images ?? [];

        if (empty($ownedImagePaths) || array_diff($existingImagePaths, $ownedImagePaths)) {
            throw ValidationException::withMessages([
                'existing_image_paths' => ['Some selected product files do not belong to this product.'],
            ]);
        }

        foreach ($existingImagePaths as $existingImagePath) {
            if (!is_string($existingImagePath) || !preg_match('/^items\/[A-Za-z0-9._-]+$/', $existingImagePath)) {
                throw ValidationException::withMessages([
                    'existing_image_paths' => ['The selected product file path is invalid.'],
                ]);
            }
        }
    }

    private function deleteRemovedProductFiles(array $previousImagePaths, array $currentImagePaths): void
    {
        $removedImagePaths = array_values(array_diff($previousImagePaths, $currentImagePaths));

        if (!empty($removedImagePaths)) {
            Storage::disk('public')->delete($removedImagePaths);
        }
    }

    /**
     * Menghapus produk jika tidak memiliki transaksi pendukung
     */
    public function destroy(ProductEntity $productEntity): array
    {
        if ($this->productRepository->hasTransactions($productEntity->uuid)) {
            return [
                'status' => 'error',
                'message' => 'Produk sudah digunakan dalam transaksi, tidak bisa dihapus.',
            ];
        }

        $this->productRepository->delete($productEntity);

        return [
            'status' => 'success',
            'message' => 'Produk berhasil dihapus.',
        ];
    }

    public function getPageData(ProductEntity $filter): array
    {
        return $this->productRepository->getPageData($filter);
    }

    public function getFilterOptions(ProductEntity $filter): array
    {
        return $this->productRepository->getFilterOptions($filter);
    }

    public function findByUuid(ProductEntity $entity): ?ProductEntity
    {
        return $this->productRepository->findByUuid($entity);
    }

    public function delete(ProductEntity $entity): void
    {
        $this->productRepository->delete($entity);
    }

    public function getFormOptions(): array
    {
        return $this->productRepository->getFormOptions();
    }

    public function getCompanyOptions(): array
    {
        return $this->productRepository->getCompanyOptions();
    }

    public function getDropdownOptions(string $type, string $search, string $selected, int $limit): array
    {
        return $this->productRepository->getDropdownOptions($type, $search, $selected, $limit);
    }

    public function getDefaultCompanyUuid(): ?string
    {
        return $this->productRepository->getDefaultCompanyUuid();
    }

    public function getProductForEdit(string $uuid, string $branchUuid, string $companyUuid, bool $isSuperadmin = false): ?ProductEntity
    {
        $product = $this->productRepository->findWithRelations($uuid, $branchUuid, $companyUuid, $isSuperadmin);

        if (!$product) {
            return null;
        }

        return new ProductEntity(
            uuid: $product->uuid,
            code: $product->code,
            name: $product->name,
            barcode: $product->barcode,
            description: $product->description,
            item_name_alt: $product->item_name_alt,
            unit: $product->base_unit_uuid,
            unit_name: $product->baseUnit?->name ?? $product->unit,
            location: $product->location,
            min_stock: (float) $product->min_stock,
            max_stock: (float) $product->max_stock,
            max_order: (float) $product->max_order,
            remarks: $product->note,
            price_buy: (float) $product->base_price_buy,
            price_sell: (float) $product->base_price_sell,
            opening_cost: (float) $product->opening_cost,
            kind: $product->kind,
            item_type: $product->item_type,
            is_assembly: (bool) $product->is_bom_enabled,
            is_active: (bool) $product->is_active,
            image: $product->image,
            images: $product->images->pluck('path')->values()->all(),
            group_uuid: $product->item_group_lookup_uuid,
            type_uuid: $product->item_type_lookup_uuid,
            brand_uuid: $product->brand_lookup_uuid,
            origin_uuid: $product->origin_lookup_uuid,
            grade_uuid: $product->grade_lookup_uuid,
            coa_sales_uuid: $product->coa_sales_uuid,
            coa_inventory_uuid: $product->coa_inventory_uuid,
            coa_sales_discount_uuid: $product->coa_sales_discount_uuid,
            coa_hpp_uuid: $product->coa_hpp_uuid,
            coa_sales_return_uuid: $product->coa_sales_return_uuid,
            coa_purchase_return_uuid: $product->coa_purchase_return_uuid,
            coa_suspend_uuid: $product->coa_suspend_uuid,
            accounts: $product->accounts->map(fn ($row) => ['uuid' => $row->uuid, 'account_role' => $row->account_role, 'account_uuid' => $row->account_uuid, 'account_code' => $row->account?->code, 'account_name' => $row->account?->name])->all(),
            units: $product->units->map(fn ($row) => ['uuid' => $row->uuid, 'unit_uuid' => $row->unit_uuid, 'conversion_ratio' => (float) $row->conversion_ratio, 'note' => $row->note, 'is_default' => (bool) $row->is_base, 'is_active' => (bool) $row->is_active])->all(),
            opening_balances: $product->openingBalances->map(fn ($row) => ['uuid' => $row->uuid, 'location_uuid' => $row->location_uuid, 'opening_qty' => (float) $row->opening_qty, 'period' => $row->period_month])->all(),
            prices: $product->prices->map(fn ($row) => ['uuid' => $row->uuid, 'price_level_uuid' => $row->price_level_uuid, 'price_sell' => (float) $row->price, 'price_buy' => (float) $row->price_buy, 'discount_amount' => (float) $row->discount_amount, 'discount_percent' => (float) $row->discount_percent, 'base_qty' => (float) $row->base_qty, 'is_active' => (bool) $row->is_active])->all(),
            discounts: $product->discountRules->map(fn ($row) => ['uuid' => $row->uuid, 'quantity' => (float) $row->min_qty, 'unit_uuid' => $row->unit_uuid, 'discount_percent' => $row->discount_type === 'percentage' ? (float) $row->amount : 0, 'discount_amount' => $row->discount_type === 'amount' ? (float) $row->amount : 0, 'base_qty' => (float) $row->base_qty])->all(),
        );
    }
}
