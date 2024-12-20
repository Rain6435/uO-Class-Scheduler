<?php

namespace App\Exceptions;

use Exception;

class ScheduleConflictException extends Exception
{
    public function __construct(
        string $message = "Schedule has conflicting sections",
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly array $conflicts = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getConflicts(): array
    {
        return $this->conflicts;
    }
}
