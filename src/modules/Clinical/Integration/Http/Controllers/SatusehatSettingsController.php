<?php

namespace App\Modules\Clinical\Integration\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;
use App\Modules\Clinical\Integration\Services\SatusehatSettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SatusehatSettingsController extends Controller
{
    public function __construct(
        private SatusehatSettingsService $service
    ) {
        parent::__construct();
    }

    public function index(): Response
    {
        $settings = $this->service->getSettings();

        return Inertia::render('Clinical/Integration/presentations/views/SatusehatSettingsIndex', [
            'settings' => $settings?->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'org_uuid'      => 'nullable|string|max:255',
            'client_uuid'   => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:500',
            'base_url'      => 'nullable|string|max:500',
            'auth_url'      => 'nullable|string|max:500',
            'is_active'     => 'boolean',
        ]);

        $entity = SatusehatSettingsEntity::fromArray(array_merge($data, [
            'actor_uuid' => $request->user()?->uuid,
        ]));

        $this->service->saveSettings($entity);

        return back()->with('success', 'Konfigurasi SATUSEHAT disimpan.');
    }
}