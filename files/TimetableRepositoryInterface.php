<?php

namespace App\Repositories\Contracts;

use App\DTOs\SectionData;
use App\Models\CourseSection;
use App\Models\Term;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TimetableRepositoryInterface
{
    public function upsertSection(SectionData $section): CourseSection;

    /**
     * Get sections by various criteria
     *
     * @param string $term
     * @param int $year
     * @param string|null $subjectCode
     * @param int $perPage
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function getSections(
        string $term,
        int $year,
        ?string $subjectCode = null,
        int $perPage = 15,
        array $relations = []
    ): LengthAwarePaginator;

    /**
     * Get sections for a specific course
     *
     * @param string $courseCode
     * @param string $term
     * @param int $year
     * @param array $relations
     * @return Collection
     */
    public function getSectionsByCourse(
        string $courseCode,
        string $term,
        int $year,
        array $relations = []
    ): Collection;

    /**
     * Search sections by various criteria
     *
     * @param string $term
     * @param int $year
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchSections(
        string $term,
        int $year,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Get all available terms with section counts
     *
     * @return Collection
     */
    public function getAvailableTerms(): Collection;
}
