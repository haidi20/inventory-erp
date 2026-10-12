<?php

namespace App\Modules\Core\Identity\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model: relasi many-to-many antara User dan Branch.
 * company_uuid ada di pivot karena 1 user bisa punya branch di multiple companies.
 * 
 * @property string $uuid
 * @property string $user_uuid
 * @property string $company_uuid
 * @property string $branch_uuid
 * @property bool $is_default
 * @property int|null $created_at
 * @property string|null $created_by
 */
class UserBranch extends Model
{
    use HasUuids;

    protected $dateFormat = 'U';

    protected $table = 'user_branches';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    const UPDATED_AT = null;

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * User yang memiliki akses ke branch ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Company tempat branch ini berada.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }

    /**
     * Branch yang diberikan akses ke user ini.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_uuid', 'uuid');
    }
}
