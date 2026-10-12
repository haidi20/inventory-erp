<?php

namespace App\Modules\Operational\Sales\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Sales\Entities\SaleEntity;
use App\Modules\Operational\Sales\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        private SaleService $service
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $context = SaleEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]);

        return $this->apiResource(
            $this->service->listSales($context)
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $data = $request->validate([
            'sale_no' => 'required|string|max:50',
            'patient_uuid' => 'nullable|uuid',
            'cashier_uuid' => 'nullable|uuid',
            'paid' => 'numeric|min:0',
            'payment_status' => 'nullable|string|max:20',
            'payment_method' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:service,product',
            'items.*.item_uuid' => 'required|uuid',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $entity = SaleEntity::fromArray(array_merge($data, [
            'actor_uuid' => $user->uuid,
            'branch_uuid' => $branchUuid,
            'company_uuid' => $companyUuid,
        ]));

        $this->service->validateBeforeCreate($entity);
        $sale = $this->service->createSale($entity);

        $this->audit('sale.create', $sale, null, $sale->toArray());

        return $this->apiResource($sale)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $sale)
    {
        $user = $request->user();
        $branchUuid = current_branch_uuid();
        $companyUuid = current_company_uuid();

        $result = $this->service->getSale(
            $sale,
            $branchUuid,
            $companyUuid
        );

        abort_unless($result, 404);

        return $this->apiResource($result);
    }
}
