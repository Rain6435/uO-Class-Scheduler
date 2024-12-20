<?php

namespace App\Repositories;

use App\Models\SavedSchedule;
use App\Models\CourseSection;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\ScheduleConflictException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    public function findById(int $id)
    {
        return SavedSchedule::with(['sections.course', 'sections.schedules'])
            ->findOrFail($id);
    }

    public function findByShareToken(string $token)
    {
        return SavedSchedule::with(['sections.course', 'sections.schedules'])
            ->where('share_token', $token)
            ->firstOrFail();
    }

    public function getForUser(?int $userId, array $filters = [])
    {
        return SavedSchedule::query()
            ->when($userId, function (Builder $query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->orWhere('is_public', true);
                });
            })
            ->when(!$userId, function (Builder $query) {
                $query->where('is_public', true);
            })
            ->when(isset($filters['term_id']), function (Builder $query) use ($filters) {
                $query->where('term_id', $filters['term_id']);
            })
            ->with(['sections.course', 'sections.schedules'])
            ->latest()
            ->paginate();
    }

    public function create(array $data)
    {
        $schedule = SavedSchedule::create($data);

        if (isset($data['sections'])) {
            foreach ($data['sections'] as $section) {
                $this->addSection($schedule->id, $section['id'], $section);
            }
        }

        return $schedule;
    }

    public function update(int $id, array $data)
    {
        $schedule = $this->findById($id);
        $schedule->update($data);
        return $schedule;
    }

    public function delete(int $id)
    {
        return SavedSchedule::destroy($id);
    }

    public function addSection(int $scheduleId, int $sectionId, array $data = [])
    {
        $schedule = $this->findById($scheduleId);

        if ($this->checkConflicts([$sectionId])) {
            throw new \Exception('Schedule has time conflicts');
        }

        $schedule->sections()->attach($sectionId, [
            'color' => $data['color'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $schedule;
    }

    public function removeSection(int $scheduleId, int $sectionId)
    {
        $schedule = $this->findById($scheduleId);
        $schedule->sections()->detach($sectionId);
        return $schedule;
    }

    public function checkConflicts(array $sectionIds)
    {
        $schedules = CourseSection::whereIn('id', $sectionIds)
            ->with('schedules')
            ->get()
            ->pluck('schedules')
            ->flatten();

        foreach ($schedules as $schedule1) {
            foreach ($schedules as $schedule2) {
                if ($schedule1->id === $schedule2->id) {
                    continue;
                }

                if ($this->schedulesOverlap($schedule1, $schedule2)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getScheduleById(int $id): ?SavedSchedule
    {
        return SavedSchedule::with(['sections.course', 'sections.schedules'])
            ->find($id);
    }

    public function createSchedule(
        int $userId,
        string $term,
        int $year,
        string $name,
        array $sectionIds
    ): SavedSchedule {
        // Check for conflicts before creating
        $conflicts = $this->checkScheduleConflicts($sectionIds);
        if (!empty($conflicts)) {
            throw new ScheduleConflictException(
                'Schedule has conflicting sections',
                conflicts: $conflicts
            );
        }

        return DB::transaction(function () use ($userId, $term, $year, $name, $sectionIds) {
            $schedule = SavedSchedule::create([
                'user_id' => $userId,
                'term' => $term,
                'year' => $year,
                'name' => $name,
            ]);

            $schedule->sections()->attach($sectionIds);

            return $schedule->load(['sections.course', 'sections.schedules']);
        });
    }
    public function getUserSchedules(int $userId): LengthAwarePaginator
    {
        return SavedSchedule::where('user_id', $userId)
            ->with(['sections.course', 'sections.schedules'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function updateSchedule(
        int $id,
        ?string $name = null,
        ?array $sectionIds = null
    ): SavedSchedule {
        // Check for conflicts if sections are being updated
        if ($sectionIds !== null) {
            $conflicts = $this->checkScheduleConflicts($sectionIds);
            if (!empty($conflicts)) {
                throw new ScheduleConflictException(
                    'Schedule has conflicting sections',
                    conflicts: $conflicts
                );
            }
        }

        return DB::transaction(function () use ($id, $name, $sectionIds) {
            $schedule = SavedSchedule::findOrFail($id);

            if ($name !== null) {
                $schedule->name = $name;
                $schedule->save();
            }

            if ($sectionIds !== null) {
                $schedule->sections()->sync($sectionIds);
            }

            return $schedule->load(['sections.course', 'sections.schedules']);
        });
    }

    public function deleteSchedule(int $id): bool
    {
        return SavedSchedule::destroy($id) > 0;
    }

    public function checkScheduleConflicts(array $sectionIds): array
    {
        $sections = CourseSection::with('schedules')
            ->whereIn('id', $sectionIds)
            ->get();

        $conflicts = [];

        foreach ($sections as $section1) {
            foreach ($sections as $section2) {
                // Skip comparing section with itself
                if ($section1->id === $section2->id) {
                    continue;
                }

                // Check for schedule overlaps
                foreach ($section1->schedules as $schedule1) {
                    foreach ($section2->schedules as $schedule2) {
                        if ($this->schedulesOverlap($schedule1, $schedule2)) {
                            $conflicts[] = [
                                'section1' => [
                                    'id' => $section1->id,
                                    'course_code' => $section1->course->course_code,
                                    'type' => $section1->type,
                                ],
                                'section2' => [
                                    'id' => $section2->id,
                                    'course_code' => $section2->course->course_code,
                                    'type' => $section2->type,
                                ],
                                'day' => $schedule1->day,
                                'time' => [
                                    'start1' => $schedule1->start_time,
                                    'end1' => $schedule1->end_time,
                                    'start2' => $schedule2->start_time,
                                    'end2' => $schedule2->end_time,
                                ],
                            ];
                        }
                    }
                }
            }
        }

        return array_unique($conflicts, SORT_REGULAR);
    }

    private function schedulesOverlap($schedule1, $schedule2): bool
    {
        if ($schedule1->day !== $schedule2->day) {
            return false;
        }

        $start1 = strtotime($schedule1->start_time);
        $end1 = strtotime($schedule1->end_time);
        $start2 = strtotime($schedule2->start_time);
        $end2 = strtotime($schedule2->end_time);

        // Check if either schedule's start time falls within the other schedule's time period
        return ($start1 >= $start2 && $start1 < $end2) ||
            ($start2 >= $start1 && $start2 < $end1);
    }
}
