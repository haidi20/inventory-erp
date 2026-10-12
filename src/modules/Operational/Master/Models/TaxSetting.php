<?php

namespace App\Modules\Operational\Master\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Core\Identity\Models\User;

/**
 * Model TaxSetting
 *
 * Konfigurasi default pajak per jenis transaksi atau entitas bisnis untuk akurasi pelaporan.
 */
class TaxSetting extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'id',
        'name',
        'percentage',
        'account_uuid',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_uuid', 'uuid');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'uuid');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'uuid');
    }

}

