<?php

namespace App\Services\Strategies\Tokens\Implementations;


use App\Models\User;
use App\Services\Strategies\Tokens\AbstractTokenStrategy;

class LoginTokenStrategy extends AbstractTokenStrategy 
{

    protected function getModelClass(): string
    {
        return User::class;
    }
    
    public function getExpiredMessage(): string
    {
        return "登入期限已過期，請重新登入";
    }

    public function getInvalidMessage(): string
    {
        return "請先登入";
    }
}