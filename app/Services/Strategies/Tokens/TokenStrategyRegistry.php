<?php

namespace App\Services\Strategies\Tokens;

use App\Services\Strategies\Tokens\Contracts\TokenStrategyInterface;
use App\Services\Strategies\Tokens\Implementations\RegisterTokenStrategy;
use App\Services\Strategies\Tokens\Implementations\LoginTokenStrategy;
use App\Services\Strategies\Tokens\Implementations\UpdateContactTokenStrategy;
use App\Services\Strategies\Tokens\Implementations\PasswordTokenStrategy;
use Exception;

class TokenStrategyRegistry
{    
    protected array $strategies = [];

    public function __construct()
    {
        $this->strategies = 
        [
            'register'       => new RegisterTokenStrategy(),
            'login'          => new LoginTokenStrategy(),
            'update_contact' => new UpdateContactTokenStrategy(),
            'password'       => new PasswordTokenStrategy(), 
        ];
    }

    public function get(string $method): TokenStrategyInterface
    {
        if (!isset($this->strategies[$method])){
            throw new Exception("未定義的 Token Method: $method");
        };
        return $this->strategies[$method];
    }
}
