<?php

namespace App\Modules\Finance\Ledger\Services;

use App\Modules\Operational\Inventory\Services\InventoryPublicService;
use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceEntity;
use App\Modules\Finance\Ledger\Entities\OpeningBalanceFilterEntity;
use App\Modules\Finance\Ledger\Repositories\OpeningBalanceRepositoryInterface;
use Illuminate\Support\Str;

class OpeningBalanceService
{
    private OpeningBalanceRepositoryInterface $repo;
    private InventoryPublicService $inventoryService;
    private JournalService $journalService;

    public function __construct(
        OpeningBalanceRepositoryInterface $repo,
        InventoryPublicService $inventoryService,
        JournalService $journalService
    ) {
        $this->repo = $repo;
        $this->inventoryService = $inventoryService;
        $this->journalService = $journalService;
    }

    public function GetOpeningBalanceHeaders(OpeningBalanceFilterEntity $filter)
    {
        return $this->repo->getOpeningBalanceHeaders($filter);
    }

    public function CreateHeader(OpeningBalanceEntity $entity)
    {
        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }
        return $this->repo->createOpeningBalanceHeader($entity);
    }

    public function PostOpeningBalance(string $headerUuid, string $userUuid)
    {
        $header = $this->repo->getOpeningBalanceHeader($headerUuid);
        if (!$header) {
            throw new \Exception("header saldo awal tidak ditemukan");
        }

        if ($header->is_posted) {
            return true;
        }

        switch ($header->type) {
            case 'ACCOUNT':
                $this->postAccountOpeningBalances($header, $userUuid);
                break;
            case 'STOCK':
                $this->postStockOpeningBalances($header, $userUuid);
                break;
        }

        $entity = new OpeningBalanceEntity(uuid: $headerUuid, is_posted: true, actor_uuid: $userUuid);
        return $this->repo->updateOpeningBalanceHeader($entity);
    }

    private function postAccountOpeningBalances(object $header, string $userUuid)
    {
        $mapping = $this->repo->findMappingByKey('BALANCE');
        if (!$mapping || empty($mapping->account_uuid)) {
            throw new \Exception("mapping akun penyeimbang (BALANCE) tidak ditemukan");
        }

        $balances = $this->repo->getOpeningBalances($header->branch_uuid);

        foreach ($balances as $bal) {
            if ($bal->opening_balance == 0) continue;

            $amount = $bal->opening_balance;
            if ($amount > 0) {
                $debitAcc = $bal->account_uuid;
                $creditAcc = $mapping->account_uuid;
            } else {
                $amount = abs($amount);
                $debitAcc = $mapping->account_uuid;
                $creditAcc = $bal->account_uuid;
            }

            $this->journalService->UpsertOpeningBalanceJournal([
                'branch_uuid' => $header->branch_uuid,
                'reference_uuid' => $header->uuid,
                'reference_no' => $header->transaction_no,
                'description' => "Posting Saldo Awal: " . ($bal->account->name ?? $bal->account_uuid),
                'debit_account_uuid' => $debitAcc,
                'credit_account_uuid' => $creditAcc,
                'amount' => $amount,
                'journal_date' => $header->transaction_date,
                'actor_uuid' => $userUuid,
            ]);
        }
    }

    private function postStockOpeningBalances(object $header, string $userUuid)
    {
        $itemBalances = $this->repo->getItemOpeningBalances($header->branch_uuid);

        foreach ($itemBalances as $ib) {
            if ($ib->opening_qty <= 0) continue;

            $this->inventoryService->saveMovement($this->inventoryService->makeMovementEntity(
                branchUuid: $header->branch_uuid,
                productUuid: $ib->item_uuid,
                warehouseUuid: $ib->location_uuid,
                date: $header->transaction_date,
                type: 'IN',
                refType: StockMovementReferenceType::OPENING_BALANCE,
                refUuid: $header->uuid,
                qty: $ib->opening_qty,
                costPrice: $ib->opening_unit_cost,
                createdBy: $userUuid
            ));
        }
    }
}
