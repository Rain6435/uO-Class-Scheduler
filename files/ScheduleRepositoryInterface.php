<?php

namespace App\Repositories\Contracts;

use App\Models\SavedSchedule;
use Illuminate\Pagination\LengthAwarePaginator;

interface ScheduleRepositoryInterface
{
    public function findById(int $id);
    public function findByShareToken(string $token);
    public function getForUser(?int $userId, array $filters = []);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function addSection(int $scheduleId, int $sectionId, array $data = []);
    public function removeSection(int $scheduleId, int $sectionId);
    public function checkConflicts(array $sectionIds);

    /**
     * Get user's saved schedules
     *
     * @param int $userId
     * @return LengthAwarePaginator
     */
    public function getUserSchedules(int $userId): LengthAwarePaginator;

    /**
     * Get a specific schedule by ID
     *
     * @param int $id
     * @return SavedSchedule|null
     */
    public function getScheduleById(int $id): ?SavedSchedule;

    /**
     * Create a new schedule
     *
     * @param int $userId
     * @param string $term
     * @param int $year
     * @param string $name
     * @param array $sectionIds
     * @return SavedSchedule
     * @throws \App\Exceptions\ScheduleConflictException
     */
    public function createSchedule(
        int $userId,
        string $term,
        int $year,
        string $name,
        array $sectionIds
    ): SavedSchedule;

    /**
     * Update an existing schedule
     *
     * @param int $id
     * @param string|null $name
     * @param array|null $sectionIds
     * @return SavedSchedule
     * @throws \App\Exceptions\ScheduleConflictException
     */
    public function updateSchedule(
        int $id,
        ?string $name = null,
        ?array $sectionIds = null
    ): SavedSchedule;

    /**
     * Delete a schedule
     *
     * @param int $id
     * @return bool
     */
    public function deleteSchedule(int $id): bool;

    /**
     * Check for conflicts between sections
     *
     * @param array $sectionIds
     * @return array
     */
    public function checkScheduleConflicts(array $sectionIds): array;
}
