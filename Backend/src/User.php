<?php

namespace App;

class User
{

    private $id;
    private $name;
    private $nickName;
    private $email;
    private $password;

    public function __construct($name, $nickName, $email, $password)
    {
        $this->name = $name;
        $this->nickName = $nickName;
        $this->email = $email;
        $this->password = $password;
    }



}