<?php

namespace App\DTOs;

readonly class CourseData
{
    public function __construct(
        public string $courseCode,
        public string $subjectCode,
        public string $title,
        public int $credits,
        public string $description,
        public array $prerequisites,
        public array $components
    ) {}

    public function toArray(): array
    {
        return [
            'course_code' => $this->courseCode,
            'subject_code' => $this->subjectCode,
            'title' => $this->title,
            'credits' => $this->credits,
            'description' => $this->description,
            'prerequisites' => $this->prerequisites,
            'components' => $this->components,
        ];
    }
}
