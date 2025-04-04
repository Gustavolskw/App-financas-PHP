<?php
namespace Acc\JWT;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;


class UtilJwt
{
    public function decodeJwt($token): stdClass
    {
        $key = new Key($_ENV['JWT_SECRET'], 'HS256');
        $decoded = JWT::decode($token, $key);

        return $decoded;
    }


}