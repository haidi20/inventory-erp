<?php

namespace App\Modules\Operational\Sales\Services;

use App\Modules\Operational\Sales\Entities\ServicePackageEntity;
use App\Modules\Operational\Sales\Repositories\ServicePackageRepositoryInterface;
use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Services\ProductService;
use App\Modules\Operational\Sales\Services\ServiceService;
use Illuminate\Validation\ValidationException;

class ServicePackageService
{
    public function __construct(
        protected ServicePackageRepositoryInterface $repository,
        protected ProductService $productService,
        protected ServiceService $serviceService
    ) {}

    public function listPackages(ServicePackageEntity $context)
    {
        return $this->repository->list($context);
    }

    public function createPackage(ServicePackageEntity $entity): mixed
    {
        $this->validateBeforeSave($entity);
        return $this->repository->save($entity);
    }

    public function getPackage(string $uuid, string $branchUuid, string $companyUuid): mixed
    {
        return $this->repository->find($uuid, $branchUuid, $companyUuid);
    }

    public function updatePackage(ServicePackageEntity $entity): mixed
    {
        $this->validateBeforeSave($entity, $entity->uuid);
        return $this->repository->update($entity);
    }

    public function validateBeforeSave(ServicePackageEntity $entity, ?string $ignoreUuid = null): void
    {
        $errors = [];

        if ($entity->code && $this->repository->codeExists($entity->code, $entity->branch_uuid, $entity->company_uuid, $ignoreUuid)) {
            $errors['code'] = ['The code has already been taken.'];
        }

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
                }

                continue;
            }

            if ($itemType === 'service' && !$this->serviceService->getService($itemUuid, $entity->branch_uuid, $entity->company_uuid)) {
                $errors["items.{$index}.item_uuid"] = ['The selected service uuid is invalid.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function deletePackage(string $uuid, string $branchUuid, string $companyUuid): void
    {
        $this->repository->delete($uuid, $branchUuid, $companyUuid);
    }
}
