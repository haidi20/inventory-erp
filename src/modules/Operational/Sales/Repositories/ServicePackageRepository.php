<?php

namespace App\Modules\Operational\Sales\Repositories;

use App\Modules\Operational\Master\Models\ServicePackage;
use App\Modules\Operational\Master\Models\ServicePackageItem;
use App\Modules\Operational\Sales\Entities\ServicePackageEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServicePackageRepository implements ServicePackageRepositoryInterface
{
    public function list(ServicePackageEntity $context): LengthAwarePaginator
    {
        return ServicePackage::query()
            ->with('items')
            ->where('branch_uuid', $context->branch_uuid)
            ->where('company_uuid', $context->company_uuid)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function save(ServicePackageEntity $entity): ServicePackage
    {
        DB::beginTransaction();
        try {
            $package = ServicePackage::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'code' => $entity->code,
                'name' => $entity->name,
                'price' => $entity->price,
                'is_active' => $entity->is_active,
            ]);

            $this->syncItems($package, $entity->items, $entity->branch_uuid, $entity->company_uuid);

            DB::commit();
            return $package->load('items');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(ServicePackageEntity $entity): ServicePackage
    {
        DB::beginTransaction();
        try {
            $package = ServicePackage::query()
                ->where('uuid', $entity->uuid)
                ->where('branch_uuid', $entity->branch_uuid)
                ->where('company_uuid', $entity->company_uuid)
                ->firstOrFail();

            $payload = array_filter([
                'code' => $entity->code,
                'name' => $entity->name,
                'price' => $entity->price,
                'is_active' => $entity->is_active,
            ], fn ($value) => $value !== null);

            $package->update($payload);

            if ($entity->items !== []) {
                $this->syncItems($package, $entity->items, $entity->branch_uuid, $entity->company_uuid);
            }

            DB::commit();
            return $package->load('items');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?ServicePackage
    {
        return ServicePackage::query()
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid): void
    {
        DB::beginTransaction();
        try {
            ServicePackage::query()
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
        return ServicePackage::query()
            ->where('code', $code)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }

    protected function syncItems(ServicePackage $package, array $items, string $branchUuid, string $companyUuid): void
    {
        if (empty($items)) {
            return;
        }

        ServicePackageItem::query()
            ->where('service_package_uuid', $package->uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->delete();

        foreach ($items as $item) {
            $package->items()->create(array_merge($item, [
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $branchUuid,
                'company_uuid' => $companyUuid,
            ]));
        }
    }
}
