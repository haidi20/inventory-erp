<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\ItemConversionEntity;
use App\Shared\Traits\ResolvesKlinikContext;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\ItemConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class ItemConversionController
 * 
 * Mengelola API Konversi Satuan (UOM) antar barang.
 * Sesuai standar featuredev: TDD, Entity, dan Server-Side Features.
 */
class ItemConversionController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(private readonly ItemConversionService $itemConversionService)
    {
    }

    /**
     * Menampilkan daftar konversi satuan dengan filter, paginasi, dan sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $entity = ItemConversionEntity::fromArray($this->buildFilters($request));
        $result = $this->itemConversionService->index($entity);
        $payload = $this->buildPayload($result, $request);

        return response()->json($payload);
    }

    /**
     * Menyimpan atau memperbarui data konversi satuan (Upsert).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);
        $entity = ItemConversionEntity::fromArray([
            ...$data,
            'branch_uuid' => $request->user()?->branch_uuid,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]);

        $conversion = $this->itemConversionService->save($entity);

        return response()->json($conversion->toArray());
    }

    /**
     * Menghapus data konversi satuan.
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $entity = new ItemConversionEntity(
            uuid: $uuid,
            company_uuid: $this->resolveCompanyUuid($request),
            branch_uuid: (string) ($request->user()?->branch_uuid ?? '')
        );

        $this->itemConversionService->delete($entity);

        return response()->json(['success' => true]);
    }

    /**
     * Membangun payload respon standar dengan paginasi dan filter.
     */
    private function buildPayload(array $result, Request $request): array
    {
        $filters = $this->buildFilters($request);
        $page = (int) ($result['current_page'] ?? 1);
        $perPage = (int) ($result['per_page'] ?? 15);
        $total = (int) ($result['total'] ?? 0);
        $lastPage = max(1, (int) ceil($total / $perPage));

        return [
            'data' => $result['items'] ?? [],
            'meta' => [
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => $lastPage,
            ],
            'filters' => $filters,
        ];
    }

    /**
     * Membangun array filter dari request.
     */
    private function buildFilters(Request $request): array
    {
        return [
            'search' => (string) $request->string('search')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'updated_at')->trim(),
            'sort_dir' => (string) $request->string('sort_dir', 'desc')->trim(),
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 15),
            'branch_uuid' => (string) ($request->user()?->branch_uuid ?? ''),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ];
    }

    /**
     * Validasi input request.
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'uuid' => ['nullable', 'string', 'uuid'],
            'item_uuid' => ['nullable', 'string', 'uuid'], // Null for global conversion
            'from_unit_uuid' => ['required', 'string', 'uuid'],
            'to_unit_uuid' => ['required', 'string', 'uuid'],
            'conv_ratio' => ['required', 'numeric', 'min:0.00001'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
