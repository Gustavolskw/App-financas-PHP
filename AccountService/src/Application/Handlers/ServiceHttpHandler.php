<?php

namespace App\Application\Handlers;

use DomainException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ServiceHttpHandler
{

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public
    function handleUserValidationRequest(string $url, $queryParams = [], $headers = []): bool
    {

        //"http://nginx/auth/user/verify/$userId"

        $client = new Client();
        $response = $client->request("GET", $url, [
            'query' => $queryParams,
            'timeout' => 5.0,
            'headers' => $headers,
        ],
        );
        $json = (string)$response->getBody();
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if ($data === null && $response->getStatusCode() !== 204) {
            $this->logger->error("Error decoding JSON response from $url");
            throw new DomainException("Error decoding JSON response from $url");
        }
        $this->logger->info("Response from $url: " . $data);
        return $data;
    }

}