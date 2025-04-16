<?php
namespace Acc\Controllers;

use Acc\Services\AccountService;
use Acc\Translations\ValidationMessages;
use Exception;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Acc\DTO\HttpResponse;


class AccountController
{
    private $accountService;
    private $validator;

    private $resp;


    public function __construct()
    {
        $this->accountService = new AccountService();
        $loader = new ArrayLoader();
        $translator = new Translator($loader, 'en');
        $this->validator = new ValidationFactory($translator);
        $this->resp = new HttpResponse();

    }

    public function createAccount(Request $request, Response $response): void
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];
            $rules = [
                'userId' => 'sometimes|integer',
                'email' => 'required|email|max:255',
                'name' => 'required|string|min:6|max:255',
                'description' => 'required|string|min:10|max:255',
            ];

            $validation = $this->validator->make($data, $rules, ValidationMessages::getMessages());
            if ($validation->fails()) {
                $this->resp->response(['error' => $validation->errors()->all()], 422, $response);
                return;
            }

            $valData = $validation->validated();

            $authHeader = $request->header['authorization'] ?? '';
            $token = str_replace('Bearer ', '', $authHeader);
            var_dump($token);
            $userId = $valData['userId'] ?? null;
            $result = $this->accountService->createAccount($userId, $valData['email'], $valData['name'], $valData['description'], $token);

            if ($result['status'] === 401) {
                $this->resp->response(['error' => $result['message']], 401, $response);
                return;
            }
            $this->resp->response($result, 200, $response);
        } catch (Exception $e) {
            $this->resp->exceptionResponse($e, $response);
        }
    }


    public function getAccount(Request $request, Response $response, int $id)
    {
        $data = ['id' => $id];
        $rules = [
            'id' => 'required|integer',
        ];
        $validation = $this->validator->make($data, $rules, ValidationMessages::getMessages());
        if ($validation->fails()) {
            $this->resp->response(['error' => $validation->errors()->all()], 422, $response);
            return;
        }
        $valData = $validation->validated();
        try {
            $result = $this->accountService->getAccountById($valData['id']);
            $this->resp->response($result, 200, $response);
        } catch (Exception $e) {
            $this->resp->exceptionResponse($e, $response);
        }
    }

    public function getUserAccounts(Request $request, Response $response, int $id)
    {
        $data = ['id' => $id];
        $rules = [
            'id' => 'required|integer',
        ];
        $validation = $this->validator->make($data, $rules, ValidationMessages::getMessages());
        if ($validation->fails()) {
            $this->resp->response(['error' => $validation->errors()->all()], 422, $response);
            return;
        }
        $valData = $validation->validated();
        try {
            $result = $this->accountService->getUserAccounts($valData['id']);
            $this->resp->response($result, 200, $response);
        } catch (Exception $e) {
            $this->resp->exceptionResponse($e, $response);
        }
    }

    public function reactivateAccount(Request $request, Response $response, int $id)
    {
        $data = ['id' => $id];
        $rules = [
            'id' => 'required|integer',
        ];
        $validation = $this->validator->make($data, $rules, ValidationMessages::getMessages());
        if ($validation->fails()) {
            $this->resp->response(['error' => $validation->errors()->all()], 422, $response);
            return;
        }
        $valData = $validation->validated();
        try {
            $result = $this->accountService->reactivateAccount($valData['id']);
            $this->resp->response($result, 200, $response);
        } catch (Exception $e) {
            $this->resp->exceptionResponse($e, $response);
        }
    }

    public function inactivateAccount(Request $request, Response $response, int $id)
    {
        $data = ['id' => $id];
        $rules = [
            'id' => 'required|integer',
        ];
        $validation = $this->validator->make($data, $rules, ValidationMessages::getMessages());
        if ($validation->fails()) {
            $this->resp->response(['error' => $validation->errors()->all()], 422, $response);
            return;
        }
        $valData = $validation->validated();
        try {
            $result = $this->accountService->inactivateAccount($valData['id']);
            $this->resp->response($result, 200, $response);
        } catch (Exception $e) {
            $this->resp->exceptionResponse($e, $response);
        }
    }

}