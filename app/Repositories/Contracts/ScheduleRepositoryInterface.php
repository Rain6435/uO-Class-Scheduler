<?php


namespace App\Repositories\Contracts;

use App\DTOs\SavedScheduleData;
use App\Models\SavedSchedule;
use Illuminate\Pagination\LengthAwarePaginator;

interface ScheduleRepositoryInterface
{
    public function getScheduleById(int $id): ?SavedSchedule;
    public function getScheduleByShareToken(string $token): ?SavedSchedule;
    public function getUserSchedules(int $userId): LengthAwarePaginator;
    public function createSchedule(SavedScheduleData $data): SavedSchedule;
    public function updateSchedule(int $id, SavedScheduleData $data): SavedSchedule;
    public function deleteSchedule(int $id, int $userId): bool;
}
