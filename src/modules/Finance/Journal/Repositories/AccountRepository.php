<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountRepository implements AccountRepositoryInterface
{
    public function list(AccountEntity $context): LengthAwarePaginator
    {
        $search = trim((string) ($context->search ?? ''));
        $searchColumn = in_array($context->search_column, ['code', 'name', 'type'], true)
            ? $context->search_column
            : 'all';
        $sortColumn = in_array($context->sort_by, ['code', 'name', 'type', 'is_active'], true)
            ? $context->sort_by
            : 'code';
        $sortDirection = strtolower((string) $context->sort_direction) === 'desc' ? 'desc' : 'asc';

        $query = Account::query()
            ->where('branch_uuid', $context->branch_uuid)
            ->where('company_uuid', $context->company_uuid)
            ->when($search !== '', function ($builder) use ($search, $searchColumn): void {
                $builder->where(function ($nested) use ($search, $searchColumn): void {
                    if ($searchColumn === 'all') {
                        $nested->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('type', 'like', "%{$search}%");

                        return;
                    }

                    $nested->where($searchColumn, 'like', "%{$search}%");
                });
            });

        return $query
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($context->per_page, ['*'], 'page', $context->page);
    }

    public function save(AccountEntity $entity): Account
    {
        DB::beginTransaction();
        try {
            DB::commit();

            return Account::query()->create([
                'uuid' => (string) Str::uuid(),
                'branch_uuid' => $entity->branch_uuid,
                'company_uuid' => $entity->company_uuid,
                'code' => $entity->code,
                'name' => $entity->name,
                'type' => $entity->type,
                'parent_uuid' => $entity->parent_uuid,
                'is_active' => $entity->is_active,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(AccountEntity $entity): Account
    {
        DB::beginTransaction();
        try {
            $account = Account::query()
                ->where('uuid', $entity->uuid)
                ->where('branch_uuid', $entity->branch_uuid)
                ->where('company_uuid', $entity->company_uuid)
                ->firstOrFail();

            $payload = array_filter([
                'code' => $entity->code,
                'name' => $entity->name,
                'type' => $entity->type,
                'parent_uuid' => $entity->parent_uuid,
                'is_active' => $entity->is_active,
            ], fn ($value) => $value !== null);

            $account->update($payload);

            DB::commit();

            return $account;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find(string $uuid, string $branchUuid, string $companyUuid): ?Account
    {
        return Account::query()
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->first();
    }

    public function existsInContext(string $uuid, string $branchUuid, string $companyUuid): bool
    {
        return Account::query()
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->exists();
    }

    public function codeExists(string $code, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): bool
    {
        return Account::query()
            ->where('code', $code)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }

    public function findParentByCode(string $code): ?Account
    {
        return Account::query()
            ->where('code', $code)
            ->whereNull('parent_uuid')
            ->first();
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid): void
    {
        Account::query()
            ->where('uuid', $uuid)
            ->where('branch_uuid', $branchUuid)
            ->where('company_uuid', $companyUuid)
            ->firstOrFail()
            ->delete();
    }
}
