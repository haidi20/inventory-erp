<?php

namespace App\Modules\Finance\Ledger\Entities;

/**
 * LedgerFilterEntity
 * 
 * Entitas untuk filter pencarian pada Buku Besar.
 */
class LedgerFilterEntity
{
    public function __construct(
        public string $account_uuid,
        public string $start_date,
        public string $end_date,
        public ?string $branch_uuid = null
    ) {}
}
