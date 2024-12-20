<?php

namespace App\Repositories;

use App\DTOs\ImportantDateData;
use App\Models\ImportantDate;
use App\Models\Term;
use App\Repositories\Contracts\ImportantDatesRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ImportantDatesRepository implements ImportantDatesRepositoryInterface
{
    public function upsertDate(ImportantDateData $date): ImportantDate
    {
        return DB::transaction(function () use ($date) {
            // Get or create term
            $term = Term::firstOrCreate([
                'term' => $date->term,
                'year' => $date->year,
            ]);

            // Create or update the date
            return ImportantDate::updateOrCreate(
                [
                    'term_id' => $term->id,
                    'category' => $date->category,
                    'description' => $date->description,
                ],
                [
                    'start_date' => $date->startDate,
                    'end_date' => $date->endDate,
                ]
            );
        });
    }

    public function getDatesByTerm(string $term, int $year): Collection
    {
        return ImportantDate::join('terms', 'terms.id', '=', 'important_dates.term_id')
            ->where('terms.term', $term)
            ->where('terms.year', $year)
            ->orderBy('start_date')
            ->select('important_dates.*')
            ->get();
    }

    public function getDatesByCategory(string $category): Collection
    {
        return ImportantDate::where('category', $category)
            ->orderBy('start_date')
            ->get();
    }

    public function getImportantDates(
        ?string $term = null,
        ?int $year = null,
        ?string $category = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = ImportantDate::query()
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->where('year', $year))
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderBy('start_date')
            ->orderBy('end_date');

        return $query->paginate($perPage);
    }

    public function getImportantDateById(int $id): ?ImportantDate
    {
        return ImportantDate::find($id);
    }

    public function searchImportantDates(
        ?string $term = null,
        ?int $year = null,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = ImportantDate::query()
            ->when($term, fn($q) => $q->where('term', $term))
            ->when($year, fn($q) => $q->where('year', $year))
            ->when(
                isset($filters['category']),
                fn($q) => $q->where('category', $filters['category'])
            )
            ->when(
                isset($filters['description']),
                fn($q) => $q->where('description', 'like', '%' . $filters['description'] . '%')
            )
            ->when(
                isset($filters['start_date']),
                fn($q) => $q->where('start_date', '>=', $filters['start_date'])
            )
            ->when(
                isset($filters['end_date']),
                fn($q) => $q->where('end_date', '<=', $filters['end_date'])
            )
            ->orderBy('start_date')
            ->orderBy('end_date');

        return $query->paginate($perPage);
    }

    public function getAvailableCategories(): array
    {
        return ImportantDate::distinct()
            ->orderBy('category')
            ->pluck('category')
            ->all();
    }
}
