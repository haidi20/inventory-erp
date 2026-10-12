<?php

namespace App\Modules\Finance\Ledger\Services;

use App\Modules\Finance\Ledger\Repositories\AuditLogRepository;

class AuditLogService
{
    public function __construct(
        protected AuditLogRepository $repository
    ) {}

    public function record(array $data): void
    {
        $this->repository->create($data);
    }
}
