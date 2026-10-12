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
use App\Modules\Operational\Master\Models\Product;

/**
 * Model PurchaseReturnItem
 *
 * Rincian barang yang dikembalikan (retur) beserta alasan retur yang spesifik.
 */
class PurchaseReturnItem extends Model implements AuditableContract {
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
        'return_uuid',
        'product_uuid',
        'qty',
        'price',
        'subtotal',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'return_uuid', 'uuid');
    }

    public function return()
    {
        return $this->purchaseReturn();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}



