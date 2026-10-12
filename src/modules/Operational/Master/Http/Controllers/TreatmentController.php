<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\TreatmentEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\TreatmentService;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    public function __construct(private TreatmentService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $entity = TreatmentEntity::fromArray(array_merge($request->all(), [
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
            'code'            => 'required|string|max:100',
            'name'            => 'required|string|max:255',
            'duration_minutes' => 'integer|min:0',
            'price'           => 'numeric|min:0',
            'doctor_fee'      => 'numeric|min:0',
            'is_active'       => 'boolean',
        ]);

        $user = auth()->user();
        $entity = TreatmentEntity::fromArray(array_merge($data, [
            'branch_uuid'  => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid'   => $user->uuid,
        ]));

        $treatment = $this->service->save($entity);
        $this->audit('treatment.create', null, null, $treatment->toArray());

        return $this->apiResource($treatment->toArray());
    }

    public function show(string $uuid)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $treatment = $this->service->findByUuid($uuid, $companyUuid);

        if (!$treatment) {
            abort(404, 'Treatment not found');
        }

        return $this->apiResource($treatment->toArray());
    }
}
