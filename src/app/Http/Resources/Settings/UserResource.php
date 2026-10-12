<?php

namespace App\Http\Resources\Settings;

use App\Http\Resources\Concerns\HasFormattedTimestamps;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    use HasFormattedTimestamps;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $allRoles = $this->roles ?? collect();
        $role = $allRoles->first();
        $employee = $this->employee;

        // Deterministic color based on name
        $colors = ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6', '#f43f5e'];
        $colorIndex = crc32($this->uuid ?? $this->name) % count($colors);
        $avatarColor = $colors[abs($colorIndex)];

        return $this->formatPayloadTimestamps([
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'role_uuid' => $role?->uuid,
            'role_name' => $role?->name ?? 'NO ROLE',
            'role_label' => $role?->label ?? 'User Tanpa Hak Akses',
            'role_uuids' => $allRoles->pluck('uuid')->values()->toArray(),
            'roles' => $allRoles->map(fn($r) => [
                'uuid'  => $r->uuid,
                'name'  => $r->name,
                'label' => $r->label,
            ])->values()->toArray(),
            'employee_uuid' => $employee?->uuid,
            'employee_name' => $employee?->full_name ?? $employee?->name,
            'created_at_label' => $this->created_at ? \Carbon\Carbon::createFromTimestamp($this->created_at)->diffForHumans() : '-',
            'avatar_initial' => strtoupper(substr($this->name, 0, 1)),
            'avatar_color' => $avatarColor,
            'is_current_user' => (Auth::check() && $this->uuid === Auth::user()->uuid),
        ]);
    }
}
