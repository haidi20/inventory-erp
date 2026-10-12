<?php

namespace App\Modules\Finance\Journal\Services;

use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Repositories\AccountRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function __construct(
        protected AccountRepositoryInterface $repository
    ) {}

    public function listAccounts(AccountEntity $context)
    {
        return $this->repository->list($context);
    }

    public function createAccount(AccountEntity $entity): mixed
    {
        return $this->repository->save($entity);
    }

    public function getAccount(string $uuid, string $branchUuid, string $companyUuid): mixed
    {
        return $this->repository->find($uuid, $branchUuid, $companyUuid);
    }

    public function validateBeforeSave(array $data, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): void
    {
        $errors = [];

        if (isset($data['code']) && $this->repository->codeExists($data['code'], $branchUuid, $companyUuid, $ignoreUuid)) {
            $errors['code'] = ['The code has already been taken.'];
        }

        if (! empty($data['parent_uuid']) && ! $this->repository->existsInContext($data['parent_uuid'], $branchUuid, $companyUuid)) {
            $errors['parent_uuid'] = ['The selected parent uuid is invalid.'];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function updateAccount(AccountEntity $entity): mixed
    {
        return $this->repository->update($entity);
    }

    public function resolveAccountType(int $code, string $direction): string
    {
        if ($direction === 'DEBET') {
            return $code >= 70000 ? 'expense' : 'asset';
        }

        if ($code >= 50000 && $code < 60000) {
            return 'equity';
        }

        if ($code >= 60000 && $code < 70000) {
            return 'revenue';
        }

        return 'liability';
    }

    public function createParentAccount(string $code, string $name, string $direction): mixed
    {
        return $this->repository->save(new AccountEntity(
            code: $code,
            name: $name,
            type: $this->resolveAccountType((int) $code, $direction),
            parent_uuid: null,
            is_header: true,
            is_active: true,
        ));
    }

    public function updateParentAccount(string $uuid, string $code, string $name, string $direction): mixed
    {
        return $this->repository->update(new AccountEntity(
            uuid: $uuid,
            code: $code,
            name: $name,
            type: $this->resolveAccountType((int) $code, $direction),
        ));
    }

    public function createSubAccount(string $parentCode, string $code, string $name, string $direction): mixed
    {
        $parent = $this->repository->findParentByCode($parentCode);

        return $this->repository->save(new AccountEntity(
            code: $code,
            name: $name,
            type: $parent?->type ?? ($direction === 'DEBET' ? 'asset' : 'liability'),
            parent_uuid: $parent?->uuid,
            is_header: false,
            is_active: true,
        ));
    }

    public function updateSubAccount(string $uuid, string $code, string $name): mixed
    {
        return $this->repository->update(new AccountEntity(
            uuid: $uuid,
            code: $code,
            name: $name,
        ));
    }

    public function deleteAccount(string $uuid, string $branchUuid, string $companyUuid): void
    {
        $this->repository->delete($uuid, $branchUuid, $companyUuid);
    }
}
