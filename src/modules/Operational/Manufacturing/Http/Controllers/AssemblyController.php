<?php

namespace App\Modules\Operational\Manufacturing\Http\Controllers;

use App\Modules\Operational\Manufacturing\Entities\AssemblyEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Manufacturing\Services\AssemblyService;
use Illuminate\Http\Request;

class AssemblyController extends Controller
{
    public function __construct(private AssemblyService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $entity = new AssemblyEntity(
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
                'page'     => $entity->page,
                'per_page' => $entity->per_page,
            ],
        ]);
    }

    public function show(string $uuid)
    {
        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $assembly = $this->service->getByUuid($uuid);

        if (!$assembly || $assembly->company_uuid !== $companyUuid) {
            abort(404, 'Assembly not found');
        }

        return $this->apiResource($assembly->toArray());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_uuid'      => 'required|string',
            'unit_uuid'      => 'required|string',
            'location_uuid' => 'required|string',
            'date'           => 'required|integer',
            'qty'            => 'required|numeric',
            'description'    => 'nullable|string',
            'status'         => 'nullable|string',
        ]);

        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $entity = new AssemblyEntity(
            branch_uuid:    current_branch_uuid(),
            company_uuid:   $companyUuid,
            item_uuid:      $data['item_uuid'],
            unit_uuid:      $data['unit_uuid'],
            location_uuid: $data['location_uuid'],
            date:           (int) $data['date'],
            qty:            (float) $data['qty'],
            description:    $data['description'] ?? null,
            status:         $data['status'] ?? 'draft',
            actor_uuid:     $user->uuid,
        );

        $assembly = $this->service->save($entity);

        return $this->apiResource($assembly->toArray());
    }

    public function destroy(string $uuid)
    {
        $user        = auth()->user();
        $companyUuid = current_company_uuid();

        $assembly = $this->service->getByUuid($uuid);

        if (!$assembly || $assembly->company_uuid !== $companyUuid) {
            return response()->noContent();
        }

        $this->service->delete($uuid);

        return response()->noContent();
    }
}
