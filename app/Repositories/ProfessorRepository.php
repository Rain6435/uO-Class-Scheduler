<?php

namespace App\Repositories;

use App\DTOs\ProfessorData;
use App\Models\Professor;
use App\Repositories\Contracts\ProfessorRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProfessorRepository implements ProfessorRepositoryInterface
{
    public function upsertProfessor(ProfessorData $professor): Professor
    {
        return Professor::updateOrCreate(
            [
                'first_name' => $professor->firstName,
                'last_name' => $professor->lastName,
            ],
            [
                'rating' => $professor->rating,
                'total_ratings' => $professor->totalRatings,
                'rmp_id' => $professor->rmpId,
            ]
        );
    }

    public function getProfessorsByName(string $firstName, string $lastName): Collection
    {
        return Professor::where('first_name', 'LIKE', "%{$firstName}%")
            ->where('last_name', 'LIKE', "%{$lastName}%")
            ->get();
    }

    public function getProfessors(
        ?string $subject = null,
        ?string $term = null,
        ?int $year = null,
        int $perPage = 15,
        string $sortBy = 'last_name',
        string $sortDirection = 'asc'
    ): LengthAwarePaginator {
        $query = Professor::query()
            ->when($subject, function ($q) use ($subject) {
                $q->whereHas('sections', function ($q) use ($subject) {
                    $q->whereHas('course', function ($q) use ($subject) {
                        $q->where('subject_code', $subject);
                    });
                });
            })
            ->when($term && $year, function ($q) use ($term, $year) {
                $q->whereHas('sections', function ($q) use ($term, $year) {
                    $q->where('term', $term)->where('year', $year);
                });
            });

        // Handle sorting
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    public function getProfessorById(int $id): ?Professor
    {
        return Professor::with(['sections.course', 'sections.schedules'])->find($id);
    }

    public function searchProfessors(
        string $query,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $search = Professor::query()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->when(
                isset($filters['subject']),
                fn($q) => $q->whereHas('sections', function ($q) use ($filters) {
                    $q->whereHas('course', function ($q) use ($filters) {
                        $q->where('subject_code', $filters['subject']);
                    });
                })
            )
            ->when(
                isset($filters['rating_min']),
                fn($q) => $q->where('rating', '>=', $filters['rating_min'])
            )
            ->when(
                isset($filters['rating_max']),
                fn($q) => $q->where('rating', '<=', $filters['rating_max'])
            )
            ->when(
                isset($filters['total_ratings_min']),
                fn($q) => $q->where('total_ratings', '>=', $filters['total_ratings_min'])
            )
            ->orderBy('last_name')
            ->orderBy('first_name');

        return $search->paginate($perPage);
    }

    public function getTeachingHistory(int $id): array
    {
        return DB::table('course_sections')
        ->join('professor_section', 'course_sections.id', '=', 'professor_section.section_id')
        ->join('courses', 'course_sections.course_id', '=', 'courses.id')
        ->where('professor_section.professor_id', $id)
            ->select(
                'courses.subject_code',
                'courses.course_code',
                'courses.title',
                'course_sections.term',
                'course_sections.year',
                'course_sections.type'
            )
            ->orderBy('course_sections.year', 'desc')
            ->orderBy('course_sections.term', 'desc')
            ->get()
            ->groupBy(['year', 'term'])
            ->toArray();
    }
}
