<?php

namespace App\Application\Actions\Account;

use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

class ListAccountsAction extends AccountAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $body = $this->getFormData();

        $this->logger->debug('ListAccountsAction: ' . json_encode($body));
        //exit;



        //$accounts = $this->accountHandler->getAllAccounts();

        return $this->respondWithData($body);
    }
}