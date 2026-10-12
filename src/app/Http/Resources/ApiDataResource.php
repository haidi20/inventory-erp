<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasFormattedTimestamps;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiDataResource extends JsonResource
{
    use HasFormattedTimestamps;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = parent::toArray($request);
        if (!is_array($payload)) {
            return ['data' => $payload];
        }

        return $this->formatPayloadTimestamps($payload);
    }
}

