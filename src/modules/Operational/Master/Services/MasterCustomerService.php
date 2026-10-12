<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\MasterCustomerEntity;
use App\Modules\Operational\Master\Repositories\MasterCustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * MasterCustomerService
 *
 * Port dari BE: master_customer_service.go. Mengelola Master Customer (Pelanggan)
 * — CRUD, pencarian, dan proteksi hapus bila sudah memiliki transaksi.
 */
class MasterCustomerService
{
    public function __construct(protected MasterCustomerRepositoryInterface $customerRepository) {}

    public function index(MasterCustomerEntity $entity): Collection
    {
        $result = $this->customerRepository->getPageData($entity);
        return collect($result['items'] ?? []);
    }

    public function store(MasterCustomerEntity $entity): MasterCustomerEntity
    {
        $this->validateEntity($entity);
        return DB::transaction(fn () => $this->customerRepository->create($entity));
    }

    public function update(MasterCustomerEntity $entity): MasterCustomerEntity
    {
        if (trim((string) $entity->uuid) === '') {
            throw new \InvalidArgumentException('ID customer wajib diisi.');
        }
        $this->validateEntity($entity);
        return DB::transaction(fn () => $this->customerRepository->update($entity));
    }

    public function destroy(string $uuid): array
    {
        if (trim($uuid) === '') {
            throw new \InvalidArgumentException('ID customer tidak valid.');
        }
        if ($this->customerRepository->hasTransactions($uuid)) {
            throw new \InvalidArgumentException('Customer tidak bisa dihapus karena sudah memiliki riwayat transaksi.');
        }

        $this->customerRepository->delete(new MasterCustomerEntity(uuid: $uuid));
        return [
            'status'  => 'success',
            'message' => 'Customer dihapus.',
        ];
    }

    private function validateEntity(MasterCustomerEntity $entity): void
    {
        if (trim((string) $entity->name) === '') {
            throw new \InvalidArgumentException('Nama customer wajib diisi.');
        }
        if ($entity->email && !filter_var($entity->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Format email tidak valid.');
        }
    }

    private function buildEntity(array $input): MasterCustomerEntity
    {
        return new MasterCustomerEntity(
            uuid: trim((string) ($input['uuid'] ?? '')) ?: null,
            name: trim((string) ($input['name'] ?? '')),
            code: $input['code'] ?? null,
            branch_uuid: $input['branch_uuid'] ?? null,
            company_uuid: $input['company_uuid'] ?? null,
            email: $input['email'] ?? null,
            phone: $input['phone'] ?? null,
            npwp: $input['npwp'] ?? null,
            term_uuid: $input['term_uuid'] ?? null,
            limit_piutang: (float) ($input['limit_piutang'] ?? 0),
            price_level_uuid: $input['price_level_uuid'] ?? null,
            salesman_uuid: $input['salesman_uuid'] ?? null,
            coa_piut_uuid: $input['coa_piut_uuid'] ?? null,
            rayon: $input['rayon'] ?? null,
            note: $input['note'] ?? null,
            is_active: filter_var($input['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            addresses: (array) ($input['addresses'] ?? []),
            contacts: (array) ($input['contacts'] ?? []),
            actor_uuid: $input['actor_uuid'] ?? null,
        );
    }
}
