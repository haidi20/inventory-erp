<?php

namespace App\Modules\Clinical\Pharmacy\Models;

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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model DispensingItem
 *
 * Rincian obat, dosis, dan aturan pakai yang diserahkan dalam satu sesi dispensing.
 */
class DispensingItem extends Model implements AuditableContract {
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
        'dispensing_uuid',
        'product_uuid',
        'qty',
        'price_sell',
        'subtotal',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price_sell' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function dispensing()
    {
        return $this->belongsTo(Dispensing::class, 'dispensing_uuid', 'uuid');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}



