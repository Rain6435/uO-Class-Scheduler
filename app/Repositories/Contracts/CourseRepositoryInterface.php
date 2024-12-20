<?php

namespace App\Repositories\Contracts;

use App\DTOs\CourseData;
use App\DTOs\SubjectData;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CourseRepositoryInterface
{
    public function upsertSubject(SubjectData $subject): Subject;
    public function upsertCourse(CourseData $course): Course;
    public function getAllSubjects(): Collection;
    public function getSubjectsByCodes(array $codes): Collection;

    /**
     * Get all courses, optionally filtered by subject
     *
     * @param string|null $subjectCode
     * @param int $perPage
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function getAllCourses(?string $subjectCode = null, int $perPage = 15, array $relations = []): LengthAwarePaginator;

    /**
     * Get a course by its code
     *
     * @param string $code
     * @param array $relations
     * @return Course|null
     */
    public function getCourseByCode(string $code, array $relations = []): ?Course;

    /**
     * Search courses by various criteria
     *
     * @param string|null $query
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchCourses(?string $query = null, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
