<?php

namespace App\Modules\Operational\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Service;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model SaleItem
 *
 * Detail item (jasa/barang) yang terjual dalam satu transaksi penjualan beserta harganya.
 */
class SaleItem extends Model implements AuditableContract {
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
        'sale_uuid',
        'item_type',
        'item_uuid',
        'qty',
        'price',
        'subtotal',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_uuid', 'uuid');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'item_uuid', 'uuid');
    }
}



