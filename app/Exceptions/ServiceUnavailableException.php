<?php

namespace App\Exceptions;

use Exception;

class ServiceUnavailableException extends Exception
{
    public function __construct(
        string $message = "External service is unavailable",
        int $code = 503,
        ?\Throwable $previous = null,
        private readonly ?string $service = null,
        private readonly ?int $retryAfter = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
