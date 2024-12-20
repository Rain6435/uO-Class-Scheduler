<?php

namespace App\DTOs;

readonly class SubjectData
{
    public function __construct(
        public string $subjectCode,
        public string $name,
        public string $catalogUrl
    ) {}

    public function toArray(): array
    {
        return [
            'subject_code' => $this->subjectCode,
            'name' => $this->name,
            'catalog_url' => $this->catalogUrl,
        ];
    }
}
