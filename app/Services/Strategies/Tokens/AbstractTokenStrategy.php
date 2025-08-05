<?php

namespace App\Services\Strategies\Tokens;

use Illuminate\Support\Str; 
use App\Models\Base\BaseTokenModel;
use App\Services\Strategies\Tokens\Contracts\TokenStrategyInterface;
use Carbon\Carbon;

abstract class AbstractTokenStrategy implements TokenStrategyInterface
{
    abstract protected function getModelClass(): string;
    abstract public function getExpiredMessage(): string;

    public function resolveModel(string $token, array $scopes = []): ?BaseTokenModel
    {
        $class = $this->getModelClass();
        return $class::token($token)->applyNamedScopes($scopes)->first();
    }

    public function isExpired(BaseTokenModel $model): bool
    {
        return !$model->getTokenExpiresAt() || Carbon::now()->isAfter($model->getTokenExpiresAt());
    }

    public function handleExpired(BaseTokenModel $model): void
    {
        $model->proceedTo('expired')->save();
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