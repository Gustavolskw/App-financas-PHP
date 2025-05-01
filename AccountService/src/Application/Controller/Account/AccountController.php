<?php

namespace App\Application\Controller\Account;

use App\Application\Controller\Controller;
use App\Application\UseCases\Account\GetAllAccountsCase;
use App\Domain\Interfaces\AccountRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class AccountController extends Controller
{
    private AccountRepository $accountRepository;
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger, AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->logger = $logger;
        parent::__construct();
    }

    public function getAllAccounts(Request $request, Response $response): Response
    {
        $getAllAccountsCase = new GetAllAccountsCase($this->logger, $this->accountRepository);
        $accounts = $getAllAccountsCase->execute();
        return $this->respondWithData($response, $accounts);
    }
}
