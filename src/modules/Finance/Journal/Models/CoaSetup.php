<?php

namespace App\Modules\Finance\Journal\Models;


use App\Shared\Traits\HasBranchScope;
use App\Shared\Traits\HasCompanyScope;use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasSoftDeleteUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoaSetup extends Model implements AuditableContract {
    protected $dateFormat = 'U';

    use HasBranchScope;
    use HasCompanyScope;
    use Auditable, Blameable, HasFactory, HasSoftDeleteUnique;

    protected $table = 'coa_setups';

    protected $primaryKey = 'program_account';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];
}
