<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Collections\SectionCollection;
use App\Http\Requests\Timetable\IndexRequest;
use App\Http\Requests\Timetable\SearchRequest;
use App\Repositories\Contracts\TimetableRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TimetableController extends Controller
{
    public function __construct(
        private readonly TimetableRepositoryInterface $repository
    ) {}

    /**
     * List sections for a term, optionally filtered by subject
     *
     * @param IndexRequest $request
     * @return SectionCollection
     */
    public function index(IndexRequest $request): SectionCollection
    {
        $sections = $this->repository->getSections(
            term: $request->input('term'),
            year: $request->input('year'),
            subjectCode: $request->input('subject'),
            perPage: $request->input('per_page', 15),
            relations: ['course', 'schedules', 'professors']
        );

        return new SectionCollection($sections);
    }

    /**
     * Get timetable for a specific course
     *
     * @param string $courseCode
     * @param string $term
     * @param int $year
     * @return SectionCollection|JsonResponse
     */
    public function show(string $courseCode, string $term, int $year): SectionCollection|JsonResponse
    {
        $sections = $this->repository->getSectionsByCourse(
            courseCode: $courseCode,
            term: $term,
            year: $year,
            relations: ['course', 'schedules', 'professors']
        );

        if ($sections->isEmpty()) {
            return response()->json([
                'message' => 'No sections found for this course'
            ], Response::HTTP_NOT_FOUND);
        }

        return new SectionCollection($sections);
    }

    /**
     * Search sections by various criteria
     *
     * @param SearchRequest $request
     * @return SectionCollection
     */
    public function search(SearchRequest $request): SectionCollection
    {
        $sections = $this->repository->searchSections(
            term: $request->input('term'),
            year: $request->input('year'),
            filters: $request->only([
                'subject',
                'course',
                'professor',
                'type',
                'days',
                'status',
                'time_start',
                'time_end'
            ]),
            perPage: $request->input('per_page', 15)
        );

        return new SectionCollection($sections);
    }

    /**
     * Get available terms
     *
     * @return JsonResponse
     */
    public function terms(): JsonResponse
    {
        $terms = $this->repository->getAvailableTerms();

        return response()->json([
            'data' => $terms->map(fn($term) => [
                'term' => $term->term,
                'year' => $term->year,
                'sections_count' => $term->sections_count
            ])
        ]);
    }
}
