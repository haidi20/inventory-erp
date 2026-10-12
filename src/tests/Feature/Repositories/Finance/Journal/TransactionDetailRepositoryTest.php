<?php

namespace Tests\Feature\Repositories\Finance\Journal;

use App\Modules\Finance\Journal\Repositories\TransactionDetailRepository;
use Tests\TestCase;

class TransactionDetailRepositoryTest extends TestCase
{

    public function test_create_transaction_details(): void
    {
        $method = new \ReflectionMethod(TransactionDetailRepository::class, 'createTransactionDetails');
        $this->assertTrue($method->isPublic());
    }
}