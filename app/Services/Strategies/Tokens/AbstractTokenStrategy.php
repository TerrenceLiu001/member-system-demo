<?php

namespace App\Services\Strategies\Tokens;

use Illuminate\Support\Str; 
use App\Contracts\Model\Tokens\TokenCapableInterface;
use App\Services\Strategies\Tokens\Contracts\TokenStrategyInterface;
use Carbon\Carbon;

abstract class AbstractTokenStrategy implements TokenStrategyInterface
{
    abstract protected function getModelClass(): string;
    abstract public function getExpiredMessage(): string;

    public function resolveModel(string $token, array $scopes = []): ?TokenCapableInterface
    {
        $class = $this->getModelClass();
        return $class::token($token)->applyNamedScopes($scopes)->first();
    }

    public function isExpired(TokenCapableInterface $model): bool
    {
        return !$model->getTokenExpiresAt() || Carbon::now()->isAfter($model->getTokenExpiresAt());
    }

    public function handleExpired(TokenCapableInterface $model): void
    {
        $model->proceedTo('expired');
    }

    public function getInvalidMessage(): string
    {
        return "連結無效，請重新流程";
    }

    public function generateToken(?int $length = 64): string
    {
        $modelClass = $this->getModelClass();

        do {
            $token = Str::random($length);
            $exist = $modelClass::token($token)->exists();
        } while ($exist);

        return $token;
    }
}