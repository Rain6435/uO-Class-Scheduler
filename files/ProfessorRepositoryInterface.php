<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use App\DTOs\ProfessorData;
use App\Models\Professor;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProfessorRepositoryInterface
{
    public function upsertProfessor(ProfessorData $professor): Professor;

    public function getProfessorsByName(string $firstName, string $lastName): Collection;

    /**
     * Get professors with optional filtering
     *
     * @param string|null $subject
     * @param string|null $term
     * @param int|null $year
     * @param int $perPage
     * @param string $sortBy
     * @param string $sortDirection
     * @return LengthAwarePaginator
     */
    public function getProfessors(
        ?string $subject = null,
        ?string $term = null,
        ?int $year = null,
        int $perPage = 15,
        string $sortBy = 'last_name',
        string $sortDirection = 'asc'
    ): LengthAwarePaginator;

    /**
     * Get a specific professor by ID
     *
     * @param int $id
     * @return Professor|null
     */
    public function getProfessorById(int $id): ?Professor;

    /**
     * Search professors by various criteria
     *
     * @param string $query
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchProfessors(
        string $query,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Get professor's teaching history
     *
     * @param int $id
     * @return array
     */
    public function getTeachingHistory(int $id): array;
}
