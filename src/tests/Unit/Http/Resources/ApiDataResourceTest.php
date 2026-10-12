<?php

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\ApiDataResource;
use Tests\TestCase;

class ApiDataResourceTest extends TestCase
{
    public function test_it_adds_formatted_labels_for_unix_timestamp_fields(): void
    {
        $resource = new ApiDataResource([
            'created_at' => 1704067200, // 01-01-2024
            'profile' => [
                'birth_date' => 946684800, // 01-01-2000
            ],
        ]);

        $payload = $resource->resolve();

        $this->assertSame('01-01-2024 08:00:00', $payload['created_at_formatted']);
        $this->assertSame('01 Januari 2024 08:00:00', $payload['created_at_formatted_long']);

        $this->assertSame('01-01-2000', $payload['profile']['birth_date_formatted']);
        $this->assertSame('01 Januari 2000', $payload['profile']['birth_date_formatted_long']);
    }

    public function test_it_formats_date_string_fields_with_indonesia_standard(): void
    {
        $resource = new ApiDataResource([
            'journal_date' => '2024-12-25',
            'description' => 'closing period',
        ]);

        $payload = $resource->resolve();

        $this->assertSame('25-12-2024', $payload['journal_date_formatted']);
        $this->assertSame('25 Desember 2024', $payload['journal_date_formatted_long']);
        $this->assertArrayNotHasKey('description_formatted', $payload);
    }
}
