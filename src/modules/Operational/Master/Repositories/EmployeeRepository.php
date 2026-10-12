<?php

namespace App\Modules\Operational\Master\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Operational\Master\Models\Employee;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function getPageData(EmployeeEntity $employeeEntity): array
    {
        $query = Employee::query()
            ->with('user')
            ->where('branch_uuid', $employeeEntity->branch_uuid)
            ->when($employeeEntity->company_uuid, fn ($builder) => $builder->where('company_uuid', $employeeEntity->company_uuid))
            ->orderBy('name');

        if (($employeeEntity->search ?? '') !== '') {
            $search = $employeeEntity->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('specialization', 'LIKE', "%{$search}%")
                    ->orWhere('sip_number', 'LIKE', "%{$search}%")
                    ->orWhere('nik', 'LIKE', "%{$search}%");
            });
        }

        if (($employeeEntity->filter_role ?? '') !== '') {
            $query->where('role', $employeeEntity->filter_role);
        }

        $paginator = $query->paginate($employeeEntity->per_page ?? 25)->withQueryString();

        return [
            'items' => array_map(fn($m) => EmployeeEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'totalAll' => Employee::query()
                ->where('branch_uuid', $employeeEntity->branch_uuid)
                ->when($employeeEntity->company_uuid, fn ($builder) => $builder->where('company_uuid', $employeeEntity->company_uuid))
                ->count(),
            'totalDoctors' => Employee::query()
                ->where('branch_uuid', $employeeEntity->branch_uuid)
                ->when($employeeEntity->company_uuid, fn ($builder) => $builder->where('company_uuid', $employeeEntity->company_uuid))
                ->where('role', 'dokter')
                ->count(),
            'activeEmployees' => Employee::query()
                ->where('branch_uuid', $employeeEntity->branch_uuid)
                ->when($employeeEntity->company_uuid, fn ($builder) => $builder->where('company_uuid', $employeeEntity->company_uuid))
                ->where('is_active', true)
                ->count(),
        ];
    }

    public function save(EmployeeEntity $employeeEntity): EmployeeEntity
    {
        DB::beginTransaction();
        try {
            $employee = Employee::updateOrCreate(
                [
                    'uuid' => $employeeEntity->uuid ?? (string) \Illuminate\Support\Str::uuid(),
                    'branch_uuid' => $employeeEntity->branch_uuid,
                    'company_uuid' => $employeeEntity->company_uuid,
                ],
                [
                    'nik' => $employeeEntity->nik,
                    'name' => $employeeEntity->name,
                    'role' => $employeeEntity->role,
                    'specialization' => $employeeEntity->specialization,
                    'fee_default' => $employeeEntity->fee_default ?? 0.0,
                    'sip_number' => $employeeEntity->sip_number,
                    'str_number' => $employeeEntity->str_number,
                    'ihs_practitioner_id' => $employeeEntity->ihs_practitioner_id,
                    'is_active' => $employeeEntity->is_active ?? true,
                    'company_uuid' => $employeeEntity->company_uuid,
                ]
            );

            DB::commit();
            return EmployeeEntity::fromModel($employee->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findByUuid(EmployeeEntity $entity): ?EmployeeEntity
    {
        $model = Employee::query()
            ->where('uuid', $entity->uuid)
            ->where('branch_uuid', $entity->branch_uuid)
            ->when($entity->company_uuid, fn ($builder) => $builder->where('company_uuid', $entity->company_uuid))
            ->first();

        return $model ? EmployeeEntity::fromModel($model) : null;
    }

    public function updatePractitionerId(string $nik, string $practitionerId): void
    {
        Employee::query()
            ->where('nik', $nik)
            ->update([
                'ihs_practitioner_id' => $practitionerId,
                'satusehat_last_sync' => time(),
            ]);
    }

    public function hasUser(EmployeeEntity $employeeEntity): bool
    {
        $employee = Employee::query()
            ->where('uuid', $employeeEntity->uuid)
            ->where('branch_uuid', $employeeEntity->branch_uuid)
            ->when($employeeEntity->company_uuid, fn ($builder) => $builder->where('company_uuid', $employeeEntity->company_uuid))
            ->firstOrFail();
            
        return (bool) $employee->user_uuid;
    }

    public function delete(EmployeeEntity $employeeEntity): void
    {
        DB::beginTransaction();
        try {
            Employee::query()
                ->where('uuid', $employeeEntity->uuid)
                ->where('branch_uuid', $employeeEntity->branch_uuid)
                ->when($employeeEntity->company_uuid, fn ($builder) => $builder->where('company_uuid', $employeeEntity->company_uuid))
                ->firstOrFail()
                ->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
