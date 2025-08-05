<?php

namespace App\Services\Strategies\Tokens\Implementations;


use App\Models\PasswordUpdate;
use App\Services\Strategies\Tokens\AbstractTokenStrategy;

class PasswordTokenStrategy extends AbstractTokenStrategy 
{

    protected function getModelClass(): string
    {
        return PasswordUpdate::class;
    }

    public function getExpiredMessage(): string
    {
        return "密碼重設連結已過期，請重新操作";
    }

}