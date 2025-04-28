<?php

namespace App\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;

interface ActionInterface
{
    public function action(): Response;
}
