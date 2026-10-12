<?php

namespace App\Modules\Operational\Master\Models;

use App\Modules\Operational\Master\Models\Product;
use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasSoftDeleteUnique;

/**
 * Model ProductQuotation
 *
 * Dokumentasi penawaran harga dari supplier untuk bahan pertimbangan proses pengadaan.
 */
class ProductQuotation extends Model implements AuditableContract {
    protected $dateFormat = 'U';
    
    use Auditable, Blameable, HasFactory, HasUuids, HasSoftDeleteUnique;


    

    protected $table = 'product_quotation';

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}



