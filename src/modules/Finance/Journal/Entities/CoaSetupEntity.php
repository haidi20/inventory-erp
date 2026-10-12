<?php

namespace App\Modules\Finance\Journal\Entities;

/**
 * CoaSetupEntity
 * 
 * Entitas untuk mengelola pemetaan akun otomatis (COA Setup) per modul/program.
 */
class CoaSetupEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $program_name = null,
        public ?string $account_uuid = null,
        public ?string $value_source = null,
        public ?string $type_saldo = 'debit',
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            program_name: $data['program_name'] ?? $data['menu_name'] ?? null,
            account_uuid: $data['account_uuid'] ?? null,
            value_source: $data['value_source'] ?? null,
            type_saldo: $data['type_saldo'] ?? 'debit',
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'program_name' => $this->program_name,
            'account_uuid' => $this->account_uuid,
            'value_source' => $this->value_source,
            'type_saldo' => $this->type_saldo,
        ];
    }
}
