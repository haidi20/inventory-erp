<?php

namespace App\Shared\Models;

use OwenIt\Auditing\Models\Audit as BaseAudit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Custom Audit model untuk support UUID sebagai foreign key + multi-org context.
 *
 * Tambahan dari base:
 * - UUID primary key
 * - Kolom company_uuid & branch_uuid untuk compliance audit trail per faskes
 * - Cast user_id & auditable_id sebagai string (UUID)
 */
class Audit extends BaseAudit
{
    use HasUuids;

    protected $dateFormat = 'U';

    protected $table = 'audits';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'old_values'   => 'json',
        'new_values'   => 'json',
        'user_id'      => 'string',
        'auditable_id' => 'string',
        'company_uuid' => 'string',
        'branch_uuid'  => 'string',
    ];

    /**
     * Override getAttribute untuk memastikan UUID fields selalu string.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($value !== null && in_array($key, ['user_id', 'auditable_id', 'company_uuid', 'branch_uuid'])) {
            return (string) $value;
        }

        return $value;
    }

    /**
     * Relasi ke Company untuk audit trail per faskes.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    /**
     * Relasi ke Branch untuk audit trail per cabang.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }

    /**
     * Scope: filter audit records berdasarkan company.
     */
    public function scopeForCompany($query, string $companyUuid)
    {
        return $query->where('company_uuid', $companyUuid);
    }

    /**
     * Scope: filter audit records berdasarkan branch.
     */
    public function scopeForBranch($query, string $branchUuid)
    {
        return $query->where('branch_uuid', $branchUuid);
    }
}