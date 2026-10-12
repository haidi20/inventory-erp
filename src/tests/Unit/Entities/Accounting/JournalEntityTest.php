<?php

namespace Tests\Unit\Entities\Accounting;

use Tests\TestCase;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use Illuminate\Support\Carbon;

class JournalEntityTest extends TestCase
{
    /** @test */
    public function it_casts_string_date_to_unix_timestamp_integer()
    {
        $data = [
            'branch_uuid' => 'branch-1',
            'reference_no' => 'REF-001',
            'journal_date' => '2026-04-16',
            'lines' => []
        ];

        $entity = JournalEntity::fromArray($data);

        $this->assertIsInt($entity->journal_date);
        $this->assertEquals(strtotime('2026-04-16'), $entity->journal_date);
    }

    /** @test */
    public function it_casts_carbon_object_to_unix_timestamp_integer()
    {
        $now = Carbon::now();
        $data = [
            'branch_uuid' => 'branch-1',
            'reference_no' => 'REF-001',
            'journal_date' => $now,
            'lines' => []
        ];

        $entity = JournalEntity::fromArray($data);

        $this->assertIsInt($entity->journal_date);
        $this->assertEquals($now->timestamp, $entity->journal_date);
    }

    /** @test */
    public function it_uses_current_time_if_date_is_missing()
    {
        $data = [
            'branch_uuid' => 'branch-1',
            'reference_no' => 'REF-001',
            'lines' => []
        ];

        $before = time();
        $entity = JournalEntity::fromArray($data);
        $after = time();

        $this->assertIsInt($entity->journal_date);
        $this->assertGreaterThanOrEqual($before, $entity->journal_date);
        $this->assertLessThanOrEqual($after, $entity->journal_date);
    }

    /** @test */
    public function it_casts_scheduled_at_to_unix_timestamp_integer()
    {
        $data = [
            'branch_uuid' => 'branch-1',
            'reference_no' => 'REF-001',
            'scheduled_at' => '2026-04-20 10:00:00',
            'lines' => []
        ];

        $entity = JournalEntity::fromArray($data);

        $this->assertIsInt($entity->scheduled_at);
        $this->assertEquals(strtotime('2026-04-20 10:00:00'), $entity->scheduled_at);
    }
}
