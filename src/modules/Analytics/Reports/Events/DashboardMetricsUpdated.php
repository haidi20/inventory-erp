<?php

namespace App\Modules\Analytics\Reports\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardMetricsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $branchUuid,
        public readonly string $companyUuid,
        public readonly string $metricKey,
        public readonly float $metricValue,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.'.$this->branchUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dashboard.metrics.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'branch_uuid' => $this->branchUuid,
            'company_uuid' => $this->companyUuid,
            'metric_key' => $this->metricKey,
            'metric_value' => $this->metricValue,
        ];
    }
}
