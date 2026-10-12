<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\TreatmentPackageEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\TreatmentPackageService;
use Illuminate\Http\Request;

class TreatmentPackageController extends Controller
{
    public function __construct(private TreatmentPackageService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $entity = TreatmentPackageEntity::fromArray(array_merge($request->all(), [
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
            'code'      => 'required|string|max:100',
            'name'      => 'required|string|max:255',
            'price'     => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $user = auth()->user();
        $entity = TreatmentPackageEntity::fromArray(array_merge($data, [
            'branch_uuid'  => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid'   => $user->uuid,
        ]));

        $package = $this->service->save($entity);
        $this->audit('treatment_package.create', null, null, $package->toArray());

        return $this->apiResource($package->toArray());
    }

    public function show(string $uuid)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $package = $this->service->findByUuid($uuid, $companyUuid);

        if (!$package) {
            abort(404, 'Treatment package not found');
        }

        return $this->apiResource($package->toArray());
    }
}
