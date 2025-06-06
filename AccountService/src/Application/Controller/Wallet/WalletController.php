<?php

namespace App\Application\Controller\Wallet;

use App\Application\Controller\Controller;

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