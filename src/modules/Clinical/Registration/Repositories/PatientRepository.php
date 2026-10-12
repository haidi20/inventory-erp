<?php

namespace App\Modules\Clinical\Registration\Repositories;

use App\Modules\Clinical\Registration\Entities\PatientEntity;
use App\Modules\Clinical\Registration\Models\Patient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * PatientRepository
 * 
 * Implementasi repository untuk manajemen data Pasien.
 * Menangani pendaftaran, pencarian rekam medis, dan statistik kepesertaan asuransi.
 */
class PatientRepository implements PatientRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(PatientEntity $entity): array
    {
        $query = Patient::query()->orderBy('name', 'ASC');
        if (!empty($entity->company_uuid)) { $query->where('company_uuid', $entity->company_uuid); }
        if (!empty($entity->branch_uuid)) { $query->where('branch_uuid', $entity->branch_uuid); }

        if (!empty($entity->search)) {
            $search = strtolower($entity->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(medical_record_no) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(phone) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(bpjs_number) LIKE ?", ["%{$search}%"]);
            });
        }

        if (!empty($entity->gender_filter)) {
            $query->where('gender', $entity->gender_filter);
        }

        $paginator = $query->paginate($entity->per_page, ['*'], 'page', $entity->page);

        // Stats scoped by company/branch
        $baseStats = Patient::query();
        if (!empty($entity->company_uuid)) { $baseStats->where('company_uuid', $entity->company_uuid); }
        if (!empty($entity->branch_uuid)) { $baseStats->where('branch_uuid', $entity->branch_uuid); }
        $totalPatients = (clone $baseStats)->count();
        $activePatients = (clone $baseStats)->where('is_active', true)->count();
        $insuredPatients = (clone $baseStats)->whereNotNull('bpjs_number')->where('bpjs_number', '!=', '')->count();

        return [
            'items' => array_map(fn($m) => PatientEntity::fromModel($m), $paginator->items()),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'page' => $paginator->currentPage(),
            'search' => $entity->search,
            'gender' => $entity->gender_filter,
            'totalPatients' => $totalPatients,
            'activePatients' => $activePatients,
            'insuredPatients' => $insuredPatients
        ];
    }

    /**
     * @inheritDoc
     */

    public function findByUuid(PatientEntity $entity): ?PatientEntity
    {
        $model = Patient::query()
            ->where('uuid', $entity->uuid)
            ->where('deleted_at', 0)
            ->first();
        return $model ? PatientEntity::fromModel($model) : null;
    }

    public function create(PatientEntity $entity): PatientEntity
    {
        DB::beginTransaction();
        try {
            $model = Patient::query()->create([
                'uuid' => $entity->uuid ?? (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'medical_record_no' => trim($entity->medical_record_no),
                'name' => trim($entity->name),
                'birth_date' => $entity->birth_date,
                'gender' => $entity->gender,
                'phone' => $entity->phone,
                'bpjs_number' => $entity->bpjs_number,
                'is_active' => $entity->is_active ?? true,
                'created_by' => $entity->actor_uuid,
            ]);

            DB::commit();
            return PatientEntity::fromModel($model);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function listActiveByCompany(string $companyUuid): array
    {
        return Patient::query()
            ->where('company_uuid', $companyUuid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => PatientEntity::fromModel($model))
            ->all();
    }

    /**
     * @inheritDoc
     */
    public function update(PatientEntity $entity): PatientEntity
    {
        DB::beginTransaction();
        try {
            $patient = Patient::query()->findOrFail(trim($entity->uuid));
            $patient->update([
                'company_uuid' => $entity->company_uuid,
                'branch_uuid' => $entity->branch_uuid,
                'medical_record_no' => trim($entity->medical_record_no),
                'name' => trim($entity->name),
                'birth_date' => $entity->birth_date,
                'gender' => $entity->gender,
                'phone' => $entity->phone,
                'bpjs_number' => $entity->bpjs_number,
                'is_active' => $entity->is_active ?? true,
                'updated_by' => $entity->actor_uuid,
            ]);
            DB::commit();
            return PatientEntity::fromModel($patient);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getByUuid(PatientEntity $entity): ?PatientEntity
    {
        $model = Patient::query()->where('uuid', $entity->uuid)->first();
        return $model ? PatientEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */

    public function save(PatientEntity $entity): PatientEntity
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return $entity->uuid ? $this->update($entity) : $this->create($entity);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(PatientEntity $entity): void
    {
        DB::beginTransaction();
        try {
            Patient::query()->where('uuid', $entity->uuid)->firstOrFail()->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function updateSatusehatInfo(string $nik, string $satusehatId): void
    {
        DB::beginTransaction();
        try {
            Patient::query()
                ->where('phone', $nik) // Fallback as per BE logic or specific NIK field if exists
                ->orWhere('bpjs_number', $nik)
                ->update([
                    'satusehat_uuid' => $satusehatId,
                    'satusehat_synced_at' => time(),
                ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateIhsNumberByNik(string $nik, string $ihsNumber): void
    {
        Patient::query()
            ->where('nik', $nik)
            ->update([
                'ihs_number' => $ihsNumber,
                'satusehat_last_sync' => time(),
            ]);
    }
}
