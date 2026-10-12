<?php

namespace App\Modules\Operational\Sales\Services;

use App\Modules\Operational\Sales\Entities\SaleEntity;
use App\Modules\Operational\Sales\Repositories\SaleRepositoryInterface;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Operational\Master\Services\ProductService;
use App\Modules\Operational\Master\Services\EmployeeService;
use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Services\PatientService;
use App\Modules\Operational\Sales\Entities\ServiceEntity;
use App\Modules\Operational\Sales\Services\ServiceService;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        protected SaleRepositoryInterface $repository,
        protected PatientService $patientService,
        protected EmployeeService $employeeService,
        protected ProductService $productService,
        protected ServiceService $serviceService
    ) {}

    public function listSales(SaleEntity $context)
    {
        return $this->repository->list($context);
    }

    public function createSale(SaleEntity $entity): mixed
    {
        $this->ensureProductItemsHaveHpp($entity);

        $this->computeTotals($entity);

        return $this->repository->save($entity);
    }

    public function getSale(string $uuid, string $branchUuid, string $companyUuid): mixed
    {
        return $this->repository->find($uuid, $branchUuid, $companyUuid);
    }

    protected function ensureProductItemsHaveHpp(SaleEntity $entity): void
    {
        $errors = $this->resolveItemErrors($entity);

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function validateBeforeCreate(SaleEntity $entity): void
    {
        $errors = [];

        if ($this->repository->saleNoExists($entity->sale_no, $entity->branch_uuid, $entity->company_uuid)) {
            $errors['sale_no'] = ['The sale no has already been taken.'];
        }

        if (!empty($entity->patient_uuid) && !$this->patientService->findByUuid(new PatientEntity(
            uuid: $entity->patient_uuid,
            branch_uuid: $entity->branch_uuid,
            company_uuid: $entity->company_uuid
        ))) {
            $errors['patient_uuid'] = ['The selected patient uuid is invalid.'];
        }

        if (!empty($entity->cashier_uuid) && !$this->employeeService->findByUuid(new EmployeeEntity(
            uuid: $entity->cashier_uuid,
            branch_uuid: $entity->branch_uuid,
            company_uuid: $entity->company_uuid
        ))) {
            $errors['cashier_uuid'] = ['The selected cashier uuid is invalid.'];
        }

        $errors = array_merge($errors, $this->resolveItemErrors($entity));

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function resolveItemErrors(SaleEntity $entity): array
    {
        $errors = [];

        foreach ($entity->items as $index => $item) {
            $itemType = $item['item_type'] ?? null;
            $itemUuid = $item['item_uuid'] ?? null;

            if ($itemType === 'product') {
                $product = $this->productService->findByUuid(new ProductEntity(
                    uuid: $itemUuid,
                    branch_uuid: $entity->branch_uuid,
                    company_uuid: $entity->company_uuid
                ));

                if (!$product) {
                    $errors["items.{$index}.item_uuid"] = ['The selected product uuid is invalid.'];
                    continue;
                }

                if ((float) ($product->price_buy ?? 0) <= 0) {
                    $errors["items.{$index}.item_uuid"] = [
                        "Transaksi ditolak: Produk {$product->name} belum memiliki Harga Modal (HPP). Silakan hubungi bagian Farmasi / Gudang.",
                    ];
                }

                continue;
            }

            if ($itemType === 'service' && !$this->serviceService->getService($itemUuid, $entity->branch_uuid, $entity->company_uuid)) {
                $errors["items.{$index}.item_uuid"] = ['The selected service uuid is invalid.'];
            }
        }

        return $errors;
    }

    protected function computeTotals(SaleEntity $entity): void
    {
        $total = 0.0;
        foreach ($entity->items as $item) {
            $total += (float) $item['qty'] * (float) $item['price'];
        }

        $paid = (float) ($entity->paid ?? 0);

        $entity->total_amount = $total;
        $entity->change = max(0, $paid - $total);
        $entity->payment_status = $entity->payment_status ?? ($paid >= $total ? 'paid' : 'partial');
        $entity->sale_at = $entity->sale_at ?? time();
    }
}
