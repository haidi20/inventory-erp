<?php

namespace App\Modules\Clinical\MedicalRecord\Services;

use App\Modules\Clinical\MedicalRecord\Entities\DoctorTaskFilterEntity;
use App\Modules\Clinical\MedicalRecord\Repositories\DoctorTaskRepository;

class DoctorTaskService
{
    public function __construct(private DoctorTaskRepository $doctorTaskRepository)
    {
    }

    public function index(DoctorTaskFilterEntity $entity): array
    {
        $activeTasks = $this->doctorTaskRepository->getActiveTasks($entity);
        $completedToday = $this->doctorTaskRepository->getCompletedTodayCounts($entity);

        $doctorSummaries = $this->doctorTaskRepository
            ->getDoctors($entity)
            ->map(function ($doctor) use ($activeTasks, $completedToday) {
                $doctorTasks = $activeTasks->where('doctor_uuid', $doctor->uuid);

                return [
                    'doctor' => $doctor,
                    'active' => $doctorTasks->count(),
                    'waiting' => $doctorTasks->where('status', 'waiting')->count(),
                    'in_progress' => $doctorTasks->where('status', 'in_progress')->count(),
                    'completed_today' => (int) ($completedToday[$doctor->uuid] ?? 0),
                ];
            })
            ->filter(fn (array $summary) => $summary['active'] > 0 || $summary['completed_today'] > 0)
            ->values();

        return [
            'activeTasks' => $activeTasks,
            'doctorSummaries' => $doctorSummaries,
            'recentCompleted' => $this->doctorTaskRepository->getRecentCompleted($entity),
            'stats' => [
                'active' => $activeTasks->count(),
                'waiting' => $activeTasks->where('status', 'waiting')->count(),
                'in_progress' => $activeTasks->where('status', 'in_progress')->count(),
                'completed_today' => (int) $completedToday->sum(),
            ],
        ];
    }
}

