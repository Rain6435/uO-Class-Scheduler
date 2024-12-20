<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\SectionSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleConflictService
{
    /**
     * Check for conflicts between sections
     */
    public function checkConflicts(array $sectionIds): array
    {
        $conflicts = [];
        $sectionConflicts = [];

        // Get all sections with their schedules and courses
        $sections = CourseSection::with(['schedules', 'course'])
            ->whereIn('id', $sectionIds)
            ->get()
            ->keyBy('id');

        foreach ($sections as $section1) {
            $courseId1 = $section1->course->getCourseCode();

            $conflictInfo = [
                'courseId' => $courseId1,
                'sectionId' => $section1->section_id,
                'conflictsWith' => []
            ];

            foreach ($sections as $section2) {
                // Skip same section
                if ($section1->id === $section2->id) {
                    continue;
                }

                $courseId2 = $section2->course->getCourseCode();

                // Skip same course
                if ($courseId1 === $courseId2) {
                    continue;
                }

                $hasConflict = false;

                // Check conflicts between schedules
                foreach ($section1->schedules as $schedule1) {
                    foreach ($section2->schedules as $schedule2) {
                        if ($this->schedulesOverlap($schedule1, $schedule2)) {
                            $hasConflict = true;

                            $conflicts[] = [
                                'course1' => $courseId1,
                                'course2' => $courseId2,
                                'day' => $schedule1->day,
                                'time1' => $this->formatTime($schedule1),
                                'time2' => $this->formatTime($schedule2),
                                'section1' => $section1->section_id,
                                'section2' => $section2->section_id,
                            ];
                        }
                    }
                }

                if ($hasConflict) {
                    $conflictInfo['conflictsWith'][] = [
                        'courseId' => $courseId2,
                        'sectionId' => $section2->section_id
                    ];
                }
            }

            if (!empty($conflictInfo['conflictsWith'])) {
                $sectionConflicts[] = $conflictInfo;
            }
        }

        return [
            'conflicts' => $conflicts,
            'sectionConflicts' => $sectionConflicts
        ];
    }

    /**
     * Check if schedules overlap
     */
    private function schedulesOverlap(SectionSchedule $schedule1, SectionSchedule $schedule2): bool
    {
        if ($schedule1->day !== $schedule2->day) {
            return false;
        }

        $start1 = $this->parseTime($schedule1->start_time);
        $end1 = $this->parseTime($schedule1->end_time);
        $start2 = $this->parseTime($schedule2->start_time);
        $end2 = $this->parseTime($schedule2->end_time);

        return ($start1 >= $start2 && $start1 < $end2) ||
            ($start2 >= $start1 && $start2 < $end1);
    }

    /**
     * Parse time string to timestamp
     */
    private function parseTime(string $time): int
    {
        return Carbon::createFromFormat('H:i:s', $time)->timestamp;
    }

    /**
     * Format time for display
     */
    private function formatTime(SectionSchedule $schedule): string
    {
        $start = Carbon::createFromFormat('H:i:s', $schedule->start_time)
            ->format('g:i A');
        $end = Carbon::createFromFormat('H:i:s', $schedule->end_time)
            ->format('g:i A');

        return "{$start} - {$end}";
    }

    /**
     * Check if a section is selectable
     */
    public function isSectionSelectable(
        string $courseId,
        string $sectionId,
        array $selectedSections,
        array $sectionConflicts
    ): bool {
        $conflictInfo = collect($sectionConflicts)
            ->first(fn($c) => $c['courseId'] === $courseId && $c['sectionId'] === $sectionId);

        if (!$conflictInfo) {
            return true;
        }

        return !collect($conflictInfo['conflictsWith'])->some(
            fn($conflict) => collect($selectedSections)->some(
                fn($selected) =>
                $selected['courseId'] === $conflict['courseId'] &&
                    $selected['sectionId'] === $conflict['sectionId']
            )
        );
    }
}
