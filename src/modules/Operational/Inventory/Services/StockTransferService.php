<?php

namespace App\Modules\Operational\Inventory\Services;

use App\Modules\Operational\Inventory\Repositories\StockTransferRepositoryInterface;
use App\Modules\Operational\Inventory\Repositories\StockRepositoryInterface;
use App\Modules\Operational\Inventory\Entities\StockTransferEntity;
use App\Modules\Operational\Inventory\Entities\StockMovementEntity;
use App\Modules\Operational\Inventory\Enums\StockMovementReferenceType;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Modules\Operational\Sales\Enums\TransactionStatus;

use App\Modules\Operational\Inventory\Entities\StockTransferFilterEntity;

class StockTransferService
{
    private StockTransferRepositoryInterface $repo;
    private StockRepositoryInterface $stockRepo;

    public function __construct(StockTransferRepositoryInterface $repo, StockRepositoryInterface $stockRepo)
    {
        $this->repo = $repo;
        $this->stockRepo = $stockRepo;
    }

    public function GetList(StockTransferFilterEntity $filter)
    {
        return $this->repo->getPageData($filter);
    }

    public function GetDetail(string $id, string $branchUuid, string $companyUuid): ?StockTransferEntity
    {
        return $this->repo->getByUuid($id, $branchUuid, $companyUuid);
    }

    public function CreateOrUpdate(StockTransferEntity $entity, string $companyUuid): ?StockTransferEntity
    {
        if ($entity->source_location_uuid === $entity->destination_location_uuid) {
            throw new \Exception("gudang asal dan tujuan tidak boleh sama");
        }

        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
            $entity->document_no = $entity->document_no ?? $this->generateNoPag($entity->branch_uuid, $companyUuid, $entity->date);
        }

        return $this->repo->store($entity);
    }

    private function generateNoPag(string $branchUuid, string $companyUuid, int $date): string
    {
        $dt = Carbon::createFromTimestamp($date);
        $period = $dt->format('Y-m');
        $prefix = 'PAG';
        $seq = $this->repo->getNextSequence($branchUuid, $companyUuid, $prefix, $period);
        return sprintf("%s-%s-%05d", $prefix, $dt->format('y-m'), $seq);
    }

    public function Post(StockTransferEntity $entity): ?StockTransferEntity
    {
        $pag = $this->repo->getByUuid($entity->uuid, $entity->branch_uuid, $entity->company_uuid);
        if (!$pag || $pag->status === TransactionStatus::POSTED) {
            return $pag;
        }

        $pag->status = TransactionStatus::POSTED;
        $pag->actor_uuid = $entity->actor_uuid;

        DB::beginTransaction();
        try {
            foreach ($pag->items ?? [] as $item) {
                $this->stockRepo->saveMovement(new StockMovementEntity(
                    branch_uuid: $pag->branch_uuid,
                    company_uuid: $pag->company_uuid,
                    product_uuid: $item->product_uuid,
                    location_uuid: $pag->source_location_uuid,
                    date: $pag->date,
                    type: 'OUT',
                    ref_type: StockMovementReferenceType::TRANSFER,
                    ref_uuid: $pag->uuid,
                    unit_uuid: $item->unit_uuid,
                    qty: $item->qty,
                    created_by: $entity->actor_uuid
                ));

                $this->stockRepo->saveMovement(new StockMovementEntity(
                    branch_uuid: $pag->branch_uuid,
                    company_uuid: $pag->company_uuid,
                    product_uuid: $item->product_uuid,
                    location_uuid: $pag->destination_location_uuid,
                    date: $pag->date,
                    type: 'IN',
                    ref_type: StockMovementReferenceType::TRANSFER,
                    ref_uuid: $pag->uuid,
                    unit_uuid: $item->unit_uuid,
                    qty: $item->qty,
                    created_by: $entity->actor_uuid
                ));
            }

            $posted = $this->repo->store($pag);
            DB::commit();
            return $posted;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
