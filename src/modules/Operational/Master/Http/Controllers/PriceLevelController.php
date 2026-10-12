<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\PriceLevelEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\PriceLevelService;
use Illuminate\Http\Request;

class PriceLevelController extends Controller
{
    public function __construct(private PriceLevelService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $entity = PriceLevelEntity::fromArray(array_merge($request->all(), [
            'branch_uuid'  => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
        ]));

        $result = $this->service->getPageData($entity);

        return $this->apiResource(collect(array_map(fn($item) => $item->toArray(), $result['items'])))
            ->additional([
                'meta' => [
                    'total'    => $result['total'],
                    'page'     => $result['page'],
                    'per_page' => $result['per_page'],
                ],
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string|max:100',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $user = auth()->user();
        $entity = PriceLevelEntity::fromArray(array_merge($data, [
            'branch_uuid'  => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid'   => $user->uuid,
        ]));

        $priceLevel = $this->service->save($entity);
        $this->audit('price_level.create', null, null, $priceLevel->toArray());

        return $this->apiResource($priceLevel->toArray());
    }

    public function show(string $uuid)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $priceLevel = $this->service->findByUuid($uuid, $companyUuid);

        if (!$priceLevel) {
            abort(404, 'Price level not found');
        }

        return $this->apiResource($priceLevel->toArray());
    }

    public function update(Request $request, string $uuid)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $priceLevel = $this->service->findByUuid($uuid, $companyUuid);

        if (!$priceLevel) {
            abort(404, 'Price level not found');
        }

        $data = $request->validate([
            'code'        => 'required|string|max:100',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $entity = PriceLevelEntity::fromArray(array_merge($data, [
            'uuid'         => $uuid,
            'branch_uuid'  => $priceLevel->branch_uuid,
            'company_uuid' => $companyUuid,
            'actor_uuid'   => $user->uuid,
        ]));

        $updated = $this->service->save($entity);
        $this->audit('price_level.update', $priceLevel->toArray(), $updated->toArray(), null);

        return $this->apiResource($updated->toArray());
    }

    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $priceLevel = $this->service->findByUuid($uuid, $companyUuid);

        if (!$priceLevel) {
            return response()->noContent();
        }

        $this->service->delete($priceLevel);
        $this->audit('price_level.delete', $priceLevel->toArray(), null, null);

        return response()->noContent();
    }
}
