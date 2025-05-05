<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Domain\Exception\InvalidUserException;
use App\Domain\Exception\ResourceNotFoundException;
use DomainException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = 500;
        $error = new ErrorHandler(
            ErrorHandler::SERVER_ERROR,
            'An internal error has occurred while processing your request.'
        );

        
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $error->setDescription($exception->getMessage());

            if ($exception instanceof HttpNotFoundException) {
                $error->setType(ErrorHandler::RESOURCE_NOT_FOUND);
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $error->setType(ErrorHandler::NOT_ALLOWED);
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $error->setType(ErrorHandler::UNAUTHENTICATED);
            } elseif ($exception instanceof HttpForbiddenException) {
                $error->setType(ErrorHandler::INSUFFICIENT_PRIVILEGES);
            } elseif ($exception instanceof HttpBadRequestException) {
                $error->setType(ErrorHandler::BAD_REQUEST);
            } elseif ($exception instanceof HttpNotImplementedException) {
                $error->setType(ErrorHandler::NOT_IMPLEMENTED);
            }
        }

        if (
            !($exception instanceof HttpException)
            && $exception instanceof Throwable
            && $this->displayErrorDetails
        ) {

             if($exception instanceof DomainException) {
                 $error->setType("DOMAIN_EXCEPTION");
                 $error->setDescription($exception->getMessage());
                 $statusCode = 400;
             }
             else if ($exception instanceof ResourceNotFoundException){
                $statusCode = 204;
             }
             else if ($exception instanceof InvalidUserException)
             {
                 $error->setType("AUTH_USER_EXCEPTION");
                 $error->setDescription($exception->getMessage());
                 $statusCode = 400;
             }
             else {
                 $error->setDescription($exception->getMessage());
             }           
        }

        $payload = new PayloadHandler($statusCode, null, $error);
        $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
