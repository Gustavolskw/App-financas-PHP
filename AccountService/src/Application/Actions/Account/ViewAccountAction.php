<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\ActionInterface;
use Psr\Http\Message\ResponseInterface as Response;

class ViewAccountAction extends AccountAction implements ActionInterface
{

    public function action(): Response 
    {
        return $this->respondWithData([
            'message' => 'View Account Action'
        ]);
    }



}