<?php

namespace App\Modules\Operational\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Clinical\Registration\Models\Patient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Operational\Master\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model Sale
 *
 * Header transaksi penjualan atau kasir yang mencatat total pendapatan dan cara pembayaran.
 */
class Sale extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasBranchScope, HasCompanyScope, HasUuids, HasSoftDeleteUnique;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'branch_uuid',
        'company_uuid',
        'registration_uuid',
        'patient_uuid',
        'cashier_uuid',
        'sale_no',
        'total_amount',
        'paid',
        'change',
        'payment_status',
        'payment_method',
        'sale_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function getTotalAttribute(): float
    {
        return (float) $this->total_amount;
    }

    public function getStatusAttribute(): ?string
    {
        return $this->payment_status;
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_uuid', 'uuid');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_uuid', 'uuid');
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_uuid', 'uuid');
    }

    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_uuid', 'uuid');
    }
}


