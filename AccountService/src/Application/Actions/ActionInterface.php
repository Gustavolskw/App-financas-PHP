<?php

namespace App\Application\Actions;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

interface ActionInterface
{
    public function action(): Response;
}