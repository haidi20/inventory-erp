<?php

namespace App\Modules\Finance\Journal\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Finance\Journal\Entities\AccountEntity;
use App\Modules\Finance\Journal\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $service
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $context = AccountEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
            'search' => trim((string) $request->query('search', '')),
            'search_column' => (string) $request->query('search_column', 'all'),
            'page' => max(1, (int) $request->query('page', 1)),
            'per_page' => min(100, max(1, (int) $request->query('per_page', 10))),
            'sort_by' => (string) $request->query('sort_by', 'code'),
            'sort_direction' => (string) $request->query('sort_direction', 'asc'),
        ]);

        return $this->apiResource($this->service->listAccounts($context));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_uuid' => 'nullable|uuid',
            'is_active' => 'boolean',
        ]);

        $this->service->validateBeforeSave($data, $branchUuid, $companyUuid);

        $entity = AccountEntity::fromArray(array_merge($data, [
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $account = $this->service->createAccount($entity);

        $this->audit('account.create', $account, null, $account->toArray());

        return $this->apiResource($account);
    }

    public function show(Request $request, string $account)
    {
        $user = $request->user();

        $result = $this->service->getAccount($account, current_branch_uuid(), current_company_uuid());

        abort_unless($result, 404);

        return $this->apiResource($result);
    }

    public function update(Request $request, string $account)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getAccount($account, $branchUuid, $companyUuid);
        abort_unless($existing, 404);

        $before = $existing->toArray();
        $data = $request->validate([
            'code' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:asset,liability,equity,revenue,expense',
            'parent_uuid' => 'nullable|uuid',
            'is_active' => 'boolean',
        ]);

        $this->service->validateBeforeSave($data, $branchUuid, $companyUuid, $account);

        $entity = AccountEntity::fromArray(array_merge($data, [
            'uuid' => $account,
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $updated = $this->service->updateAccount($entity);

        $this->audit('account.update', $updated, $before, $updated->toArray());

        return $this->apiResource($updated);
    }

    public function destroy(Request $request, string $account)
    {
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $existing = $this->service->getAccount($account, $branchUuid, $companyUuid);

        abort_unless($existing, 404);

        $before = $existing->toArray();
        $this->service->deleteAccount($account, $branchUuid, $companyUuid);
        $this->audit('account.delete', $existing, $before, null);

        return response()->noContent();
    }
}
