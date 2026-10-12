<?php

namespace App\Modules\Operational\Manufacturing\Repositories;

use App\Modules\Operational\Manufacturing\Models\Assembly;
use App\Modules\Operational\Manufacturing\Models\AssemblyItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Modules\Operational\Manufacturing\Entities\AssemblyEntity;

/**
 * Class AssemblyRepository
 *
 * Implementasi repository perakitan barang (Assembly) menggunakan Eloquent.
 */
class AssemblyRepository implements AssemblyRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function index(AssemblyEntity $entity): array
    {
        $query = Assembly::query();

        if (!empty($entity->company_uuid)) {
            $query->where('company_uuid', $entity->company_uuid);
        }

        if (!empty($entity->branch_uuid)) {
            $query->where('branch_uuid', $entity->branch_uuid);
        }

        if (!empty($entity->search)) {
            $s = '%' . Str::lower($entity->search) . '%';
            $query->whereRaw('LOWER(document_no) LIKE ?', [$s]);
        }

        $total = $query->count();

        $items = $query->orderBy('created_at', 'desc')
            ->offset(($entity->page - 1) * $entity->per_page)
            ->limit($entity->per_page)
            ->get();

        return [
            'items' => $items->map(fn($m) => AssemblyEntity::fromModel($m))->toArray(),
            'total' => $total
        ];
    }

    /**
     * @inheritDoc
     */
    public function findByID(string $uuid): ?AssemblyEntity
    {
        $model = Assembly::where('uuid', $uuid)->first();
        return $model ? AssemblyEntity::fromModel($model) : null;
    }

    /**
     * @inheritDoc
     */
    public function save(AssemblyEntity $entity): AssemblyEntity
    {
        DB::beginTransaction();
        try {
            $uuid = $entity->uuid ?? (string) Str::uuid();
            
            $assembly = Assembly::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'branch_uuid'    => $entity->branch_uuid,
                    'company_uuid'   => $entity->company_uuid,
                    'document_no'    => $entity->assembly_no ?? ('AS-' . strtoupper(Str::random(8))),
                    'date'           => $entity->date ?? time(),
                    'item_uuid'      => $entity->item_uuid,
                    'unit_uuid'      => $entity->unit_uuid,
                    'location_uuid' => $entity->location_uuid,
                    'quantity'       => $entity->qty,
                    'notes'          => $entity->description,
                    'status'         => $entity->status,
                ]
            );

            // Sync Items (Delete and Re-create like in BE)
            AssemblyItem::where('assembly_uuid', $assembly->uuid)->delete();

            if (!empty($entity->items)) {
                foreach ($entity->items as $item) {
                    AssemblyItem::create([
                        'uuid'          => (string) Str::uuid(),
                        'assembly_uuid' => $assembly->uuid,
                        'branch_uuid'   => $assembly->branch_uuid,
                        'company_uuid'  => $assembly->company_uuid,
                        'item_uuid'     => $item['item_uuid'],
                        'unit_uuid'     => $item['unit_uuid'],
                        'quantity'      => $item['qty'] ?? $item['quantity'] ?? 0,
                        'cost'          => $item['price'] ?? $item['cost'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return $this->findByID($assembly->uuid);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid): bool
    {
        $assembly = Assembly::where('uuid', $uuid)->first();
        if (!$assembly) {
            return false;
        }

        return (bool) $assembly->delete();
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
                } catch (\Exception $e) {
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
}

