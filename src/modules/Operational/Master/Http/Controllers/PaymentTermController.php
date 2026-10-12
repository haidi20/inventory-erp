<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\PaymentTermEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\PaymentTermService;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    public function __construct(private PaymentTermService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $entity = PaymentTermEntity::fromArray(array_merge($request->all(), [
            'branch_uuid'  => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_days'    => 'integer|min:0',
            'is_active'   => 'boolean',
            'is_primary'  => 'boolean',
        ]);

        $user = auth()->user();
        $entity = PaymentTermEntity::fromArray(array_merge($data, [
            'branch_uuid'  => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid'   => $user->uuid,
        ]));

        $term = $this->service->save($entity);
        $this->audit('payment_term.create', null, null, $term->toArray());

        return $this->apiResource($term->toArray());
    }

    public function show(string $uuid)
    {
        $user = auth()->user();
        $term = $this->service->findByUuid(new PaymentTermEntity(
            uuid: $uuid,
            branch_uuid:  current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if (!$term) {
            abort(404, 'Payment term not found');
        }

        return $this->apiResource($term->toArray());
    }

    public function update(Request $request, string $uuid)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_days'    => 'integer|min:0',
            'is_active'   => 'boolean',
            'is_primary'  => 'boolean',
        ]);

        $user = auth()->user();
        $existing = $this->service->findByUuid(new PaymentTermEntity(
            uuid: $uuid,
            branch_uuid:  current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if (!$existing) {
            abort(404, 'Payment term not found');
        }

        $updated = $this->service->save(PaymentTermEntity::fromArray(array_merge(
            $existing->toArray(),
            $data,
            ['uuid' => $uuid, 'actor_uuid' => $user->uuid]
        )));

        $this->audit('payment_term.update', null, $existing->toArray(), $updated->toArray());

        return $this->apiResource($updated->toArray());
    }

    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $existing = $this->service->findByUuid(new PaymentTermEntity(
            uuid: $uuid,
            branch_uuid:  current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if ($existing) {
            $this->service->delete($existing);
            $this->audit('payment_term.delete', null, $existing->toArray(), null);
        }

        return response()->noContent();
    }
}
