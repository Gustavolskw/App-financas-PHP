<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\Action;
use App\Application\Handlers\AccountHandlerInterface;
use Psr\Log\LoggerInterface;

abstract class AccountAction extends Action
{
    protected AccountHandlerInterface $accountHandler;
    public function __construct(LoggerInterface $logger, AccountHandlerInterface $accountHandler)
    {
        parent::__construct($logger);
        $this->accountHandler = $accountHandler;
    }
}
