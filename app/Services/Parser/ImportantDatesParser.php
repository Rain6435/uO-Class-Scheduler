<?php

namespace App\Services\Parser;

use App\DTOs\ImportantDateData;
use App\Services\Scraper\Contracts\ParserInterface;
use Carbon\Carbon;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class ImportantDatesParser implements ParserInterface
{
    /**
     * Parse raw important dates data
     */
    public function parse(mixed $rawData): array
    {
        $crawler = new Crawler($rawData['html']);
        $dates = [];

        // Find all term sections
        $crawler->filter('.uottawa-M-1-1o2 details.collapsible')->each(function (Crawler $termNode) use (&$dates) {
            // Extract term and year from summary
            $summaryText = strtolower($termNode->filter('summary')->text());
            if (! preg_match('/([a-z-]+)\s+term\s+([0-9]{4})/', $summaryText, $matches)) {
                return;
            }

            $term = $matches[1];
            $year = (int) $matches[2];

            // Process each category section
            $this->processCategories($termNode, $term, $year, $dates);
        });

        return $dates;
    }

    /**
     * Process categories within a term
     */
    private function processCategories(Crawler $termNode, string $term, int $year, array &$dates): void
    {
        // Process general dates
        $this->processDateTable(
            $termNode->filter('table')->first(),
            'general',
            $term,
            $year,
            $dates
        );

        // Process category sections
        $termNode->filter('.uoe--content > details')->each(function (Crawler $categoryNode) use ($term, $year, &$dates) {
            $category = trim($categoryNode->filter('summary')->text());

            // Process main category table
            $this->processDateTable(
                $categoryNode->filter('table')->first(),
                $category,
                $term,
                $year,
                $dates
            );

            // Process subcategories
            $categoryNode->filter('details')->each(function (Crawler $subCategoryNode) use ($term, $year, $category, &$dates) {
                $subCategory = $category.' - '.trim($subCategoryNode->filter('summary')->text());

                $this->processDateTable(
                    $subCategoryNode->filter('table')->first(),
                    $subCategory,
                    $term,
                    $year,
                    $dates
                );
            });
        });
    }

    /**
     * Process a date table
     */
    private function processDateTable(Crawler $table, string $category, string $term, int $year, array &$dates): void
    {
        if (! $table->count()) {
            return;
        }

        $headers = [];
        $table->filter('th')->each(function (Crawler $th) use (&$headers) {
            $headers[] = strtolower(trim($th->text()));
        });

        $dateIdx = array_search('dates', $headers);
        $descIdx = array_search('activity', $headers) ?? array_search('description', $headers);

        if ($dateIdx === false || $descIdx === false) {
            return;
        }

        $table->filter('tbody tr')->each(function (Crawler $row) use ($category, $term, $year, $dateIdx, $descIdx, &$dates) {
            $cells = $row->filter('td');
            if ($cells->count() <= max($dateIdx, $descIdx)) {
                return;
            }

            $dateStr = trim($cells->eq($dateIdx)->text());
            $description = trim($cells->eq($descIdx)->text());

            // Parse date range if present
            $dateRange = explode(' to ', $dateStr);
            $startDate = $this->parseDate($dateRange[0], $term, $year);
            $endDate = isset($dateRange[1]) ? $this->parseDate($dateRange[1], $term, $year) : null;

            if (! $startDate) {
                return;
            }

            $dates[] = new ImportantDateData(
                term: $term,
                year: $year,
                category: $category,
                description: $description,
                startDate: $startDate->format('Y-m-d'),
                endDate: $endDate?->format('Y-m-d')
            );
        });
    }

    /**
     * Parse a date string into a Carbon instance
     */
    private function parseDate(string $dateStr, string $term, int $year): ?Carbon
    {
        try {
            $date = Carbon::createFromFormat('F j, Y', trim($dateStr));
            if (! $date) {
                $date = Carbon::createFromFormat('F j', trim($dateStr));
                if (! $date) {
                    return null;
                }

                // Handle academic year spanning
                $date->year($year);
                if ($term === 'winter' && $date->month >= 8) {
                    $date->subYear();
                } elseif ($term !== 'winter' && $date->month <= 7) {
                    $date->addYear();
                }
            }

            return $date;
        } catch (Exception $e) {
            return null;
        }
    }
}
