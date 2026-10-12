<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\MasterCustomerEntity;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Models\PersonAddress;
use App\Modules\Operational\Master\Models\PersonContact;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MasterCustomerRepository
 * 
 * Implementasi repository untuk manajemen data Master Pelanggan (Customer).
 * Menangani sinkronisasi alamat, kontak, dan validasi transaksi terkait.
 */
class MasterCustomerRepository implements MasterCustomerRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(MasterCustomerEntity $entity): array
    {
        $query = Person::query()
            ->where('is_supplier', false)
            ->where('deleted_at', 0)
            ->orderBy('name', 'ASC');

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(COALESCE(email, '')) LIKE ?", ["%{$search}%"]);
            });
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => MasterCustomerEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'active_customers' => Person::query()
                ->where('is_supplier', false)
                ->where('is_active', 'Y')
                ->where('deleted_at', 0)
                ->count(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function create(MasterCustomerEntity $entity): MasterCustomerEntity
    {
        return $this->saveEntity($entity);
    }

    /**
     * @inheritDoc
     */
    public function update(MasterCustomerEntity $entity): MasterCustomerEntity
    {
        return $this->saveEntity($entity);
    }

    /**
     * @internal
     */
    private function saveEntity(MasterCustomerEntity $entity): MasterCustomerEntity
    {
        DB::beginTransaction();
        try {
            $data = [
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'name' => trim($entity->name),
                'email' => $entity->email,
                'address' => $entity->address,
                'phone' => $entity->phone,
                'is_active' => $entity->is_active ? 'Y' : 'N',
                'is_supplier' => false,
                'is_customer' => true,
            ];

            if (empty($entity->uuid)) {
                $data['uuid'] = (string) Str::uuid();
                $data['code'] = 'CUST-' . strtoupper(Str::random(6));
                $person = Person::query()->create($data);
            } else {
                $person = Person::query()->findOrFail(trim($entity->uuid));
                if (empty($person->code)) {
                    $data['code'] = 'CUST-' . strtoupper(Str::random(6));
                }
                $person->update($data);
            }

            // Sync addresses
            if (!empty($entity->addresses)) {
                $person->addresses()->delete();
                foreach ($entity->addresses as $addr) {
                    $person->addresses()->create(array_merge($addr, [
                        'uuid' => (string) Str::uuid(),
                    ]));
                }
            }

            // Sync contacts
            if (!empty($entity->contacts)) {
                $person->contacts()->delete();
                foreach ($entity->contacts as $cont) {
                    $person->contacts()->create(array_merge($cont, [
                        'uuid' => (string) Str::uuid(),
                    ]));
                }
            }

            DB::commit();
            return MasterCustomerEntity::fromModel($person->load(['addresses', 'contacts']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(MasterCustomerEntity $entity): void
    {
        Person::query()->findOrFail(trim($entity->uuid))->delete();
    }

    /**
     * @inheritDoc
     */
    public function hasTransactions(string $uuid): bool
    {
        $tables = [
            ['table' => 'sales_orders', 'column' => 'person_uuid'],
            ['table' => 'sales_invoices', 'column' => 'person_uuid'],
            ['table' => 'delivery_orders', 'column' => 'person_uuid'],
            ['table' => 'sales', 'column' => 'customer_uuid'],
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t['table'])) {
                if (DB::table($t['table'])->where($t['column'], $uuid)->where('deleted_at', 0)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }
}
