<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\Action;
use App\Application\Handlers\AccountHandler;
use Psr\Log\LoggerInterface;


abstract class AccountAction extends Action
{
    protected AccountHandler $accountHandler;
   public function __construct(LoggerInterface $logger)
   {
       parent::__construct($logger);
        $this->accountHandler = new AccountHandler();
   }
}