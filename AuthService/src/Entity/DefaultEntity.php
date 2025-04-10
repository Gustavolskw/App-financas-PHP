<?php 

namespace Auth\Entity;

use Carbon\Carbon;


abstract class DefaultEntity {
    protected $id;
    protected $created_at;
    protected $updated_at;


    protected function  setCreatedAt(){
        $this->created_at = Carbon::now();
    }
    protected function setUpdatedAt(){
        $this->updated_at = Carbon::now();
    }

}