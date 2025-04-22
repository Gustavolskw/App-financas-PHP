<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\ActionInterface;
use Psr\Http\Message\ResponseInterface as Response;

class ListAccountsAction extends AccountAction implements ActionInterface
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $body = $this->getFormData();

        $this->logger->debug('ListAccountsAction: ' . json_encode($body));
        

        $accounts = $this->accountHandler->getAllAccounts();

        $this->logger->debug('Accounts Found on List Action'. json_encode($accounts));

        return $this->respondWithData($accounts);
    }
}