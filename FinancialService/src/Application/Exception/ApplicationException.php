<?php

namespace App\Application\Exception;

class ApplicationException extends \Exception
{
    private string $errorCode {
        get {
            return $this->errorCode;
        }
    }
    private array $context {
        get {
            return $this->context;
        }
    }

    public function __construct(
        string $message,
        ?int $HttpCode,
        string $errorCode,
        array $context
    ) {
        parent::__construct($message, $HttpCode??400);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

}