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
    public static function get(string $method): TokenStrategyInterface
    {
        return match ($method) {
            'register'       => new RegisterTokenStrategy(),
            'login'          => new LoginTokenStrategy(),
            'update_contact' => new UpdateContactTokenStrategy(),
            'password'       => new PasswordTokenStrategy(),
            default          => throw new Exception("未定義的 token method: $method")
        };
    }
}
