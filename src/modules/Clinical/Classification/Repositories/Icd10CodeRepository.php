<?php

namespace App\Modules\Clinical\Classification\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Clinical\Classification\Entities\Icd10CodeEntity;
use App\Modules\Clinical\Classification\Models\Icd10Code;
use Illuminate\Support\Str;

class Icd10CodeRepository implements Icd10CodeRepositoryInterface
{
    public function getPageData(Icd10CodeEntity $entity): array
    {
        $query = Icd10Code::query()
            ->where('deleted_at', 0)
            ->orderBy('code', 'ASC');

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(name_uuid) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(name_en) LIKE ?', ["%{$search}%"]);
            });
        }

        if (!empty($entity->category)) {
            $query->where('category', $entity->category);
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        return [
            'items'    => array_map(fn ($m) => Icd10CodeEntity::fromModel($m), $paginator->items()),
            'total'    => $paginator->total(),
            'page'     => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    public function findByUuid(string $uuid): ?Icd10CodeEntity
    {
        $model = Icd10Code::where('uuid', $uuid)->where('deleted_at', 0)->first();
        return $model ? Icd10CodeEntity::fromModel($model) : null;
    }

    public function findByCode(string $code): ?Icd10CodeEntity
    {
        $model = Icd10Code::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
            ->where('deleted_at', 0)
            ->first();
        return $model ? Icd10CodeEntity::fromModel($model) : null;
    }

    public function save(Icd10CodeEntity $entity): Icd10CodeEntity
    {
        DB::beginTransaction();
        try {
            $model = Icd10Code::updateOrCreate(
                ['uuid' => $entity->uuid ?? (string) Str::uuid()],
                [
                    'code'      => strtoupper(trim($entity->code)),
                    'name_uuid' => trim($entity->name_uuid),
                    'name_en'   => $entity->name_en ? trim($entity->name_en) : null,
                    'category'  => $entity->category,
                    'updated_by' => $entity->actor_uuid,
                ]
            );

            DB::commit();
            return Icd10CodeEntity::fromModel($model->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $uuid, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            Icd10Code::where('uuid', $uuid)->firstOrFail()->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
