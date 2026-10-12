<?php

namespace App\Modules\Clinical\Integration\Jobs;

use App\Modules\Clinical\Integration\Events\SatusehatSyncProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SatusehatSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly string $companyUuid,
        public readonly string $syncBatchUuid,
        public readonly array $patientUuids = [],
    ) {}

    public function handle(): void
    {
        $total = max(count($this->patientUuids), 1);

        foreach ($this->patientUuids as $index => $patientUuid) {
            $percentage = (int) ((($index + 1) / $total) * 100);

            broadcast(new SatusehatSyncProgress(
                companyUuid: $this->companyUuid,
                syncBatchUuid: $this->syncBatchUuid,
                status: 'processing',
                percentage: $percentage,
            ));
        }

        broadcast(new SatusehatSyncProgress(
            companyUuid: $this->companyUuid,
            syncBatchUuid: $this->syncBatchUuid,
            status: 'success',
            percentage: 100,
        ));
    }
}
