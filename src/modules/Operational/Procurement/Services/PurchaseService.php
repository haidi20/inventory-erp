<?php

namespace App\Modules\Operational\Procurement\Services;

use App\Modules\Operational\Procurement\Entities\PurchaseItemEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseFilterEntity;
use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Operational\Procurement\Repositories\PurchaseRepositoryInterface;
use App\Modules\Operational\Inventory\Services\StockBalanceService;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Operational\Sales\Enums\TransactionStatus;
use Illuminate\Support\Str;

class PurchaseService
{
    protected PurchaseRepositoryInterface $repo;
    protected StockBalanceService $stockService;
    protected JournalService $journalService;

    public function __construct(
        PurchaseRepositoryInterface $repo,
        StockBalanceService $stockService,
        JournalService $journalService
    ) {
        $this->repo = $repo;
        $this->stockService = $stockService;
        $this->journalService = $journalService;
    }

    public function getList(PurchaseFilterEntity $filter)
    {
        return $this->repo->findAll($filter);
    }

    /**
     * CreatePurchase adalah entry point utama untuk transaksi pembelian.
     * Secara otomatis memicu update stok dan penjurnalan akuntansi.
     */
    public function CreatePurchase(PurchaseRecordEntity $entity): PurchaseRecordEntity
    {
        $branchUuid = $entity->branch_uuid;
        $companyUuid = $entity->company_uuid;
        $actorUuid = $entity->actor_uuid ?? (string) Str::uuid();

        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }

        // Ensure status is Posted for a new Purchase
        $entity->status = TransactionStatus::POSTED;

        // 1. Save Purchase Record
        $saved = $this->repo->save($entity);

        // 2. Update Stock (Stock Movement IN)
        foreach ($entity->items as $item) {
            $itemData = $item instanceof PurchaseItemEntity
                ? [
                    'product_uuid' => $item->product_uuid,
                    'unit_uuid' => $item->unit_uuid,
                    'qty' => $item->qty,
                    'price' => $item->price,
                ]
                : $item;

            $movement = new StockMovementEntity(
                branch_uuid: $branchUuid,
                company_uuid: $companyUuid,
                product_uuid: $itemData['product_uuid'] ?? null,
                location_uuid: $entity->location_uuid ?? null,
                date: (int) $entity->date,
                type: 'IN',
                ref_type: StockMovementReferenceType::PURCHASE,
                ref_uuid: $saved->uuid,
                unit_uuid: $itemData['unit_uuid'] ?? null,
                qty: (float) ($itemData['qty'] ?? 0),
                cost_price: (float) ($itemData['price'] ?? 0),
                created_by: $actorUuid
            );
            $this->stockService->saveMovement($movement);
        }

        // 3. Create Journal (Accounting)
        $accounts = json_decode($entity->description ?? '{}', true);
        $inventoryAcc = $accounts['inventory_account'] ?? (string) Str::uuid();
        $payableAcc = $accounts['payable_account'] ?? (string) Str::uuid();

        $journalDto = JournalEntity::fromArray([
            'branch_uuid' => $branchUuid,
            'reference_no' => $entity->document_no,
            'reference_uuid' => $saved->uuid,
            'reference_type' => JournalReferenceType::PURCHASE,
            'journal_date' => $entity->date,
            'description' => "Pembelian: " . $entity->document_no,
            'total_amount' => $entity->total_amount,
            'actor_uuid' => $actorUuid,
            'lines' => [
                [
                    'branch_uuid' => $branchUuid,
                    'account_uuid' => $inventoryAcc,
                    'debit' => $entity->total_amount,
                    'credit' => 0,
                ],
                [
                    'branch_uuid' => $branchUuid,
                    'account_uuid' => $payableAcc,
                    'debit' => 0,
                    'credit' => $entity->total_amount,
                ]
            ]
        ]);

        $this->journalService->UpsertJournal($journalDto);

        return $saved;
    }

    public function show(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseRecordEntity
    {
        return $this->repo->findByUuid($uuid, $branchUuid, $companyUuid);
    }

    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid)
    {
        $this->repo->delete($uuid, $branchUuid, $companyUuid, $actorUuid);
    }
}
