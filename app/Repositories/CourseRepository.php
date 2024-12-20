<?php

namespace App\Repositories;

use App\DTOs\CourseData;
use App\DTOs\SubjectData;
use App\Models\Course;
use App\Models\Subject;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseRepository implements CourseRepositoryInterface
{
    public function upsertSubject(SubjectData $subject): Subject
    {
        return Subject::updateOrCreate(
            ['code' => $subject->subjectCode],
            [
                'name' => $subject->name,
                'catalog_url' => $subject->catalogUrl,
            ]
        );
    }

    public function upsertCourse(CourseData $course): Course
    {
        return DB::transaction(function () use ($course) {
            // Create or update the course
            $courseModel = Course::updateOrCreate(
                ['code' => $course->courseCode],
                [
                    'subject_code' => $course->subjectCode,
                    'title' => $course->title,
                    'description' => $course->description,
                    'credits' => $course->credits,
                ]
            );

            // Sync prerequisites
            if (! empty($course->prerequisites)) {
                $prereqIds = Course::whereIn('code', $course->prerequisites)
                    ->pluck('id')
                    ->toArray();

                $courseModel->prerequisites()->sync($prereqIds);
            }

            // Update components
            $courseModel->components = $course->components;
            $courseModel->save();

            return $courseModel;
        });
    }

    public function getAllSubjects(): Collection
    {
        return Subject::orderBy('code')->get();
    }

    public function getSubjectsByCodes(array $codes): Collection
    {
        return Subject::whereIn('code', $codes)
            ->orderBy('code')
            ->get();
    }

    public function getAllCourses(?string $subjectCode = null, int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        return Course::with($relations)
            ->when($subjectCode, function (Builder $query) use ($subjectCode) {
                $query->where('subject_code', $subjectCode);
            })
            ->orderBy('subject_code')
            ->orderBy('code')
            ->paginate($perPage);
    }

    public function getCourseByCode(string $code, array $relations = []): ?Course
    {
        return Course::with($relations)
            ->where('code', $code)
            ->first();
    }

    public function searchCourses(?string $query = null, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Course::with(['subject', 'prerequisites'])
        ->when($query, function (Builder $query, string $searchQuery) {
            $query->where(function (Builder $q) use ($searchQuery) {
                $q->where('code', 'like', "%{$searchQuery}%")
                ->orWhere('title', 'like', "%{$searchQuery}%")
                ->orWhere('description', 'like', "%{$searchQuery}%");
            });
        })
            ->when(
                isset($filters['subject']),
                fn($q) => $q->where('subject_code', $filters['subject'])
            )
            ->when(
                isset($filters['level']),
                fn($q) => $q->where('code', 'like', "%{$filters['level']}%")
            )
            ->when(
                isset($filters['credits']),
                fn($q) => $q->where('credits', $filters['credits'])
            )
            ->orderBy('subject_code')
            ->orderBy('code')
            ->paginate($perPage);
    }
}
