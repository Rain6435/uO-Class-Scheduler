<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfessorResource;
use App\Http\Requests\Professor\IndexRequest;
use App\Http\Requests\Professor\SearchRequest;
use App\Repositories\Contracts\ProfessorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProfessorController extends Controller
{
    public function __construct(
        private readonly ProfessorRepositoryInterface $repository
    ) {}

    /**
     * List professors, optionally filtered by criteria
     *
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $professors = $this->repository->getProfessors(
            subject: $request->input('subject'),
            term: $request->input('term'),
            year: $request->input('year'),
            perPage: $request->input('per_page', 15),
            sortBy: $request->input('sort_by', 'last_name'),
            sortDirection: $request->input('sort_direction', 'asc')
        );

        return ProfessorResource::collection($professors);
    }

    /**
     * Get details for a specific professor
     *
     * @param int $id
     * @return ProfessorResource|JsonResponse
     */
    public function show(int $id): ProfessorResource|JsonResponse
    {
        $professor = $this->repository->getProfessorById($id);

        if (!$professor) {
            return response()->json([
                'message' => 'Professor not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new ProfessorResource($professor);
    }

    /**
     * Search professors by various criteria
     *
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     */
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $professors = $this->repository->searchProfessors(
            query: $request->input('query'),
            filters: $request->only([
                'subject',
                'rating_min',
                'rating_max',
                'total_ratings_min'
            ]),
            perPage: $request->input('per_page', 15)
        );

        return ProfessorResource::collection($professors);
    }

    /**
     * Get professor's teaching history
     *
     * @param int $id
     * @return JsonResponse
     */
    public function history(int $id): JsonResponse
    {
        $professor = $this->repository->getProfessorById($id);

        if (!$professor) {
            return response()->json([
                'message' => 'Professor not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $history = $this->repository->getTeachingHistory($id);

        return response()->json([
            'data' => $history
        ]);
    }
}
