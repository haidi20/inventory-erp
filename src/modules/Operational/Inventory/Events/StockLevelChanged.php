<?php

namespace App\Modules\Operational\Inventory\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockLevelChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $itemUuid,
        public readonly string $branchUuid,
        public readonly string $companyUuid,
        public readonly string $locationUuid,
        public readonly float $newQuantity,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.'.$this->branchUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.level.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'item_uuid' => $this->itemUuid,
            'branch_uuid' => $this->branchUuid,
            'company_uuid' => $this->companyUuid,
            'location_uuid' => $this->locationUuid,
            'new_quantity' => $this->newQuantity,
        ];
    }
}
