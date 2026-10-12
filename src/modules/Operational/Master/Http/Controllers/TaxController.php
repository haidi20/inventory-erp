<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Operational\Master\Entities\TaxEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Master\Services\TaxService;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct(private TaxService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $entity = TaxEntity::fromArray(array_merge($request->all(), [
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
            'tax_code'                    => 'required|string|max:50',
            'percentage'                  => 'numeric|min:0|max:100',
            'is_active'                   => 'boolean',
            'is_primary'                  => 'boolean',
            'is_income_tax'               => 'boolean',
            'legacy_tax_type_uuid'        => 'nullable|string',
            'legacy_sale_account_uuid'    => 'nullable|string',
            'legacy_purchase_account_uuid' => 'nullable|string',
        ]);

        $user = auth()->user();
        $entity = TaxEntity::fromArray(array_merge($data, [
            'branch_uuid'  => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
            'actor_uuid'   => $user->uuid,
        ]));

        $tax = $this->service->save($entity);
        $this->audit('tax.create', null, null, $tax->toArray());

        return $this->apiResource($tax->toArray());
    }

    public function show(string $uuid)
    {
        $user = auth()->user();
        $tax = $this->service->findByUuid(new TaxEntity(
            uuid: $uuid,
            branch_uuid:  current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if (!$tax) {
            abort(404, 'Tax not found');
        }

        return $this->apiResource($tax->toArray());
    }

    public function update(Request $request, string $uuid)
    {
        $data = $request->validate([
            'tax_code'                    => 'sometimes|string|max:50',
            'percentage'                  => 'numeric|min:0|max:100',
            'is_active'                   => 'boolean',
            'is_primary'                  => 'boolean',
            'is_income_tax'               => 'boolean',
            'legacy_tax_type_uuid'        => 'nullable|string',
            'legacy_sale_account_uuid'    => 'nullable|string',
            'legacy_purchase_account_uuid' => 'nullable|string',
        ]);

        $user = auth()->user();
        $existing = $this->service->findByUuid(new TaxEntity(
            uuid: $uuid,
            branch_uuid:  current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if (!$existing) {
            abort(404, 'Tax not found');
        }

        $updated = $this->service->save(TaxEntity::fromArray(array_merge(
            $existing->toArray(),
            $data,
            ['uuid' => $uuid, 'actor_uuid' => $user->uuid]
        )));

        $this->audit('tax.update', null, $existing->toArray(), $updated->toArray());

        return $this->apiResource($updated->toArray());
    }

    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $existing = $this->service->findByUuid(new TaxEntity(
            uuid: $uuid,
            branch_uuid:  current_branch_uuid(),
            company_uuid: current_company_uuid(),
        ));

        if ($existing) {
            $this->service->delete($existing);
            $this->audit('tax.delete', null, $existing->toArray(), null);
        }

        return response()->noContent();
    }
}
