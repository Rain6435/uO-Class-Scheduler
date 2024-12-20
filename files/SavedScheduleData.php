<?php

namespace App\DTOs;

readonly class ScheduleData
{
    public function __construct(
        public string $day,
        public string $startTime,
        public string $endTime,
        public string $room,
        public string $startDate,
        public string $endDate
    ) {}

    public function toArray(): array
    {
        return [
            'day' => $this->day,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'room' => $this->room,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }
}
