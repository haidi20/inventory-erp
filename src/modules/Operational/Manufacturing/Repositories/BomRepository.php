<?php

namespace App\Modules\Operational\Manufacturing\Repositories;

use App\Modules\Operational\Manufacturing\Models\Bom;
use App\Modules\Operational\Manufacturing\Models\BomDetail;
use App\Modules\Operational\Manufacturing\Models\BomCost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Modules\Operational\Manufacturing\Entities\BomEntity;

/**
 * Class BomRepository
 *
 * Implementasi repository Formula (BOM) menggunakan Eloquent.
 */
class BomRepository implements BomRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPageData(BomEntity $entity): array
    {
        $query = Bom::query();

        if (!empty($entity->search)) {
            $s = '%' . Str::lower($entity->search) . '%';
            $query->where(function($q) use ($s) {
                $q->whereRaw('LOWER(document_no) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(reference_no) LIKE ?', [$s]);
            });
        }

        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }

        $total = $query->count();

        $items = $query->orderBy('created_at', 'desc')
            ->offset(($entity->page - 1) * $entity->per_page)
            ->limit($entity->per_page)
            ->get();

        return [
            'items' => $items->map(fn($m) => BomEntity::fromModel($m))->toArray(),
            'total' => $total,
            'page' => $entity->page,
            'per_page' => $entity->per_page
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByUUID(string $uuid): ?BomEntity
    {
        $model = Bom::where('uuid', $uuid)->first();

        return $model ? BomEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(BomEntity $entity): ?BomEntity
    {
        DB::beginTransaction();
        try {
            $uuid = $entity->uuid ?? (string) Str::uuid();
            $actorUuid = $entity->actor_uuid ?? 'system';

            $bom = Bom::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'branch_uuid'  => $entity->branch_uuid,
                    'company_uuid' => $entity->company_uuid,
                    'document_no'  => $entity->code ?? ('BOM-' . strtoupper(Str::random(8))),
                    'item_uuid'    => $entity->item_uuid,
                    'unit_uuid'    => $entity->unit_uuid,
                    'quantity'     => $entity->qty,
                    'note'         => $entity->description,
                    'is_active'    => $entity->is_active,
                ]
            );

            // Sync Details
            BomDetail::where('bom_uuid', $bom->uuid)->delete();
            if (!empty($entity->items)) {
                foreach ($entity->items as $d) {
                    BomDetail::create([
                        'uuid'      => (string) Str::uuid(),
                        'bom_uuid'  => $bom->uuid,
                        'item_uuid' => $d['item_uuid'],
                        'unit_uuid' => $d['unit_uuid'],
                        'quantity'  => $d['qty'] ?? $d['quantity'] ?? 0,
                        'ratio'     => $d['ratio'] ?? 0,
                        'note'      => $d['note'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return $this->findByUUID($bom->uuid);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid, string $actorUuid): bool
    {
        DB::beginTransaction();
        try {
            BomDetail::where('bom_uuid', $uuid)->delete();
            BomCost::where('bom_uuid', $uuid)->delete();
            $bom = Bom::where('uuid', $uuid)->first();
            if ($bom) {
                DB::commit();
                return (bool) $bom->delete();
            }
            DB::commit();
            return false;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getByDocumentNo(string $docNo, string $companyUuid = ''): ?BomEntity
    {
        $query = Bom::whereRaw('UPPER(document_no) = ?', [Str::upper(Str::trim($docNo))]);
        if ($companyUuid !== '') {
            $query->where('company_uuid', $companyUuid);
        }
        $model = $query->first();
        return $model ? BomEntity::fromModel($model) : null;
    }

    public function saveMany(array $entities): array
    {
        DB::beginTransaction();
        $success = 0;
        $failed = 0;
        $errors = [];

        try {
            foreach ($entities as $index => $entity) {
                try {
                    $this->save($entity);
                    $success++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'total' => $success + $failed,
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * @inheritDoc
     */
    public function listForExport(BomEntity $entity): array
    {
        $query = Bom::query();

        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }

        if (!empty($entity->search)) {
            $like = '%' . Str::lower($entity->search) . '%';
            $query->whereRaw('LOWER(document_no) LIKE ? OR LOWER(reference_no) LIKE ?', [$like, $like]);
        }

        $items = $query->orderBy('document_no', 'asc')->get();

        return $items->map(fn($m) => BomEntity::fromModel($m))->toArray();
    }
}


