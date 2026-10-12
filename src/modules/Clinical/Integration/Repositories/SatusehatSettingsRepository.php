<?php

namespace App\Modules\Clinical\Integration\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;
use App\Modules\Clinical\Integration\Models\SatusehatSetting;
use Illuminate\Support\Str;

class SatusehatSettingsRepository implements SatusehatSettingsRepositoryInterface
{
    public function get(): ?SatusehatSettingsEntity
    {
        $model = SatusehatSetting::query()
            ->where('deleted_at', 0)
            ->first();

        return $model ? SatusehatSettingsEntity::fromModel($model) : null;
    }

    public function save(SatusehatSettingsEntity $entity): SatusehatSettingsEntity
    {
        DB::beginTransaction();
        try {
            $existing = SatusehatSetting::query()
                ->where('deleted_at', 0)
                ->first();

            if ($existing) {
                $existing->update([
                    'org_uuid'      => $entity->org_uuid,
                    'client_uuid'   => $entity->client_uuid,
                    'client_secret' => $entity->client_secret,
                    'base_url'      => $entity->base_url,
                    'auth_url'      => $entity->auth_url,
                    'is_active'     => $entity->is_active,
                    'updated_by'    => $entity->actor_uuid,
                ]);

                DB::commit();
                return SatusehatSettingsEntity::fromModel($existing->fresh());
            }

            $model = SatusehatSetting::query()->create([
                'uuid'          => (string) Str::uuid(),
                'org_uuid'      => $entity->org_uuid,
                'client_uuid'   => $entity->client_uuid,
                'client_secret' => $entity->client_secret,
                'base_url'      => $entity->base_url,
                'auth_url'      => $entity->auth_url,
                'is_active'     => $entity->is_active,
                'created_by'    => $entity->actor_uuid,
            ]);

            DB::commit();
            return SatusehatSettingsEntity::fromModel($model);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}