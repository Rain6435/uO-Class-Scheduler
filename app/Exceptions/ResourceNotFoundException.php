<?php

namespace App\Exceptions;

use Exception;

class ResourceNotFoundException extends Exception
{
    public function __construct(
        string $message = "Requested resource not found",
        int $code = 404,
        ?\Throwable $previous = null,
        private readonly ?string $resourceType = null,
        private readonly mixed $resourceId = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function getResourceId(): mixed
    {
        return $this->resourceId;
    }
}
