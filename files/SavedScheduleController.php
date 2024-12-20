<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavedScheduleResource;
use App\Http\Requests\Schedule\StoreRequest;
use App\Http\Requests\Schedule\UpdateRequest;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Collections\SavedScheduleCollection;


class SavedScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $repository
    ) {}

    /**
     * Get user's saved schedules
     */
    public function index(): SavedScheduleCollection
    {
        $schedules = $this->repository->getUserSchedules(
            auth()->id()
        );

        return new SavedScheduleCollection($schedules);
    }

    /**
     * Get a specific schedule
     */
    public function show(int $id): SavedScheduleResource|JsonResponse
    {
        $schedule = $this->repository->getScheduleById($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Schedule not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if user owns this schedule or if it's shared
        if ($schedule->user_id !== auth()->id() && !$schedule->is_shared) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], Response::HTTP_FORBIDDEN);
        }

        return new SavedScheduleResource($schedule);
    }

    /**
     * Create a new schedule
     */
    public function store(StoreRequest $request): SavedScheduleResource
    {
        $schedule = $this->repository->createSchedule(
            userId: auth()->id(),
            term: $request->input('term'),
            year: $request->input('year'),
            name: $request->input('name'),
            sectionIds: $request->input('section_ids')
        );

        return new SavedScheduleResource($schedule);
    }

    /**
     * Update a schedule
     */
    public function update(UpdateRequest $request, int $id): SavedScheduleResource|JsonResponse
    {
        $schedule = $this->repository->getScheduleById($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Schedule not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($schedule->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], Response::HTTP_FORBIDDEN);
        }

        $schedule = $this->repository->updateSchedule(
            id: $id,
            name: $request->input('name'),
            sectionIds: $request->input('section_ids')
        );

        return new SavedScheduleResource($schedule);
    }

    /**
     * Delete a schedule
     */
    public function destroy(int $id): JsonResponse
    {
        $schedule = $this->repository->getScheduleById($id);

        if (!$schedule) {
            return response()->json([
                'message' => 'Schedule not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($schedule->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], Response::HTTP_FORBIDDEN);
        }

        $this->repository->deleteSchedule($id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Check for schedule conflicts
     */
    public function checkConflicts(array $sectionIds): JsonResponse
    {
        $conflicts = $this->repository->checkScheduleConflicts($sectionIds);

        return response()->json([
            'conflicts' => $conflicts
        ]);
    }
}
