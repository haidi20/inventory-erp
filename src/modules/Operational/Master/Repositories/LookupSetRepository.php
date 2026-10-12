<?php

namespace App\Modules\Operational\Master\Repositories;

use App\Modules\Operational\Master\Entities\LookupSetEntity;
use App\Modules\Operational\Master\Entities\LookupSetListResultEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueEntity;
use App\Modules\Operational\Master\Entities\LookupSetValueListResultEntity;
use App\Modules\Operational\Master\Models\LookupSet;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\TempLookup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * LookupSetRepository
 *
 * Implementasi repository untuk manajemen data referensi dan mekanisme staging.
 * Menangani set data, nilai-nilai terkait, dan sinkronisasi dari tabel temporer.
 */
class LookupSetRepository implements LookupSetRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function storeSet(LookupSetEntity $entity): LookupSetEntity
    {
        $uuid = trim((string) ($entity->uuid ?? '')) ?: (string) Str::uuid();
        $name = trim((string) ($entity->name ?? ''));
        $legacyId = $entity->id ?? $this->nextLegacySetId();
        $programCode = trim((string) ($entity->program_code ?? ''));

        if ($programCode === '') {
            $programCode = 'SET-'.strtoupper(substr(str_replace('-', '', $uuid), 0, 8));
        }

        $model = LookupSet::query()->create([
            'uuid' => $uuid,
            'id' => $legacyId,
            'lookup_set_uuid' => $legacyId,
            'code' => $programCode,
            'name' => $name,
            'source' => 'app',
            'program_code' => $programCode,
            'company_uuid' => trim((string) ($entity->company_uuid ?? '')),
            'description' => null,
            'user_can_edit_flag' => 'Y',
            'is_active' => true,
        ]);

        return LookupSetEntity::fromModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getSetByUuid(LookupSetEntity $entity): ?LookupSetEntity
    {
        $model = LookupSet::query()
            ->where('uuid', trim((string) $entity->uuid))
            ->when(! empty($entity->company_uuid), fn ($q) => $q->where('company_uuid', $entity->company_uuid))
            ->first();

        return $model ? LookupSetEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetById(LookupSetEntity $entity): ?LookupSetEntity
    {
        $rawId = trim((string) ($entity->id ?? ''));

        if ($rawId === '') {
            return null;
        }

        if (Str::isUuid($rawId)) {
            $model = LookupSet::query()
                ->where('uuid', $rawId)
                ->first();

            return $model ? LookupSetEntity::fromModel($model) : null;
        }

        $model = LookupSet::query()
            ->where('lookup_set_uuid', (int) $rawId)
            ->first();

        return $model ? LookupSetEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetByProgramCode(LookupSetEntity $entity): ?LookupSetEntity
    {
        $programCode = trim((string) ($entity->program_code ?? ''));

        if ($programCode === '') {
            return null;
        }

        $model = LookupSet::query()
            ->where('program_code', $programCode)
            ->when(! empty($entity->company_uuid), fn ($q) => $q->where(function ($w) use ($entity) {
                $w->where('company_uuid', $entity->company_uuid)
                    ->orWhere('company_uuid', '');
            }))
            ->first();

        return $model ? LookupSetEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetByName(LookupSetEntity $entity): ?LookupSetEntity
    {
        $name = trim((string) ($entity->name ?? ''));

        if ($name === '') {
            return null;
        }

        $model = LookupSet::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when(! empty($entity->company_uuid), fn ($q) => $q->where(function ($w) use ($entity) {
                $w->where('company_uuid', $entity->company_uuid)
                    ->orWhere('company_uuid', '');
            }))
            ->first();

        return $model ? LookupSetEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSet(LookupSetEntity $entity): LookupSetEntity
    {
        $set = LookupSet::query()
            ->where('uuid', trim((string) $entity->uuid))
            ->firstOrFail();
        $programCode = trim((string) ($entity->program_code ?? ''));

        $set->update([
            'name' => trim((string) ($entity->name ?? '')),
            'code' => $programCode !== '' ? $programCode : $set->code,
            'program_code' => $programCode !== '' ? $programCode : $set->program_code,
        ]);

        return LookupSetEntity::fromModel($set->fresh());
    }

    /**
     * {@inheritDoc}
     */
    public function destroySet(string $uuid, ?string $companyUuid = null): void
    {
        LookupSet::query()
            ->where('uuid', trim($uuid))
            ->when($companyUuid, fn ($q) => $q->where('company_uuid', $companyUuid))
            ->firstOrFail()
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getSetIndexData(LookupSetEntity $entity): LookupSetListResultEntity
    {
        $query = LookupSet::query();

        if (! empty($entity->company_uuid)) {
            $query->where(function ($q) use ($entity) {
                $q->where('company_uuid', $entity->company_uuid)
                    ->orWhere('company_uuid', '');
            });
        }

        $totalRecords = (clone $query)->count();
        if ($entity->filter_active !== null) {
            $query->where('is_active', (bool) $entity->filter_active);
        }

        if (! empty($entity->search)) {
            $s = strtolower($entity->search);
            $column = strtolower(trim((string) ($entity->search_column ?? 'all')));

            $query->where(function ($q) use ($s, $column) {
                if ($column === 'name') {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$s}%"]);

                    return;
                }

                if ($column === 'program_code') {
                    $q->whereRaw('LOWER(program_code) LIKE ?', ["%{$s}%"]);

                    return;
                }

                $q->whereRaw('LOWER(name) LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('LOWER(program_code) LIKE ?', ["%{$s}%"]);
            });
        }

        $totalFiltered = $query->count();

        $sortBy = strtolower(trim((string) ($entity->sort_by ?? 'name')));
        if (! in_array($sortBy, ['name', 'program_code'], true)) {
            $sortBy = 'name';
        }

        $sortDirection = strtolower(trim((string) ($entity->sort_direction ?? 'asc')));
        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $query->orderBy($sortBy, $sortDirection)
            ->orderBy('created_at', 'desc');

        $items = $query->offset($entity->start)
            ->limit($entity->length)
            ->get();

        return new LookupSetListResultEntity(
            items: $items->map(fn (LookupSet $set): LookupSetEntity => LookupSetEntity::fromModel($set))->all(),
            total: $totalRecords,
            filtered: $totalFiltered,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function storeValue(LookupSetValueEntity $entity): LookupSetValueEntity
    {
        DB::beginTransaction();
        try {
            $lookupSetUuid = $this->resolveLookupSetUuid($entity);
            // lookup_values.company_uuid NOT NULL: ambil dari entity, fallback
            // ke company pemilik lookup set (Section 30 multi-org).
            $companyUuid = $entity->company_uuid
                ?? LookupSet::query()->where('uuid', $lookupSetUuid)->value('company_uuid');
            $value = LookupValue::query()->create([
                'uuid' => $entity->uuid ?? (string) Str::uuid(),
                'lookup_set_uuid' => $lookupSetUuid,
                'program_code' => $entity->program_code1,
                'display_name' => trim((string) $entity->display_name),
                'value' => trim((string) ($entity->display_name ?? '')),
                'description' => $entity->description,
                'seq' => $entity->seq,
                'order_priority' => $entity->seq,
                'is_default' => false,
                'is_active' => strtoupper((string) $entity->active_flag) === 'Y',
                'active_flag' => $entity->active_flag,
                'price_up_beli' => (string) $entity->price_up_beli,
                'diskon_pilih' => (string) $entity->diskon_pilih,
                'persen1' => (string) $entity->persen1,
                'persen2' => (string) $entity->persen2,
                'company_uuid' => $companyUuid,
            ]);

            foreach ($entity->details as $detail) {
                $value->details()->create([
                    'uuid' => (string) Str::uuid(),
                    'idx' => (int) ($detail['idx'] ?? $detail['index'] ?? 0),
                    'value' => $detail['value'],
                    'company_uuid' => $entity->company_uuid,
                ]);
            }

            DB::commit();

            return LookupSetValueEntity::fromModel($value->load('details'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getValueByUuid(LookupSetValueEntity $entity): ?LookupSetValueEntity
    {
        $model = LookupValue::query()
            ->with(['details' => function ($q) {
                $q->where('deleted_at', 0);
            }])
            ->where('uuid', trim((string) $entity->uuid))
            ->first();

        return $model ? LookupSetValueEntity::fromModel($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function updateValue(LookupSetValueEntity $entity): LookupSetValueEntity
    {
        DB::beginTransaction();
        try {
            $value = LookupValue::query()
                ->where('uuid', trim((string) $entity->uuid))
                ->firstOrFail();
            $value->update([
                'program_code' => $entity->program_code1,
                'display_name' => trim((string) $entity->display_name),
                'value' => trim((string) ($entity->display_name ?? '')),
                'description' => $entity->description,
                'seq' => $entity->seq,
                'order_priority' => $entity->seq,
                'is_active' => strtoupper((string) $entity->active_flag) === 'Y',
                'active_flag' => $entity->active_flag,
                'price_up_beli' => (string) $entity->price_up_beli,
                'diskon_pilih' => (string) $entity->diskon_pilih,
                'persen1' => (string) $entity->persen1,
                'persen2' => (string) $entity->persen2,
            ]);

            // Sync details: delete and re-create as per Go implementation
            $value->details()->delete();

            foreach ($entity->details as $detail) {
                $value->details()->create([
                    'uuid' => (string) Str::uuid(),
                    'idx' => (int) ($detail['idx'] ?? $detail['index'] ?? 0),
                    'value' => $detail['value'],
                    'company_uuid' => $entity->company_uuid,
                ]);
            }

            DB::commit();

            return LookupSetValueEntity::fromModel($value->fresh()->load('details'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function destroyValue(string $uuid, ?string $companyUuid = null): void
    {
        LookupValue::query()
            ->where('uuid', trim($uuid))
            ->when($companyUuid, fn ($q) => $q->where('company_uuid', $companyUuid))
            ->firstOrFail()
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getValueIndexData(LookupSetValueEntity $entity): LookupSetValueListResultEntity
    {
        $baseQuery = LookupValue::query()
            ->where('deleted_at', 0);

        if ($entity->lookup_set_uuid) {
            $lookupSetId = trim((string) $entity->lookup_set_uuid);

            if (Str::isUuid($lookupSetId)) {
                $baseQuery->where('lookup_set_uuid', $lookupSetId);
            } else {
                $legacyId = (int) $lookupSetId;
                $baseQuery->whereHas('lookupSet', function ($q) use ($legacyId) {
                    $q->where('lookup_set_uuid', $legacyId);
                });
            }
        } elseif ($entity->lookup_set_uuid) {
            $baseQuery->where('lookup_set_uuid', $entity->lookup_set_uuid);
        }

        $totalRecords = (clone $baseQuery)->count();

        if (! empty($entity->search)) {
            $s = strtolower($entity->search);
            $baseQuery->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(display_name) LIKE ?', ["%{$s}%"])
                    ->orWhereRaw("LOWER(COALESCE(program_code, '')) LIKE ?", ["%{$s}%"])
                    ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", ["%{$s}%"]);
            });
        }

        $totalFiltered = (clone $baseQuery)->count();

        $items = $baseQuery->with('details')
            ->offset($entity->start)
            ->limit($entity->length)
            ->get();

        return new LookupSetValueListResultEntity(
            items: $items->map(fn (LookupValue $value): LookupSetValueEntity => LookupSetValueEntity::fromModel($value))->all(),
            total: $totalRecords,
            filtered: $totalFiltered,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getValuesBySetId(LookupSetEntity $entity): LookupSetValueListResultEntity
    {
        $query = LookupValue::query()
            ->with(['details' => function ($q) {
                $q->where('deleted_at', 0);
            }])
            ->where('active_flag', 'Y')
            ->orderBy('seq', 'ASC')
            ->orderBy('display_name', 'ASC');

        $rawSetId = trim((string) ($entity->uuid ?: $entity->id));
        if (Str::isUuid($rawSetId)) {
            $query->where('lookup_set_uuid', $rawSetId);
        } else {
            $legacyId = (int) $rawSetId;
            $query->whereHas('lookupSet', function ($q) use ($legacyId) {
                $q->where('lookup_set_uuid', $legacyId);
            });
        }

        $items = $query->get()->map(fn (LookupValue $value): LookupSetValueEntity => LookupSetValueEntity::fromModel($value))->all();

        return new LookupSetValueListResultEntity($items, count($items), count($items));
    }

    /**
     * {@inheritDoc}
     */
    public function getValuesBySetName(LookupSetEntity $entity): LookupSetValueListResultEntity
    {
        $items = LookupValue::query()
            ->join('lookup_sets', 'lookup_sets.uuid', '=', 'lookup_values.lookup_set_uuid')
            ->with(['details' => function ($q) {
                $q->where('deleted_at', 0);
            }])
            ->where('lookup_sets.name', trim((string) $entity->name))
            ->where('lookup_sets.deleted_at', 0)
            ->when(! empty($entity->company_uuid), fn ($q) => $q->where(function ($w) use ($entity) {
                $w->where('lookup_sets.company_uuid', $entity->company_uuid)
                    ->orWhere('lookup_sets.company_uuid', '');
            }))
            ->where('lookup_values.active_flag', 'Y')
            ->where('lookup_values.deleted_at', 0)
            ->orderBy('lookup_values.seq', 'ASC')
            ->orderBy('lookup_values.display_name', 'ASC')
            ->select('lookup_values.*')
            ->get()
            ->map(fn (LookupValue $value): LookupSetValueEntity => LookupSetValueEntity::fromModel($value))
            ->all();

        return new LookupSetValueListResultEntity($items, count($items), count($items));
    }

    /**
     * {@inheritDoc}
     */
    public function upsertValue(LookupSetValueEntity $entity): LookupSetValueEntity
    {
        $lookupSetUuid = $this->resolveLookupSetUuid($entity);
        $query = LookupValue::query()
            ->where('lookup_set_uuid', $lookupSetUuid)
            ->where('display_name', trim((string) $entity->display_name));

        if (! empty($entity->program_code1)) {
            $query = LookupValue::query()
                ->where('lookup_set_uuid', $lookupSetUuid)
                ->where('program_code', $entity->program_code1);
        }

        $existing = $query->first();

        if ($existing) {
            $entity->uuid = $existing->uuid;

            return $this->updateValue($entity);
        }

        return $this->storeValue($entity);
    }

    /**
     * {@inheritDoc}
     */
    public function syncTempToMaster(int|string $setId): void
    {
        $setIdString = trim((string) $setId);
        $temps = TempLookup::query()
            ->with('details')
            ->where('lookup_set_uuid', $setIdString)
            ->get();

        foreach ($temps as $t) {
            $entity = new LookupSetValueEntity(
                lookup_set_uuid: $t->lookup_set_uuid ?: $setIdString,
                program_code1: $t->program_code,
                display_name: $t->display_name,
                description: $t->description,
                seq: (int) $t->seq,
                active_flag: $t->active_flag ?? 'Y',
                price_up_beli: (float) $t->price_up_beli,
                diskon_pilih: (float) $t->diskon_pilih,
                persen1: (float) $t->persen1,
                persen2: (float) $t->persen2,
                details: $t->details->map(fn ($d) => ['idx' => $d->idx, 'value' => $d->value])->toArray()
            );

            $this->upsertValue($entity);
        }

        $this->clearTempValues($setIdString);
    }

    /**
     * {@inheritDoc}
     */
    public function clearTempValues(int|string $setId): void
    {
        DB::beginTransaction();
        try {
            $setIdString = trim((string) $setId);
            $tempUuids = TempLookup::query()
                ->where('lookup_set_uuid', $setIdString)
                ->pluck('uuid');

            DB::table('temp_lov_details')
                ->whereIn('temp_lov_uuid', $tempUuids)
                ->delete();

            TempLookup::query()
                ->where('lookup_set_uuid', $setIdString)
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function resolveLookupSetUuid(LookupSetValueEntity $entity): string
    {
        $lookupSetUuid = trim((string) ($entity->lookup_set_uuid ?? ''));
        if ($lookupSetUuid !== '') {
            return $lookupSetUuid;
        }

        $lookupSetId = trim((string) ($entity->lookup_set_uuid ?? ''));
        if ($lookupSetId === '') {
            throw new \InvalidArgumentException('lookup_set_uuid wajib diisi.');
        }

        if (Str::isUuid($lookupSetId)) {
            return $lookupSetId;
        }

        $set = LookupSet::query()
            ->where('lookup_set_uuid', (int) $lookupSetId)
            ->first();

        if ($set === null) {
            throw new \InvalidArgumentException('lookup_set_uuid tidak ditemukan.');
        }

        return $set->uuid;
    }

    private function nextLegacySetId(): int
    {
        $maxId = (int) LookupSet::query()->max('lookup_set_uuid');

        return $maxId > 0 ? $maxId + 1 : 1;
    }
}
