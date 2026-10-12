<?php

namespace App\Modules\Clinical\Classification\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\Classification\Entities\Icd10CodeEntity;
use App\Modules\Clinical\Classification\Services\Icd10CodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Icd10CodeController extends Controller
{
    public function __construct(
        private readonly Icd10CodeService $service,
    ) {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $entity = new Icd10CodeEntity(
            search:   $request->string('search')->trim()->toString(),
            category: $request->string('category')->trim()->toString() ?: null,
            page:     (int) $request->input('page', 1),
            per_page: (int) $request->input('per_page', 15),
        );

        $result = $this->service->getPageData($entity);

        return $this->apiResource(collect($result['items']))->additional([
            'meta' => [
                'total'    => $result['total'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $entity = $this->service->findByUuid($uuid);
        abort_if(!$entity, 404, 'ICD-10 code tidak ditemukan.');
        return $this->apiResource($entity);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'      => 'required|string|max:10',
            'name_uuid' => 'required|string|max:255',
            'name_en'   => 'nullable|string|max:255',
            'category'  => 'nullable|string|max:100',
        ]);

        $entity = new Icd10CodeEntity(
            code:      $data['code'],
            name_uuid: $data['name_uuid'],
            name_en:   $data['name_en'] ?? null,
            category:  $data['category'] ?? null,
            actor_uuid: auth()->user()?->uuid,
        );

        $saved = $this->service->save($entity);
        $this->audit('icd10_code.create', null, null, (array) $saved);

        return $this->apiResource($saved);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate([
            'code'      => 'required|string|max:10',
            'name_uuid' => 'required|string|max:255',
            'name_en'   => 'nullable|string|max:255',
            'category'  => 'nullable|string|max:100',
        ]);

        $existing = $this->service->findByUuid($uuid);
        abort_if(!$existing, 404, 'ICD-10 code tidak ditemukan.');

        $entity = new Icd10CodeEntity(
            uuid:      $uuid,
            code:      $data['code'],
            name_uuid: $data['name_uuid'],
            name_en:   $data['name_en'] ?? null,
            category:  $data['category'] ?? null,
            actor_uuid: auth()->user()?->uuid,
        );

        $saved = $this->service->save($entity);
        $this->audit('icd10_code.update', (array) $existing, null, (array) $saved);

        return $this->apiResource($saved);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $existing = $this->service->findByUuid($uuid);
        abort_if(!$existing, 404, 'ICD-10 code tidak ditemukan.');

        $this->service->delete($uuid, auth()->user()?->uuid ?? '');
        $this->audit('icd10_code.delete', (array) $existing, null, null);

        return response()->json(null, 204);
    }
}
