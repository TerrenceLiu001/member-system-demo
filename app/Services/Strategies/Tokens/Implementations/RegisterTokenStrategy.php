<?php

namespace App\Services\Strategies\Tokens\Implementations;


use App\Models\Guest;
use App\Services\Strategies\Tokens\AbstractTokenStrategy;

class RegisterTokenStrategy extends AbstractTokenStrategy 
{
    
    protected function getModelClass(): string
    {
        return Guest::class;
    }


    public function getExpiredMessage(): string
    {
        return "驗證連結已過期，請重新註冊";
    }

}