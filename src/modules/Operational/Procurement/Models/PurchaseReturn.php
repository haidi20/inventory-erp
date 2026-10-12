<?php

namespace App\Modules\Operational\Procurement\Models;

use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Core\Identity\Models\User;

/**
 * Model PurchaseReturn
 *
 * Header transaksi pengembalian barang ke supplier akibat kerusakan atau ketidaksamaan spesifikasi.
 */
class PurchaseReturn extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasUuids, HasSoftDeleteUnique, HasCompanyScope, HasBranchScope;


    

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [

        'uuid',
        'id',
        'company_uuid',
        'branch_uuid',
        'return_no',
        'purchase_uuid',
        'supplier_uuid',
        'reason',
        'total',
        'returned_by_uuid',
        'returned_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_uuid', 'uuid');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_uuid', 'uuid');
    }

    public function returner()
    {
        return $this->belongsTo(User::class, 'returned_by_uuid', 'uuid');
    }

    public function returnedBy()
    {
        return $this->returner();
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'return_uuid', 'uuid');
    }
}



