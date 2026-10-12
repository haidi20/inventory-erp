<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\EmployeeEntity;
use App\Modules\Core\Identity\Entities\ActionResponseEntity;
use App\Modules\Operational\Master\Repositories\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use App\Modules\Core\Identity\Services\UserService;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private UserService $userService
    )
    {
    }

    public function index(EmployeeEntity $employeeEntity): array
    {
        return $this->employeeRepository->getPageData($employeeEntity);
    }

    public function store(EmployeeEntity $employeeEntity): EmployeeEntity
    {
        Validator::make($employeeEntity->toArray(), [
            'branch_uuid' => ['required', 'uuid'],
            'company_uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'fee_default' => ['nullable', 'numeric', 'min:0'],
            'sip_number' => ['nullable', 'string', 'max:100'],
            'str_number' => ['nullable', 'string', 'max:100'],
        ])->validate();

        if ($employeeEntity->user_uuid && !$this->userService->uuidExists($employeeEntity->user_uuid)) {
            throw ValidationException::withMessages(['user_uuid' => ['The selected user uuid is invalid.']]);
        }

        return $this->employeeRepository->save($employeeEntity);
    }

    public function update(EmployeeEntity $employeeEntity): EmployeeEntity
    {
        Validator::make($employeeEntity->toArray(), [
            'uuid' => ['required', 'uuid'],
            'branch_uuid' => ['required', 'uuid'],
            'company_uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'fee_default' => ['nullable', 'numeric', 'min:0'],
            'sip_number' => ['nullable', 'string', 'max:100'],
            'str_number' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable'],
        ])->validate();

        if ($employeeEntity->user_uuid && !$this->userService->uuidExists($employeeEntity->user_uuid)) {
            throw ValidationException::withMessages(['user_uuid' => ['The selected user uuid is invalid.']]);
        }

        return $this->employeeRepository->save($employeeEntity);
    }

    public function destroy(EmployeeEntity $employeeEntity): ActionResponseEntity
    {
        if (empty($employeeEntity->uuid)) {
            throw new \InvalidArgumentException('id pegawai tidak valid');
        }

        if ($this->employeeRepository->hasUser($employeeEntity)) {
            return new ActionResponseEntity(
                success: false,
                status: 'error',
                message: 'Pegawai masih terhubung ke akun user.'
            );
        }

        $this->employeeRepository->delete($employeeEntity);

        return new ActionResponseEntity(
            success: true,
            status: 'success',
            message: 'Pegawai dihapus.'
        );
    }

    public function getPageData(EmployeeEntity $entity): array
    {
        return $this->employeeRepository->getPageData($entity);
    }

    public function save(EmployeeEntity $entity): EmployeeEntity
    {
        return $this->employeeRepository->save($entity);
    }

    public function findByUuid(EmployeeEntity $entity): ?EmployeeEntity
    {
        return $this->employeeRepository->findByUuid($entity);
    }

    public function delete(EmployeeEntity $entity): void
    {
        $this->employeeRepository->delete($entity);
    }
}
