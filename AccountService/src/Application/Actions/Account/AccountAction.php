<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\Action;
use App\Application\Actions\ActionInterface;
use App\Application\Handlers\AccountHandler;
use Psr\Log\LoggerInterface;


abstract class AccountAction extends Action
{
    protected AccountHandler $accountHandler;
   public function __construct(LoggerInterface $logger, AccountHandler $accountHandler)
   {
       parent::__construct($logger);
        $this->accountHandler = $accountHandler;
   }
}