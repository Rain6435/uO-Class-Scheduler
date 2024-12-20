<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedAccessException extends Exception
{
    public function __construct(
        string $message = "Unauthorized access",
        int $code = 403,
        ?\Throwable $previous = null,
        private readonly ?string $resource = null,
        private readonly ?string $action = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }
}
