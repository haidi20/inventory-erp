<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\PersonEntity;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Models\PersonCustomField;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * PersonRepository
 * 
 * Implementasi repository untuk manajemen data Subjek (Person/Customer/Supplier).
 * Mendukung pencarian kompleks, manajemen custom fields, dan integrasi audit.
 */
class PersonRepository implements PersonRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(PersonEntity $entity): array
    {
        $query = Person::query()
            ->with(['customFields' => function ($q) {
                $q->where('deleted_at', 0);
            }])
            ->where('deleted_at', 0)
            ->orderBy('name', 'ASC');

        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }
        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }

        if ($entity->supplier_flag !== null) {
            $query->where('is_supplier', $entity->supplier_flag === 1);
        }

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            if ($column !== 'all' && in_array($column, ['name', 'code'])) {
                $query->whereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(code) LIKE ?", ["%{$search}%"]);
                });
            }
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
            'active_people' => Person::query()->where('is_active', 'Y')->count(),
            'active_supps' => Person::query()->where('is_active', 'Y')->where('is_supplier', true)->count(),
            'active_custs' => Person::query()->where('is_active', 'Y')->where('is_supplier', false)->count(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(string $uuid, string $branchUuid, string $companyUuid): ?Person
    {
        return Person::query()
            ->with(['customFields' => function ($q) {
                $q->where('deleted_at', 0);
            }])
            ->where('uuid', trim($uuid))
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->where('deleted_at', 0)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function save(PersonEntity $entity): Person
    {
        DB::beginTransaction();
        try {
            $data = $entity->toArray();

            if (!empty($entity->uuid)) {
                $person = Person::query()
                    ->where('uuid', trim($entity->uuid))
                    ->where('branch_uuid', $entity->branch_uuid)
                    ->where('company_uuid', $entity->company_uuid)
                    ->first();
                
                if (!$person) {
                    $data['uuid'] = trim($entity->uuid);
                    $person = Person::query()->create($data);
                } else {
                    $person->update($data);
                }
            } else {
                $data['uuid'] = (string) Str::uuid();
                $person = Person::query()->create($data);
            }

            // Handle Dynamic Custom Fields
            if (!empty($entity->custom_fields)) {
                $person->customFields()->delete(); // Soft delete via relation

                foreach ($entity->custom_fields as $cf) {
                    $person->customFields()->create([
                        'uuid' => (string) Str::uuid(),
                        'field_key' => $cf['field_key'],
                        'field_value' => $cf['field_value'],
                        'field_type' => $cf['field_type'] ?? 'string',
                    ]);
                }
            }

            DB::commit();
            return $person->load('customFields');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(PersonEntity $entity): void
    {
        DB::beginTransaction();
        try {
            $person = Person::query()
                ->where('uuid', trim($entity->uuid))
                ->firstOrFail();

            // Cascade delete custom fields
            $person->customFields()->delete();
            $person->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function listForExport(PersonEntity $entity): array
    {
        $query = Person::query()
            ->with(['customFields' => function ($q) {
                $q->where('deleted_at', 0);
            }])
            ->where('deleted_at', 0)
            ->orderBy('name', 'ASC');

        if ($entity->supplier_flag !== null) {
            $query->where('is_supplier', $entity->supplier_flag === 1);
        }

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(code) LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->get()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getByCode(string $code): ?Person
    {
        return Person::query()
            ->whereRaw("UPPER(code) = ?", [strtoupper(trim($code))])
            ->where('deleted_at', 0)
            ->first();
    }
}
