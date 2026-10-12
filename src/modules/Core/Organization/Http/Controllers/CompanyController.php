<?php

namespace App\Modules\Core\Organization\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Core\Organization\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(protected CompanyService $companyService) {}

    public function index(Request $request): JsonResponse
    {
        $entity = new CompanyEntity(
            search: $request->input('search', ''),
            search_column: $request->input('search_column', 'all'),
            per_page: (int) $request->input('per_page', 15),
            page: (int) $request->input('page', 1),
            filter_active: $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        );

        $items = $this->companyService->index($entity);
        return response()->json(['data' => $items]);
    }

    public function show(string $uuid): JsonResponse
    {
        $entity = new CompanyEntity(uuid: $uuid);
        $result = $this->companyService->getByUuid($entity);
        if (!$result) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        return response()->json(['data' => $result]);
    }

    public function store(Request $request): JsonResponse
    {
        $entity = CompanyEntity::fromArray($request->all());
        $result = $this->companyService->store($entity);
        return response()->json(['data' => $result], 201);
    }

    public function update(Request $request, string $company): JsonResponse
    {
        $entity = CompanyEntity::fromArray(array_merge($request->all(), ['uuid' => $company]));
        $result = $this->companyService->update($entity);
        return response()->json(['data' => $result]);
    }

    public function destroy(string $company): JsonResponse
    {
        $this->companyService->destroy(new CompanyEntity(uuid: $company));
        return response()->json(['message' => 'Deleted'], 200);
    }
}