<?php

namespace App\Modules\Finance\Ledger\Models;

use App\Modules\Operational\Procurement\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model Payable
 *
 * Mencatat kewajiban hutang dagang kepada supplier dari transaksi pembelian yang belum lunas.
 */
class Payable extends Model implements AuditableContract {
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
        'reference_type',
        'reference_uuid',
        'amount',
        'paid',
        'status',
        'due_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'reference_uuid', 'uuid');
    }
}




