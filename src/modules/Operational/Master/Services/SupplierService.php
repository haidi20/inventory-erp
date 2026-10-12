<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\SupplierEntity;
use App\Modules\Operational\Master\Repositories\SupplierRepository;
use Illuminate\Support\Facades\Validator;

class SupplierService
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function index(SupplierEntity $supplierEntity): array
    {
        return $this->supplierRepository->getPageData($supplierEntity);
    }

    public function store(SupplierEntity $supplierEntity): SupplierEntity
    {
        Validator::make($supplierEntity->toArray(), [
            'branch_uuid' => ['required', 'string'],
            'company_uuid' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'term_days' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        return $this->supplierRepository->save($supplierEntity);
    }

    public function update(SupplierEntity $supplierEntity): SupplierEntity
    {
        Validator::make($supplierEntity->toArray(), [
            'uuid' => ['required', 'string'],
            'branch_uuid' => ['required', 'string'],
            'company_uuid' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'term_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ])->validate();

        return $this->supplierRepository->save($supplierEntity);
    }

    public function destroy(SupplierEntity $supplierEntity): array
    {
        if ($this->supplierRepository->hasPurchases($supplierEntity)) {
            return [
                'status' => 'error',
                'message' => 'Supplier sudah memiliki transaksi, tidak bisa dihapus.',
            ];
        }

        $this->supplierRepository->delete($supplierEntity);

        return [
            'status' => 'success',
            'message' => 'Supplier dihapus.',
        ];
    }

    public function getPageData(SupplierEntity $entity): array
    {
        return $this->supplierRepository->getPageData($entity);
    }

    public function save(SupplierEntity $entity): SupplierEntity
    {
        return $this->supplierRepository->save($entity);
    }

    public function findByUuid(SupplierEntity $entity): ?SupplierEntity
    {
        return $this->supplierRepository->findByUuid($entity);
    }

    public function delete(SupplierEntity $entity): void
    {
        $this->supplierRepository->delete($entity);
    }
}
