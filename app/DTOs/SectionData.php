<?php

namespace App\DTOs;

readonly class SectionData
{
    public function __construct(
        public string $sectionId,
        public string $courseCode,
        public string $term,
        public int $year,
        public string $type,
        public string $status,
        public array $schedule,
        public array $professors
    ) {}

    public function toArray(): array
    {
        return [
            'section_id' => $this->sectionId,
            'course_code' => $this->courseCode,
            'term' => $this->term,
            'year' => $this->year,
            'type' => $this->type,
            'status' => $this->status,
            'schedule' => $this->schedule,
            'professors' => $this->professors,
        ];
    }
}
