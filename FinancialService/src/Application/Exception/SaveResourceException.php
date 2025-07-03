<?php

namespace App\Application\Exception;

class SaveResourceException extends ApplicationException
{
    public function __construct(
        string $message,
        array $context
    ) {
        parent::__construct($message, 400, 'SAVE_RESOURCE_EXCEPTION', $context);
    }
}