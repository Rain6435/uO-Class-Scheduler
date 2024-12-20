<?php

namespace App\Exceptions;

use Exception;

class ParserException extends Exception
{
    public function __construct(
        string $message = "Data parsing failed",
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly ?array $context = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
