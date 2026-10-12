<?php

namespace App\Modules\Core\Identity\Models;

use App\Modules\Core\Organization\Models\Company;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model: relasi many-to-many antara User dan Company.
 * 
 * @property string $uuid
 * @property string $user_uuid
 * @property string $company_uuid
 * @property bool $is_default
 * @property int|null $created_at
 * @property string|null $created_by
 */
class UserCompany extends Model
{
    use HasUuids;

    protected $dateFormat = 'U';

    protected $table = 'user_companies';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    const UPDATED_AT = null;

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * User yang memiliki akses ke company ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Company yang diberikan akses ke user ini.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_uuid', 'uuid');
    }
}
