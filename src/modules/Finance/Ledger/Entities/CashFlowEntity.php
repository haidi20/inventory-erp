<?php
namespace App\Modules\Finance\Ledger\Entities;

/**
 * CashFlowEntity
 * 
 * Data Entity for Cash Flow Statement results.
 */
class CashFlowEntity
{
    public function __construct(
        public array $operatingActivities = [],
        public float $operatingTotal = 0,
        public array $investingActivities = [],
        public float $investingTotal = 0,
        public array $financingActivities = [],
        public float $financingTotal = 0,
        public float $netCashFlow = 0,
        public float $beginningCash = 0,
        public float $endingCash = 0,
        public array $periodInfo = [],
        public array $branchInfo = [],
        public string $generatedAt = ''
    ) {
        $this->generatedAt = $generatedAt ?: now()->toDateTimeString();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            operatingActivities: $data['operating_activities'] ?? [],
            operatingTotal: (float)($data['operating_total'] ?? 0),
            investingActivities: $data['investing_activities'] ?? [],
            investingTotal: (float)($data['investing_total'] ?? 0),
            financingActivities: $data['financing_activities'] ?? [],
            financingTotal: (float)($data['financing_total'] ?? 0),
            netCashFlow: (float)($data['net_cash_flow'] ?? 0),
            beginningCash: (float)($data['beginning_cash'] ?? 0),
            endingCash: (float)($data['ending_cash'] ?? 0),
            periodInfo: $data['period_info'] ?? [],
            branchInfo: $data['branch_info'] ?? [],
            generatedAt: $data['generated_at'] ?? now()->toDateTimeString()
        );
    }

    public function toArray(): array
    {
        return [
            'operating_activities' => $this->operatingActivities,
            'operating_total' => $this->operatingTotal,
            'investing_activities' => $this->investingActivities,
            'investing_total' => $this->investingTotal,
            'financing_activities' => $this->financingActivities,
            'financing_total' => $this->financingTotal,
            'net_cash_flow' => $this->netCashFlow,
            'beginning_cash' => $this->beginningCash,
            'ending_cash' => $this->endingCash,
            'period_info' => $this->periodInfo,
            'branch_info' => $this->branchInfo,
            'generated_at' => $this->generatedAt
        ];
    }
}
