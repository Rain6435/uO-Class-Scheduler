<?php

namespace App\Repositories\Contracts;

use App\DTOs\ImportantDateData;
use App\Models\ImportantDate;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ImportantDatesRepositoryInterface
{
    public function upsertDate(ImportantDateData $date): ImportantDate;

    public function getDatesByTerm(string $term, int $year): Collection;

    public function getDatesByCategory(string $category): Collection;

    /**
     * Get important dates with optional filtering
     *
     * @param string|null $term
     * @param int|null $year
     * @param string|null $category
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getImportantDates(
        ?string $term = null,
        ?int $year = null,
        ?string $category = null,
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Get a specific important date by ID
     *
     * @param int $id
     * @return ImportantDate|null
     */
    public function getImportantDateById(int $id): ?ImportantDate;

    /**
     * Search important dates by various criteria
     *
     * @param string|null $term
     * @param int|null $year
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchImportantDates(
        ?string $term = null,
        ?int $year = null,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Get list of unique categories
     *
     * @return array
     */
    public function getAvailableCategories(): array;
}
