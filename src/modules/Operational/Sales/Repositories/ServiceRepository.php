<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Master\Models\Service;
use App\Modules\Operational\Sales\Entities\ServiceEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function list(ServiceEntity $context): LengthAwarePaginator
    {
        return Service::query()
            ->where('branch_uuid', $context->branch_uuid)
            ->where('company_uuid', $context->company_uuid)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function save(ServiceEntity $entity): Service
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return Service::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'code' => $entity->code,
                'name' => $entity->name,
                'duration_minutes' => $entity->duration_minutes,
                'price' => $entity->price,
                'doctor_fee' => $entity->doctor_fee,
                'is_active' => $entity->is_active,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(ServiceEntity $entity): Service
    {
        DB::beginTransaction();
        try {
            $service = Service::query()
                ->where('uuid', $entity->uuid)
                ->where('branch_uuid', $entity->branch_uuid)
                ->where('company_uuid', $entity->company_uuid)
                ->firstOrFail();

            $payload = array_filter([
                'code' => $entity->code,
                'name' => $entity->name,
                'duration_minutes' => $entity->duration_minutes,
                'price' => $entity->price,
                'doctor_fee' => $entity->doctor_fee,
                'is_active' => $entity->is_active,
            ], fn ($value) => $value !== null);

            $service->update($payload);

            DB::commit();
            return $service;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?Service
    {
        return Service::query()
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid): void
    {
        DB::beginTransaction();
        try {
            Service::query()
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

    public function codeExists(string $code, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): bool
    {
        return Service::query()
            ->where('code', $code)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }
}
