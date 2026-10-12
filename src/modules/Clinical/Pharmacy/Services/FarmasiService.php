<?php

namespace App\Modules\Clinical\Pharmacy\Services;

use App\Modules\Clinical\Pharmacy\Entities\DispensingEntity;
use App\Modules\Clinical\Pharmacy\Repositories\DispensingRepository;
use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;
use App\Modules\Operational\Inventory\Enums\StockMovementType;
use App\Modules\Operational\Inventory\Services\InventoryPublicService;
use App\Modules\Operational\Master\Services\MasterPublicService;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnEntity;
use App\Modules\Operational\Procurement\Services\ProcurementPublicService;

class FarmasiService
{
    public function __construct(
        private MasterPublicService $masterService,
        private InventoryPublicService $inventoryRepo,
        private DispensingRepository $dispensingRepo,
        private ProcurementPublicService $procurementService
    ) {}

    public function receivePurchase(string $uuid): void
    {
        $purchase = $this->procurementService->findPurchaseByUuid($uuid);

        if ($purchase->isReceived()) {
            throw new \Exception('Pembelian ini sudah pernah diterima.');
        }

        foreach ($purchase->items as $item) {
            $this->masterService->incrementProductStock($item->product_uuid, $item->qty);

            $movement = StockMovementEntity::fromArray([
                'product_uuid' => $item->product_uuid,
                'qty' => $item->qty,
                'type' => StockMovementType::IN->value,
                'price' => $item->price,
                'notes' => 'Penerimaan dari PO: '.$purchase->invoice_no,
                'user_uuid' => auth()->user()->uuid,
                'reference_type' => StockMovementReferenceType::PURCHASE->value,
                'reference_uuid' => $purchase->uuid,
            ]);
            $this->inventoryRepo->createMovement($movement);
        }

        $this->procurementService->markPurchaseReceived($purchase);
    }

    public function processDispensing(DispensingEntity $entity): void
    {
        $no = 'DSP-'.date('Ymd').'-'.str_pad(
            (string) ($this->dispensingRepo->countToday() + 1), 3, '0', STR_PAD_LEFT
        );

        $entity->dispensing_no = $no;
        $entity->dispensed_by_uuid = auth()->user()->uuid;
        $entity->total = collect($entity->items)->sum(fn ($i) => $i->qty * $i->price_sell);

        $dispensing = $this->dispensingRepo->create($entity);

        foreach ($entity->items as $item) {
            $stock = $this->masterService->getProductStock($item->product_uuid);
            if ($stock < $item->qty) {
                $product = $this->masterService->findProductByUuid($item->product_uuid);
                throw new \Exception("Stok {$product?->name} tidak cukup (tersedia: {$stock})");
            }

            $item->dispensing_uuid = $dispensing->uuid;
            $item->subtotal = $item->qty * $item->price_sell;
            $this->dispensingRepo->createItem($item);

            $this->masterService->decrementProductStock($item->product_uuid, $item->qty);

            $movement = StockMovementEntity::fromArray([
                'product_uuid' => $item->product_uuid,
                'qty' => $item->qty,
                'type' => StockMovementType::OUT->value,
                'price' => $item->price_sell,
                'notes' => 'Dispensing: '.$no,
                'user_uuid' => auth()->user()->uuid,
                'reference_type' => StockMovementReferenceType::DISPENSING->value,
                'reference_uuid' => $dispensing->uuid,
            ]);
            $this->inventoryRepo->createMovement($movement);
        }
    }

    public function voidDispensing(string $uuid): void
    {
        $dispensing = $this->dispensingRepo->findByUuid($uuid);

        foreach ($dispensing->items as $item) {
            $this->masterService->incrementProductStock($item->product_uuid, $item->qty);
        }
        $this->inventoryRepo->deleteMovementByReference(
            StockMovementReferenceType::DISPENSING->value,
            $dispensing->uuid
        );
        $this->dispensingRepo->delete($dispensing->uuid);
    }

    public function processReturn(PurchaseReturnEntity $entity): void
    {
        $purchase = $this->procurementService->findPurchaseByUuid($entity->purchase_uuid);

        $no = 'RTR-'.date('Ymd').'-'.str_pad(
            (string) ($this->procurementService->countReturnsToday() + 1), 3, '0', STR_PAD_LEFT
        );

        $entity->return_no = $no;
        $entity->supplier_uuid = $purchase->supplier_uuid;
        $entity->returned_by_uuid = auth()->user()->uuid;
        $entity->total = collect($entity->items)->sum(fn ($i) => $i->qty * $i->price);

        $retur = $this->procurementService->createReturn($entity);

        foreach ($entity->items as $item) {
            $item->return_uuid = $retur->uuid;
            $item->subtotal = $item->qty * $item->price;
            $this->procurementService->createReturnItem($item);

            $this->masterService->decrementProductStock($item->product_uuid, $item->qty);

            $movement = StockMovementEntity::fromArray([
                'product_uuid' => $item->product_uuid,
                'qty' => $item->qty,
                'type' => StockMovementType::OUT->value,
                'price' => $item->price,
                'notes' => 'Retur ke supplier: '.$no,
                'user_uuid' => auth()->user()->uuid,
                'reference_type' => StockMovementReferenceType::PURCHASE_RETURN->value,
                'reference_uuid' => $retur->uuid,
            ]);
            $this->inventoryRepo->createMovement($movement);
        }
    }

    public function voidReturn(string $uuid): void
    {
        $retur = $this->procurementService->findReturnByUuid($uuid);

        foreach ($retur->items as $item) {
            $this->masterService->incrementProductStock($item->product_uuid, $item->qty);
        }
        $this->inventoryRepo->deleteMovementByReference(
            StockMovementReferenceType::PURCHASE_RETURN->value,
            $retur->uuid
        );
        $this->procurementService->deleteReturn($retur);
    }

    public function adjustStock(StockMovementEntity $entity): void
    {
        $stock = $this->masterService->getProductStock($entity->product_uuid);
        $diff = $entity->qty_actual - $stock;

        $this->masterService->decrementProductStock($entity->product_uuid, $stock);
        $this->masterService->incrementProductStock($entity->product_uuid, $entity->qty_actual);

        $entity->qty = abs($diff);
        $entity->type = StockMovementType::ADJUST->value;
        $entity->notes = ($diff >= 0 ? '[+] ' : '[-] ').($entity->notes ?? 'Penyesuaian stok opname');
        $entity->created_by = auth()->user()->uuid;
        $entity->ref_type = StockMovementReferenceType::ADJUSTMENT;
        $entity->ref_uuid = null;

        $this->inventoryRepo->createMovement($entity);
    }
}
