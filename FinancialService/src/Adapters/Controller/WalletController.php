<?php

namespace App\Adapters\Controller;

use Psr\Log\LoggerInterface;

class WalletController extends Controller
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger, )
    {
        $this->logger = $logger;
        parent::__construct();
    }
}