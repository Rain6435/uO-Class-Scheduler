<?php

namespace App\DTOs;

readonly class SavedScheduleData
{
    public function __construct(
        public int $userId,
        public string $term,
        public int $year,
        public string $name,
        public array $sectionIds,
        public ?bool $isShared = false,
        public ?string $shareToken = null,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'term' => $this->term,
            'year' => $this->year,
            'name' => $this->name,
            'section_ids' => $this->sectionIds,
            'is_shared' => $this->isShared,
            'share_token' => $this->shareToken,
        ];
    }
}
