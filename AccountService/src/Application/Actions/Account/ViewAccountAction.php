<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\ActionInterface;
use App\Domain\Account\AccountNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;

class ViewAccountAction extends AccountAction implements ActionInterface
{

    /**
     * @throws AccountNotFoundException
     */
    public function action(): Response
    {
        $id = (int) $this->resolveArg('id');
        $account = $this->accountHandler->getAccountById($id);
        $this->logger->info("Account viewed successfully");
        return $this->respondWithData([
            'message' => 'View Account Action',
            'account' => $account->toArray()
        ]);
    }



}