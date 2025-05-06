<?php

namespace App\Application\Controller\Account;

use App\Application\Controller\Controller;
use App\Application\UseCases\Account\CreateAccountCase;
use App\Application\UseCases\Account\GetAllAccountsCase;
use App\Application\UseCases\Account\UpdateAccountCase;
use App\Domain\Exception\InvalidUserException;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\Validation\ValidationMessages;
use Dotenv\Exception\ValidationException;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
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

    /**
     * @throws ResourceNotFoundException
     * @throws JsonException
     */
    public function getAllAccounts(Request $request, Response $response): Response
    {
        $getAllAccountsCase = new GetAllAccountsCase($this->logger, $this->accountRepository);
        $accounts = $getAllAccountsCase->execute();
        return $this->respondWithData($response, $accounts);
    }


    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws JsonException
     */
    public function createAccount(Request $request, Response $response){
        $body = $this->getFormData($request);
        $rules = [
            'userId' => 'required|integer',
            'userEmail' => 'required|email',
            'name' => 'required|string',
            'description' => 'string',
        ];

        $validator  = $this->validator->make($body, $rules, ValidationMessages::getMessages());
        if ($validator->fails()) {
            $errors = "";
            foreach ($validator->errors()->all() as $error) {
                $errors .= $error . "\n";
            }
            throw new ValidationException(
                $errors
            );
        }
        $accountValidatedData = $validator->validated();
        $createAccountCase = new CreateAccountCase($this->logger, $this->accountRepository);
        $accountDTOCreated = $createAccountCase->execute($accountValidatedData);
        return $this->respondWithData($response, $accountDTOCreated->toArray(), 201);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws JsonException|ResourceNotFoundException
     */
    public function getAccountById(Request $request, Response $response, array $args)
    {
        $accountId = $this->resolveArg("id", $request, $args);
        $rules  = [
            'id' => 'required|integer'
        ];
        $validator  = $this->validator->make($accountId, $rules, ValidationMessages::getMessages());

        if($validator->fails())
        {
            throw new ValidationException($validator->errors());
        }
        $validatedId = $validator->validated();
        $getAccountCase = new GetAllAccountsCase($this->logger, $this->accountRepository);
        $account = $getAccountCase->execute($validatedId);
        return $this->respondWithData($response, $account);
    }


    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws JsonException
     */
    public function updateAccountData(Request $request, Response $response, array $args)
    {
     $body = $this->getFormData($request);
        $accountId = $this->resolveArg("id", $request, $args);
        $body += $accountId;
        $rules  = [
            'id' => 'required|integer',
            'name' => 'string',
            'description' => 'string',
        ];
        $validator  = $this->validator->make($body, $rules, ValidationMessages::getMessages());

        if($validator->fails())
        {
            throw new ValidationException($validator->errors());
        }
        $validatedData = $validator->validated();
        $updateAccountCase = new UpdateAccountCase($this->logger, $this->accountRepository);
        $accountDTOUpdated = $updateAccountCase->execute($validatedData, $accountId );
        return $this->respondWithData($response, $accountDTOUpdated->toArray(), 200);
    }
}
