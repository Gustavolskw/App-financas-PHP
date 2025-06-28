<?php

namespace App\Domain\Exception;

use App\Application\Exception\ApplicationException;
use Slim\App;

class InvalidUserDataException extends ApplicationException
{

    public function __construct(
        string $message,
        array $context
    ) {
        parent::__construct($message, 422, 'INVALID_USER_DATA', $context);
    }

}