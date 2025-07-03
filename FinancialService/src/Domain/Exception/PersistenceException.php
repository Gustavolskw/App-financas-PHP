<?php

namespace App\Domain\Exception;

use App\Application\Exception\ApplicationException;

class PersistenceException extends ApplicationException
{
    public function __construct(
        string $message,
        ?array $context
    ) {
        parent::__construct($message, 400, 'PERSISTENCE_EXCEPTION', $context??[]);
    }

}