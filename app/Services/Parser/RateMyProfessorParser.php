<?php

namespace App\Services\Parser;

use App\DTOs\ProfessorData;
use App\Services\Scraper\Contracts\ParserInterface;

class RateMyProfessorParser implements ParserInterface
{
    /**
     * Parse raw RateMyProfessor data into structured format
     */
    public function parse(mixed $rawData): array
    {
        if (! isset($rawData['response']['docs'])) {
            return [];
        }

        return array_map(function ($professor) {
            return new ProfessorData(
                firstName: $professor['teacherfirstname_t'] ?? '',
                lastName: $professor['teacherlastname_t'] ?? '',
                rating: $professor['averageratingscore_rf'] ?? -1,
                totalRatings: $professor['total_number_of_ratings_i'] ?? 0,
                rmpId: $professor['pk_id'] ?? null
            );
        }, $rawData['response']['docs']);
    }
}
