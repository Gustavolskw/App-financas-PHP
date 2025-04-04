<?php

namespace Acc\Http;

use GuzzleHttp\Client;


class ExternalConsumer
{
    public function consumeUserValidaty(int $userId, string $email)
    {
        $client = new Client();



        $url = "http://0.0.0.0:9501/user/verify/{$userId}";


        $response = $client->request('GET', $url, [
            'query' => ['email' => $email],
            'timeout' => 5.0,
        ]);


        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        }
    }
}