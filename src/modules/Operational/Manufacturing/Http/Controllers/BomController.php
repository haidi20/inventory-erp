<?php

namespace App\Modules\Operational\Manufacturing\Http\Controllers;

use App\Modules\Operational\Manufacturing\Entities\BomEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Manufacturing\Services\BomService;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function __construct(private BomService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $entity = new BomEntity(
            branch_uuid:  current_branch_uuid(),
            company_uuid: $companyUuid,
            search:       $request->input('search'),
            page:         (int) $request->input('page', 1),
            per_page:     (int) $request->input('per_page', 15),
        );

        $result = $this->service->index($entity);

        return $this->apiResource(collect($result['items']))->additional([
            'meta' => [
                'total'    => $result['total'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
            ],
        ]);
    }

    public function show(string $uuid)
    {
        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $bom = $this->service->getByUuid($uuid);

        if (!$bom || $bom->company_uuid !== $companyUuid) {
            abort(404, 'BOM not found');
        }

        return $this->apiResource($bom->toArray());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_uuid'   => 'required|string',
            'unit_uuid'   => 'required|string',
            'qty'         => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $entity = new BomEntity(
            branch_uuid:  current_branch_uuid(),
            company_uuid: $companyUuid,
            item_uuid:    $data['item_uuid'],
            unit_uuid:    $data['unit_uuid'],
            qty:          (float) $data['qty'],
            description:  $data['description'] ?? null,
            actor_uuid:   $user->uuid,
        );

        $bom = $this->service->saveSimple($entity);

        return $this->apiResource($bom->toArray());
    }

    public function destroy(string $uuid)
    {
        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $bom = $this->service->getByUuid($uuid);

        if (!$bom || $bom->company_uuid !== $companyUuid) {
            return response()->noContent();
        }

        $this->service->destroy($uuid, $user->uuid);

        return response()->noContent();
    }
}
