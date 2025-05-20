<?php

namespace App\Application\Controller;

use App\Application\Handlers\PayloadHandler;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidationFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

abstract class Controller
{
    protected ValidationFactory $validator;

    public function __construct()
    {
        $loader = new ArrayLoader();
        $translator = new Translator($loader, 'en');
        $this->validator = new ValidationFactory($translator);
    }

    protected function getFormData(Request $request): object|array
    {
        return $request->getParsedBody();
    }

    /**
     * @param string $name
     * @param Request $request
     * @param array $args
     * @return mixed
     */
    protected function resolveArg(string $name, Request $request, array $args): mixed
    {
        if (!isset($args[$name])) {
            throw new HttpBadRequestException($request, "Could not resolve argument `{$name}`.");
        }

        return $args[$name];
    }

    /**
     * @throws \JsonException
     */
    protected function respond(PayloadHandler $payload, Response $response): Response
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $response->getBody()->write($json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }

    /**
     * @throws \JsonException
     */
    protected function respondWithData(Response $response, $data = null, int $statusCode = 200): Response
    {
        $payload = new PayloadHandler($statusCode, $data);

        return $this->respond($payload, $response);
    }
}
