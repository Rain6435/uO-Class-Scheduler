<?php

namespace App\Repositories;

use App\DTOs\SectionData;
use App\Models\CourseSection;
use App\Models\Professor;
use App\Models\SectionSchedule;
use App\Models\Term;
use App\Repositories\Contracts\TimetableRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class TimetableRepository implements TimetableRepositoryInterface
{
    public function upsertSection(SectionData $section): CourseSection
    {
        return DB::transaction(function () use ($section) {
            // Get or create term
            $term = Term::firstOrCreate([
                'term' => $section->term,
                'year' => $section->year,
            ]);

            // Create or update the section
            $sectionModel = CourseSection::updateOrCreate(
                [
                    'section_id' => $section->sectionId,
                    'term_id' => $term->id,
                    'course_code' => $section->courseCode,
                ],
                [
                    'type' => $section->type,
                    'status' => $section->status,
                ]
            );

            // Update schedules
            $sectionModel->schedules()->delete();
            foreach ($section->schedule as $schedule) {
                SectionSchedule::create([
                    'section_id' => $sectionModel->id,
                    'day' => $schedule['day'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'room' => $schedule['room'],
                    'start_date' => $schedule['start_date'],
                    'end_date' => $schedule['end_date'],
                ]);
            }

            // Sync professors
            $professorIds = [];
            foreach ($section->professors as $professorName) {
                // Attempt to parse name
                $nameParts = explode(' ', trim($professorName));
                $lastName = array_pop($nameParts);
                $firstName = implode(' ', $nameParts);

                $professor = Professor::firstOrCreate([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);

                $professorIds[] = $professor->id;
            }
            $sectionModel->professors()->sync($professorIds);

            return $sectionModel;
        });
    }

    public function getSectionsBySubject(string $subjectCode, string $term, int $year): Collection
    {
        return CourseSection::with(['course', 'schedules', 'professors'])
        ->whereRaw('SUBSTR(course_code, 1, 3) = ?', [$subjectCode])
            ->whereHas('term', function ($query) use ($term, $year) {
                $query->where('term', $term)
                    ->where('year', $year);
            })
            ->orderBy('course_code')
            ->orderBy('section_id')
            ->get();
    }

    public function getSections(
        string $term,
        int $year,
        ?string $subjectCode = null,
        int $perPage = 15,
        array $relations = []
    ): LengthAwarePaginator {
        return CourseSection::with($relations)
            ->whereHas('term', function (Builder $query) use ($term, $year) {
                $query->where('term', $term)
                    ->where('year', $year);
            })
            ->when($subjectCode, function (Builder $query) use ($subjectCode) {
                $query->whereRaw('SUBSTR(course_code, 1, 3) = ?', [$subjectCode]);
            })
            ->orderBy('course_code')
            ->orderBy('section_id')
            ->paginate($perPage);
    }

    public function getSectionsByCourse(
        string $courseCode,
        string $term,
        int $year,
        array $relations = []
    ): Collection {
        return CourseSection::with($relations)
            ->where('course_code', $courseCode)
            ->whereHas('term', function (Builder $query) use ($term, $year) {
                $query->where('term', $term)
                    ->where('year', $year);
            })
            ->orderBy('section_id')
            ->get();
    }

    public function searchSections(
        string $term,
        int $year,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return CourseSection::with(['course', 'schedules', 'professors'])
        ->whereHas('term', function (Builder $query) use ($term, $year) {
            $query->where('term', $term)
                ->where('year', $year);
        })
            ->when(
                isset($filters['subject']),
                fn($q) => $q->whereRaw('SUBSTR(course_code, 1, 3) = ?', [$filters['subject']])
            )
            ->when(
                isset($filters['course']),
                fn($q) => $q->where('course_code', $filters['course'])
            )
            ->when(
                isset($filters['type']),
                fn($q) => $q->where('type', $filters['type'])
            )
            ->when(
                isset($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['professor']),
                fn($q) => $q->whereHas('professors', function (Builder $query) use ($filters) {
                    $query->where('first_name', 'like', "%{$filters['professor']}%")
                    ->orWhere('last_name', 'like', "%{$filters['professor']}%");
                })
            )
            ->when(
                isset($filters['days']),
                fn($q) => $q->whereHas('schedules', function (Builder $query) use ($filters) {
                    $query->whereIn('day', $filters['days']);
                })
            )
            ->when(
                isset($filters['time_start']),
                fn($q) => $q->whereHas('schedules', function (Builder $query) use ($filters) {
                    $query->where('start_time', '>=', $filters['time_start']);
                })
            )
            ->when(
                isset($filters['time_end']),
                fn($q) => $q->whereHas('schedules', function (Builder $query) use ($filters) {
                    $query->where('end_time', '<=', $filters['time_end']);
                })
            )
            ->orderBy('course_code')
            ->orderBy('section_id')
            ->paginate($perPage);
    }

    public function getAvailableTerms(): Collection
    {
        return Term::withCount('sections')
        ->orderBy('year', 'desc')
        ->orderByRaw("FIELD(term, 'fall', 'summer', 'winter')")
        ->get();
    }
}
