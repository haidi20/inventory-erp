<?php

namespace App\Modules\Core\Organization\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Support\Str;

/**
 * CompanyRepository
 * 
 * Implementasi repository untuk manajemen data Perusahaan (Company).
 * Mengatur logika akses data, pencarian, dan persistence untuk model Company.
 */
class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(CompanyEntity $entity): array
    {
        $query = Company::query()
            ->where('deleted_at', 0)
            ->orderBy('name', 'ASC');

        if ($entity->filter_active !== null) {
            $query->where('is_active', (bool) $entity->filter_active);
        }

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $column = strtolower($entity->search_column ?? 'all');

            if ($column !== 'all' && in_array($column, ['name', 'email', 'phone'])) {
                $query->whereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
            } else {
                $query->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"]);
            }
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items' => array_map(fn($m) => CompanyEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'search' => $entity->search,
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(string $uuid): ?CompanyEntity
    {
        $model = Company::query()
            ->where('uuid', trim($uuid))
            ->where('deleted_at', 0)
            ->first();

        return $model ? CompanyEntity::fromModel($model) : null;
    }

    public function findFirstActive(): ?CompanyEntity
    {
        $model = Company::query()
            ->where('is_active', true)
            ->where('deleted_at', 0)
            ->orderBy('id')
            ->first();

        return $model ? CompanyEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(CompanyEntity $entity): CompanyEntity
    {
        DB::beginTransaction();
        try {
            $company = Company::updateOrCreate(
                [
                    'uuid' => $entity->uuid ?? (string) Str::uuid(),
                ],
                [
                    'name' => trim($entity->name),
                    'clinic_type' => $entity->clinic_type,
                    'registration_number' => $entity->registration_number,
                    'address' => $entity->address,
                    'phone' => $entity->phone,
                    'email' => $entity->email,
                    'website' => $entity->website,
                    'timezone' => $entity->timezone ?? 'Asia/Makassar',
                    'is_active' => $entity->is_active ?? true,
                ]
            );

            DB::commit();
            return CompanyEntity::fromModel($company->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid): void
    {
        DB::beginTransaction();
        try {
            Company::query()
                ->where('uuid', trim($uuid))
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
