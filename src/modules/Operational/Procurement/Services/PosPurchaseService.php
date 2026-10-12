<?php

namespace App\Modules\Operational\Procurement\Services;

use App\Modules\Operational\Procurement\Entities\PosPurchaseEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Repositories\PosPurchaseRepositoryInterface;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Operational\Master\Services\ProductService;
use App\Modules\Operational\Master\Services\SupplierService;
use Illuminate\Validation\ValidationException;

class PosPurchaseService
{
    public function __construct(
        protected PosPurchaseRepositoryInterface $repository,
        protected SupplierService $supplierService,
        protected ProductService $productService
    ) {}

    public function listPurchases(PosPurchaseEntity $context)
    {
        return $this->repository->list($context);
    }

    public function createPurchase(PosPurchaseEntity $entity): PurchaseRecordEntity
    {
        $this->validateBeforeCreate($entity);
        $this->computeTotals($entity);

        return $this->repository->save($entity);
    }

    public function validateBeforeCreate(PosPurchaseEntity $entity): void
    {
        $errors = [];

        if ($this->repository->invoiceNoExists($entity->invoice_no, $entity->branch_uuid, $entity->company_uuid)) {
            $errors['invoice_no'] = ['The invoice no has already been taken.'];
        }

        $supplier = $this->supplierService->findByUuid(new SupplierEntity(
            uuid: $entity->supplier_uuid,
            branch_uuid: $entity->branch_uuid,
            company_uuid: $entity->company_uuid
        ));

        if (!$supplier) {
            $errors['supplier_uuid'] = ['The selected supplier uuid is invalid.'];
        }

        foreach ($entity->items as $index => $item) {
            $product = $this->productService->findByUuid(new ProductEntity(
                uuid: $item['product_uuid'] ?? null,
                branch_uuid: $entity->branch_uuid,
                company_uuid: $entity->company_uuid
            ));

            if (!$product) {
                $errors["items.{$index}.product_uuid"] = ['The selected product uuid is invalid.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function getPurchase(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseRecordEntity
    {
        return $this->repository->find($uuid, $branchUuid, $companyUuid);
    }

    protected function computeTotals(PosPurchaseEntity $entity): void
    {
        $total = 0.0;
        foreach ($entity->items as $item) {
            $total += (float) $item['qty'] * (float) $item['price'];
        }

        $paid = (float) ($entity->paid ?? 0);

        $entity->total = $total;
        $entity->status = $entity->status ?? ($paid >= $total ? 'paid' : 'partial');
        $entity->purchase_date = isset($entity->purchase_date) ? strtotime($entity->purchase_date) : time();
    }
}
