<?php

namespace Auth\Entity;

use Carbon\Carbon;


abstract class DefaultEntity
{
    protected $id;


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }


}