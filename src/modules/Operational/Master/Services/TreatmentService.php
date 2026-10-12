<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\TreatmentEntity;
use App\Modules\Operational\Master\Repositories\TreatmentRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TreatmentService
{
    public function __construct(private TreatmentRepositoryInterface $treatmentRepository)
    {
    }

    public function index(TreatmentEntity $treatmentEntity): array
    {
        return $this->treatmentRepository->getPageData($treatmentEntity);
    }

    public function store(TreatmentEntity $treatmentEntity): TreatmentEntity
    {
        Validator::make($treatmentEntity->toArray(), [
            'branch_uuid' => ['required', 'uuid'],
            'company_uuid' => ['required', 'uuid'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'doctor_fee' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        if ($this->treatmentRepository->getByCode($treatmentEntity)) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        return $this->treatmentRepository->save($treatmentEntity);
    }

    public function update(TreatmentEntity $treatmentEntity): TreatmentEntity
    {
        Validator::make($treatmentEntity->toArray(), [
            'uuid' => ['required', 'uuid'],
            'branch_uuid' => ['required', 'uuid'],
            'company_uuid' => ['required', 'uuid'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'doctor_fee' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable'],
        ])->validate();

        $existing = $this->treatmentRepository->getByCode($treatmentEntity);
        if ($existing && $existing->uuid !== $treatmentEntity->uuid) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        return $this->treatmentRepository->save($treatmentEntity);
    }

    public function destroy(TreatmentEntity $treatmentEntity): void
    {
        $this->treatmentRepository->delete($treatmentEntity);
    }

    public function getPageData(TreatmentEntity $entity): array
    {
        return $this->treatmentRepository->getPageData($entity);
    }

    public function save(TreatmentEntity $entity): TreatmentEntity
    {
        return $this->treatmentRepository->save($entity);
    }

    public function findByUuid(TreatmentEntity $entity): ?TreatmentEntity
    {
        return $this->treatmentRepository->findByUuid($entity);
    }
}
