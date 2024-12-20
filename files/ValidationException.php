<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct(
        string $message = "Validation failed",
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly array $errors = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
