<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\ActionInterface;
use Psr\Http\Message\ResponseInterface as Response;

class ListAccountsAction extends AccountAction implements ActionInterface
{

    /**
     * @inheritDoc
     */
    public function action(): Response
    {
        $accounts = $this->accountHandler->getAllAccounts();

        $this->logger->info('Accounts Found on List Action');

        return $this->respondWithData($accounts);
    }
}