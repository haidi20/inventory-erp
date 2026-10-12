<?php
namespace App\Modules\Finance\Ledger\Entities;
class OpeningBalanceFilterEntity {
    public function __construct(public ?string $branch_uuid = null, public ?string $type = null, public int $page = 1, public int $per_page = 10) {}
}
