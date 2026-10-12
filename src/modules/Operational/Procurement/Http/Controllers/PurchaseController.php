<?php

namespace App\Modules\Operational\Procurement\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Procurement\Entities\PosPurchaseEntity;
use App\Modules\Operational\Procurement\Services\PosPurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        private PosPurchaseService $service
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $context = PosPurchaseEntity::fromArray([
            'branch_uuid' => current_branch_uuid(),
            'company_uuid' => current_company_uuid(),
        ]);

        return $this->apiResource($this->service->listPurchases($context));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();
        $data = $request->validate([
            'invoice_no' => 'required|string|max:50',
            'supplier_uuid' => 'required|uuid',
            'purchase_date' => 'nullable|date',
            'paid' => 'numeric|min:0',
            'status' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_uuid' => 'required|uuid',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:1',
        ]);

        $entity = PosPurchaseEntity::fromArray(array_merge($data, [
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $purchase = $this->service->createPurchase($entity);

        $this->audit('purchase.create', $purchase, null, $purchase->toArray());

        return $this->apiResource($purchase)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $purchase)
    {
        $user = $request->user();

        $result = $this->service->getPurchase($purchase, current_branch_uuid(), current_company_uuid());

        abort_unless($result, 404);

        return $this->apiResource($result);
    }
}
