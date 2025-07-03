<?php

namespace App\Domain\Exception;

use App\Application\Exception\ApplicationException;
use Exception;

class ResourceNotFoundException extends ApplicationException
{
    public function __construct(
        string $message,
        array $context = []
    ) {
        parent::__construct($message, 400, 'RESOURCE_NOT_FOUND', $context ?? []);
    }
}
