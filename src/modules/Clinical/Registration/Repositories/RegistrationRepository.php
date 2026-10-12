<?php

namespace App\Modules\Clinical\Registration\Repositories;

use App\Modules\Clinical\Registration\Entities\RegistrationEntity;
use App\Modules\Clinical\Registration\Models\Registration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationRepository implements RegistrationRepositoryInterface
{
    public function list(RegistrationEntity $context): LengthAwarePaginator
    {
        return Registration::query()
            ->with(['patient', 'doctor'])
            ->where('branch_uuid', $context->branch_uuid)
            ->where('company_uuid', $context->company_uuid)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function save(RegistrationEntity $entity): RegistrationEntity
    {
        DB::beginTransaction();
        try {
            DB::commit();
            $registration = Registration::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'queue_no' => $entity->queue_no,
                'patient_uuid' => $entity->patient_uuid,
                'doctor_uuid' => $entity->doctor_uuid,
                'status' => $entity->status,
                'registered_at' => $entity->registered_at,
                'notes' => $entity->notes,
            ]);
            return $this->toEntity($registration);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(RegistrationEntity $entity): RegistrationEntity
    {
        DB::beginTransaction();
        try {
            $registration = Registration::query()
                ->where('uuid', $entity->uuid)
                ->where('branch_uuid', $entity->branch_uuid)
                ->where('company_uuid', $entity->company_uuid)
                ->firstOrFail();

            $payload = array_filter([
                'queue_no' => $entity->queue_no,
                'patient_uuid' => $entity->patient_uuid,
                'doctor_uuid' => $entity->doctor_uuid,
                'status' => $entity->status,
                'registered_at' => $entity->registered_at,
                'notes' => $entity->notes,
            ], fn ($value) => $value !== null);

            $registration->update($payload);

            DB::commit();
            return $this->toEntity($registration);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?RegistrationEntity
    {
        $registration = Registration::query()
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();

        return $registration ? $this->toEntity($registration) : null;
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid): void
    {
        DB::beginTransaction();
        try {
            Registration::query()
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

    private function toEntity(Registration $registration): RegistrationEntity
    {
        return RegistrationEntity::fromArray($registration->toArray());
    }
}
