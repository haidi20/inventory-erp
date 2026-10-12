<?php

namespace App\Modules\Clinical\Integration\Services;

use App\Modules\Clinical\Integration\Repositories\SatusehatMenuRepository;

class SatusehatMenuService
{
    public function __construct(private SatusehatMenuRepository $satusehatMenuRepository)
    {
    }

    public function overview(): array
    {
        return $this->satusehatMenuRepository->getOverview();
    }

    public function sync(): array
    {
        return $this->satusehatMenuRepository->getOverview();
    }

    public function encounter(): array
    {
        return $this->satusehatMenuRepository->getOverview();
    }

    public function fhir(): array
    {
        return $this->satusehatMenuRepository->getOverview();
    }

    public function config(): array
    {
        return $this->satusehatMenuRepository->getOverview();
    }
}
