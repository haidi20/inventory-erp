<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;
use App\Modules\Operational\Master\Repositories\TreatmentPackageRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TreatmentPackageService
{
    public function __construct(private TreatmentPackageRepositoryInterface $treatmentPackageRepository)
    {
    }

    public function index(TreatmentPackageEntity $treatmentPackageEntity): array
    {
        return $this->treatmentPackageRepository->getPageData($treatmentPackageEntity);
    }

    public function store(TreatmentPackageEntity $treatmentPackageEntity): TreatmentPackageEntity
    {
        Validator::make($treatmentPackageEntity->toArray(), [
            'branch_uuid' => ['required', 'uuid'],
            'company_uuid' => ['required', 'uuid'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        if ($this->treatmentPackageRepository->getByCode($treatmentPackageEntity)) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        return $this->treatmentPackageRepository->save($treatmentPackageEntity);
    }

    public function update(TreatmentPackageEntity $treatmentPackageEntity): TreatmentPackageEntity
    {
        Validator::make($treatmentPackageEntity->toArray(), [
            'uuid' => ['required', 'uuid'],
            'branch_uuid' => ['required', 'uuid'],
            'company_uuid' => ['required', 'uuid'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable'],
        ])->validate();

        $existing = $this->treatmentPackageRepository->getByCode($treatmentPackageEntity);
        if ($existing && $existing->uuid !== $treatmentPackageEntity->uuid) {
            throw ValidationException::withMessages(['code' => ['The code has already been taken.']]);
        }

        return $this->treatmentPackageRepository->save($treatmentPackageEntity);
    }

    public function destroy(TreatmentPackageEntity $treatmentPackageEntity): void
    {
        $this->treatmentPackageRepository->delete($treatmentPackageEntity);
    }

    public function getPageData(TreatmentPackageEntity $entity): array
    {
        return $this->treatmentPackageRepository->getPageData($entity);
    }

    public function save(TreatmentPackageEntity $entity): TreatmentPackageEntity
    {
        return $this->treatmentPackageRepository->save($entity);
    }

    public function findByUuid(TreatmentPackageEntity $entity): ?TreatmentPackageEntity
    {
        return $this->treatmentPackageRepository->findByUuid($entity);
    }
}
