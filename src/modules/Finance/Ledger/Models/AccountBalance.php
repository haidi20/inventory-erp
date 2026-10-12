<?php

namespace App\Modules\Finance\Ledger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Shared\Traits\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Traits\HasCompanyScope;
use App\Modules\Finance\Journal\Models\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Traits\HasSoftDeleteUnique;

use App\Modules\Core\Identity\Models\User;

/**
 * Model AccountBalance
 *
 * Menyimpan saldo akumulasi akun per periode untuk laporan neraca dan laba rugi.
 */
class AccountBalance extends Model implements AuditableContract {
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
        'account_uuid',
        'period',
        'opening_balance',
        'total_debit',
        'total_credit',
        'closing_balance',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'closing_balance' => 'decimal:2',
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





