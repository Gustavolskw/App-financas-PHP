<?php

namespace App\Application\Exception;

class InvalidParametersDataException extends ApplicationException
{
    public function __construct(
        string $message,
        array $context
    ) {
        parent::__construct($message, 422, 'INVALID_PARAMETERS_DATA', $context);
    }
}