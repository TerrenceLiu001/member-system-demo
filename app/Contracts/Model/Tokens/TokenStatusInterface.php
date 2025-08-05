<?php

namespace App\Contracts\Model\Tokens;

use Illuminate\Database\Eloquent\Builder;

interface TokenStatusInterface extends TokenCapableInterface
{
    public function scopeStatus(Builder $query, string $status): Builder;
    public function isRequestDone(): bool;
}
