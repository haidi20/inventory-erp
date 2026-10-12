<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use App\Shared\Traits\HasFormattedTimestamps;
use Illuminate\Support\Carbon;

class HasFormattedTimestampsTest extends TestCase
{
    private $testClass;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat anonymous class untuk testing trait
        $this->testClass = new class {
            use HasFormattedTimestamps;
        };
    }

    /** @test */
    public function it_can_format_unix_timestamp_to_indonesian_date()
    {
        // 1713254400 = 16 April 2024
        $timestamp = 1713254400;

        $this->assertEquals('16-04-2024', $this->testClass->formatShortDate($timestamp));
        $this->assertEquals('16 April 2024', $this->testClass->formatFullDate($timestamp));
    }

    /** @test */
    public function it_can_format_unix_timestamp_to_indonesian_datetime()
    {
        // 1713261600 = 16 April 2024 10:00:00
        $timestamp = 1713261600;

        $this->assertEquals('16-04-2024 10:00', $this->testClass->formatShortDateTime($timestamp));
        $this->assertEquals('16 April 2024 10:00', $this->testClass->formatFullDateTime($timestamp));
    }

    /** @test */
    public function it_returns_null_for_invalid_timestamps()
    {
        $this->assertNull($this->testClass->formatShortDate(null));
        $this->assertNull($this->testClass->formatFullDate(0)); // Anggap 0 as null/invalid for business logic if needed
    }

    /** @test */
    public function it_handles_custom_formats()
    {
        $timestamp = 1713254400;
        $this->assertEquals('2024/04/16', $this->testClass->formatTimestamp($timestamp, 'Y/m/d'));
    }
}
