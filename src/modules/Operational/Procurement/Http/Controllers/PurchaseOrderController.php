<?php

namespace App\Modules\Operational\Procurement\Http\Controllers;

use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderItemEntity;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Operational\Procurement\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $filter = new PurchaseOrderFilterEntity(
            branch_uuid:  current_branch_uuid(),
            company_uuid: $companyUuid,
            status:       $request->input('status'),
            search:       $request->input('search'),
            page:         (int) $request->input('page', 1),
            per_page:     (int) $request->input('per_page', 10),
        );

        $result = $this->service->findAll($filter);

        return $this->apiResource(collect(array_map(fn($po) => [
            'uuid'        => $po->uuid,
            'document_no' => $po->document_no,
            'date'        => $po->date,
            'status'      => $po->status,
            'notes'       => $po->notes,
        ], $result->items)))->additional([
            'meta' => [
                'total'    => $result->total,
                'page'     => $result->page,
                'per_page' => $filter->per_page,
            ],
        ]);
    }

    public function show(string $uuid)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $po = $this->service->findByUuid($uuid, $companyUuid);

        if (!$po) {
            abort(404, 'Purchase order not found');
        }

        return $this->apiResource([
            'uuid'        => $po->uuid,
            'document_no' => $po->document_no,
            'date'        => $po->date,
            'status'      => $po->status,
            'notes'       => $po->notes,
            'items'       => $po->items->map(fn($i) => [
                'uuid'         => $i->uuid,
                'product_uuid' => $i->product_uuid,
                'quantity'     => $i->quantity,
                'price'        => $i->price,
                'subtotal'     => $i->subtotal,
            ])->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_uuid'    => 'required|string',
            'location_uuid'   => 'required|string',
            'document_no'      => 'required|string|max:100',
            'date'             => 'required|integer',
            'status'           => 'sometimes|string',
            'notes'            => 'nullable|string',
            'items'            => 'array',
            'items.*.product_uuid' => 'required|string',
            'items.*.unit_uuid'    => 'required|string',
            'items.*.quantity'     => 'required|numeric|min:0',
            'items.*.price'        => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $items = array_map(fn($i) => new PurchaseOrderItemEntity(
            uuid:         null,
            product_uuid: $i['product_uuid'],
            unit_uuid:    $i['unit_uuid'],
            quantity:     (float) $i['quantity'],
            price:        (float) $i['price'],
            discount:     (float) ($i['discount'] ?? 0),
            subtotal:     (float) ($i['subtotal'] ?? ($i['quantity'] * $i['price'])),
        ), $data['items'] ?? []);

        $entity = new PurchaseOrderEntity(
            uuid:           null,
            branch_uuid:    current_branch_uuid(),
            company_uuid:   $companyUuid,
            supplier_uuid:  $data['supplier_uuid'],
            location_uuid: $data['location_uuid'],
            document_no:    $data['document_no'],
            date:           $data['date'],
            notes:          $data['notes'] ?? null,
            items:          $items,
            actor_uuid:     $user->uuid,
        );

        $po = $this->service->save($entity);
        $this->audit('purchase_order.create', null, null, ['uuid' => $po->uuid, 'document_no' => $po->document_no]);

        return $this->apiResource(['uuid' => $po->uuid, 'document_no' => $po->document_no]);
    }

    public function destroy(string $uuid)
    {
        $user = auth()->user();
        $companyUuid = current_company_uuid();

        $po = $this->service->findByUuid($uuid, $companyUuid);
        if (!$po) {
            return response()->noContent();
        }

        $this->service->delete($uuid, $user->uuid);
        $this->audit('purchase_order.delete', null, ['uuid' => $uuid], null);

        return response()->noContent();
    }
}
