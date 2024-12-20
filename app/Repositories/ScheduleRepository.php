<?php

namespace App\Repositories;

use App\DTOs\SavedScheduleData;
use App\Models\SavedSchedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use App\Exceptions\UnauthorizedAccessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    private const CACHE_TTL = 3600;

    public function getScheduleById(int $id): ?SavedSchedule
    {
        return Cache::remember("schedule:{$id}", self::CACHE_TTL, function () use ($id) {
            return SavedSchedule::with(['sections.course', 'sections.schedules'])->find($id);
        });
    }

    public function getScheduleByShareToken(string $token): ?SavedSchedule
    {
        return Cache::remember("schedule:token:{$token}", self::CACHE_TTL, function () use ($token) {
            return SavedSchedule::with(['sections.course', 'sections.schedules'])
                ->where('share_token', $token)
                ->where('is_shared', true)
                ->first();
        });
    }

    public function getUserSchedules(int $userId): LengthAwarePaginator
    {
        return SavedSchedule::with(['sections.course', 'sections.schedules'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10);
    }

    public function createSchedule(SavedScheduleData $data): SavedSchedule
    {
        return DB::transaction(function () use ($data) {
            $schedule = SavedSchedule::create([
                'user_id' => $data->userId,
                'term' => $data->term,
                'year' => $data->year,
                'name' => $data->name,
                'is_shared' => $data->isShared,
                'share_token' => $data->isShared ? $this->generateShareToken() : null,
            ]);

            $schedule->sections()->attach($data->sectionIds);

            $this->clearUserSchedulesCache($data->userId);

            return $schedule->load(['sections.course', 'sections.schedules']);
        });
    }

    public function updateSchedule(int $id, SavedScheduleData $data): SavedSchedule
    {
        $schedule = $this->getScheduleById($id);

        if (!$schedule) {
            throw new UnauthorizedAccessException('Schedule not found');
        }

        if ($schedule->user_id !== $data->userId) {
            throw new UnauthorizedAccessException('Unauthorized access to schedule');
        }

        return DB::transaction(function () use ($schedule, $data) {
            $schedule->update([
                'name' => $data->name,
                'is_shared' => $data->isShared,
                'share_token' => $data->isShared ?
                    ($schedule->share_token ?? $this->generateShareToken()) : null,
            ]);

            $schedule->sections()->sync($data->sectionIds);

            $this->clearScheduleCache($schedule->id);
            $this->clearUserSchedulesCache($schedule->user_id);

            return $schedule->load(['sections.course', 'sections.schedules']);
        });
    }

    public function deleteSchedule(int $id, int $userId): bool
    {
        $schedule = $this->getScheduleById($id);

        if (!$schedule || $schedule->user_id !== $userId) {
            return false;
        }

        $this->clearScheduleCache($id);
        $this->clearUserSchedulesCache($userId);

        return $schedule->delete();
    }

    private function generateShareToken(): string
    {
        do {
            $token = Str::random(32);
        } while (SavedSchedule::where('share_token', $token)->exists());

        return $token;
    }

    private function clearScheduleCache(int $scheduleId): void
    {
        Cache::forget("schedule:{$scheduleId}");
    }

    private function clearUserSchedulesCache(int $userId): void
    {
        Cache::forget("user:{$userId}:schedules");
    }
}
