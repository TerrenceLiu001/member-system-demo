<?php

namespace App\Services\Strategies\Tokens\Implementations;

use App\Models\UserContactUpdate;
use App\Services\Strategies\Tokens\AbstractTokenStrategy;

class UpdateContactTokenStrategy extends AbstractTokenStrategy 
{
    protected function getModelClass(): string
    {
        return UserContactUpdate::class;
    }
    
    public function getExpiredMessage(): string
    {
        return "變更通訊連結已過期，請重新操作";
    }
}