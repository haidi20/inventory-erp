<?php

namespace App\Modules\Clinical\Integration\Services;

use App\Modules\Clinical\Integration\Repositories\BpjsSettingsRepository;
use App\Modules\Clinical\Integration\Entities\BpjsSettingsEntity;
use Illuminate\Support\Facades\Validator;

class BpjsSettingsService
{
    public function __construct(private BpjsSettingsRepository $bpjsSettingsRepository)
    {
    }

    public function index(): array
    {
        $cfg = $this->bpjsSettingsRepository->get();
        $filled = collect([
            $cfg->base_url ?? null,
            $cfg->consumer_id ?? null,
            $cfg->user_key ?? null,
            $cfg->secret_key ?? null,
            $cfg->ppk_code ?? null,
        ])->filter(fn ($value) => filled($value))->count();

        return [
            'cfg' => $cfg,
            'completion' => [
                'filled' => $filled,
                'total' => 5,
            ],
        ];
    }

    public function store(BpjsSettingsEntity $entity): void
    {
        $this->bpjsSettingsRepository->save($entity);
    }
}
