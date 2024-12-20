<?php

namespace App\DTOs;

readonly class ImportantDateData
{
    public function __construct(
        public string $term,
        public int $year,
        public string $category,
        public string $description,
        public string $startDate,
        public ?string $endDate = null
    ) {}

    public function toArray(): array
    {
        return [
            'term' => $this->term,
            'year' => $this->year,
            'category' => $this->category,
            'description' => $this->description,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }
}
