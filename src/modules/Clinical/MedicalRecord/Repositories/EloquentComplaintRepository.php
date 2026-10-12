<?php

namespace App\Modules\Clinical\MedicalRecord\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Clinical\MedicalRecord\Models\Complaint;
use App\Modules\Clinical\MedicalRecord\Entities\ComplaintEntity;

class EloquentComplaintRepository implements ComplaintRepositoryInterface
{
    public function paginate(array $filters = [])
    {
        return Complaint::query()
            ->when($filters['company_uuid'] ?? null, function ($query, $companyUuid) {
                $query->where('company_uuid', $companyUuid);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('subject', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function save(ComplaintEntity $entity): ComplaintEntity
    {
        DB::beginTransaction();
        try {
            DB::commit();
            $complaint = Complaint::updateOrCreate(
                ['uuid' => $entity->uuid],
                $entity->toArray()
            );
            return ComplaintEntity::fromArray($complaint->toArray());
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, ?string $companyUuid = null): ?ComplaintEntity
    {
        $complaint = Complaint::query()
            ->where('uuid', $uuid)
            ->when($companyUuid, function ($query, $companyUuid) {
                $query->where('company_uuid', $companyUuid);
            })
            ->first();

        return $complaint ? ComplaintEntity::fromArray($complaint->toArray()) : null;
    }

    public function delete(string $uuid, ?string $companyUuid = null): bool
    {
        DB::beginTransaction();
        try {
            $complaint = Complaint::query()
                ->where('uuid', $uuid)
                ->when($companyUuid, function ($query, $companyUuid) {
                    $query->where('company_uuid', $companyUuid);
                })
                ->first();
            DB::commit();
            return $complaint ? $complaint->delete() : false;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

