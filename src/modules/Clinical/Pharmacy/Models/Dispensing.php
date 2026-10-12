<?php

namespace App\Modules\Clinical\Pharmacy\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model Dispensing
 *
 * Proses penyiapan dan penyerahan obat kepada pasien berdasarkan resep medis yang valid.
 */
class Dispensing extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'dispensing_no',
        'patient_uuid',
        'dispensed_by_uuid',
        'notes',
        'total',
        'dispensed_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_uuid', 'uuid');
    }

    /** @return BelongsTo<User, $this> */
    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by_uuid', 'uuid');
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->dispenser();
    }

    /** @return HasMany<DispensingItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(DispensingItem::class, 'dispensing_uuid', 'uuid');
    }
}



