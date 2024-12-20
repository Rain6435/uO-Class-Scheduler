<?php

namespace App\DTOs;

readonly class ProfessorData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public float $rating,
        public int $totalRatings,
        public ?string $rmpId = null
    ) {}

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'rating' => $this->rating,
            'total_ratings' => $this->totalRatings,
            'rmp_id' => $this->rmpId,
        ];
    }
}
