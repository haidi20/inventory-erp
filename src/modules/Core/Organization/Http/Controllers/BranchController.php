<?php

namespace App\Modules\Core\Organization\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(protected BranchService $branchService) {}

    public function index(Request $request): JsonResponse
    {
        $entity = new BranchEntity(
            search:       $request->input('search', ''),
            per_page:     (int) $request->input('per_page', 15),
            page:         (int) $request->input('page', 1),
            company_uuid: $request->user()?->company_uuid,
            filter_active: $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        );
        $items = $this->branchService->index($entity);
        return response()->json(['data' => $items]);
    }

    public function show(string $uuid): JsonResponse
    {
        $entity = new BranchEntity(uuid: $uuid);
        $result = $this->branchService->show($entity);
        if (!$result) {
            return response()->json(['message' => 'Branch not found'], 404);
        }
        return response()->json(['data' => $result]);
    }

    public function store(Request $request): JsonResponse
    {
        $entity = BranchEntity::fromArray($request->all());
        $result = $this->branchService->save($entity);
        return response()->json(['data' => $result], 201);
    }

    public function update(Request $request, string $branch): JsonResponse
    {
        $entity = BranchEntity::fromArray(array_merge($request->all(), ['uuid' => $branch]));
        $result = $this->branchService->save($entity);
        return response()->json(['data' => $result]);
    }

    public function destroy(string $branch): JsonResponse
    {
        $result = $this->branchService->destroy(new BranchEntity(uuid: $branch));
        if (is_array($result) && ($result['status'] ?? '') === 'error') {
            return response()->json(['message' => $result['message']], 422);
        }
        return response()->json(['message' => 'Deleted'], 200);
    }
}
