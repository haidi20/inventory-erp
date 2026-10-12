<?php

namespace App\Modules\Core\Platform\Services;

use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;
use App\Modules\Core\Platform\Repositories\NoticeRepository;

class NoticeService
{
    public function __construct(private NoticeRepository $noticeRepository)
    {
    }

    public function index(): array
    {
        $announcements = $this->noticeRepository
            ->all()
            ->sortByDesc(function (NoticeEntity $item) {
                return match ($item->priority) {
                    'high' => 3,
                    'medium' => 2,
                    default => 1,
                };
            })
            ->values();

        return [
            'announcements' => $announcements,
            'stats' => [
                'total' => $announcements->count(),
                'high_priority' => $announcements->where('priority', 'high')->count(),
                'updated_today' => $announcements->filter(fn (NoticeEntity $item) => $item->updated_at->isToday())->count(),
            ],
        ];
    }
}
