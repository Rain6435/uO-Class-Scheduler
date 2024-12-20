<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImportantDateResource;
use App\Http\Requests\ImportantDate\IndexRequest;
use App\Http\Requests\ImportantDate\SearchRequest;
use App\Repositories\Contracts\ImportantDatesRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ImportantDateController extends Controller
{
    public function __construct(
        private readonly ImportantDatesRepositoryInterface $repository
    ) {}

    /**
     * List important dates, optionally filtered by term/year
     *
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $dates = $this->repository->getImportantDates(
            term: $request->input('term'),
            year: $request->input('year'),
            category: $request->input('category'),
            perPage: $request->input('per_page', 15)
        );

        return ImportantDateResource::collection($dates);
    }

    /**
     * Get details for a specific important date
     *
     * @param int $id
     * @return ImportantDateResource|JsonResponse
     */
    public function show(int $id): ImportantDateResource|JsonResponse
    {
        $date = $this->repository->getImportantDateById($id);

        if (!$date) {
            return response()->json([
                'message' => 'Important date not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new ImportantDateResource($date);
    }

    /**
     * Search important dates by various criteria
     *
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     */
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $dates = $this->repository->searchImportantDates(
            term: $request->input('term'),
            year: $request->input('year'),
            filters: $request->only(['category', 'start_date', 'end_date', 'description']),
            perPage: $request->input('per_page', 15)
        );

        return ImportantDateResource::collection($dates);
    }

    /**
     * Get list of available categories
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = $this->repository->getAvailableCategories();

        return response()->json([
            'data' => $categories
        ]);
    }
}
