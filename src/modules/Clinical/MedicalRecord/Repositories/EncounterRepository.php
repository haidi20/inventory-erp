<?php

namespace App\Modules\Clinical\MedicalRecord\Repositories;

use App\Modules\Clinical\MedicalRecord\Entities\EncounterEntity;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EncounterRepository implements EncounterRepositoryInterface
{
    public function list(EncounterEntity $context): LengthAwarePaginator
    {
        return Encounter::query()
            ->with(['patient', 'doctor', 'items'])
            ->where('branch_uuid', $context->branch_uuid)
            ->where('company_uuid', $context->company_uuid)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function save(EncounterEntity $entity): EncounterEntity
    {
        DB::beginTransaction();
        try {
            $encounter = Encounter::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'registration_uuid' => $entity->registration_uuid,
                'patient_uuid' => $entity->patient_uuid,
                'doctor_uuid' => $entity->doctor_uuid,
                'complaint' => $entity->complaint,
                'diagnosis' => $entity->diagnosis,
                'notes' => $entity->notes,
                'status' => $entity->status,
            ]);

            $this->syncItems($encounter, $entity->items, $entity->branch_uuid, $entity->company_uuid);

            DB::commit();
            return $this->toEntity($encounter->load(['items']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(EncounterEntity $entity): EncounterEntity
    {
        DB::beginTransaction();
        try {
            $encounter = Encounter::query()
                ->where('uuid', $entity->uuid)
                ->where('branch_uuid', $entity->branch_uuid)
                ->where('company_uuid', $entity->company_uuid)
                ->firstOrFail();

            $payload = array_filter([
                'registration_uuid' => $entity->registration_uuid,
                'patient_uuid' => $entity->patient_uuid,
                'doctor_uuid' => $entity->doctor_uuid,
                'complaint' => $entity->complaint,
                'diagnosis' => $entity->diagnosis,
                'notes' => $entity->notes,
                'status' => $entity->status,
            ], fn ($value) => $value !== null);

            $encounter->update($payload);

            if (!empty($entity->items) || $entity->items !== []) {
                $this->syncItems($encounter, $entity->items, $entity->branch_uuid, $entity->company_uuid);
            }

            DB::commit();
            return $this->toEntity($encounter->load(['items']));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?EncounterEntity
    {
        $encounter = Encounter::query()
            ->with(['patient', 'doctor', 'items'])
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        return $encounter ? $this->toEntity($encounter) : null;
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid): void
    {
        DB::beginTransaction();
        try {
            Encounter::query()
                ->where('uuid', $uuid)
                ->where('branch_uuid', $branchUuid)
                ->where('company_uuid', $companyUuid)
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function syncItems(Encounter $encounter, array $items, string $branchUuid, string $companyUuid): void
    {
        if (empty($items)) {
            return;
        }

        $encounter->items()
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->delete();

        foreach ($items as $item) {
            $encounter->items()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $branchUuid,
                'company_uuid' => $companyUuid,
                'item_type' => $item['item_type'],
                'item_uuid' => $item['item_uuid'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => (float) $item['qty'] * (float) $item['price'],
            ]);
        }
    }

    private function toEntity(Encounter $encounter): EncounterEntity
    {
        return EncounterEntity::fromArray(array_merge($encounter->toArray(), [
            'items' => $encounter->items->toArray(),
        ]));
    }
}
