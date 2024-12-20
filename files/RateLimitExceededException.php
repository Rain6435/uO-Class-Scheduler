<?php

namespace App\Exceptions;

use Exception;

class RateLimitExceededException extends Exception
{
    public function __construct(
        string $message = "Rate limit exceeded",
        int $code = 429,
        ?\Throwable $previous = null,
        private readonly ?int $retryAfter = null,
        private readonly ?string $service = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function getService(): ?string
    {
        return $this->service;
    }
}
