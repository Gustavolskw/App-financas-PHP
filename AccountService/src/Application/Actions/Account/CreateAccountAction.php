<?php

namespace App\Application\Actions\Account;

use App\Application\Actions\ActionInterface;
use App\Infrastructure\Validation\ValidationMessages;
use Dotenv\Exception\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;

class CreateAccountAction extends AccountAction implements ActionInterface
{

    public function action(): Response 
    {

        $body = $this->getFormData();
        $rules = [
            'userId' => 'required|integer',
            'userEmail' => 'required|email',
            'name' => 'required|string',
            'description' => 'string',
        ];

            $validator  = $this->validator->make($body, $rules, ValidationMessages::getMessages());

            if($validator->fails()){
              throw new ValidationException(
                $validator->errors()->all()
              );
            }
            $account = $validator->validated();

        $accountCreated = $this->accountHandler->createAccount( $account);

        $this->logger->info("Account created successfully");

        //var_dump($accountCreated);
       return $this->respondWithData([
            'message' => 'Create Account Action',
            $accountCreated->toArray()
        ]);
    }
}