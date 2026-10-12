<?php

namespace App\Modules\Finance\Journal\Entities;

use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Journal as JournalModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * JournalEntity
 * 
 * Entitas untuk mengelola header jurnal akuntansi secara terstruktur.
 */
class JournalEntity
{
    /**
     * @param array<int, JournalLineEntity>|Collection<int, JournalLineEntity> $lines
     */
    public function __construct(
        public string $branch_uuid,
        public string $reference_no,
        public string $reference_uuid,
        public int $journal_date, // Unix Timestamp
        public array|Collection $lines,
        public ?JournalReferenceModel $reference_model = null,
        public ?JournalReferenceType $reference_type = null,
        public ?string $transaction_code = null,
        public ?int $scheduled_at = null,
        public ?string $description = null,
        public JournalStatus $status = JournalStatus::UNPOSTED,
        public bool $is_system = false,
        public ?string $closing_period_uuid = null,
        public ?string $reversal_status = null,
        public ?int $reversal_date = null,
        public ?string $reversal_journal_uuid = null,
        public JournalWorkflowStatus $workflow_status = JournalWorkflowStatus::DRAFT,
        public int $version = 1,
        // Legacy compatibility fields if needed
        public bool $is_adjusting = false,
        public bool $is_auto_reverse = false,
        public string $currency_code = 'IDR',
        public float $exchange_rate = 1.0,
        public bool $is_processed = false,
        public ?string $created_by = null,
        public ?string $updated_by = null,
        public ?string $deleted_by = null,
        public ?string $uuid = null,
    ) {}

    public static function fromModel($model): self
    {
        $lines = collect();
        if ($model->relationLoaded('lines')) {
            $lines = $model->lines->map(fn($l) => JournalLineEntity::fromModel($l))->values();
        }

        return new self(
            branch_uuid: $model->branch_uuid,
            reference_no: $model->reference_no,
            reference_uuid: (string) ($model->reference_uuid ?? ''),
            journal_date: (int) $model->journal_date,
            lines: $lines,
            reference_model: $model->reference_model instanceof JournalReferenceModel ? $model->reference_model : JournalReferenceModel::tryFrom($model->reference_model),
            reference_type: $model->reference_type instanceof JournalReferenceType ? $model->reference_type : JournalReferenceType::tryFrom($model->reference_type),
            transaction_code: $model->transaction_code,
            scheduled_at: $model->scheduled_at,
            description: $model->description,
            status: $model->status instanceof JournalStatus ? $model->status : (JournalStatus::tryFrom($model->status) ?? JournalStatus::UNPOSTED),
            is_system: (bool) $model->is_system,
            closing_period_uuid: $model->closing_period_uuid,
            reversal_status: $model->reversal_status,
            reversal_date: $model->reversal_date,
            reversal_journal_uuid: $model->reversal_journal_uuid,
            workflow_status: $model->workflow_status instanceof JournalWorkflowStatus ? $model->workflow_status : (JournalWorkflowStatus::tryFrom($model->workflow_status) ?? JournalWorkflowStatus::DRAFT),
            version: (int) $model->version,
            is_adjusting: (bool) $model->is_adjusting,
            is_auto_reverse: (bool) $model->is_auto_reverse,
            currency_code: $model->currency_code ?? 'IDR',
            exchange_rate: (float) ($model->exchange_rate ?? 1.0),
            is_processed: (bool) $model->is_processed,
            created_by: $model->created_by ?? null,
            updated_by: $model->updated_by ?? null,
            deleted_by: $model->deleted_by ?? null,
            uuid: $model->uuid ?? null,
        );
    }

    /**
     * Buat dari array untuk mempermudah pemrosesan batch dari controller atau service lain
     */
    public static function fromArray(array $data): self
    {
        $lines = array_map(function ($line) {
            return $line instanceof JournalLineEntity ? $line : JournalLineEntity::fromArray($line);
        }, $data['lines'] ?? []);

        return new self(
            branch_uuid: $data['branch_uuid'] ?? '',
            reference_no: $data['reference_no'] ?? '',
            reference_uuid: $data['reference_uuid'] ?? '',
            journal_date: self::toUnixTimestamp($data['journal_date'] ?? time()),
            lines: collect($lines),
            reference_model: isset($data['reference_model']) ? ($data['reference_model'] instanceof JournalReferenceModel ? $data['reference_model'] : JournalReferenceModel::tryFrom($data['reference_model'])) : null,
            reference_type: isset($data['reference_type']) ? ($data['reference_type'] instanceof JournalReferenceType ? $data['reference_type'] : JournalReferenceType::tryFrom($data['reference_type'])) : null,
            transaction_code: $data['transaction_code'] ?? null,
            scheduled_at: isset($data['scheduled_at']) ? self::toUnixTimestamp($data['scheduled_at']) : null,
            description: $data['description'] ?? null,
            status: ($data['status'] ?? null) instanceof JournalStatus ? $data['status'] : (is_string($data['status'] ?? null) ? (JournalStatus::tryFrom($data['status']) ?? JournalStatus::UNPOSTED) : JournalStatus::UNPOSTED),
            is_system: $data['is_system'] ?? false,
            closing_period_uuid: $data['closing_period_uuid'] ?? null,
            reversal_status: $data['reversal']['is_auto_reverse'] ?? false ? 'pending_reversal' : ($data['reversal_status'] ?? null),
            reversal_date: isset($data['reversal_date']) ? self::toUnixTimestamp($data['reversal_date']) : (isset($data['reversal']['reversal_date']) ? self::toUnixTimestamp($data['reversal']['reversal_date']) : null),
            reversal_journal_uuid: $data['reversal_journal_uuid'] ?? null,
            workflow_status: ($data['workflow_status'] ?? null) instanceof JournalWorkflowStatus ? $data['workflow_status'] : (is_string($data['workflow_status'] ?? null) ? (JournalWorkflowStatus::tryFrom($data['workflow_status']) ?? JournalWorkflowStatus::DRAFT) : JournalWorkflowStatus::DRAFT),
            version: $data['version'] ?? 1,
            is_adjusting: $data['is_adjusting'] ?? false,
            is_auto_reverse: $data['is_auto_reverse'] ?? ($data['reversal']['is_auto_reverse'] ?? false),
            currency_code: $data['currency_code'] ?? 'IDR',
            exchange_rate: $data['exchange_rate'] ?? 1.0,
            is_processed: $data['is_processed'] ?? false,
            created_by: $data['created_by'] ?? $data['created_by_uuid'] ?? null,
            updated_by: $data['updated_by'] ?? $data['updated_by_uuid'] ?? null,
            deleted_by: $data['deleted_by'] ?? $data['deleted_by_uuid'] ?? null,
            uuid: $data['uuid'] ?? null,
        );
    }

    /**
     * Helper untuk memastikan input waktu menjadi integer Unix Timestamp
     */
    private static function toUnixTimestamp(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if ($value instanceof Carbon) {
            return $value->timestamp;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }
        if (is_string($value)) {
            return strtotime($value) ?: time();
        }
        return time();
    }

    /**
     * Legacy compatibility for tests that still expect an Eloquent journal model.
     */
    public function fresh(array $with = []): ?JournalModel
    {
        if (empty($this->uuid)) {
            return null;
        }

        return JournalModel::query()
            ->with($with)
            ->where('uuid', $this->uuid)
            ->first();
    }

    /**
     * Legacy compatibility for tests that still expect refresh() on the service result.
     */
    public function refresh(array $with = []): ?JournalModel
    {
        return $this->fresh($with);
    }

    public function load(array|string $relations): self
    {
        if (empty($this->uuid)) {
            return $this;
        }

        $model = JournalModel::query()
            ->with((array) $relations)
            ->where('uuid', $this->uuid)
            ->first();

        if ($model && $model->relationLoaded('lines')) {
            $this->lines = $model->lines->map(fn ($line) => JournalLineEntity::fromModel($line))->values();
        }

        return $this;
    }

    public function toArray(): array
    {
        $payload = get_object_vars($this);
        $payload['status'] = $this->status->value;
        $payload['workflow_status'] = $this->workflow_status->value;
        $payload['reference_model'] = $this->reference_model?->value;
        $payload['reference_type'] = $this->reference_type?->value;
        $payload['lines'] = collect($this->lines)
            ->map(fn ($line) => get_object_vars($line))
            ->values()
            ->all();

        return $payload;
    }
}
