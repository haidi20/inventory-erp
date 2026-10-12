<?php

namespace App\Modules\Clinical\MedicalRecord\Entities;

class ComplaintEntity
{
    public function __construct(
        public ?string $uuid = null,
        public string $branch_uuid = '',
        public ?string $company_uuid = null,
        public string $type = 'pengaduan',
        public string $subject = '',
        public string $content = '',
        public string $status = 'pending',
        public bool $is_anonymous = false,
        public ?string $created_by = null,
        public ?string $updated_by = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? '',
            company_uuid: $data['company_uuid'] ?? null,
            type: $data['type'] ?? 'pengaduan',
            subject: $data['subject'] ?? '',
            content: $data['content'] ?? '',
            status: $data['status'] ?? 'pending',
            is_anonymous: (bool) ($data['is_anonymous'] ?? false),
            created_by: $data['created_by'] ?? null,
            updated_by: $data['updated_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'type' => $this->type,
            'subject' => $this->subject,
            'content' => $this->content,
            'status' => $this->status,
            'is_anonymous' => $this->is_anonymous,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }
}

