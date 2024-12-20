<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Collections\CourseCollection;
use App\Http\Resources\CourseResource;
use App\Http\Requests\Course\IndexRequest;
use App\Http\Requests\Course\SearchRequest;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseRepositoryInterface $repository
    ) {}

    /**
     * List all courses, optionally filtered by subject
     *
     * @param IndexRequest $request
     * @return CourseCollection
     */
    public function index(IndexRequest $request): CourseCollection
    {
        $courses = $this->repository->getAllCourses(
            subjectCode: $request->input('subject'),
            perPage: $request->input('per_page', 15),
            relations: ['subject', 'prerequisites']
        );

        return new CourseCollection($courses);
    }

    /**
     * Get a specific course by code
     *
     * @param string $courseCode
     * @return CourseResource|JsonResponse
     */
    public function show(string $courseCode): CourseResource|JsonResponse
    {
        $course = $this->repository->getCourseByCode(
            $courseCode,
            ['subject', 'prerequisites', 'sections.schedules', 'sections.professors']
        );

        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new CourseResource($course);
    }

    /**
     * Search courses by various criteria
     *
     * @param SearchRequest $request
     * @return CourseCollection
     */
    public function search(SearchRequest $request): CourseCollection
    {
        $courses = $this->repository->searchCourses(
            query: $request->input('query'),
            filters: $request->only(['subject', 'level', 'credits']),
            perPage: $request->input('per_page', 15)
        );

        return new CourseCollection($courses);
    }
}
