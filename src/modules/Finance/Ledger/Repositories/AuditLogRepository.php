<?php

namespace App\Modules\Finance\Ledger\Repositories;


use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Ledger\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Repository untuk Audit Trail/Logs.
 */
class AuditLogRepository
{
    /**
     * Membuat log audit baru.
     */
    public function create(array $data): AuditLog
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return AuditLog::create([
                'branch_uuid' => $data['branch_uuid'] ?? null,
                'user_uuid' => $data['user_uuid'] ?? Auth::user()?->uuid,
                'event' => $data['event'],
                'entity_type' => $data['entity_type'],
                'entity_uuid' => $data['entity_uuid'],
                'before' => $data['before'] ?? null,
                'after' => $data['after'] ?? null,
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
