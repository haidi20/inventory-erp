<?php

namespace App\Modules\Clinical\Integration\Services;

use App\Modules\Clinical\Integration\Repositories\BpjsMenuRepository;

class BpjsMenuService
{
    public function __construct(private BpjsMenuRepository $bpjsMenuRepository)
    {
    }

    public function overview(): array
    {
        return $this->bpjsMenuRepository->getOverview();
    }

    public function participantRegistration(): array
    {
        return $this->bpjsMenuRepository->getOverview();
    }

    public function membershipVerification(): array
    {
        return $this->bpjsMenuRepository->getOverview();
    }

    public function onlineReferral(): array
    {
        return $this->bpjsMenuRepository->getOverview();
    }

    public function sep(): array
    {
        return $this->bpjsMenuRepository->getOverview();
    }

    public function claimMonitoring(): array
    {
        return $this->bpjsMenuRepository->getOverview();
    }
}
